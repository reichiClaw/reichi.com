#!/usr/bin/env python3
"""
Erzeugt das Filmkorn (Entwicklungsrechner, nicht auf dem Server nötig):

  htdocs/assets/images/grain.png         eine Kachel, das statische Korn (body::after,
                                         ohne JS und bei reduzierter Bewegung)
  htdocs/assets/images/grain-frames.png  dieselben Kacheln als Filmstreifen untereinander,
                                         eine je Filmbild. main.js (Abschnitt 9) schaltet
                                         sie durch, damit das Korn wie echtes Filmkorn
                                         flimmert statt als festes Muster zu liegen.

Graustufen + Alpha: Pixel sind zufällig hell oder dunkel mit geringer Deckkraft, so dass
die Kachel über jeder Fläche nur Struktur hinzufügt, ohne die mittlere Helligkeit zu
verschieben. Rein zufälliges Rauschen kachelt nahtlos. Jedes Filmbild hat einen eigenen
Startwert, damit aufeinanderfolgende Bilder unkorreliert sind – verschobene Kopien
desselben Musters würden als Kriechen statt als Korn gelesen.

    python3 tools/build-grain.py
"""

from pathlib import Path
import random

from PIL import Image

SIZE = 160          # Kachelgröße in Pixeln (CSS: background-size 160px)
STRENGTH = 0.045    # Standardabweichung der Deckkraft (0–1); höher = kräftigeres Korn
LEVELS = 24         # Alpha-Stufen, hält die Datei klein
FRAMES = 8          # Filmbilder im Streifen (main.js leitet die Zahl aus der Bildhöhe ab)
SEED = 20260925

OUT_DIR = Path(__file__).resolve().parent.parent / "htdocs" / "assets" / "images"
OUT = OUT_DIR / "grain.png"
STRIP = OUT_DIR / "grain-frames.png"


def frame(seed: int) -> Image.Image:
    rng = random.Random(seed)
    img = Image.new("LA", (SIZE, SIZE))
    px = img.load()
    step = 255 / (LEVELS - 1)
    for y in range(SIZE):
        for x in range(SIZE):
            v = rng.gauss(0.0, STRENGTH)
            lum = 255 if v > 0 else 0
            alpha = min(1.0, abs(v))
            px[x, y] = (lum, int(round(alpha * 255 / step) * step))
    return img


def main() -> None:
    frames = [frame(SEED + i) for i in range(FRAMES)]
    frames[0].save(OUT, optimize=True)
    strip = Image.new("LA", (SIZE, SIZE * FRAMES))
    for i, img in enumerate(frames):
        strip.paste(img, (0, i * SIZE))
    strip.save(STRIP, optimize=True)
    print(f"wrote {OUT} ({OUT.stat().st_size} bytes)")
    print(f"wrote {STRIP} ({STRIP.stat().st_size} bytes, {FRAMES} frames)")


if __name__ == "__main__":
    main()
