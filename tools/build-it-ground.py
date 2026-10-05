#!/usr/bin/env python3
"""
Development-only helper for reichi.it: turns the rendered festival-ground diorama
(assets-src/originals/festival-ground-iso.jpg, generated image, plain off-white backdrop)
into the delivery files in htdocs/it/assets/images/:

  festival-ground-{960,640}.webp   – backdrop keyed out (soft alpha), cropped to the
                                     diorama, square, lossy WebP with alpha

Before keying, the stage's LED wall (a light grey rectangle in the render) is replaced by a
dark screen showing the BleedingStar logo (assets-src/originals/BS-Logo-Pfade-weiss.png),
warped into the isometric plane (LED_WALL corners, source pixels).

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

LOGO = ROOT / "assets-src" / "originals" / "BS-Logo-Pfade-weiss.png"
# LED wall corners in the source picture: top-left, top-right, bottom-right, bottom-left
LED_WALL = [(258, 236), (340, 221), (340, 266), (258, 279)]
LED_SCALE = 6  # the screen is drawn this many times larger than its on-picture size, then warped down
LED_BG = (14, 14, 18)
LED_GLOW = (255, 120, 100)  # faint warm bloom behind the logo, reichi.com's red


def perspective_coeffs(src_quad, dst_quad):
    """Coefficients for Image.transform(PERSPECTIVE): maps every output pixel (dst) to the input (src)."""
    rows = []
    for (sx, sy), (dx, dy) in zip(src_quad, dst_quad):
        rows.append([dx, dy, 1, 0, 0, 0, -sx * dx, -sx * dy])
        rows.append([0, 0, 0, dx, dy, 1, -sy * dx, -sy * dy])
    a = np.array(rows, dtype=float)
    b = np.array([c for pt in src_quad for c in pt], dtype=float)
    return np.linalg.solve(a, b).tolist()


def paint_led_wall(img: Image.Image) -> Image.Image:
    """Dark LED screen with the logo, drawn flat at LED_SCALE and warped into the quad."""
    from PIL import ImageFilter

    xs = [p[0] for p in LED_WALL]
    ys = [p[1] for p in LED_WALL]
    w = round((max(xs) - min(xs)) * LED_SCALE)
    h = round((max(ys) - min(ys)) * LED_SCALE * 0.78)  # the quad is a parallelogram; its true height is shorter than the bbox
    screen = Image.new("RGBA", (w, h), LED_BG + (255,))
    logo = Image.open(LOGO).convert("RGBA")
    lw = round(w * 0.86)
    logo = logo.resize((lw, round(logo.height * lw / logo.width)), Image.LANCZOS)
    pos = ((w - logo.width) // 2, (h - logo.height) // 2)
    glow = Image.new("RGBA", screen.size, (0, 0, 0, 0))
    tint = Image.new("RGBA", logo.size, LED_GLOW + (0,))
    tint.putalpha(logo.getchannel("A").point(lambda a: a * 0.55))
    glow.paste(tint, pos)
    glow = glow.filter(ImageFilter.GaussianBlur(w * 0.03))
    screen.alpha_composite(glow)
    screen.alpha_composite(logo, pos)
    # a few scanlines so the panel reads as LED, not as a sticker
    px = screen.load()
    for y in range(0, h, 4):
        for x in range(w):
            r, g, b, a = px[x, y]
            px[x, y] = (int(r * 0.82), int(g * 0.82), int(b * 0.82), a)

    flat = [(0, 0), (w, 0), (w, h), (0, h)]
    coeffs = perspective_coeffs(flat, LED_WALL)
    warped = screen.transform(img.size, Image.PERSPECTIVE, coeffs, Image.BICUBIC)
    # transform() fills the area outside the quad with opaque black; the mask limits the paste to the quad
    mask = Image.new("L", screen.size, 255).transform(img.size, Image.PERSPECTIVE, coeffs, Image.BICUBIC)
    warped.putalpha(mask)
    out = img.convert("RGBA")
    out.alpha_composite(warped)
    return out.convert("RGB")


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
    src = paint_led_wall(Image.open(SRC).convert("RGB"))
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
