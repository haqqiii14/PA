<?php
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AbsensiController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/absen', [AbsensiController::class, 'absen']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/tes', function () {
    return response()->json(['message' => 'Tes berhasil']);
});


