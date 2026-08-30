"""
Proof of concept: can TrOCR read the handwriting on a real NSRP form?

This is a throwaway probe, not the pipeline. It crops a handful of fields from
page 1 of a sample using hand-measured coordinates, saves each crop so the
crops themselves can be eyeballed, and runs TrOCR over them.

Saving the crops matters: if the output is wrong we need to know whether the
model failed or the crop simply missed the text.
"""

import os
import sys
import time

import cv2
from huggingface_hub import snapshot_download
from PIL import Image
from tokenizers import ByteLevelBPETokenizer
from transformers import TrOCRProcessor, VisionEncoderDecoderModel

MODEL_ID = "microsoft/trocr-large-handwritten"

BASE = os.path.dirname(os.path.abspath(__file__))
SAMPLE = os.path.join(
    BASE, "..", "storage", "app", "nsrp_samples",
    "f1c121b6-9926-4329-a484-1d2e8114f0ea.jpg",
)
CROP_DIR = os.path.join(BASE, "poc_crops")
MODEL_DIR = os.path.join(BASE, "models")

# Hand-measured on the 1536x2048 original. Generous padding because the paper
# is photographed at an angle and is not lying flat.
# name: (x1, y1, x2, y2), what a human reads there
FIELDS = [
    ("surname",        (75,  385,  300,  450), "Decierdo"),
    ("first_name",     (410, 385,  580,  450), "?? (unreadable to me)"),
    ("middle_name",    (755, 385,  930,  450), "Genclaso / Gendaso ?"),
    ("religion",       (215, 520,  390,  580), "Chatholic"),
    ("house_street",   (810, 520, 1100,  580), "Tablon Zone 4"),
    ("barangay",       (810, 552,  960,  605), "Tablon"),
    ("municipality",   (810, 590, 1100,  650), "Cagayan De Oro"),
    ("contact_number", (1185, 690, 1450, 750), "09558071389"),
]


def fetch_model_dir():
    """Download the model and give it the tokenizer.json transformers 5 needs.

    The TrOCR repo predates fast tokenizers: it ships vocab.json + merges.txt
    only, and transformers 5 no longer converts those on the fly. Building the
    byte-level BPE ourselves and writing tokenizer.json into the snapshot is a
    one-off; every later load finds it already there.
    """
    local = snapshot_download(MODEL_ID, cache_dir=MODEL_DIR)
    tokenizer_json = os.path.join(local, "tokenizer.json")

    if not os.path.exists(tokenizer_json):
        print("building tokenizer.json from vocab.json + merges.txt ...")
        bpe = ByteLevelBPETokenizer(
            os.path.join(local, "vocab.json"),
            os.path.join(local, "merges.txt"),
        )
        bpe.save(tokenizer_json)

    return local


def main():
    if not os.path.exists(SAMPLE):
        sys.exit(f"sample not found: {SAMPLE}")

    os.makedirs(CROP_DIR, exist_ok=True)
    os.makedirs(MODEL_DIR, exist_ok=True)

    image = cv2.imread(SAMPLE)
    if image is None:
        sys.exit("cv2 could not decode the sample")
    height, width = image.shape[:2]
    print(f"sample: {width}x{height}")

    print("loading trocr-base-handwritten (first run downloads ~1.3GB) ...")
    started = time.time()
    local = fetch_model_dir()
    processor = TrOCRProcessor.from_pretrained(local)
    model = VisionEncoderDecoderModel.from_pretrained(local)
    model.eval()
    print(f"model ready in {time.time() - started:.1f}s\n")

    print(f"{'field':<16} {'trocr says':<32} {'human reads'}")
    print("-" * 88)

    for name, (x1, y1, x2, y2), truth in FIELDS:
        crop = image[y1:y2, x1:x2]
        cv2.imwrite(os.path.join(CROP_DIR, f"{name}.png"), crop)

        pil = Image.fromarray(cv2.cvtColor(crop, cv2.COLOR_BGR2RGB))
        pixel_values = processor(images=pil, return_tensors="pt").pixel_values

        started = time.time()
        ids = model.generate(pixel_values, max_new_tokens=32)
        elapsed = time.time() - started

        text = processor.batch_decode(ids, skip_special_tokens=True)[0]
        print(f"{name:<16} {text[:30]:<32} {truth}   ({elapsed:.1f}s)")

    print(f"\ncrops saved to {CROP_DIR}")


if __name__ == "__main__":
    main()
