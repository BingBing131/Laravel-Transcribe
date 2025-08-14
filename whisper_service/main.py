from fastapi import FastAPI, UploadFile, File, Form, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import tempfile, os
import whisper

# Choose which Whisper model to use (fast vs accurate)
MODEL_NAME = os.getenv("WHISPER_MODEL", "base")  # tiny/base/small/medium/large
model = whisper.load_model(MODEL_NAME)  # load once at startup

app = FastAPI(title="Local Whisper Service", version="1.0")

# Let browsers/other apps call us during dev
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# The "door" other apps will use
@app.post("/transcribe")
async def transcribe(file: UploadFile = File(...), task: str = Form("transcribe")):
    if not file.filename:
        raise HTTPException(status_code=400, detail="No file uploaded.")

    # Save upload to a temp file because Whisper wants a file path
    suffix = os.path.splitext(file.filename)[1]
    with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as tmp:
        contents = await file.read()
        tmp.write(contents)
        tmp_path = tmp.name

    try:
        # Ask Whisper to listen and write the words
        result = model.transcribe(tmp_path, task=task)  # add language="en" to force English

        # Clean response
        segments = [
            {"id": i, "start": s["start"], "end": s["end"], "text": s["text"].strip()}
            for i, s in enumerate(result.get("segments", []))
        ]
        return {
            "text": result.get("text", "").strip(),
            "language": result.get("language", None),
            "segments": segments,
        }
    finally:
        # Throw the temp file away
        try: os.remove(tmp_path)
        except Exception: pass
