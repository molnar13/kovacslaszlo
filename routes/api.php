use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostalCodeController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Védett végpontok
    Route::post('/postal-codes', [PostalCodeController::class, 'store']);
    Route::put('/postal-codes/{postalCode}', [PostalCodeController::class, 'update']);
    Route::delete('/postal-codes/{postalCode}', [PostalCodeController::class, 'destroy']);
});

// Publikus végpontok
Route::get('/postal-codes', [PostalCodeController::class, 'index']);
Route::get('/postal-codes/{postalCode}', [PostalCodeController::class, 'show']);
Route::get('/postal-codes/search/{code}', [PostalCodeController::class, 'searchByCode']);