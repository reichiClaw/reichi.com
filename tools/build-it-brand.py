#!/usr/bin/env python3
"""
Erzeugt die Markenzeichen von reichi.it (Entwicklungsrechner, nicht auf dem Server nötig):

  assets-src/brand/reichi-it-logo.svg        Wortmarke „reichi it“ mit Blitz + Sechseck-R, für helle Flächen
  assets-src/brand/reichi-it-logo-dark.svg   dieselbe Marke für dunkle Flächen
  assets-src/brand/reichi-it-logo*.png       Pixelversionen (2400 px breit, transparent)
  assets-src/brand/reichi-it-mark.svg        Sechseck mit Blitz (Abzeichen)
  htdocs/it/favicon.ico, htdocs/it/assets/images/icons/*    Favicons (Blitz auf Tinte)
  htdocs/it/assets/images/share-reichi-it.png              Social-Sharing-Bild 1200×630
  htdocs/assets/images/logos/reichi-it.png                 Karte in der Projektzeile von reichi.com
  htdocs/it/assets/images/reichi-it-wordmark.svg           Wortmarke für Kopf- und Fußzeile von reichi.it

Die Wortmarke verwendet dieselbe Schriftidee wie die Website (serifenlose Systemschrift,
fett, eng gesetzt); als Datei wird sie mit Inter Bold (SIL Open Font License) in Pfade
umgewandelt, damit sie überall gleich aussieht. Der Punkt bleibt und wird zum Knoten in
Akzentfarbe; „it“ ist eine Ligatur: i- und t-Stamm sind bis zur x-Höhe zu einem Block
verbunden, der Blitz ist als Negativform herausgeschnitten (Boolesche Pfade mit skia-pathops).
Das Sechseck-R stammt aus dem vorhandenen safari-pinned-tab.svg der alten reichi.com.
Die Website bindet htdocs/it/assets/images/reichi-it-wordmark.svg ein (brand_word_html).

    pip install fonttools cairosvg pillow skia-pathops
    python3 tools/build-it-brand.py
"""

from __future__ import annotations

import io
from pathlib import Path

import cairosvg
from fontTools.pens.boundsPen import BoundsPen
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.ttLib import TTFont
import pathops
from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
FONT = Path("/usr/share/fonts/truetype/macos/Inter-Bold.ttf")
BRAND_DIR = ROOT / "assets-src" / "brand"
IT_DIR = ROOT / "htdocs" / "it"
ICON_DIR = IT_DIR / "assets" / "images" / "icons"

PAPER = "#f5f2eb"
INK = "#15151a"
ACCENT = "#00e6c3"        # elektrisches Blaugrün – nur für Flächen und Grafik
ACCENT_INK = "#00705f"    # dunkle Variante für Text auf Papier (5.4:1)
LIGHT_INK = "#f3efe7"

# Sechseck-R aus safari-pinned-tab.svg (100×100, y nach oben → Transform im <g>); erster Pfad = Ring
MARK_RING = (
    '<path d="M290 883 c-107 -63 -202 -118 -210 -123 -12 -7 -15 -47 -16 -242 -1 -128 1 -242 3 -254 '
    '4 -18 71 -62 248 -160 11 -6 54 -31 95 -56 41 -25 83 -44 93 -42 19 3 419 231 427 244 3 5 5 119 '
    '5 255 0 206 -3 248 -15 255 -70 45 -402 231 -417 234 -10 1 -106 -49 -213 -111z m373 -90 c83 -49 '
    '157 -94 162 -101 10 -13 14 -376 5 -385 -13 -13 -321 -187 -330 -187 -15 0 -313 172 -325 188 -6 8 '
    '-10 85 -10 192 0 147 3 183 16 195 20 21 301 184 317 184 7 1 81 -38 165 -86z"/>'
)
MARK_R = (
    '<path d="M380 709 c-58 -34 -108 -66 -112 -72 -12 -19 -9 -265 3 -272 6 -4 13 -5 15 -2 3 3 6 56 '
    '6 119 0 62 4 121 9 130 9 18 178 118 199 118 14 0 180 -92 180 -100 0 -3 -40 -29 -90 -58 -64 -37 '
    '-90 -58 -90 -72 0 -14 29 -36 103 -79 96 -57 137 -73 137 -52 0 8 -37 37 -65 51 -49 25 -125 74 '
    '-125 80 0 4 37 30 83 56 111 65 118 77 64 108 -137 81 -184 106 -197 106 -8 0 -62 -27 -120 -61z"/>'
)


def _mark_group(x: float, y: float, size: float, color: str, paths: str) -> str:
    s = size / 100
    return (
        f'<g transform="translate({x:.2f} {y + size:.2f}) scale({s * 0.1:.5f} {-s * 0.1:.5f})" fill="{color}">'
        + paths + "</g>"
    )


def mark_svg(x: float, y: float, size: float, color: str) -> str:
    """Das Sechseck-R mit linker oberer Ecke (x, y) und Kantenlänge size."""
    return _mark_group(x, y, size, color, MARK_RING + MARK_R)


def hexagon_svg(x: float, y: float, size: float, color: str) -> str:
    """Nur der Sechseck-Ring (ohne R) – Rahmen für das Abzeichen der IT-Abteilung."""
    return _mark_group(x, y, size, color, MARK_RING)


# Blitz: eine Form, zwei parallele Schenkel (Steigung 1:2), waagrechte Schultern, Spitze unten.
# Kasten 52×100; die Schenkelstärke (21 Einheiten waagrecht ≈ 19 senkrecht zur Kante) entspricht
# bei Höhe 0.768 em (Grundlinie bis i-Punkt) der Stammstärke von Inter Bold (0.147 em).
# Kopf- und Fußzeile der Website laden die Wortmarke als Datei (reichi-it-wordmark.svg).
BOLT_POINTS = ((31, 0), (52, 0), (32, 40), (52, 40), (14, 100), (20, 58), (2, 58))
BOLT_D = "M31 0H52L32 40H52L14 100L20 58H2Z"
BOLT_W, BOLT_H = 52.0, 100.0

# Wortmarke: Laufweite, Abstand i-Stamm → t-Stamm in der Ligatur, Rand des Blitzlochs, Blitzbreite
WORD_TRACKING = -0.03
LIG_GAP = 0.22
LIG_MARGIN = 0.055
LIG_BOLT_XS = 0.74


def bolt_svg(x: float, y: float, height: float, fill: str) -> str:
    """Blitz mit linker oberer Ecke des Kastens (x, y) und Höhe height."""
    s = height / BOLT_H
    return f'<path transform="translate({x:.2f} {y:.2f}) scale({s:.5f})" d="{BOLT_D}" fill="{fill}"/>'


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


def _glyph(font: TTFont, ch: str, size: float, x: float, baseline: float) -> pathops.Path:
    glyph_set = font.getGlyphSet()
    path = pathops.Path()
    scale = size / font["head"].unitsPerEm
    glyph_set[font.getBestCmap()[ord(ch)]].draw(TransformPen(path.getPen(glyphSet=glyph_set), (scale, 0, 0, -scale, x, baseline)))
    return path


def _advance(font: TTFont, ch: str, size: float) -> float:
    return font["hmtx"][font.getBestCmap()[ord(ch)]][0] * size / font["head"].unitsPerEm


def _bounds(font: TTFont, ch: str, size: float) -> tuple[float, float, float, float]:
    pen = BoundsPen(font.getGlyphSet())
    font.getGlyphSet()[font.getBestCmap()[ord(ch)]].draw(pen)
    xmin, ymin, xmax, ymax = pen.bounds
    k = size / font["head"].unitsPerEm
    return xmin * k, ymin * k, xmax * k, ymax * k


def _d(path: pathops.Path) -> str:
    pen = SVGPathPen(None)
    path.draw(pen)
    return pen.getCommands()


def wordmark(font: TTFont, size: float, x: float, baseline: float) -> dict:
    """„reichi.it“: Buchstaben in Tinte, der Punkt als Knoten in Akzentfarbe, „it“ als Ligatur:
    i-Stamm und t-Stamm sind bis zur x-Höhe zu einem Block verbunden, aus dem der Blitz als
    Negativform geschnitten ist. Liefert Pfaddaten und Maße (Ursprung links auf der Grundlinie)."""
    track = WORD_TRACKING * size
    word = pathops.Path()
    pos = x
    for ch in "reichi":
        word.addPath(_glyph(font, ch, size, pos, baseline))
        pos += _advance(font, ch, size) + track
    # Punkt → perfekter Kreis gleicher Größe (Knoten)
    px0, py0, px1, py1 = _bounds(font, ".", size)
    dot = {"cx": pos + (px0 + px1) / 2, "cy": baseline - (py0 + py1) / 2, "r": (px1 - px0) / 2}
    pos += _advance(font, ".", size) + track
    # Ligatur „it“
    i_x = pos
    i_right = i_x + _bounds(font, "i", size)[2]
    t0 = _glyph(font, "t", size, 0, baseline)
    probe_y = baseline - 0.3 * size
    probe = pathops.Path()
    probe.moveTo(-size, probe_y - 1); probe.lineTo(2 * size, probe_y - 1); probe.lineTo(2 * size, probe_y + 1); probe.lineTo(-size, probe_y + 1); probe.close()
    t_stem_left = pathops.op(t0, probe, pathops.PathOp.INTERSECTION).bounds[0]
    t_x = i_right + LIG_GAP * size - t_stem_left
    lig = pathops.Path()
    lig.addPath(_glyph(font, "i", size, i_x, baseline))
    lig.addPath(_glyph(font, "t", size, t_x, baseline))
    x_height = font["OS/2"].sxHeight * size / font["head"].unitsPerEm
    block = pathops.Path()
    block.moveTo(i_right - 1, baseline); block.lineTo(t_x + t_stem_left + 1, baseline)
    block.lineTo(t_x + t_stem_left + 1, baseline - x_height); block.lineTo(i_right - 1, baseline - x_height); block.close()
    lig = pathops.op(lig, block, pathops.PathOp.UNION)
    # Blitz (Logo-Geometrie, schmaler) als Loch im Block
    margin = LIG_MARGIN * size
    top = baseline - x_height + margin
    h = x_height - 2 * margin
    k = h / BOLT_H
    w = BOLT_W * LIG_BOLT_XS * k
    cx = (i_right + t_x + t_stem_left) / 2
    hole = pathops.Path()
    for n, (bx, by) in enumerate(BOLT_POINTS):
        (hole.moveTo if n == 0 else hole.lineTo)(cx - w / 2 + bx * LIG_BOLT_XS * k, top + by * k)
    hole.close()
    lig = pathops.op(lig, hole, pathops.PathOp.DIFFERENCE)
    right = t_x + _bounds(font, "t", size)[2]
    return {"word": _d(word), "dot": dot, "lig": _d(lig), "right": right, "top": baseline - 0.768 * size, "bottom": baseline + 0.01 * size}


def wordmark_svg(wm: dict, ink: str, accent: str) -> str:
    return (
        f'<path fill="{ink}" d="{wm["word"]}"/>'
        f'<circle cx="{wm["dot"]["cx"]:.2f}" cy="{wm["dot"]["cy"]:.2f}" r="{wm["dot"]["r"]:.2f}" fill="{accent}"/>'
        f'<path fill="{ink}" d="{wm["lig"]}"/>'
    )


def build_logo(font: TTFont, ink: str, accent: str, out: Path) -> tuple[str, int, int]:
    """Lockup: Sechseck-R + Wortmarke „reichi.it“. Gibt (svg, width, height) zurück."""
    size = 100.0            # Schriftgröße
    mark = 92.0             # Kantenlänge des Zeichens
    gap = 24.0
    pad = 16.0
    baseline = pad + 84.0   # Grundlinie so, dass das x-Höhen-Zentrum auf der Mitte des Zeichens liegt
    wm = wordmark(font, size, pad + mark + gap, baseline)
    width = round(wm["right"] + pad)
    height = round(pad * 2 + mark)
    svg = (
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {width} {height}" width="{width}" height="{height}" '
        f'role="img" aria-label="reichi.it">'
        f'{mark_svg(pad, pad, mark, ink)}'
        f'{wordmark_svg(wm, ink, accent)}'
        "</svg>"
    )
    out.write_text(svg, encoding="utf-8")
    return svg, width, height


def build_header_wordmark(font: TTFont) -> None:
    """Wortmarke ohne Sechseck für Kopf- und Fußzeile der Website (das Sechseck liefert logo_mark())."""
    size = 100.0
    wm = wordmark(font, size, 0, 100.0)
    top, bottom = wm["top"], wm["bottom"]
    w = round(wm["right"] + 2)
    h = round(bottom - top)
    svg = (
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 {top:.2f} {w} {h}" width="{w}" height="{h}" '
        f'role="img" aria-label="reichi.it">{wordmark_svg(wm, INK, ACCENT_INK)}</svg>'
    )
    (IT_DIR / "assets" / "images" / "reichi-it-wordmark.svg").write_text(svg, encoding="utf-8")
    print(f"wordmark viewBox 0 {top:.2f} {w} {h}  ratio {w / h:.3f}")


def render_png(svg: str, out: Path, width: int | None = None, background: str | None = None) -> None:
    kwargs = {}
    if width is not None:
        kwargs["output_width"] = width
    if background is not None:
        kwargs["background_color"] = background
    cairosvg.svg2png(bytestring=svg.encode("utf-8"), write_to=str(out), **kwargs)


def build_icons() -> None:
    ICON_DIR.mkdir(parents=True, exist_ok=True)
    # Elektrischer Blitz auf Tinte – unterscheidet den Tab klar von reichi.com (weißes R auf Schwarz)
    icon_svg = (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">'
        f'<rect width="100" height="100" fill="{INK}"/>'
        f'{bolt_svg(50 - BOLT_W * 0.72 / 2, 14, 72, ACCENT)}'
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
        f'{bolt_svg(24, 0, 100, "#000000")}'
        "</svg>"
    )
    (ICON_DIR / "safari-pinned-tab.svg").write_text(pinned, encoding="utf-8")


def build_share_image(font: TTFont) -> None:
    """1200×630: Wortmarke groß, darunter eine Zeile, auf Papier mit Rasterpunkten."""
    w, h = 1200, 630
    logo_svg, lw, lh = build_logo(font, INK, ACCENT_INK, BRAND_DIR / "reichi-it-logo.svg")
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

    # hell: Tinte + Blitz in dunklem Blaugrün (5.4:1 auf Papier); dunkel: helle Tinte + elektrischer Blitz
    light, _, _ = build_logo(font, INK, ACCENT_INK, BRAND_DIR / "reichi-it-logo.svg")
    dark, _, _ = build_logo(font, LIGHT_INK, ACCENT, BRAND_DIR / "reichi-it-logo-dark.svg")
    render_png(light, BRAND_DIR / "reichi-it-logo.png", width=2400)
    render_png(dark, BRAND_DIR / "reichi-it-logo-dark.png", width=2400)
    # Karte in der Projektzeile von reichi.com (dunkler Hintergrund)
    logos_dir = ROOT / "htdocs" / "assets" / "images" / "logos"
    logos_dir.mkdir(parents=True, exist_ok=True)
    render_png(dark, logos_dir / "reichi-it.png", width=760)
    # Abzeichen der IT-Abteilung: dasselbe Sechseck wie reichi.com, innen der Blitz statt des R
    mark_only = (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" role="img" aria-label="reichi.it">'
        f'{hexagon_svg(0, 0, 100, INK)}'
        f'{bolt_svg(50 - BOLT_W * 0.46 / 2, 27, 46, ACCENT)}</svg>'
    )
    (BRAND_DIR / "reichi-it-mark.svg").write_text(mark_only, encoding="utf-8")

    build_icons()
    build_share_image(font)
    build_header_wordmark(font)
    for p in sorted(list(BRAND_DIR.iterdir()) + list(ICON_DIR.iterdir())
                    + [IT_DIR / "favicon.ico", IT_DIR / "assets" / "images" / "share-reichi-it.png", IT_DIR / "assets" / "images" / "reichi-it-wordmark.svg", logos_dir / "reichi-it.png"]):
        print(f"{p.relative_to(ROOT)}  {p.stat().st_size} bytes")


if __name__ == "__main__":
    main()
