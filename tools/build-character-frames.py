#!/usr/bin/env python3
"""
Erzeugt die Einzelbilder für die Cursor-folgende Kopfanimation im Hero
(htdocs/assets/images/character/). Entwicklungsrechner, nicht auf dem Server nötig.

Quelle ist ein Video, in dem der Kopf einmal im Kreis durch die acht Himmelsrichtungen
dreht. Die Bilder im Repo stammen aus
assets-src/originals/character-head-turn-20261015.m4v (1280×720, 24 fps, 21 s,
blaue Bühne, Drehung gegen den Uhrzeigersinn). Der ältere Clip
character-head-turn.mp4 bleibt daneben liegen und kann als Argument übergeben werden.

Ablauf:
  1. Hintergrundfarbe aus dem dunklen Teil des Bildrands bestimmen (ein Scheinwerfer
     im Rand verschiebt den Median sonst ins Helle).
  2. Gesicht im ersten Bild lokalisieren: zuerst über Hautfarbe, und wenn das Licht
     keine Hautfarbe übrig lässt (z. B. ein blauer Scheinwerfer) über den
     Gesichtserkenner YuNet (tools/face_detection_yunet_2023mar.onnx, OpenCV Zoo,
     Apache-2.0).
  3. Merkmalspunkte im Gesicht mit Lucas-Kanade durch das Video verfolgen; die mittlere
     Verschiebung gegenüber Bild 0 ergibt pro Bild Blickwinkel (atan2) und Auslenkung.
  4. Für 64 Zielwinkel (5,625° Schritte, 0 = oben, im Uhrzeigersinn) je ein Bild wählen.
     Dynamische Programmierung über den Kreis minimiert Winkelfehler und Sprünge in der
     Auslenkung zwischen Nachbarbildern (die Videoschleife schließt sich oben nicht exakt).
  5. Quadratischen Ausschnitt (volle Höhe, horizontal auf das Gesicht zentriert) als WebP
     schreiben: frame-00.webp … frame-63.webp, center.webp (Bild 0, Blick in die Kamera),
     dazu manifest.json (Hintergrund, Gesichtsposition, Bildgröße) für das PHP-Template.

    pip install opencv-python-headless numpy
    python3 tools/build-character-frames.py [video] [--quality 82]
"""

from __future__ import annotations

import argparse
import json
import math
from pathlib import Path

import cv2
import numpy as np

ROOT = Path(__file__).resolve().parent.parent
DEFAULT_SRC = ROOT / "assets-src" / "originals" / "character-head-turn-20261015.m4v"
OUT_DIR = ROOT / "htdocs" / "assets" / "images" / "character"
FACE_MODEL = Path(__file__).resolve().parent / "face_detection_yunet_2023mar.onnx"

FRAMES = 64
STEP_DEG = 360 / FRAMES
MIN_MAGNITUDE = 8.0        # Pixel; darunter gilt der Kopf als „neutral“, nicht als Richtung
ANGLE_TOLERANCE = 6.0      # Grad; Kandidaten je Zielwinkel
SMOOTH_WEIGHT = 1.0        # Gewicht der Auslenkungs-Stetigkeit gegenüber dem Winkelfehler

COMPASS = ["UP", "UP-RIGHT", "RIGHT", "DOWN-RIGHT", "DOWN", "DOWN-LEFT", "LEFT", "UP-LEFT"]


def wrap(deg: float) -> float:
    return (deg + 180.0) % 360.0 - 180.0


def read_video(path: Path) -> tuple[list[np.ndarray], float]:
    cap = cv2.VideoCapture(str(path))
    if not cap.isOpened():
        raise SystemExit(f"cannot open {path}")
    fps = cap.get(cv2.CAP_PROP_FPS)
    frames = []
    while True:
        ok, frame = cap.read()
        if not ok:
            break
        frames.append(frame)
    if not frames:
        raise SystemExit("no frames decoded")
    return frames, fps


def background_hex(frames: list[np.ndarray]) -> tuple[str, tuple[int, int, int]]:
    border = []
    for f in frames[::10]:
        border += [f[:30].reshape(-1, 3), f[-30:].reshape(-1, 3), f[:, :30].reshape(-1, 3), f[:, -30:].reshape(-1, 3)]
    pixels = np.concatenate(border).astype(np.float32)
    # Heller Scheinwerfer im Rand zählt nicht mit: nur die dunklere Hälfte des Rands.
    luminance = pixels @ np.array([0.114, 0.587, 0.299])
    dark = pixels[luminance <= np.percentile(luminance, 50)]
    b, g, r = (int(v) for v in np.median(dark, axis=0))
    return f"#{r:02x}{g:02x}{b:02x}", (b, g, r)


def locate_face_skin(frame: np.ndarray) -> tuple[float, float, tuple[int, int, int, int]] | None:
    ycc = cv2.cvtColor(frame, cv2.COLOR_BGR2YCrCb)
    skin = cv2.inRange(ycc, (60, 135, 85), (255, 180, 135))
    skin = cv2.morphologyEx(skin, cv2.MORPH_OPEN, np.ones((7, 7), np.uint8))
    count, _, stats, centroids = cv2.connectedComponentsWithStats(skin)
    if count < 2:
        return None
    k = 1 + int(np.argmax(stats[1:, cv2.CC_STAT_AREA]))
    if stats[k, cv2.CC_STAT_AREA] < 2000:
        return None
    x, y, w, h = (int(v) for v in stats[k, :4])
    return float(centroids[k][0]), float(centroids[k][1]), (x, y, w, h)


def locate_face_detector(frame: np.ndarray) -> tuple[float, float, tuple[int, int, int, int]]:
    if not FACE_MODEL.is_file():
        raise SystemExit(f"Gesicht nicht über Hautfarbe gefunden und Modell fehlt: {FACE_MODEL}")
    height, width = frame.shape[:2]
    detector = cv2.FaceDetectorYN.create(str(FACE_MODEL), "", (width, height), 0.5, 0.3, 5000)
    detector.setInputSize((width, height))
    _, faces = detector.detect(frame)
    if faces is None or len(faces) == 0:
        raise SystemExit("no face found")
    x, y, w, h = (int(v) for v in max(faces, key=lambda f: f[2] * f[3])[:4])
    pad = int(round(0.08 * max(w, h)))
    x0, y0 = max(0, x - pad), max(0, y - pad)
    x1, y1 = min(width, x + w + pad), min(height, y + h + pad)
    return (x0 + x1) / 2, (y0 + y1) / 2, (x0, y0, x1 - x0, y1 - y0)


def locate_face(frame: np.ndarray) -> tuple[float, float, tuple[int, int, int, int]]:
    found = locate_face_skin(frame)
    if found is not None:
        return found
    print("keine Hautfarbe erkennbar, Gesichtserkenner verwendet")
    return locate_face_detector(frame)


def track(frames: list[np.ndarray], box: tuple[int, int, int, int]) -> np.ndarray:
    """Median-Verschiebung der Gesichtsmerkmale gegenüber Bild 0, Form (n, 2)."""
    g0 = cv2.cvtColor(frames[0], cv2.COLOR_BGR2GRAY)
    x, y, w, h = box
    mask = np.zeros_like(g0)
    mask[y:y + h, x:x + w] = 255
    p0 = cv2.goodFeaturesToTrack(g0, maxCorners=150, qualityLevel=0.01, minDistance=8, mask=mask)
    if p0 is None or len(p0) < 20:
        raise SystemExit("too few trackable features in the face box")
    lk = dict(winSize=(21, 21), maxLevel=3, criteria=(cv2.TERM_CRITERIA_EPS | cv2.TERM_CRITERIA_COUNT, 30, 0.01))
    prev, pts = g0, p0.copy()
    alive = np.ones(len(p0), dtype=bool)
    disp = [np.zeros(2)]
    for frame in frames[1:]:
        g = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        p1, st, _ = cv2.calcOpticalFlowPyrLK(prev, g, pts, None, **lk)
        back, st2, _ = cv2.calcOpticalFlowPyrLK(g, prev, p1, None, **lk)
        # Vorwärts-Rückwärts-Prüfung: nur Punkte behalten, die zuverlässig verfolgt wurden
        ok = (st.ravel() == 1) & (st2.ravel() == 1) & (np.linalg.norm((back - pts).reshape(-1, 2), axis=1) < 1.0)
        alive &= ok
        if alive.sum() < 5:
            raise SystemExit("tracking lost")
        disp.append(np.median((p1 - p0).reshape(-1, 2)[alive], axis=0))
        pts, prev = p1, g
    return np.array(disp)


def select_frames(angles: np.ndarray, mags: np.ndarray) -> list[int]:
    """Ein Bild je Zielwinkel; zyklische DP gegen Auslenkungssprünge."""
    usable = np.where(mags >= MIN_MAGNITUDE)[0]
    candidates: list[list[int]] = []
    for k in range(FRAMES):
        target = wrap(-90.0 + k * STEP_DEG)
        err = np.array([abs(wrap(angles[i] - target)) for i in usable])
        close = usable[err <= ANGLE_TOLERANCE]
        if len(close) == 0:
            close = usable[np.argsort(err)[:3]]
        candidates.append([int(i) for i in close])

    def angle_cost(k: int, i: int) -> float:
        target = wrap(-90.0 + k * STEP_DEG)
        return (wrap(angles[i] - target) / (STEP_DEG / 2)) ** 2

    def jump_cost(i: int, j: int) -> float:
        return SMOOTH_WEIGHT * ((mags[i] - mags[j]) / 10.0) ** 2

    best_total, best_path = math.inf, None
    for start in candidates[0]:
        cost = {start: angle_cost(0, start)}
        back: list[dict[int, int]] = [{}]
        for k in range(1, FRAMES):
            new_cost: dict[int, float] = {}
            new_back: dict[int, int] = {}
            for j in candidates[k]:
                c, p = min(((cost[i] + jump_cost(i, j), i) for i in cost), key=lambda t: t[0])
                new_cost[j] = c + angle_cost(k, j)
                new_back[j] = p
            cost, back = new_cost, back + [new_back]
        # Kreis schließen
        end, total = min(((j, cost[j] + jump_cost(j, start)) for j in cost), key=lambda t: t[1])
        if total < best_total:
            path = [end]
            for k in range(FRAMES - 1, 0, -1):
                path.append(back[k][path[-1]])
            best_total, best_path = total, path[::-1]
    assert best_path is not None and best_path[0] in candidates[0]
    return best_path


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("video", nargs="?", default=str(DEFAULT_SRC))
    ap.add_argument("--quality", type=int, default=82)
    args = ap.parse_args()

    frames, fps = read_video(Path(args.video))
    height, width = frames[0].shape[:2]
    print(f"{len(frames)} frames, {fps:g} fps, {width}x{height}, {len(frames) / fps:.1f} s")

    bg_hex, bg_bgr = background_hex(frames)
    print(f"background {bg_hex}")

    fx, fy, box = locate_face(frames[0])
    print(f"face centre ({fx:.0f}, {fy:.0f}), skin box {box}")

    disp = track(frames, box)
    angles = np.degrees(np.arctan2(disp[:, 1], disp[:, 0]))
    mags = np.hypot(disp[:, 0], disp[:, 1])

    print("compass directions (frame with the largest excursion within ±10°):")
    compass: dict[str, int] = {}
    for n, name in enumerate(COMPASS):
        target = wrap(-90.0 + n * 45.0)
        in_sector = [i for i in range(len(frames)) if abs(wrap(angles[i] - target)) <= 10.0 and mags[i] >= MIN_MAGNITUDE]
        best = max(in_sector, key=lambda i: mags[i])
        compass[name] = best
        print(f"  {name:<10} frame {best:3d}  angle {angles[best]:7.1f}°  excursion {mags[best]:5.1f} px  t={best / fps:.2f}s")
    print(f"  {'CENTER':<10} frame   0  (reference pose, looking into the camera)")

    chosen = select_frames(angles, mags)

    # Quadratischer Ausschnitt mit voller Höhe, horizontal auf das Gesicht zentriert
    side = height
    left = int(round(min(max(fx - side / 2, 0), width - side)))
    crop = (left, 0, left + side, side)
    face_in_crop = ((fx - left) / side, fy / side)

    OUT_DIR.mkdir(parents=True, exist_ok=True)
    for old in OUT_DIR.glob("*.webp"):
        old.unlink()
    params = [cv2.IMWRITE_WEBP_QUALITY, args.quality]

    def write(name: str, index: int) -> int:
        img = frames[index][crop[1]:crop[3], crop[0]:crop[2]]
        path = OUT_DIR / name
        cv2.imwrite(str(path), img, params)
        return path.stat().st_size

    total = write("center.webp", 0)
    print("selected frames (index: video frame / angle / excursion):")
    for k, i in enumerate(chosen):
        total += write(f"frame-{k:02d}.webp", i)
        if k % 8 == 0:
            print(f"  {k:2d}: frame {i:3d}  {angles[i]:7.1f}°  {mags[i]:5.1f} px   ← {COMPASS[k // 8]}")
    print(f"wrote {FRAMES + 1} WebP files, {total / 1024:.0f} KB total, {side}x{side} px")

    manifest = {
        "frames": FRAMES,
        "width": side,
        "height": side,
        "background": bg_hex,
        "face": [round(face_in_crop[0], 4), round(face_in_crop[1], 4)],
        "source_frames": chosen,
        "compass": compass,
        "center_frame": 0,
    }
    (OUT_DIR / "manifest.json").write_text(json.dumps(manifest, indent=2) + "\n")
    print(f"manifest: face at {manifest['face']} of the crop, background {bg_hex}")


if __name__ == "__main__":
    main()
