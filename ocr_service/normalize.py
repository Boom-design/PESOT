"""
Turn raw TrOCR output into the value the registration form will accept.

The model reads the strokes well and then rewrites them into English: "Oro"
comes back "Janeiro", and a phone number comes back as a word. Two corrections
undo most of that, and both are deterministic rather than another guess.

  * Format. A field that can only be digits gets everything else stripped, and a
    mobile number is recovered by finding the 11-digit run starting 09 rather
    than trusting the whole string.
  * Lexicon. Address and option fields have a closed list of legal values, so
    the output is snapped to the nearest entry.

Every correction reports how confident it is, because a low score is what the UI
uses to colour a field for the staff to check rather than silently accept.
"""

import difflib
import json
import os
import re

BASE = os.path.dirname(os.path.abspath(__file__))
LEXICON = os.path.join(BASE, "lexicon")

# Below this the correction is worse than showing the raw text: snapping
# "greensdaymate" onto some unrelated barangay would invent data rather than
# recover it.
MIN_MATCH = 0.62

RELIGIONS = [
    "Roman Catholic", "Catholic", "Iglesia ni Cristo", "Islam", "Born Again",
    "Seventh-day Adventist", "Baptist", "Protestant", "Jehovah's Witness",
    "Aglipay", "Evangelical", "Methodist", "Mormon", "None",
]

WORK_STATUS = ["Permanent", "Contractual", "Part-time", "Probationary"]

_cache = {}


def load(name):
    if name not in _cache:
        path = os.path.join(LEXICON, name)
        if not os.path.exists(path):
            _cache[name] = []
        else:
            with open(path, encoding="utf-8") as handle:
                _cache[name] = json.load(handle)
    return _cache[name]


def clean(text):
    """Strip the punctuation TrOCR likes to append and collapse whitespace."""
    text = re.sub(r"\s+", " ", (text or "").strip())
    return text.strip(" .,;:-_|")


def mobile(text):
    """Recover a Philippine mobile number from a noisy digit string.

    TrOCR returned "109558071389" for a number that reads 09558071389 — every
    digit correct with one extra in front. Rather than trust the string, the
    11-digit run that starts 09 is located inside it, which discards a leading
    or trailing stray without touching the rest.
    """
    digits_only = re.sub(r"\D", "", text or "")
    if not digits_only:
        return "", 0.0

    for start in range(len(digits_only) - 10):
        window = digits_only[start:start + 11]
        if window.startswith("09"):
            exact = len(digits_only) == 11
            return window, 1.0 if exact else 0.75

    # Landline or a badly read mobile — hand it over as digits and let the staff
    # judge, rather than reshaping it into something that looks legitimate.
    return digits_only, 0.35


def digits(text):
    value = re.sub(r"\D", "", text or "")
    return value, (0.8 if value else 0.0)


def integer(text):
    value = re.sub(r"\D", "", text or "")
    if not value:
        return "", 0.0
    return str(int(value)), 0.8


def decimal(text):
    match = re.search(r"\d+(?:[.,]\d+)?", (text or "").replace(" ", ""))
    if not match:
        return "", 0.0
    return match.group(0).replace(",", "."), 0.75


def date(text):
    """Keep only what looks like a date; the staff confirms the rest."""
    value = re.sub(r"[^\d/\-]", "", (text or "").replace(" ", ""))
    return value.strip("/-"), (0.6 if value else 0.0)


def year_range(text):
    years = re.findall(r"(?:19|20)\d{2}", text or "")
    if not years:
        return "", 0.0
    return " - ".join(years[:2]), 0.7


def match_lexicon(text, candidates):
    """Snap text to the closest legal value, or leave it alone."""
    text = clean(text)
    if not text or not candidates:
        return text, 0.0

    best, score = None, 0.0
    lowered = text.lower()
    for candidate in candidates:
        ratio = difflib.SequenceMatcher(None, lowered, candidate.lower()).ratio()
        if ratio > score:
            best, score = candidate, ratio

    if score >= MIN_MATCH:
        return best, round(score, 2)
    return text, round(score, 2)


def address(text, level):
    if level == "province":
        return match_lexicon(text, load("provinces.json"))
    if level == "municipality_city":
        return match_lexicon(text, load("cities.json"))
    if level == "barangay":
        return match_lexicon(text, load("barangays_city_of_cagayan_de_oro.json"))
    return clean(text), 0.5


def apply(field, kind, text):
    """Route a field to its correction and return (value, confidence)."""
    text = clean(text)
    if not text:
        return "", 0.0

    if field in ("province", "municipality_city", "barangay"):
        return address(text, field)
    if field == "religion":
        return match_lexicon(text, RELIGIONS)
    if field.endswith("_status"):
        return match_lexicon(text, WORK_STATUS)
    if field in ("work_1_address", "work_2_address", "work_3_address",
                 "local_location_1", "local_location_2", "local_location_3"):
        return match_lexicon(text, load("cities.json"))
    if field in ("ofw_country", "latest_deployment_country",
                 "terminated_abroad_country", "overseas_location_1",
                 "overseas_location_2", "overseas_location_3"):
        return text, 0.5

    handlers = {
        "mobile": mobile,
        "digits": digits,
        "integer": integer,
        "decimal": decimal,
        "date": date,
        "date_range": date,
        "year_range": year_range,
    }
    if kind in handlers:
        return handlers[kind](text)

    if kind == "email":
        return (text, 0.6) if "@" in text else ("", 0.0)

    return text, 0.5
