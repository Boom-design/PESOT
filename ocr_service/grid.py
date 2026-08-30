"""
Draw a labelled percentage grid over a flattened page.

The field map stores coordinates as fractions of the page rather than pixels, so
it keeps working when the next photo is taken from a slightly different distance.
Reading those fractions off a bare image is guesswork; this overlays the ruler
they are measured against.
"""

import os
import sys

import cv2

BASE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(BASE, "output")

STEP = 0.02          # a line every 2% of the page
LABEL_EVERY = 5      # label every fifth line, so every 10%


def draw_grid(page):
    height, width = page.shape[:2]
    canvas = page.copy()

    steps = int(round(1 / STEP))
    for index in range(steps + 1):
        fraction = index * STEP
        x = int(fraction * width)
        y = int(fraction * height)

        major = index % LABEL_EVERY == 0
        colour = (0, 0, 255) if major else (0, 220, 255)
        thickness = 2 if major else 1

        cv2.line(canvas, (x, 0), (x, height), colour, thickness)
        cv2.line(canvas, (0, y), (width, y), colour, thickness)

        if major:
            label = f"{fraction:.2f}"
            cv2.putText(canvas, label, (x + 3, 22),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.55, (255, 0, 0), 2)
            cv2.putText(canvas, label, (3, y - 4),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.55, (255, 0, 0), 2)

    return canvas


def main():
    flats = sorted(name for name in os.listdir(OUT) if name.endswith("_3_flat.png"))
    if not flats:
        sys.exit("run align.py first")

    for name in flats:
        stem = name.split("_")[0]
        page = cv2.imread(os.path.join(OUT, name))
        cv2.imwrite(os.path.join(OUT, f"{stem}_7_grid.png"), draw_grid(page))
        print(f"{stem}: grid written")


if __name__ == "__main__":
    main()
