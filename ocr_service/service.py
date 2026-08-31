"""
HTTP front door for the NSRP scanner.

Laravel posts an image here and gets back the form values. It exists as a
long-running service rather than a script Laravel shells out to because TrOCR
takes over a minute to load: paying that once at startup instead of once per
scan is the whole difference between usable and not.

Binds to localhost only. Nothing here authenticates a caller, so it must never
be reachable from outside the machine — Laravel is the only client, and in
deployment the two sit on a private network.
"""

import os
import time
import traceback

from flask import Flask, jsonify, request

import pipeline

app = Flask(__name__)

MAX_BYTES = 12 * 1024 * 1024
ALLOWED = {"image/jpeg", "image/jpg", "image/png"}


@app.get("/health")
def health():
    return jsonify({
        "status": "ok",
        "model_loaded": pipeline._model is not None,
        "model": pipeline.MODEL_ID,
    })


@app.post("/scan")
def scan():
    upload = request.files.get("image")
    if upload is None:
        return jsonify({"error": "No image was uploaded."}), 400

    if upload.mimetype not in ALLOWED:
        return jsonify({"error": "Upload a JPG or PNG photo of the form."}), 400

    data = upload.read(MAX_BYTES + 1)
    if len(data) > MAX_BYTES:
        return jsonify({"error": "That image is too large."}), 400

    started = time.time()
    try:
        result = pipeline.scan(data)
    except Exception:
        # The staff member can always fill the form by hand, so a failure here
        # should read as "OCR did not help this time", never as a broken page.
        traceback.print_exc()
        return jsonify({"error": "The scan failed. Please fill out the form "
                                 "manually."}), 500

    if "error" in result:
        return jsonify(result), 422

    result["seconds"] = round(time.time() - started, 1)
    return jsonify(result)


if __name__ == "__main__":
    print("loading model before accepting requests ...")
    pipeline.load_model()
    print("ready")

    app.run(
        host="127.0.0.1",
        port=int(os.environ.get("OCR_PORT", 8001)),
        threaded=False,   # one scan at a time; the model is not thread-safe
        debug=False,
    )
