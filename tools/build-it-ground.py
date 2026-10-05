#!/usr/bin/env python3
"""
Development-only helper for reichi.it: turns the rendered festival-ground diorama
(assets-src/originals/festival-ground-iso.jpg, generated image, plain off-white backdrop)
into the delivery files in htdocs/it/assets/images/:

  festival-ground-{960,640}.webp   – backdrop keyed out (soft alpha), cropped to the
                                     diorama, square, lossy WebP with alpha

The hero (templates/it/hero.php) lays the network plan over this picture; the node
coordinates there refer to the 1000×1000 box of the *cropped* image, so re-run the
template check after changing CROP_PAD or the source picture.

Requires Python 3 + Pillow + NumPy. Not needed to run the website – the output is committed.

Usage:  python3 tools/build-it-ground.py
"""
from collections import deque
from pathlib import Path

import numpy as np
from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "assets-src" / "originals" / "festival-ground-iso.jpg"
OUT = ROOT / "htdocs" / "it" / "assets" / "images"

WIDTHS = [960, 640]
WEBP_QUALITY = 82
BACKDROP = np.array([248, 242, 234])  # the render's off-white; it drifts by ~±6 per channel
KEY_LOW, KEY_HIGH = 10, 38  # colour distance → alpha 0 … 1 (soft edge keeps the ground shadow)
REGION = 48  # pixels closer than this to the backdrop may belong to it (flood fill from the border)
CROP_PAD = 0.02  # free space around the diorama, fraction of its size


def key_backdrop(rgb: np.ndarray) -> np.ndarray:
    """Alpha channel 0…1: backdrop pixels connected to the border become transparent.
    White tents and containers inside the scene stay opaque because the fill cannot reach them."""
    h, w, _ = rgb.shape
    dist = np.abs(rgb.astype(int) - BACKDROP).max(axis=2)
    candidate = dist < REGION
    seen = np.zeros((h, w), bool)
    queue = deque()
    for x in range(w):
        for y in (0, h - 1):
            if candidate[y, x]:
                seen[y, x] = True
                queue.append((y, x))
    for y in range(h):
        for x in (0, w - 1):
            if candidate[y, x] and not seen[y, x]:
                seen[y, x] = True
                queue.append((y, x))
    while queue:
        y, x = queue.popleft()
        for ny, nx in ((y + 1, x), (y - 1, x), (y, x + 1), (y, x - 1)):
            if 0 <= ny < h and 0 <= nx < w and candidate[ny, nx] and not seen[ny, nx]:
                seen[ny, nx] = True
                queue.append((ny, nx))
    soft = np.clip((dist - KEY_LOW) / (KEY_HIGH - KEY_LOW), 0, 1)
    return np.where(seen, soft, 1.0)


def crop_square(img: Image.Image) -> Image.Image:
    alpha = np.asarray(img)[:, :, 3]
    ys, xs = np.where(alpha > 60)
    x0, x1, y0, y1 = xs.min(), xs.max() + 1, ys.min(), ys.max() + 1
    side = max(x1 - x0, y1 - y0)
    pad = round(side * CROP_PAD)
    side += 2 * pad
    cx, cy = (x0 + x1) / 2, (y0 + y1) / 2
    left, top = round(cx - side / 2), round(cy - side / 2)
    # the crop may run past the picture; those parts are transparent anyway
    canvas = Image.new("RGBA", (side, side), (0, 0, 0, 0))
    canvas.paste(img, (-left, -top))
    return canvas


def main() -> None:
    src = Image.open(SRC).convert("RGB")
    rgb = np.asarray(src)
    alpha = key_backdrop(rgb)
    rgba = np.dstack([rgb, np.round(alpha * 255)]).astype(np.uint8)
    img = crop_square(Image.fromarray(rgba, "RGBA"))
    print("source", src.size, "→ cropped", img.size)
    OUT.mkdir(parents=True, exist_ok=True)
    for w in WIDTHS:
        if w > img.width:
            print("skip", w, "(would upscale)")
            continue
        out = img if w == img.width else img.resize((w, w), Image.LANCZOS)
        path = OUT / f"festival-ground-{w}.webp"
        out.save(path, "WEBP", quality=WEBP_QUALITY, method=6)
        print(path.relative_to(ROOT), f"{path.stat().st_size // 1024} KB")


if __name__ == "__main__":
    main()
