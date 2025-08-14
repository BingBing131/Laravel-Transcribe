<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Whisper Transcriber</title>
<script>
async function submitForm(e){
  e.preventDefault();
  const out=document.getElementById('output');
  out.value='Uploading & transcribing...';
  const data=new FormData(document.getElementById('form'));
  const res=await fetch('/api/transcribe',{method:'POST',body:data});
  const json=await res.json();
  out.value = res.ok ? (json.text || '(no text)') : JSON.stringify(json,null,2);
}
</script>
<style>
body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:24px}
.card{max-width:680px;margin:0 auto;padding:20px;border:1px solid #e5e7eb;border-radius:12px}
.btn{padding:10px 14px;border:none;border-radius:8px;background:#111827;color:#fff}
.row{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
textarea{width:100%;min-height:160px}
</style>
</head><body>
<div class="card">
<h1>Whisper Transcriber</h1>
<p>Choose an audio file (m4a, mp3, wav, etc.).</p>
<form id="form" onsubmit="submitForm(event)" enctype="multipart/form-data">
  <div class="row">
    <input type="file" name="audio" accept="audio/*,video/mp4,video/quicktime" required>
    <select name="task">
      <option value="transcribe">Transcribe</option>
      <option value="translate">Translate to English</option>
    </select>
    <button class="btn" type="submit">Transcribe</button>
  </div>
</form>
<h3>Result</h3>
<textarea id="output" readonly></textarea>
</div>
</body></html>
