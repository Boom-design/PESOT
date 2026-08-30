"""
Search the checkbox detector's parameters against hand-counted ground truth.

The two knobs interact: loosening the side test to keep ticked boxes lets label
letters back in, and tightening it to shut them out throws the ticked boxes away
again. Guessing one at a time was going in circles, so this scores the grid.

Ground truth was counted by eye off the flattened pages.
"""

import itertools
import os

import cv2

import checkboxes as detector

BASE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(BASE, "output")

# page stem -> (total printed boxes, boxes bearing a tick)
TRUTH = {
    "f1c121b6": (40, 8),    # page 1
    "021fb43d": (26, 5),    # page 2
}


def evaluate(open_length, close_size, coverage, min_sides):
    detector.rule_mask.__defaults__ = ()
    results = {}

    for stem, _ in TRUTH.items():
        page = cv2.imread(os.path.join(OUT, f"{stem}_3_flat.png"))
        binary = detector.binarize(page)

        horizontal = cv2.morphologyEx(
            binary, cv2.MORPH_OPEN,
            cv2.getStructuringElement(cv2.MORPH_RECT, (open_length, 1)),
        )
        vertical = cv2.morphologyEx(
            binary, cv2.MORPH_OPEN,
            cv2.getStructuringElement(cv2.MORPH_RECT, (1, open_length)),
        )
        lines = cv2.morphologyEx(
            cv2.bitwise_or(horizontal, vertical), cv2.MORPH_CLOSE,
            cv2.getStructuringElement(cv2.MORPH_RECT, (close_size, close_size)),
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
                lines, (x, y, width, height), coverage, min_sides
            ):
                continue
            boxes.append((x, y, width, height))

        boxes = detector.deduplicate(boxes)
        ticked = sum(
            1 for box in boxes
            if detector.ink_ratio(binary, box) > detector.TICK_THRESHOLD
        )
        results[stem] = (len(boxes), ticked)

    return results


def main():
    print(f"{'open':>4} {'close':>5} {'cov':>5} {'sides':>5} | "
          f"{'page1 (40/8)':>14} {'page2 (26/5)':>14} | error")
    print("-" * 74)

    scored = []
    for open_length, close_size, coverage, min_sides in itertools.product(
        (7, 9, 11), (3, 5, 7), (0.45, 0.55, 0.65, 0.75), (3, 4)
    ):
        results = evaluate(open_length, close_size, coverage, min_sides)

        error = 0
        for stem, (want_total, want_ticked) in TRUTH.items():
            got_total, got_ticked = results[stem]
            error += abs(got_total - want_total) + 2 * abs(got_ticked - want_ticked)

        scored.append((error, open_length, close_size, coverage, min_sides, results))

    scored.sort(key=lambda row: row[0])
    for error, open_length, close_size, coverage, min_sides, results in scored[:12]:
        page1 = "{}/{}".format(*results["f1c121b6"])
        page2 = "{}/{}".format(*results["021fb43d"])
        print(f"{open_length:>4} {close_size:>5} {coverage:>5} {min_sides:>5} | "
              f"{page1:>14} {page2:>14} | {error}")


if __name__ == "__main__":
    main()
