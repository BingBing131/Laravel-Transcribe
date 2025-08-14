<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TranscriptionController;

Route::get('/', [TranscriptionController::class, 'view'])->name('transcribe.view');
