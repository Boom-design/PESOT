"""
Print the checkbox positions the detector is confident about, in page fractions.

These become the seed of the field map. The detector is run at its strict
setting, where the parameter sweep showed it finds about 33 of 40 boxes with no
false positives — trading recall for precision on purpose, because a missed box
can be filled in from the regular spacing of its neighbours, while a phantom box
would quietly attach a name to a piece of label text.

Grouped into rows so the layout is readable and gaps are obvious.
"""

import os

import cv2

import checkboxes as detector

BASE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(BASE, "output")

OPEN_LENGTH = 9
CLOSE_SIZE = 3
COVERAGE = 0.75
MIN_SIDES = 3

ROW_TOLERANCE = 0.008   # boxes within this vertical distance are one row


def detect(page):
    binary = detector.binarize(page)

    horizontal = cv2.morphologyEx(
        binary, cv2.MORPH_OPEN,
        cv2.getStructuringElement(cv2.MORPH_RECT, (OPEN_LENGTH, 1)),
    )
    vertical = cv2.morphologyEx(
        binary, cv2.MORPH_OPEN,
        cv2.getStructuringElement(cv2.MORPH_RECT, (1, OPEN_LENGTH)),
    )
    lines = cv2.morphologyEx(
        cv2.bitwise_or(horizontal, vertical), cv2.MORPH_CLOSE,
        cv2.getStructuringElement(cv2.MORPH_RECT, (CLOSE_SIZE, CLOSE_SIZE)),
    )

    contours, _ = cv2.findContours(lines, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)
    boxes = []
    for contour in contours:
        x, y, width, height = cv2.boundingRect(contour)
        if not (detector.MIN_SIDE <= width <= detector.MAX_SIDE):
            continue
        if not (detector.MIN_SIDE <= height <= detector.MAX_SIDE):
            continue
        if abs(width - height) / max(width, height) > detector.MAX_ASPECT_SKEW:
            continue
        if not detector.has_four_solid_sides(
            lines, (x, y, width, height), COVERAGE, MIN_SIDES
        ):
            continue
        boxes.append((x, y, width, height))

    return detector.deduplicate(boxes), binary


def group_rows(boxes, height):
    rows = []
    for box in sorted(boxes, key=lambda b: b[1]):
        centre = (box[1] + box[3] / 2) / height
        for row in rows:
            if abs(row[0] - centre) <= ROW_TOLERANCE:
                row[1].append(box)
                break
        else:
            rows.append([centre, [box]])
    return rows


def main():
    for stem, label in (("f1c121b6", "PAGE 1 (front)"), ("021fb43d", "PAGE 2 (back)")):
        page = cv2.imread(os.path.join(OUT, f"{stem}_3_flat.png"))
        height, width = page.shape[:2]

        boxes, binary = detect(page)
        rows = group_rows(boxes, height)

        print(f"\n===== {label} — {len(boxes)} boxes in {len(rows)} rows =====")
        print(f"{'y':>6}  {'x':>6} {'w':>6} {'h':>6}  {'ink':>5}  state")
        print("-" * 46)

        for centre, row in rows:
            for box in sorted(row, key=lambda b: b[0]):
                x, y, box_width, box_height = box
                ink = detector.ink_ratio(binary, box)
                state = "TICK" if ink > detector.TICK_THRESHOLD else "."
                print(f"{centre:>6.3f}  {x / width:>6.3f} "
                      f"{box_width / width:>6.3f} {box_height / height:>6.3f}  "
                      f"{ink:>5.2f}  {state}")
            print()


if __name__ == "__main__":
    main()
