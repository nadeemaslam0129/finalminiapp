<?php 
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {

    // Get authenticated user info
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });

    // Transactions
    Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index']);
    Route::post('/transactions', [\App\Http\Controllers\TransactionController::class, 'store']);

    // Users for dropdown (id + name)
    Route::get('/users', function () {
        return User::select('id', 'name')->get();
    });
         Route::get('/notifications', [\App\Http\Controllers\TransactionController::class, 'notifications']);

});
