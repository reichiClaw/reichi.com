#!/usr/bin/env python3
"""
Development-only helper: generates the delivery image variants in
htdocs/assets/images/ from the untouched originals in assets-src/originals/.

Requires Python 3 + Pillow. It is NOT needed to deploy or run the website;
the generated files are committed. Re-run it only when originals change or
when you add a new photograph (see README.md, "Fotos austauschen").

Usage:  python3 tools/build-images.py
"""
from pathlib import Path
from PIL import Image, ImageOps

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "assets-src" / "originals"
OUT = ROOT / "htdocs" / "assets" / "images"

JPEG_QUALITY = 80
WEBP_QUALITY = 78

# name -> (source file, [widths], optional crop definition)
# crop: dict(ratio=(w, h), cx=0..1 horizontal focus, cy=0..1 vertical focus, widths=[...])
PHOTOS = {
    "portrait-hood": dict(src="DSC6295_square.jpg", widths=[480, 600, 800, 1200]),
    "stage-red": dict(
        src="11059538_883376491737597_7864039594443423760_o-1.jpg",
        widths=[640, 1024, 1600, 2048],
        crop=dict(ratio=(4, 5), cx=0.5, cy=0.45, widths=[540, 900]),
    ),
    "crowd-club": dict(src="DSC8135.jpg", widths=[480, 900]),
    "stage-blue": dict(src="MG_6693.jpg", widths=[480, 900]),
    "max-the-sax-prag-1": dict(src="mtsprag1.jpg", widths=[480, 800, 1200]),
    "max-the-sax-prag-2": dict(src="mtsprag0.jpg", widths=[480, 800, 1200]),
    "max-the-sax-prag-3": dict(src="maxthesax.jpg", widths=[480, 800, 1200]),
    "portrait-studio": dict(
        src="ChristianReichingerFrontpage.jpg",
        widths=[800, 1200, 1600],
        crop=dict(ratio=(4, 5), cx=0.67, cy=0.5, widths=[480, 800, 1000]),
    ),
}

# Logos: keep format (PNG transparency / JPEG), downscale only, 1x + 2x.
LOGOS = {
    "supervision": dict(src="supervision_front.png", widths=[400, 800]),
    "max-the-sax": dict(src="Max_logo_positiv_BG.png", widths=[400, 800]),
    "soulsanity": dict(src="SoulSanity-Logo.png", widths=[]),
    "summarock": dict(src="summarock.jpg", widths=[400, 800]),
    "sonic-vt": dict(src="sonic-vt.png", widths=[]),
    "scorpios": dict(src="17634736_1219831068139114_6404694967964005005_n-2.jpg", widths=[]),
    "i-tuepfe-rider": dict(src="ituepferider.png", widths=[400, 800]),
    "stadlmusi": dict(src="Stadlmusi.jpg", widths=[300, 600]),
    "hoamspue": dict(src="HoamspueLogo.jpg", widths=[400, 800]),
    "freundlich-kompetent": dict(src="fk-2.png", widths=[]),
    "his-name-is-sandusky": dict(src="50446397_2086117541453459_9173580442337542144_n.jpg", widths=[]),
    "prague-metronome-festival": dict(src="Prague-Metronome-Festival.png", widths=[]),
    "bleedingstar": dict(src="BS-Logo-Pfade-weiss.png", widths=[]),
    "rstream": dict(src="rstream.png", widths=[]),
}


def save_photo(img: Image.Image, stem: str, width: int) -> None:
    if img.width < width:
        return  # never upscale
    h = round(img.height * width / img.width)
    out = img.resize((width, h), Image.LANCZOS)
    out.save(OUT / "photos" / f"{stem}-{width}.jpg", "JPEG", quality=JPEG_QUALITY, optimize=True, progressive=True)
    out.save(OUT / "photos" / f"{stem}-{width}.webp", "WEBP", quality=WEBP_QUALITY, method=6)


def crop_to_ratio(img: Image.Image, ratio, cx: float, cy: float) -> Image.Image:
    rw, rh = ratio
    target = rw / rh
    if img.width / img.height > target:
        new_w = round(img.height * target)
        x0 = min(max(round(img.width * cx - new_w / 2), 0), img.width - new_w)
        return img.crop((x0, 0, x0 + new_w, img.height))
    new_h = round(img.width / target)
    y0 = min(max(round(img.height * cy - new_h / 2), 0), img.height - new_h)
    return img.crop((0, y0, img.width, y0 + new_h))


def build_photos() -> None:
    for stem, spec in PHOTOS.items():
        img = ImageOps.exif_transpose(Image.open(SRC / spec["src"])).convert("RGB")
        for w in spec["widths"]:
            save_photo(img, stem, w)
        if "crop" in spec:
            c = spec["crop"]
            cropped = crop_to_ratio(img, c["ratio"], c["cx"], c["cy"])
            for w in c["widths"]:
                save_photo(cropped, f"{stem}-portrait", w)
        print("photo", stem, img.size)


def build_logos() -> None:
    for stem, spec in LOGOS.items():
        src = SRC / spec["src"]
        img = Image.open(src)
        ext = ".png" if img.mode in ("RGBA", "P", "LA") or src.suffix.lower() == ".png" else ".jpg"
        widths = [w for w in spec["widths"] if w < img.width] or [img.width]
        for w in widths:
            out = img if w == img.width else img.resize((w, round(img.height * w / img.width)), Image.LANCZOS)
            suffix = "" if len(widths) == 1 else f"-{w}"
            path = OUT / "logos" / f"{stem}{suffix}{ext}"
            if ext == ".png":
                out.convert("RGBA").save(path, "PNG", optimize=True)
            else:
                out.convert("RGB").save(path, "JPEG", quality=85, optimize=True, progressive=True)
        print("logo", stem, img.size, widths)


if __name__ == "__main__":
    (OUT / "photos").mkdir(parents=True, exist_ok=True)
    (OUT / "logos").mkdir(parents=True, exist_ok=True)
    build_photos()
    build_logos()
