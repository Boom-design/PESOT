"""
Find every printed checkbox on a flattened page and decide which are ticked.

No model is involved. A checkbox is a small printed square, and a ticked one has
ink inside it — that is the whole idea, and it is why this is the most accurate
part of the pipeline rather than the hardest.

Two details from the real samples drive the implementation:

  * People tick past the edges of the box. The tail of a tick often lands well
    outside it, and a staff verification mark sits in the left margin of the
    sample forms. So ink is only ever counted strictly inside the square, with
    the printed border itself inset out of the measurement.
  * The forms are creased. A fold can read as a faint line across a box, so the
    threshold has to sit above paper noise rather than at the first dark pixel.
"""

import os
import sys

import align
import cv2
import numpy as np

BASE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(BASE, "output")

# Every printed checkbox on the form comes out about 2% of the page wide; the
# label text around it tops out near 1%. That gap is the cleanest signal on the
# page, and the floor sits in the middle of it — set any lower and the O, D and
# A of the printed labels are admitted as checkboxes, which is exactly what the
# first attempt did.
#
# Derived from the canonical page rather than written as pixels, because these
# were once fixed numbers and every one of them silently went wrong the day the
# page size changed.
MIN_SIDE = round(align.PAGE_WIDTH * 0.0145)
MAX_SIDE = round(align.PAGE_WIDTH * 0.0275)

# Length a stroke must run to count as a printed rule rather than handwriting.
# Also scaled: at a fixed 9px this began eating the strokes of digits once the
# page grew, and whole fields started reading as blank paper.
RULE_LENGTH = max(7, round(align.PAGE_WIDTH * 0.00725))

# How square a contour has to be to count as a checkbox at all.
MAX_ASPECT_SKEW = 0.35

# Fraction of the interior that must be ink before the box counts as ticked.
# Paper texture and a crease sit around 2-4%; a real tick clears 12% easily.
TICK_THRESHOLD = 0.10
UNSURE_BAND = (0.06, 0.16)


def binarize(page):
    grey = cv2.cvtColor(page, cv2.COLOR_BGR2GRAY)
    return cv2.adaptiveThreshold(
        grey, 255, cv2.ADAPTIVE_THRESH_MEAN_C, cv2.THRESH_BINARY_INV, 25, 12
    )


def rule_mask(binary):
    """Keep only strokes long and straight enough to be printed rules.

    A tick is short and curved, so opening with a bar-shaped kernel erases it
    while leaving the sides of a checkbox intact. Searching for the squares here
    rather than in the raw image is what lets a ticked box still be found: the
    tick would otherwise merge with the border, the contour would stop being a
    quadrilateral, and precisely the boxes we care about would be the ones
    thrown away.
    """
    horizontal = cv2.morphologyEx(
        binary, cv2.MORPH_OPEN, cv2.getStructuringElement(cv2.MORPH_RECT, (RULE_LENGTH, 1))
    )
    vertical = cv2.morphologyEx(
        binary, cv2.MORPH_OPEN, cv2.getStructuringElement(cv2.MORPH_RECT, (1, RULE_LENGTH))
    )
    lines = cv2.bitwise_or(horizontal, vertical)

    # A tick crossing the border splits that side into two runs too short to
    # survive the opening, so the closing has to be wide enough to bridge the
    # gap the tick left behind — otherwise the only boxes that stay whole are
    # the empty ones, which are exactly the ones we do not need.
    return cv2.morphologyEx(
        lines, cv2.MORPH_CLOSE, cv2.getStructuringElement(cv2.MORPH_RECT, (5, 5))
    )


def find_boxes(binary):
    """Return the bounding box of every printed square that looks like a checkbox."""
    lines = rule_mask(binary)
    contours, _ = cv2.findContours(lines, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)
    boxes = []

    for contour in contours:
        x, y, width, height = cv2.boundingRect(contour)

        if not (MIN_SIDE <= width <= MAX_SIDE and MIN_SIDE <= height <= MAX_SIDE):
            continue
        if abs(width - height) / max(width, height) > MAX_ASPECT_SKEW:
            continue

        if not has_four_solid_sides(lines, (x, y, width, height)):
            continue

        boxes.append((x, y, width, height))

    return deduplicate(boxes)


def has_four_solid_sides(binary, box, min_coverage=0.55, min_sides=3):
    """Reject anything whose bounding edges are not drawn like a ruled square.

    This is what separates a printed square from a letter. The label text sits at
    the same size as the boxes, and letters like O, D, A and N are closed enough
    to pass a shape test — but none of them ink the edges of their bounding box
    the way a ruled square does.

    Three of four sides rather than all four: a tick drawn hard through a border
    can wipe out a whole side, and demanding all four silently discarded every
    ticked box. No letter reaches three fully drawn sides, so the discrimination
    survives the concession.
    """
    x, y, width, height = box
    band = max(2, int(min(width, height) * 0.16))

    sides = (
        binary[y: y + band, x: x + width],                    # top
        binary[y + height - band: y + height, x: x + width],  # bottom
        binary[y: y + height, x: x + band],                   # left
        binary[y: y + height, x + width - band: x + width],   # right
    )

    drawn = sum(
        1 for side in sides
        if side.size and np.count_nonzero(side) / side.size >= min_coverage
    )
    return drawn >= min_sides


def deduplicate(boxes):
    """Drop boxes that sit on top of each other.

    findContours returns the inner and outer edge of the same printed square, so
    every box is found twice.
    """
    kept = []
    for box in sorted(boxes, key=lambda b: b[2] * b[3], reverse=True):
        x, y, width, height = box
        centre = (x + width / 2, y + height / 2)
        if any(
            abs(centre[0] - (kx + kw / 2)) < kw * 0.6
            and abs(centre[1] - (ky + kh / 2)) < kh * 0.6
            for kx, ky, kw, kh in kept
        ):
            continue
        kept.append(box)
    return sorted(kept, key=lambda b: (b[1], b[0]))


def ink_ratio(binary, box):
    """Fraction of the box interior that is ink, ignoring the printed border."""
    x, y, width, height = box
    inset = max(3, int(min(width, height) * 0.22))

    interior = binary[y + inset: y + height - inset, x + inset: x + width - inset]
    if interior.size == 0:
        return 0.0

    return float(np.count_nonzero(interior)) / interior.size


def main():
    flats = sorted(name for name in os.listdir(OUT) if name.endswith("_3_flat.png"))
    if not flats:
        sys.exit("run align.py first — no flattened pages in output/")

    for name in flats:
        stem = name.split("_")[0]
        page = cv2.imread(os.path.join(OUT, name))
        binary = binarize(page)

        boxes = find_boxes(binary)
        overlay = page.copy()

        ticked = unsure = 0
        for box in boxes:
            x, y, width, height = box
            ratio = ink_ratio(binary, box)

            if UNSURE_BAND[0] <= ratio <= UNSURE_BAND[1]:
                colour, thickness = (0, 165, 255), 2   # orange: needs a human
                unsure += 1
            elif ratio > TICK_THRESHOLD:
                colour, thickness = (0, 0, 255), 3     # red: ticked
                ticked += 1
            else:
                colour, thickness = (0, 200, 0), 1     # green: empty

            cv2.rectangle(overlay, (x, y), (x + width, y + height), colour, thickness)
            cv2.putText(overlay, f"{ratio:.2f}", (x + width + 2, y + height),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.3, colour, 1)

        cv2.imwrite(os.path.join(OUT, f"{stem}_6_checkboxes.png"), overlay)
        print(f"{stem}: {len(boxes)} boxes found, {ticked} ticked, {unsure} unsure")

    print(f"\noverlays in {OUT}")


if __name__ == "__main__":
    main()
