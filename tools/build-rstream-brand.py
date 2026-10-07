#!/usr/bin/env python3
"""
Erzeugt die Markendateien von rstream.at aus dem vorhandenen R-Stream-Logo
(Entwicklungsrechner, nicht auf dem Server nötig):

  assets-src/brand/rstream-logo.svg            Sechseck-R + „STREAM“, Tinte, für helle Flächen
  assets-src/brand/rstream-logo-dark.svg       dieselbe Marke hell, für dunkle Flächen
  assets-src/brand/rstream-logo*.png           Pixelversionen (2400 px breit, transparent)
  htdocs/rstream/assets/images/rstream-mark.svg       Sechseck-R allein (Kopf- und Fußzeile, brand_mark)
  htdocs/rstream/assets/images/rstream-wordmark.svg   „STREAM“ allein (Kopf- und Fußzeile, brand_wordmark)
  htdocs/rstream/favicon.ico, htdocs/rstream/assets/images/icons/*   Favicons (Sechseck-R in Violett auf Tinte)
  htdocs/rstream/assets/images/share-rstream.png      Social-Sharing-Bild 1200×630
  htdocs/assets/images/logos/rstream.png              Karte in der Projektzeile von reichi.com
                                                      (Sechseck violett, „STREAM“ hell, 760 px)
  htdocs/assets/images/logos/rstream-paper.png        dieselbe Karte für reichi.it (Sechseck violett,
                                                      „STREAM“ dunkel); Kopien über tools/sync-site-assets.sh

Quelle ist assets-src/originals/rstream.png (744×182, schwarz auf transparent) – das Logo,
das schon die alte rstream.at und die Projektzeile von reichi.com verwendet haben. Es wird mit
potrace (reine Python-Portierung „potracer“) in Pfade gewandelt, vierfach hochgerechnet
für glatte Kurven; die Abweichung zur Pixelvorlage liegt unter 0,2 % der Fläche. Das
Sechseck ist der linke Teil (x < 170), „STREAM“ der rechte. Die Unterzeile des Sharing-Bilds
ist Inter Bold (SIL Open Font License) als Pfade.

    pip install potracer fonttools cairosvg pillow numpy
    python3 tools/build-rstream-brand.py
"""

from __future__ import annotations

import io
from pathlib import Path

import cairosvg
import numpy as np
import potrace
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.ttLib import TTFont
from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "assets-src" / "originals" / "rstream.png"
FONT = Path("/usr/share/fonts/truetype/macos/Inter-Bold.ttf")
BRAND_DIR = ROOT / "assets-src" / "brand"
SITE_DIR = ROOT / "htdocs" / "rstream"
IMG_DIR = SITE_DIR / "assets" / "images"
ICON_DIR = IMG_DIR / "icons"

INK = "#0b0b10"          # Hintergrund der Website (rstream.css --bg)
LIGHT_INK = "#f3efe7"    # helle Tinte (style.css --ink)
DARK_INK = "#15151a"     # Tinte für helle Flächen
ACCENT = "#b14dff"       # Violett (rstream.css --accent)
MARK_SPLIT = 170         # x-Grenze zwischen Sechseck und Wortmarke in der Vorlage
SCALE = 4                # Hochrechnung vor dem Vektorisieren


def trace(img: Image.Image) -> list[tuple[str, tuple[float, float, float, float]]]:
    """Vektorisiert die Alpha-Maske. Liefert je Kontur (Pfaddaten, Bounding-Box) in Vorlagen-Pixeln."""
    big = img.resize((img.width * SCALE, img.height * SCALE), Image.LANCZOS)
    alpha = np.asarray(big)[:, :, 3] > 127
    # potracer: 0 = Vordergrund, daher invertieren
    path = potrace.Bitmap(~alpha).trace(turdsize=4, alphamax=1.0, opticurve=True, opttolerance=0.2)
    out = []
    for curve in path:
        pts = []
        sp = curve.start_point
        d = [f"M{sp.x / SCALE:.2f} {sp.y / SCALE:.2f}"]
        pts.append((sp.x / SCALE, sp.y / SCALE))
        for seg in curve:
            e = seg.end_point
            if seg.is_corner:
                c = seg.c
                d.append(f"L{c.x / SCALE:.2f} {c.y / SCALE:.2f}L{e.x / SCALE:.2f} {e.y / SCALE:.2f}")
                pts += [(c.x / SCALE, c.y / SCALE)]
            else:
                c1, c2 = seg.c1, seg.c2
                d.append(f"C{c1.x / SCALE:.2f} {c1.y / SCALE:.2f} {c2.x / SCALE:.2f} {c2.y / SCALE:.2f} {e.x / SCALE:.2f} {e.y / SCALE:.2f}")
                pts += [(c1.x / SCALE, c1.y / SCALE), (c2.x / SCALE, c2.y / SCALE)]
            pts.append((e.x / SCALE, e.y / SCALE))
        d.append("Z")
        xs = [p[0] for p in pts]
        ys = [p[1] for p in pts]
        out.append(("".join(d), (min(xs), min(ys), max(xs), max(ys))))
    return out


def path_svg(parts: list[str], fill: str, transform: str = "") -> str:
    t = f' transform="{transform}"' if transform else ""
    return f'<path fill="{fill}" fill-rule="evenodd"{t} d="{"".join(parts)}"/>'


def svg_doc(width: float, height: float, body: str, view: tuple[float, float, float, float] | None = None, label: str = "R-Stream") -> str:
    vx, vy, vw, vh = view or (0, 0, width, height)
    return (
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="{vx:.2f} {vy:.2f} {vw:.2f} {vh:.2f}" '
        f'width="{round(width)}" height="{round(height)}" role="img" aria-label="{label}">{body}</svg>'
    )


def render_png(svg: str, out: Path, width: int | None = None) -> None:
    kwargs = {"output_width": width} if width else {}
    cairosvg.svg2png(bytestring=svg.encode("utf-8"), write_to=str(out), **kwargs)


def text_path(font: TTFont, text: str, size: float, tracking_em: float = 0.0) -> tuple[str, float]:
    glyph_set = font.getGlyphSet()
    cmap = font.getBestCmap()
    scale = size / font["head"].unitsPerEm
    hmtx = font["hmtx"]
    pen = SVGPathPen(glyph_set)
    pos = 0.0
    for i, ch in enumerate(text):
        name = cmap[ord(ch)]
        glyph_set[name].draw(TransformPen(pen, (scale, 0, 0, -scale, pos, 0)))
        pos += hmtx[name][0] * scale
        if i < len(text) - 1:
            pos += tracking_em * size
    return pen.getCommands(), pos


def main() -> None:
    if not SRC.is_file():
        raise SystemExit(f"Vorlage nicht gefunden: {SRC}")
    img = Image.open(SRC).convert("RGBA")
    w, h = img.size
    contours = trace(img)
    mark = [d for d, bb in contours if bb[2] < MARK_SPLIT]
    word = [d for d, bb in contours if bb[2] >= MARK_SPLIT]
    bbox = lambda sel: (  # noqa: E731
        min(bb[0] for d, bb in contours if (bb[2] < MARK_SPLIT) == sel),
        min(bb[1] for d, bb in contours if (bb[2] < MARK_SPLIT) == sel),
        max(bb[2] for d, bb in contours if (bb[2] < MARK_SPLIT) == sel),
        max(bb[3] for d, bb in contours if (bb[2] < MARK_SPLIT) == sel),
    )
    mx0, my0, mx1, my1 = bbox(True)
    wx0, wy0, wx1, wy1 = bbox(False)
    print(f"Sechseck {mx0:.1f} {my0:.1f} {mx1:.1f} {my1:.1f} · Wortmarke {wx0:.1f} {wy0:.1f} {wx1:.1f} {wy1:.1f}")

    BRAND_DIR.mkdir(parents=True, exist_ok=True)
    IMG_DIR.mkdir(parents=True, exist_ok=True)
    ICON_DIR.mkdir(parents=True, exist_ok=True)

    # Vollständiges Logo, hell und dunkel
    light = svg_doc(w, h, path_svg(mark + word, DARK_INK))
    dark = svg_doc(w, h, path_svg(mark + word, LIGHT_INK))
    (BRAND_DIR / "rstream-logo.svg").write_text(light, encoding="utf-8")
    (BRAND_DIR / "rstream-logo-dark.svg").write_text(dark, encoding="utf-8")
    render_png(light, BRAND_DIR / "rstream-logo.png", width=2400)
    render_png(dark, BRAND_DIR / "rstream-logo-dark.png", width=2400)

    # Kopf- und Fußzeile: Zeichen und Wortmarke getrennt, hell (dunkle Website). Die Wortmarke
    # wird in der Höhe der Versalien beschnitten, damit sie wie Text in der Zeile sitzt.
    pad = 1.0
    mark_svg = svg_doc(mx1 - mx0 + 2 * pad, my1 - my0 + 2 * pad, path_svg(mark, LIGHT_INK),
                       view=(mx0 - pad, my0 - pad, mx1 - mx0 + 2 * pad, my1 - my0 + 2 * pad))
    word_svg = svg_doc(wx1 - wx0 + 2 * pad, wy1 - wy0 + 2 * pad, path_svg(word, LIGHT_INK),
                       view=(wx0 - pad, wy0 - pad, wx1 - wx0 + 2 * pad, wy1 - wy0 + 2 * pad), label="STREAM")
    (IMG_DIR / "rstream-mark.svg").write_text(mark_svg, encoding="utf-8")
    (IMG_DIR / "rstream-wordmark.svg").write_text(word_svg, encoding="utf-8")
    print(f"brand_mark_size in content-rstream.php: [{round(mx1 - mx0 + 2 * pad)}, {round(my1 - my0 + 2 * pad)}]")
    print(f"brand_wordmark_size in content-rstream.php: [{round(wx1 - wx0 + 2 * pad)}, {round(wy1 - wy0 + 2 * pad)}]")

    # Favicons: Sechseck-R in Violett auf Tinte – unterscheidet den Tab von reichi.com (weißes R
    # auf Schwarz) und reichi.it (Blitz auf Tinte)
    mh = my1 - my0
    s = 74 / mh
    icon_svg = (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">'
        f'<rect width="100" height="100" fill="{INK}"/>'
        f'{path_svg(mark, ACCENT, f"translate({50 - (mx1 - mx0) * s / 2 - mx0 * s:.3f} {13 - my0 * s:.3f}) scale({s:.5f})")}'
        "</svg>"
    )
    sizes = {
        "favicon-16x16.png": 16, "favicon-32x32.png": 32, "android-chrome-96x96.png": 96,
        "apple-touch-icon.png": 180, "icon-192.png": 192, "icon-512.png": 512,
    }
    for name, px in sizes.items():
        render_png(icon_svg, ICON_DIR / name, width=px)
    frames = []
    for px in (48, 32, 16):
        buf = io.BytesIO()
        cairosvg.svg2png(bytestring=icon_svg.encode("utf-8"), write_to=buf, output_width=px)
        frames.append(Image.open(io.BytesIO(buf.getvalue())).convert("RGBA"))
    frames[0].save(SITE_DIR / "favicon.ico", format="ICO", sizes=[(48, 48), (32, 32), (16, 16)], append_images=frames[1:])
    # Safari-Pinned-Tab: einfarbige Silhouette, Farbe setzt der Browser
    s2 = 100 / mh
    pinned = (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">'
        f'{path_svg(mark, "#000000", f"translate({50 - (mx1 - mx0) * s2 / 2 - mx0 * s2:.3f} {-my0 * s2:.3f}) scale({s2:.5f})")}'
        "</svg>"
    )
    (ICON_DIR / "safari-pinned-tab.svg").write_text(pinned, encoding="utf-8")

    # Sharing-Bild 1200×630: Logo hell auf Tinte, darunter eine Zeile, Violett-Balken
    if not FONT.is_file():
        raise SystemExit(f"Schrift nicht gefunden: {FONT}")
    font = TTFont(str(FONT))
    sw, sh = 1200, 630
    ls = 760 / w
    sub_d, sub_w = text_path(font, "Livestream-Produktion: Mehrkamera, Bildregie & Übertragung", 38, -0.01)
    share = (
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {sw} {sh}" width="{sw}" height="{sh}">'
        f'<rect width="{sw}" height="{sh}" fill="{INK}"/>'
        f'<rect x="0" y="0" width="{sw}" height="6" fill="{ACCENT}"/>'
        f'{path_svg(mark + word, LIGHT_INK, f"translate({(sw - w * ls) / 2:.2f} 200) scale({ls:.5f})")}'
        f'<path transform="translate({(sw - sub_w) / 2:.2f} 455)" fill="{LIGHT_INK}" fill-opacity="0.78" d="{sub_d}"/>'
        f'<rect x="{sw / 2 - 36}" y="490" width="72" height="4" fill="{ACCENT}"/>'
        "</svg>"
    )
    render_png(share, IMG_DIR / "share-rstream.png")

    # Karten in den Projektzeilen der anderen Websites, 760 px breit wie reichi-it.png: das
    # Sechseck trägt das Violett von rstream.at als Akzent, „STREAM“ steht in der Tinte der
    # jeweiligen Seite – hell für reichi.com (dunkler Grund), dunkel für reichi.it (Papier).
    # Kein CSS-Invert mehr, das würde aus dem Violett ein Grün machen.
    logos = ROOT / "htdocs" / "assets" / "images" / "logos"
    card = logos / "rstream.png"
    card_paper = logos / "rstream-paper.png"
    render_png(svg_doc(w, h, path_svg(mark, ACCENT) + path_svg(word, LIGHT_INK)), card, width=760)
    render_png(svg_doc(w, h, path_svg(mark, ACCENT) + path_svg(word, DARK_INK)), card_paper, width=760)
    with Image.open(card) as card_img:
        print(f"Kartenlogo content.php / content-it.php: 'width' => {card_img.width}, 'height' => {card_img.height}")

    for p in sorted(list(BRAND_DIR.glob("rstream-*")) + list(ICON_DIR.iterdir())
                    + [SITE_DIR / "favicon.ico", IMG_DIR / "share-rstream.png", IMG_DIR / "rstream-mark.svg", IMG_DIR / "rstream-wordmark.svg", card, card_paper]):
        print(f"{p.relative_to(ROOT)}  {p.stat().st_size} bytes")


if __name__ == "__main__":
    main()
