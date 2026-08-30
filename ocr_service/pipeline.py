"""
One photo of an NSRP form in, one dictionary of form values out.

Order of work: flatten the page, decide which side of the sheet it is, read the
checkboxes from ink, read the handwriting with TrOCR, then correct the text
against formats and lexicons. Checkboxes come first on purpose — they are the
bulk of the form and the part that does not depend on the model at all, so a
scan still returns most of its value even when the handwriting defeats it.

Every value carries a confidence. Nothing here decides to save anything; the
staff member reviews the filled form with the person standing in front of them.
"""

import io
import json
import os

import cv2
import numpy as np
import torch
from PIL import Image

import align
import checkboxes as detector
import normalize
import verify_map

BASE = os.path.dirname(os.path.abspath(__file__))
FIELDS = os.path.join(BASE, "fields")
MODEL_DIR = os.path.join(BASE, "models")
MODEL_ID = "microsoft/trocr-large-handwritten"

# A crop with less handwriting than this is blank paper. Skipping it is not only
# faster: TrOCR asked to read an empty box will confidently invent a word — the
# first end-to-end run filled an empty "suffix" with "1957-000" and an empty
# household ID with "1".
#
# Counted in pixels of ink, not as a fraction of the crop. Fractions punish a
# short answer in a wide box: "Tablon" in the barangay field covers under 1% of
# it and was being discarded as empty, while a large box of speckle passed.
# Measured on the sample pages at the canonical page size: written fields land
# around 65 pixels and up while empty ones stay under 32. Rescale this alongside
# PAGE_WIDTH — it counts pixels, so it moves with the page.
MIN_WRITING_PIXELS = 48

# Ink islands smaller than this are paper grain or the edge of a printed rule;
# larger ones are a smudge or a piece of the form that survived removal.
MIN_BLOB = 12
MAX_BLOB = 4000

# Crops are read in one batch rather than one at a time; the form has enough
# fields that the per-call overhead dominates otherwise.
BATCH_SIZE = 8

_model = None
_processor = None
_maps = {}


def load_map(page):
    if page not in _maps:
        with open(os.path.join(FIELDS, f"page{page}.json"), encoding="utf-8") as handle:
            _maps[page] = json.load(handle)
    return _maps[page]


def load_model():
    """Load TrOCR once and keep it resident.

    The model takes over a minute to come up from cold, which is the whole
    reason this runs as a service instead of a script invoked per scan.
    """
    global _model, _processor
    if _model is not None:
        return _processor, _model

    from huggingface_hub import snapshot_download
    from tokenizers import ByteLevelBPETokenizer
    from transformers import TrOCRProcessor, VisionEncoderDecoderModel

    local = snapshot_download(MODEL_ID, cache_dir=MODEL_DIR)
    tokenizer_json = os.path.join(local, "tokenizer.json")
    if not os.path.exists(tokenizer_json):
        bpe = ByteLevelBPETokenizer(
            os.path.join(local, "vocab.json"), os.path.join(local, "merges.txt")
        )
        bpe.save(tokenizer_json)

    _processor = TrOCRProcessor.from_pretrained(local)
    _model = VisionEncoderDecoderModel.from_pretrained(local)
    _model.eval()
    return _processor, _model


def handwriting_mask(binary):
    """Ink that a person put there, with the printed form taken away.

    Every blank on this form sits on a printed rule or inside a ruled cell, so
    raw darkness cannot tell "empty" from "empty with a line under it". Removing
    the long straight strokes leaves only what was written by hand, which is the
    thing worth asking whether it exists.
    """
    rules = detector.rule_mask(binary)
    thick = cv2.dilate(rules, cv2.getStructuringElement(cv2.MORPH_RECT, (3, 3)))
    return cv2.bitwise_and(binary, cv2.bitwise_not(thick))


def has_writing(mask, box=None):
    """Is there handwriting in this region, and how much of it.

    Returns the pixel count rather than a ratio so the caller can judge a one
    character answer and a full line by the same standard.
    """
    region = mask if box is None else mask[box[1]:box[3], box[0]:box[2]]
    if region.size == 0:
        return False, 0

    _, _, stats, _ = cv2.connectedComponentsWithStats(region, 8)
    written = sum(
        int(stat[cv2.CC_STAT_AREA]) for stat in stats[1:]
        if MIN_BLOB <= stat[cv2.CC_STAT_AREA] <= MAX_BLOB
    )
    return written >= MIN_WRITING_PIXELS, written


def flatten(image):
    corners, _ = align.find_page(image)
    if corners is None:
        return None
    return align.warp_to_page(image, corners)


def identify_page(page, binary):
    """Decide which side of the sheet this is by trying both maps.

    Reading the printed heading would mean a second model for printed text. The
    checkbox layouts of the two sides are different enough to tell them apart on
    their own: the right map finds a printed square under nearly every centre it
    names, the wrong one mostly finds blank paper.
    """
    height, width = binary.shape[:2]
    scores = {}

    for number in (1, 2):
        field_map = load_map(number)
        hits = sum(
            1 for spec in field_map["checkboxes"].values()
            if verify_map.snap_to_box(binary, spec["centre"], width, height)
        )
        scores[number] = hits / max(1, len(field_map["checkboxes"]))

    best = max(scores, key=scores.get)
    return best, scores


def read_checkboxes(field_map, binary):
    height, width = binary.shape[:2]
    results = {}

    for name, spec in field_map["checkboxes"].items():
        snapped = verify_map.snap_to_box(binary, spec["centre"], width, height)
        if snapped is None:
            results[name] = {"ticked": None, "ink": None, "group": spec["group"],
                             "value": spec["value"], "confidence": 0.0}
            continue

        ink = detector.ink_ratio(binary, snapped)
        low, high = detector.UNSURE_BAND
        confidence = 0.4 if low <= ink <= high else 0.95

        results[name] = {
            "ticked": ink > detector.TICK_THRESHOLD,
            "ink": round(ink, 3),
            "group": spec["group"],
            "value": spec["value"],
            "confidence": confidence,
        }

    return results


def read_mark_cells(field_map, writing):
    """The language grid has no printed squares, only ticks in empty cells.

    Measured on the handwriting mask, so the cell's own printed borders are not
    mistaken for a tick — reading raw darkness here reported all twelve cells
    ticked, Mandarin included, on a form where Mandarin is blank.
    """
    height, width = writing.shape[:2]
    results = {}

    for name, spec in field_map.get("mark_cells", {}).items():
        if name == "note":
            continue

        x1, y1, x2, y2 = spec["box"]
        box = (int(x1 * width), int(y1 * height), int(x2 * width), int(y2 * height))
        ticked, ink = has_writing(writing, box)

        results[name] = {
            "ticked": ticked,
            "ink": ink,
            "group": spec["group"],
            "value": spec["value"],
            "confidence": 0.85 if abs(ink - MIN_WRITING_PIXELS) > 15 else 0.4,
        }

    return results


def crop_text_fields(field_map, page, writing):
    """Cut out every text field, dropping the ones that are blank paper."""
    height, width = page.shape[:2]
    crops, blank = [], []

    for name, spec in field_map["text_fields"].items():
        x1, y1, x2, y2 = spec["box"]
        box = (int(x1 * width), int(y1 * height), int(x2 * width), int(y2 * height))

        written, _ = has_writing(writing, box)
        if not written:
            blank.append(name)
            continue

        crops.append((name, spec.get("kind", "free"),
                      page[box[1]:box[3], box[0]:box[2]]))

    return crops, blank


def upscale(crop, factor=2):
    """Enlarge a crop before the model sees it.

    No information is added by this, and it still helps a great deal: the same
    contact number came back "joassatory" at native size and "(04558071389)"
    at twice that. Perspective-correcting the page resamples the strokes soft,
    and the model has an easier time on a larger, smoother rendering of them.

    Costs nothing worth measuring — TrOCR resizes everything to 384x384 anyway,
    so only the interpolation itself is extra.
    """
    return cv2.resize(crop, None, fx=factor, fy=factor, interpolation=cv2.INTER_CUBIC)


def read_text(crops):
    """Run TrOCR over the crops and return raw text plus a per-field confidence."""
    if not crops:
        return {}

    processor, model = load_model()
    results = {}

    for start in range(0, len(crops), BATCH_SIZE):
        batch = crops[start:start + BATCH_SIZE]
        images = [
            Image.fromarray(cv2.cvtColor(upscale(crop), cv2.COLOR_BGR2RGB))
            for _, _, crop in batch
        ]
        pixel_values = processor(images=images, return_tensors="pt").pixel_values

        with torch.no_grad():
            output = model.generate(
                pixel_values,
                max_new_tokens=40,
                output_scores=True,
                return_dict_in_generate=True,
            )

        texts = processor.batch_decode(output.sequences, skip_special_tokens=True)

        # Mean probability of the tokens the model actually chose. A field the
        # model was unsure about is exactly the one the staff should look at.
        probabilities = [
            torch.softmax(step, dim=-1).max(dim=-1).values for step in output.scores
        ]
        stacked = torch.stack(probabilities, dim=1) if probabilities else None

        for index, (name, kind, _) in enumerate(batch):
            score = float(stacked[index].mean()) if stacked is not None else 0.5
            results[name] = (texts[index], kind, round(score, 3))

    return results


def scan(image_bytes):
    array = np.frombuffer(image_bytes, dtype=np.uint8)
    image = cv2.imdecode(array, cv2.IMREAD_COLOR)
    if image is None:
        return {"error": "Could not read that image file."}

    page = flatten(image)
    if page is None:
        return {"error": "Could not find the form in the photo. Lay the paper "
                         "flat, keep all four corners in frame, and try again."}

    binary = detector.binarize(page)
    writing = handwriting_mask(binary)

    number, scores = identify_page(page, binary)
    field_map = load_map(number)

    ticks = read_checkboxes(field_map, binary)
    ticks.update(read_mark_cells(field_map, writing))

    crops, blank = crop_text_fields(field_map, page, writing)
    raw = read_text(crops)

    fields = {}
    for name, (text, kind, model_score) in raw.items():
        value, fix_score = normalize.apply(name, kind, text)
        if not value:
            continue
        fields[name] = {
            "value": value,
            "raw": text,
            "confidence": round(min(model_score, max(fix_score, 0.3)), 2),
        }

    return {
        "page": number,
        "page_scores": {str(k): round(v, 2) for k, v in scores.items()},
        "fields": fields,
        "checkboxes": ticks,
        "blank_fields": blank,
    }
