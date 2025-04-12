<?php
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AbsensiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/absen', [AbsensiController::class, 'absen']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/tes', function () {
    return response()->json(['message' => 'Tes berhasil']);
});


