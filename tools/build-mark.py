#!/usr/bin/env python3
"""
Erzeugt die Icons von reichi.com aus dem Sechseck-R mit scharfen Kanten (tools/mark.py,
Vorlage assets-src/originals/reichi-mark.svg). Entwicklungsrechner, nicht auf dem Server nötig:

  htdocs/assets/images/icons/safari-pinned-tab.svg   Silhouette, Farbe setzt der Browser (color-Attribut)
  htdocs/favicon.ico (48/32/16), icons/favicon-16x16.png, favicon-32x32.png,
  icons/android-chrome-96x96.png                     Zeichen in Tinte auf transparent, volle Höhe
                                                      (wie die alten Favicons der WordPress-Seite)
  icons/apple-touch-icon.png (180), icon-192.png, icon-512.png
                                                      weißes Zeichen auf #0d0d10 (theme_color), ⅔ Höhe

Farben bleiben wie bisher: reichi.com weiß/Tinte, reichi.it Blitz in Blaugrün (build-it-brand.py),
rstream.at Violett (build-rstream-brand.py) – nur die Geometrie des Zeichens ist neu.

    pip install cairosvg pillow
    python3 tools/build-mark.py
"""

from __future__ import annotations

import io
from pathlib import Path

import cairosvg
from PIL import Image

from mark import mark_svg

ROOT = Path(__file__).resolve().parent.parent
SITE_DIR = ROOT / "htdocs"
ICON_DIR = SITE_DIR / "assets" / "images" / "icons"

INK = "#171819"          # Tinte der alten Favicons
THEME = "#0d0d10"        # theme_color / Hintergrund der Website
WHITE = "#ffffff"


def svg_doc(body: str) -> str:
    return (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">'
        f"{body}</svg>"
    )


def render_png(svg: str, out: Path, width: int) -> None:
    cairosvg.svg2png(bytestring=svg.encode("utf-8"), write_to=str(out), output_width=width)


def main() -> None:
    ICON_DIR.mkdir(parents=True, exist_ok=True)

    pinned = svg_doc(mark_svg(0, 0, 100, "#000000"))
    (ICON_DIR / "safari-pinned-tab.svg").write_text(pinned, encoding="utf-8")

    small = svg_doc(mark_svg(0, 0, 100, INK))
    for name, px in {"favicon-16x16.png": 16, "favicon-32x32.png": 32, "android-chrome-96x96.png": 96}.items():
        render_png(small, ICON_DIR / name, px)
    frames = []
    for px in (48, 32, 16):
        buf = io.BytesIO()
        cairosvg.svg2png(bytestring=small.encode("utf-8"), write_to=buf, output_width=px)
        frames.append(Image.open(io.BytesIO(buf.getvalue())).convert("RGBA"))
    frames[0].save(SITE_DIR / "favicon.ico", format="ICO", sizes=[(48, 48), (32, 32), (16, 16)], append_images=frames[1:])

    # Opake Kacheln: Zeichen 67 hoch, mittig
    big = svg_doc(f'<rect width="100" height="100" fill="{THEME}"/>{mark_svg(16.5, 16.5, 67, WHITE)}')
    for name, px in {"apple-touch-icon.png": 180, "icon-192.png": 192, "icon-512.png": 512}.items():
        render_png(big, ICON_DIR / name, px)

    for p in sorted(ICON_DIR.iterdir()) + [SITE_DIR / "favicon.ico"]:
        print(f"{p.relative_to(ROOT)}  {p.stat().st_size} bytes")


if __name__ == "__main__":
    main()
