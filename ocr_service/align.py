"""
Step one of the pipeline: turn a phone photo of an NSRP form into a flat,
canonically sized page.

Everything downstream — the field coordinate map, the checkbox reader — assumes
the page always arrives at the same size with the same orientation. That only
holds if this step is right, so it writes debug images at each stage rather than
silently handing on a bad warp.

Two things are deliberately avoided:

  * Fixed pixel coordinates measured off one photo. Those break the moment
    somebody stands a little closer. Coordinates live in normalised 0..1 space
    and are resolved against the canonical page instead.
  * Trusting the paper outline alone. The samples we have are creased and one is
    cropped at the top edge, so the page contour can be wrong. The printed table
    border is the more reliable anchor and is detected as a cross-check.
"""

import os
import sys

import cv2
import numpy as np

BASE = os.path.dirname(os.path.abspath(__file__))
SAMPLES = os.path.join(BASE, "..", "storage", "app", "nsrp_samples")
OUT = os.path.join(BASE, "output")

# A4 portrait, sized to sit just under a phone photo of the form rather than at
# a round dpi figure. The first version warped a 1536x2048 photo down to
# 1240x1754 and threw away a third of the pixels before the model ever saw them;
# a contact number that read correctly off the raw photo came back mangled off
# the flattened one. Detail that is discarded here cannot be recovered later.
PAGE_WIDTH = 1450
PAGE_HEIGHT = 2050


def order_corners(points):
    """Return the four corners as top-left, top-right, bottom-right, bottom-left."""
    points = points.reshape(4, 2).astype("float32")
    ordered = np.zeros((4, 2), dtype="float32")

    total = points.sum(axis=1)
    ordered[0] = points[np.argmin(total)]   # top-left has the smallest x+y
    ordered[2] = points[np.argmax(total)]   # bottom-right the largest

    diff = np.diff(points, axis=1)
    ordered[1] = points[np.argmin(diff)]    # top-right has the smallest y-x
    ordered[3] = points[np.argmax(diff)]

    return ordered


def find_page(image):
    """Find the sheet of paper as a quadrilateral.

    The photos are shot on a dark surface, so the paper is by far the brightest
    large region. Otsu separates it cleanly, and closing fills the pen marks and
    creases that would otherwise break the outline apart.
    """
    grey = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    blurred = cv2.GaussianBlur(grey, (7, 7), 0)
    _, binary = cv2.threshold(blurred, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)

    kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (25, 25))
    closed = cv2.morphologyEx(binary, cv2.MORPH_CLOSE, kernel)

    contours, _ = cv2.findContours(closed, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    if not contours:
        return None, closed

    largest = max(contours, key=cv2.contourArea)
    image_area = image.shape[0] * image.shape[1]
    if cv2.contourArea(largest) < image_area * 0.2:
        return None, closed

    perimeter = cv2.arcLength(largest, True)
    for tolerance in (0.02, 0.03, 0.05, 0.08):
        approximated = cv2.approxPolyDP(largest, tolerance * perimeter, True)
        if len(approximated) == 4:
            return order_corners(approximated), closed

    # Not a clean quadrilateral — a corner is probably outside the frame.
    # The minimum-area rectangle still gives a usable warp.
    box = cv2.boxPoints(cv2.minAreaRect(largest))
    return order_corners(np.array(box, dtype="int32")), closed


def warp_to_page(image, corners):
    destination = np.array(
        [[0, 0], [PAGE_WIDTH - 1, 0],
         [PAGE_WIDTH - 1, PAGE_HEIGHT - 1], [0, PAGE_HEIGHT - 1]],
        dtype="float32",
    )
    matrix = cv2.getPerspectiveTransform(corners, destination)
    return cv2.warpPerspective(image, matrix, (PAGE_WIDTH, PAGE_HEIGHT))


def detect_table(page):
    """Find the printed horizontal and vertical rules of the form.

    These are the strongest structure on the page and the cross-check that the
    warp landed square: if the detected lines are not close to axis-aligned,
    the page corners were wrong.
    """
    grey = cv2.cvtColor(page, cv2.COLOR_BGR2GRAY)
    binary = cv2.adaptiveThreshold(
        grey, 255, cv2.ADAPTIVE_THRESH_MEAN_C, cv2.THRESH_BINARY_INV, 15, 10
    )

    horizontal_kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (60, 1))
    vertical_kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (1, 40))

    horizontal = cv2.morphologyEx(binary, cv2.MORPH_OPEN, horizontal_kernel, iterations=2)
    vertical = cv2.morphologyEx(binary, cv2.MORPH_OPEN, vertical_kernel, iterations=2)

    return horizontal, vertical, cv2.bitwise_or(horizontal, vertical)


def main():
    os.makedirs(OUT, exist_ok=True)

    samples = sorted(
        name for name in os.listdir(SAMPLES) if name.lower().endswith((".jpg", ".png"))
    )
    if not samples:
        sys.exit(f"no samples in {SAMPLES}")

    for name in samples:
        stem = os.path.splitext(name)[0][:8]
        image = cv2.imread(os.path.join(SAMPLES, name))
        if image is None:
            print(f"{stem}: could not decode, skipped")
            continue

        corners, mask = find_page(image)
        cv2.imwrite(os.path.join(OUT, f"{stem}_1_mask.png"), mask)

        if corners is None:
            print(f"{stem}: PAGE NOT FOUND")
            continue

        outlined = image.copy()
        cv2.polylines(outlined, [corners.astype(int)], True, (0, 0, 255), 8)
        cv2.imwrite(os.path.join(OUT, f"{stem}_2_corners.png"), outlined)

        page = warp_to_page(image, corners)
        cv2.imwrite(os.path.join(OUT, f"{stem}_3_flat.png"), page)

        horizontal, vertical, grid = detect_table(page)
        cv2.imwrite(os.path.join(OUT, f"{stem}_4_grid.png"), grid)

        overlay = page.copy()
        overlay[grid > 0] = (0, 0, 255)
        cv2.imwrite(os.path.join(OUT, f"{stem}_5_overlay.png"), overlay)

        horizontal_count = cv2.connectedComponents(horizontal)[0] - 1
        vertical_count = cv2.connectedComponents(vertical)[0] - 1
        print(f"{stem}: page found, {horizontal_count} horizontal rules, "
              f"{vertical_count} vertical rules")

    print(f"\ndebug images in {OUT}")


if __name__ == "__main__":
    main()
