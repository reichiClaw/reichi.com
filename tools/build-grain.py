#!/usr/bin/env python3
"""
Erzeugt die Filmkorn-Kachel htdocs/assets/images/grain.png (Entwicklungsrechner, nicht
auf dem Server nötig). Graustufen + Alpha: Pixel sind zufällig hell oder dunkel mit
geringer Deckkraft, so dass die Kachel über jeder Fläche nur Struktur hinzufügt, ohne die
mittlere Helligkeit zu verschieben. Rein zufälliges Rauschen kachelt nahtlos.

    python3 tools/build-grain.py
"""

from pathlib import Path
import random

from PIL import Image

SIZE = 160          # Kachelgröße in Pixeln (CSS: background-size 160px)
STRENGTH = 0.045    # Standardabweichung der Deckkraft (0–1); höher = kräftigeres Korn
LEVELS = 24         # Alpha-Stufen, hält die Datei klein
SEED = 20260925

OUT = Path(__file__).resolve().parent.parent / "htdocs" / "assets" / "images" / "grain.png"


def main() -> None:
    rng = random.Random(SEED)
    img = Image.new("LA", (SIZE, SIZE))
    px = img.load()
    step = 255 / (LEVELS - 1)
    for y in range(SIZE):
        for x in range(SIZE):
            v = rng.gauss(0.0, STRENGTH)
            lum = 255 if v > 0 else 0
            alpha = min(1.0, abs(v))
            px[x, y] = (lum, int(round(alpha * 255 / step) * step))
    img.save(OUT, optimize=True)
    print(f"wrote {OUT} ({OUT.stat().st_size} bytes)")


if __name__ == "__main__":
    main()
