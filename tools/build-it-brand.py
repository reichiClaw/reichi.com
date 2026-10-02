#!/usr/bin/env python3
"""
Erzeugt die Markenzeichen von reichi.it (Entwicklungsrechner, nicht auf dem Server nötig):

  assets-src/brand/reichi-it-logo.svg        Wortmarke „reichi.it“ + Sechseck-R, für helle Flächen
  assets-src/brand/reichi-it-logo-dark.svg   dieselbe Marke für dunkle Flächen
  assets-src/brand/reichi-it-logo*.png       Pixelversionen (2400 px breit, transparent)
  assets-src/brand/reichi-it-mark.svg        nur das Sechseck-R in Akzentfarbe
  htdocs/it/favicon.ico, htdocs/it/assets/images/icons/*    Favicons (Marke auf Papier)
  htdocs/it/assets/images/share-reichi-it.png              Social-Sharing-Bild 1200×630

Die Wortmarke verwendet dieselbe Schriftidee wie die Website (serifenlose Systemschrift,
fett, eng gesetzt); als Datei wird sie mit Inter Bold (SIL Open Font License) in Pfade
umgewandelt, damit sie überall gleich aussieht. Das Sechseck-R stammt aus dem vorhandenen
safari-pinned-tab.svg der alten reichi.com.

    pip install fonttools cairosvg pillow
    python3 tools/build-it-brand.py
"""

from __future__ import annotations

import io
from pathlib import Path

import cairosvg
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.ttLib import TTFont
from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
FONT = Path("/usr/share/fonts/truetype/macos/Inter-Bold.ttf")
BRAND_DIR = ROOT / "assets-src" / "brand"
IT_DIR = ROOT / "htdocs" / "it"
ICON_DIR = IT_DIR / "assets" / "images" / "icons"

PAPER = "#f5f2eb"
INK = "#15151a"
ACCENT = "#0b6fb3"
LIGHT_INK = "#f3efe7"

# Sechseck-R aus safari-pinned-tab.svg (100×100, y nach oben → Transform im <g>)
MARK_PATHS = (
    '<path d="M290 883 c-107 -63 -202 -118 -210 -123 -12 -7 -15 -47 -16 -242 -1 -128 1 -242 3 -254 '
    '4 -18 71 -62 248 -160 11 -6 54 -31 95 -56 41 -25 83 -44 93 -42 19 3 419 231 427 244 3 5 5 119 '
    '5 255 0 206 -3 248 -15 255 -70 45 -402 231 -417 234 -10 1 -106 -49 -213 -111z m373 -90 c83 -49 '
    '157 -94 162 -101 10 -13 14 -376 5 -385 -13 -13 -321 -187 -330 -187 -15 0 -313 172 -325 188 -6 8 '
    '-10 85 -10 192 0 147 3 183 16 195 20 21 301 184 317 184 7 1 81 -38 165 -86z"/>'
    '<path d="M380 709 c-58 -34 -108 -66 -112 -72 -12 -19 -9 -265 3 -272 6 -4 13 -5 15 -2 3 3 6 56 '
    '6 119 0 62 4 121 9 130 9 18 178 118 199 118 14 0 180 -92 180 -100 0 -3 -40 -29 -90 -58 -64 -37 '
    '-90 -58 -90 -72 0 -14 29 -36 103 -79 96 -57 137 -73 137 -52 0 8 -37 37 -65 51 -49 25 -125 74 '
    '-125 80 0 4 37 30 83 56 111 65 118 77 64 108 -137 81 -184 106 -197 106 -8 0 -62 -27 -120 -61z"/>'
)


def mark_svg(x: float, y: float, size: float, color: str) -> str:
    """Das Sechseck-R mit linker oberer Ecke (x, y) und Kantenlänge size."""
    s = size / 100
    return (
        f'<g transform="translate({x:.2f} {y + size:.2f}) scale({s * 0.1:.5f} {-s * 0.1:.5f})" fill="{color}">'
        + MARK_PATHS + "</g>"
    )


def text_paths(font: TTFont, text: str, size: float, x: float, baseline: float, tracking_em: float) -> tuple[str, float]:
    """Wandelt text in SVG-Pfaddaten um (Ursprung links auf der Grundlinie). Liefert (d, Breite)."""
    glyph_set = font.getGlyphSet()
    cmap = font.getBestCmap()
    upem = font["head"].unitsPerEm
    scale = size / upem
    hmtx = font["hmtx"]
    kern = font["GPOS"] if "GPOS" in font else None  # Kerning wird hier bewusst nicht ausgewertet
    del kern
    pen = SVGPathPen(glyph_set)
    pos = 0.0
    for i, ch in enumerate(text):
        name = cmap[ord(ch)]
        tpen = TransformPen(pen, (scale, 0, 0, -scale, x + pos, baseline))
        glyph_set[name].draw(tpen)
        pos += hmtx[name][0] * scale
        if i < len(text) - 1:
            pos += tracking_em * size
    return pen.getCommands(), pos


def build_logo(font: TTFont, ink: str, accent: str, out: Path) -> tuple[str, int, int]:
    """Wortmarke: Mark + „reichi“ + „.it“. Gibt (svg, width, height) zurück."""
    size = 100.0            # Schriftgröße
    mark = 92.0             # Kantenlänge des Zeichens
    gap = 24.0
    pad = 16.0
    baseline = pad + 84.0   # Grundlinie so, dass das x-Höhen-Zentrum auf der Mitte des Zeichens liegt
    x = pad + mark + gap
    d_word, w_word = text_paths(font, "reichi", size, x, baseline, -0.03)
    d_it, w_it = text_paths(font, ".it", size, x + w_word - 0.01 * size, baseline, -0.03)
    width = round(x + w_word + w_it + pad)
    height = round(pad * 2 + mark)
    mark_y = pad
    svg = (
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {width} {height}" width="{width}" height="{height}" '
        f'role="img" aria-label="reichi.it">'
        f'{mark_svg(pad, mark_y, mark, ink)}'
        f'<path fill="{ink}" d="{d_word}"/>'
        f'<path fill="{accent}" d="{d_it}"/>'
        "</svg>"
    )
    out.write_text(svg, encoding="utf-8")
    return svg, width, height


def render_png(svg: str, out: Path, width: int | None = None, background: str | None = None) -> None:
    kwargs = {}
    if width is not None:
        kwargs["output_width"] = width
    if background is not None:
        kwargs["background_color"] = background
    cairosvg.svg2png(bytestring=svg.encode("utf-8"), write_to=str(out), **kwargs)


def build_icons() -> None:
    ICON_DIR.mkdir(parents=True, exist_ok=True)
    # Marke in Akzentfarbe auf Papier – unterscheidet den Tab klar von reichi.com (weiß auf schwarz)
    icon_svg = (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">'
        f'<rect width="100" height="100" fill="{PAPER}"/>'
        f'{mark_svg(13, 13, 74, ACCENT)}'
        "</svg>"
    )
    sizes = {
        "favicon-16x16.png": 16, "favicon-32x32.png": 32, "android-chrome-96x96.png": 96,
        "apple-touch-icon.png": 180, "icon-192.png": 192, "icon-512.png": 512,
    }
    for name, px in sizes.items():
        render_png(icon_svg, ICON_DIR / name, width=px)
    # favicon.ico mit 16/32/48 px
    frames = []
    for px in (48, 32, 16):
        buf = io.BytesIO()
        cairosvg.svg2png(bytestring=icon_svg.encode("utf-8"), write_to=buf, output_width=px)
        frames.append(Image.open(io.BytesIO(buf.getvalue())).convert("RGBA"))
    frames[0].save(IT_DIR / "favicon.ico", format="ICO", sizes=[(48, 48), (32, 32), (16, 16)], append_images=frames[1:])
    # Safari-Pinned-Tab: einfarbige Silhouette (Farbe setzt der Browser über das color-Attribut)
    pinned = (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">'
        f'{mark_svg(0, 0, 100, "#000000")}'
        "</svg>"
    )
    (ICON_DIR / "safari-pinned-tab.svg").write_text(pinned, encoding="utf-8")


def build_share_image(font: TTFont) -> None:
    """1200×630: Wortmarke groß, darunter eine Zeile, auf Papier mit Rasterpunkten."""
    w, h = 1200, 630
    logo_svg, lw, lh = build_logo(font, INK, ACCENT, BRAND_DIR / "reichi-it-logo.svg")
    scale = 760 / lw
    inner = logo_svg[logo_svg.index(">") + 1 : logo_svg.rindex("</svg>")]
    sub_d, sub_w = text_paths(font, "Event-IT: Netzwerk, WLAN & Support", 40, 0, 0, -0.01)
    dots = "".join(
        f'<circle cx="{x}" cy="{y}" r="1.4" fill="rgba(21,21,26,0.16)"/>'
        for x in range(20, w, 40) for y in range(20, h, 40)
    )
    lx = (w - lw * scale) / 2
    ly = 200
    sub_x = (w - sub_w) / 2
    svg = (
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w} {h}" width="{w}" height="{h}">'
        f'<rect width="{w}" height="{h}" fill="{PAPER}"/>{dots}'
        f'<g transform="translate({lx:.2f} {ly}) scale({scale:.5f})">{inner}</g>'
        f'<path transform="translate({sub_x:.2f} 450)" fill="{INK}" fill-opacity="0.72" d="{sub_d}"/>'
        f'<rect x="{w / 2 - 36}" y="486" width="72" height="4" fill="{ACCENT}"/>'
        "</svg>"
    )
    render_png(svg, IT_DIR / "assets" / "images" / "share-reichi-it.png")


def main() -> None:
    if not FONT.is_file():
        raise SystemExit(f"Schrift nicht gefunden: {FONT}")
    font = TTFont(str(FONT))
    BRAND_DIR.mkdir(parents=True, exist_ok=True)

    light, _, _ = build_logo(font, INK, ACCENT, BRAND_DIR / "reichi-it-logo.svg")
    dark, _, _ = build_logo(font, LIGHT_INK, "#3fa0e0", BRAND_DIR / "reichi-it-logo-dark.svg")
    render_png(light, BRAND_DIR / "reichi-it-logo.png", width=2400)
    render_png(dark, BRAND_DIR / "reichi-it-logo-dark.png", width=2400)
    mark_only = (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" role="img" aria-label="reichi">'
        f'{mark_svg(0, 0, 100, ACCENT)}</svg>'
    )
    (BRAND_DIR / "reichi-it-mark.svg").write_text(mark_only, encoding="utf-8")

    build_icons()
    build_share_image(font)
    for p in sorted(list(BRAND_DIR.iterdir()) + list(ICON_DIR.iterdir()) + [IT_DIR / "favicon.ico", IT_DIR / "assets" / "images" / "share-reichi-it.png"]):
        print(f"{p.relative_to(ROOT)}  {p.stat().st_size} bytes")


if __name__ == "__main__":
    main()
