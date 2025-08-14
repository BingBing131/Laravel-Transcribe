use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TranscriptionController;

Route::post('/transcribe', [TranscriptionController::class, 'transcribe'])->name('transcribe.api');
