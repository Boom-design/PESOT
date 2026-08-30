# NSRP handwriting scanner

A small Flask service that turns a photograph of a filled-in NSRP form into the
values the registration form expects. Laravel calls it over localhost; it holds
no database connection and no state of its own.

`StaffWebController::nsrpScan` posts an image to `POST /scan` and gets back
fields, checkboxes and a confidence for each. Nothing here decides to save
anything — the staff member reviews the filled form with the person standing in
front of them.

There is no scanner on the jobseeker side, deliberately. Someone filling in
their own form is typing what they already know; the scanner exists for staff
copying somebody else's handwriting off paper.

## How it reads a form

1. **`align.py`** — finds the sheet in the photo and warps it flat to a fixed
   1450×2050 page. Every coordinate downstream is stored in 0..1 space and
   resolved against that page, so standing closer changes nothing.
2. **`checkboxes.py`** — finds the printed squares by their geometry and calls
   one ticked when more than 10% of its interior is ink. **No model is involved**,
   which is why this is the most reliable part of the pipeline rather than the
   hardest — and the NSRP form is mostly checkboxes.
3. **`pipeline.py`** — decides which side of the sheet it is, cuts out the text
   fields that actually contain writing, and reads them with
   `microsoft/trocr-large-handwritten`.
4. **`normalize.py`** — TrOCR reads the strokes well and then rewrites them into
   English: `Oro` comes back `Janeiro`, a phone number comes back as a word.
   Formats are enforced and addresses snapped to the PSGC lexicon.

The proof-of-concept scripts — `poc_test.py`, `sweep_checkboxes.py`,
`dump_boxes.py`, `grid.py`, `verify_map.py` — are the bench the thresholds in
`checkboxes.py` were measured on. Keep them: if the form changes, they are how
the numbers get re-derived.

## Setup

Not committed to git: `venv/` and `models/`. Together they are about 5.5 GB.

```bash
cd ocr_service
python -m venv venv
venv/bin/pip install -r requirements.txt          # Windows: venv\Scripts\pip
venv/bin/python fetch_psgc.py                     # address lexicon, needs a connection
```

The **first run downloads about 4.57 GB** from Hugging Face into `models/`.
That happens once. Every start after it still takes roughly a minute to load the
weights into memory, which is the whole reason this runs as a service rather
than a script invoked per scan.

Budget **3 GB of RAM** for this process alone, measured while loaded and idle.

## Running it

**Windows, during development** — one terminal, left open:

```
cd C:\xampp\htdocs\PESO_backend\ocr_service
venv\Scripts\python.exe service.py
```

Wait for `ready` before scanning. Closing the terminal stops the scanner and
Laravel starts answering *"The scanner is not running."*

**Linux, on the server** — nobody runs anything. Create
`/etc/systemd/system/peso-ocr.service`:

```ini
[Unit]
Description=PESO NSRP handwriting scanner
After=network.target

[Service]
Type=simple
User=peso
WorkingDirectory=/var/www/peso/ocr_service
ExecStart=/var/www/peso/ocr_service/venv/bin/python service.py
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Then:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now peso-ocr
sudo systemctl status peso-ocr
```

It starts at boot and restarts if it crashes. Staff only ever upload a photo.

## Settings

Laravel reads these from `.env`:

| Key | Default | Notes |
|---|---|---|
| `OCR_SERVICE_URL` | `http://127.0.0.1:8001` | localhost only; never expose port 8001 |
| `OCR_SERVICE_TIMEOUT` | `180` | seconds. A page takes about a minute on CPU |

`service.py` reads `OCR_PORT` if you need to move it off 8001. It binds to
`127.0.0.1` on purpose — the scanner has no authentication of its own and must
never be reachable from outside the machine.

`threaded=False`: one scan at a time. The model is not thread-safe, and two
concurrent scans would need 6 GB.

## When something goes wrong

| What you see | What it means |
|---|---|
| `The scanner is not running.` | Nothing is listening on 8001. Check `systemctl status peso-ocr` |
| `ImportError: libGL.so.1` | Plain `opencv-python` got installed. It must be `opencv-python-headless` |
| A 2.5 GB torch download | The `--extra-index-url` line was dropped; pip took the CUDA build |
| `Could not find the form in the photo` | Lay the paper flat with all four corners in frame |
| A blank field filled with nonsense | Ink below `MIN_WRITING_PIXELS` should be skipped — check the threshold against the page size |
