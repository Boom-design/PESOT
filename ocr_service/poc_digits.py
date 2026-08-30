"""
Second probe: does constraining the decoder to digits rescue the number fields?

The first probe showed the encoder sees the digits fine but the RoBERTa decoder
rewrites them into English words ("09558071389" came back "toassoonists").
Here we mask every token that is not made purely of digits, so the only thing
the decoder is able to emit is a number, and compare against the same crop
unconstrained.
"""

import os
import sys
import time

import cv2
from huggingface_hub import snapshot_download
from PIL import Image
from tokenizers import ByteLevelBPETokenizer
from transformers import TrOCRProcessor, VisionEncoderDecoderModel

MODEL_ID = "microsoft/trocr-base-handwritten"
BASE = os.path.dirname(os.path.abspath(__file__))
SAMPLE = os.path.join(
    BASE, "..", "storage", "app", "nsrp_samples",
    "f1c121b6-9926-4329-a484-1d2e8114f0ea.jpg",
)
MODEL_DIR = os.path.join(BASE, "models")

# Numeric-only fields on page 1, plus one word field as a control.
FIELDS = [
    ("contact_number", (1185, 690, 1450, 750), "09558071389", True),
    ("height",         (1185, 640, 1450, 695), "5.4",         True),
    ("months_looking", (1230, 845, 1330, 890), "2",           True),
    ("barangay",       (810,  552,  960, 605), "Tablon",      False),
]


def fetch_model_dir():
    local = snapshot_download(MODEL_ID, cache_dir=MODEL_DIR)
    tokenizer_json = os.path.join(local, "tokenizer.json")
    if not os.path.exists(tokenizer_json):
        bpe = ByteLevelBPETokenizer(
            os.path.join(local, "vocab.json"), os.path.join(local, "merges.txt")
        )
        bpe.save(tokenizer_json)
    return local


def digit_token_ids(tokenizer):
    """Every token whose text is only digits, spaces, dots or dashes.

    Byte-level BPE prefixes a leading space with 'G-with-dot', so the raw token
    string is normalised before testing.
    """
    allowed = set(tokenizer.all_special_ids)
    for token, token_id in tokenizer.get_vocab().items():
        text = token.replace("Ġ", " ").strip()
        if text and all(character in "0123456789.-" for character in text):
            allowed.add(token_id)
    return sorted(allowed)


def main():
    image = cv2.imread(SAMPLE)
    if image is None:
        sys.exit("cv2 could not decode the sample")

    print("loading model ...")
    started = time.time()
    local = fetch_model_dir()
    processor = TrOCRProcessor.from_pretrained(local)
    model = VisionEncoderDecoderModel.from_pretrained(local)
    model.eval()
    print(f"ready in {time.time() - started:.1f}s\n")

    allowed = digit_token_ids(processor.tokenizer)
    print(f"digit-only tokens allowed: {len(allowed)} "
          f"of {len(processor.tokenizer.get_vocab())}\n")

    print(f"{'field':<16} {'free':<24} {'digits-only':<24} {'truth'}")
    print("-" * 86)

    for name, (x1, y1, x2, y2), truth, numeric in FIELDS:
        crop = image[y1:y2, x1:x2]
        cv2.imwrite(os.path.join(BASE, "poc_crops", f"d_{name}.png"), crop)

        pil = Image.fromarray(cv2.cvtColor(crop, cv2.COLOR_BGR2RGB))
        pixel_values = processor(images=pil, return_tensors="pt").pixel_values

        free = processor.batch_decode(
            model.generate(pixel_values, max_new_tokens=24),
            skip_special_tokens=True,
        )[0]

        if numeric:
            constrained = processor.batch_decode(
                model.generate(
                    pixel_values,
                    max_new_tokens=24,
                    prefix_allowed_tokens_fn=lambda batch, ids: allowed,
                ),
                skip_special_tokens=True,
            )[0]
        else:
            constrained = "(not numeric, skipped)"

        print(f"{name:<16} {free[:22]:<24} {constrained[:22]:<24} {truth}")


if __name__ == "__main__":
    main()
