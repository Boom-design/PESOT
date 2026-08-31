"""
Cache the PSGC place names the OCR output is corrected against.

The registration form already drives its province / city / barangay dropdowns
from psgc.gitlab.io, so those lists are the authoritative spelling of every
address the system will accept. Matching OCR output against them turns a near
miss into the exact value the form needs — "Cagayan DePoro" only has to be
closer to "Cagayan de Oro" than to anything else in the list.

Run once with a connection; the service reads the cache and never calls out.
"""

import json
import os
import sys

import requests

BASE = os.path.dirname(os.path.abspath(__file__))
CACHE = os.path.join(BASE, "lexicon")
API = "https://psgc.gitlab.io/api"

# Barangay lists are per-city and there are 42,000 of them nationwide, so only
# the cities PESO Cagayan de Oro actually serves are pulled in full.
# Spelled the way PSGC spells it, which is not the way anyone writes it on the
# form — the lexicon match is what closes that gap.
LOCAL_CITIES = ["City of Cagayan De Oro"]

TIMEOUT = 30


def get(path):
    response = requests.get(f"{API}/{path}", timeout=TIMEOUT)
    response.raise_for_status()
    return response.json()


def main():
    os.makedirs(CACHE, exist_ok=True)

    print("provinces ...")
    provinces = get("provinces.json")
    names = sorted(entry["name"] for entry in provinces)
    write("provinces.json", names)

    print("cities and municipalities ...")
    cities = get("cities-municipalities.json")
    write("cities.json", sorted(entry["name"] for entry in cities))

    for city_name in LOCAL_CITIES:
        match = next((c for c in cities if c["name"] == city_name), None)
        if not match:
            print(f"  {city_name}: not found in PSGC, skipped")
            continue

        print(f"barangays of {city_name} ...")
        barangays = get(f"cities-municipalities/{match['code']}/barangays.json")
        slug = city_name.lower().replace(" ", "_")
        write(f"barangays_{slug}.json", sorted(b["name"] for b in barangays))

    print("\ncached to " + CACHE)


def write(name, values):
    path = os.path.join(CACHE, name)
    with open(path, "w", encoding="utf-8") as handle:
        json.dump(values, handle, ensure_ascii=False, indent=1)
    print(f"  {name}: {len(values)}")


if __name__ == "__main__":
    try:
        main()
    except requests.RequestException as error:
        sys.exit(f"PSGC fetch failed ({error}); the service falls back to raw OCR text")
