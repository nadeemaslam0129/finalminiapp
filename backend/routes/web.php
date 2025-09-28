<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ---------------------------------------------------
// AUTH ROUTES (Sanctum Cookie based auth)
// ---------------------------------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/user', function (\Illuminate\Http\Request $request) {
    return $request->user();
});


Route::get('/{any}', function () {
})->where('any', '.*');
