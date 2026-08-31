"""
Draw a field map over its page so the coordinates can be checked by eye.

A map is a long list of numbers that all look equally plausible in a text
editor; the only honest way to know whether "surname" points at the surname is
to see it drawn on the form. Every region is outlined and named, and checkbox
centres are shown both where the map claims they are and where the snap lands.
"""

import json
import os
import sys

import cv2

import checkboxes as detector

BASE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(BASE, "output")
FIELDS = os.path.join(BASE, "fields")

# How far from the mapped centre to look for the real printed square.
SNAP_RADIUS = 0.020


def snap_to_box(binary, centre, width, height):
    """Find the printed square nearest a mapped centre.

    The map is measured by hand off one photo, so a few pixels of drift is
    expected and harmless — as long as the ink is finally measured inside the
    real border rather than wherever the hand-measurement happened to land.
    Returns None when there is no square there, which is itself worth seeing:
    it means the map is pointing at empty paper.
    """
    centre_x = int(centre[0] * width)
    centre_y = int(centre[1] * height)
    radius = int(SNAP_RADIUS * width)

    x1, y1 = max(0, centre_x - radius), max(0, centre_y - radius)
    x2, y2 = min(width, centre_x + radius), min(height, centre_y + radius)

    window = binary[y1:y2, x1:x2]
    if window.size == 0:
        return None

    lines = detector.rule_mask(window)
    contours, _ = cv2.findContours(lines, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)

    best = None
    for contour in contours:
        bx, by, bw, bh = cv2.boundingRect(contour)
        if not (detector.MIN_SIDE <= bw <= detector.MAX_SIDE):
            continue
        if not (detector.MIN_SIDE <= bh <= detector.MAX_SIDE):
            continue
        if abs(bw - bh) / max(bw, bh) > detector.MAX_ASPECT_SKEW:
            continue

        distance = abs(bx + bw / 2 - radius) + abs(by + bh / 2 - radius)
        if best is None or distance < best[0]:
            best = (distance, (x1 + bx, y1 + by, bw, bh))

    return best[1] if best else None


def main():
    page_number = sys.argv[1] if len(sys.argv) > 1 else "1"
    stem = {"1": "f1c121b6", "2": "021fb43d"}[page_number]

    with open(os.path.join(FIELDS, f"page{page_number}.json"), encoding="utf-8") as handle:
        field_map = json.load(handle)

    page = cv2.imread(os.path.join(OUT, f"{stem}_3_flat.png"))
    height, width = page.shape[:2]
    binary = detector.binarize(page)
    canvas = page.copy()

    def draw(box, colour, label, thickness=2):
        x1, y1, x2, y2 = box
        cv2.rectangle(canvas, (int(x1 * width), int(y1 * height)),
                      (int(x2 * width), int(y2 * height)), colour, thickness)
        cv2.putText(canvas, label, (int(x1 * width) + 2, int(y1 * height) - 3),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.34, colour, 1)

    for name, spec in field_map.get("text_fields", {}).items():
        draw(spec["box"], (255, 0, 0), name)

    for name, spec in field_map.get("mark_cells", {}).items():
        if name == "note":
            continue
        draw(spec["box"], (200, 0, 200), name, 1)

    missed = []
    for name, spec in field_map.get("checkboxes", {}).items():
        centre = spec["centre"]
        snapped = snap_to_box(binary, centre, width, height)

        point = (int(centre[0] * width), int(centre[1] * height))
        cv2.drawMarker(canvas, point, (0, 140, 255), cv2.MARKER_CROSS, 9, 1)

        if snapped is None:
            missed.append(name)
            cv2.putText(canvas, name, (point[0] + 6, point[1] + 4),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.32, (0, 0, 255), 1)
            continue

        bx, by, bw, bh = snapped
        ink = detector.ink_ratio(binary, snapped)
        colour = (0, 0, 255) if ink > detector.TICK_THRESHOLD else (0, 170, 0)
        cv2.rectangle(canvas, (bx, by), (bx + bw, by + bh), colour, 2)
        cv2.putText(canvas, f"{name} {ink:.2f}", (bx + bw + 3, by + bh - 2),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.32, colour, 1)

    path = os.path.join(OUT, f"{stem}_8_map.png")
    cv2.imwrite(path, canvas)

    total = len(field_map.get("checkboxes", {}))
    ticked = sum(
        1 for spec in field_map.get("checkboxes", {}).values()
        if (snapped := snap_to_box(binary, spec["centre"], width, height))
        and detector.ink_ratio(binary, snapped) > detector.TICK_THRESHOLD
    )

    print(f"page {page_number}: {total - len(missed)}/{total} checkboxes snapped, "
          f"{ticked} ticked")
    if missed:
        print("no square found under: " + ", ".join(missed))
    print(f"overlay: {path}")


if __name__ == "__main__":
    main()
