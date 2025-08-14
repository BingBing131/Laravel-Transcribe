<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TranscriptionController extends Controller
{
    // Shows a tiny upload page
    public function view()
    {
        return view('transcribe');
    }

    // Receives the file, forwards to Python, returns JSON
    public function transcribe(Request $request)
    {
        // 1) Check it's a real audio file (<= ~50 MB)
        $validator = Validator::make($request->all(), [
            'audio' => 'required|file|max:51200|mimetypes:audio/mpeg,audio/mp4,audio/x-m4a,audio/wav,audio/x-wav,audio/aac,audio/flac,audio/ogg,video/mp4,video/quicktime'
        ], [
            'audio.mimetypes' => 'Please upload a common audio format (mp3, m4a, wav, aac, flac, ogg).'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2) Prepare request for Python
        $file = $request->file('audio');
        $task = $request->input('task', 'transcribe');
        $url = rtrim(env('PY_SERVICE_URL', 'http://127.0.0.1:8000'), '/') . '/transcribe';

        // 3) Send the file to Python
        $response = Http::timeout(intval(env('HTTP_CLIENT_TIMEOUT', 60)))
            ->attach('file', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
            ->asMultipart()
            ->post($url, ['task' => $task]);

        // 4) Bubble up any error from Python
        if ($response->failed()) {
            return response()->json([
                'message' => 'Transcription service error.',
                'service_response' => $response->json()
            ], 502);
        }

        // 5) Hand JSON back to the browser
        return response()->json($response->json(), 200);
    }
}
