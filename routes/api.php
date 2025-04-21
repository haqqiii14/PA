<?php
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AbsensiController;
use App\Http\Controllers\AbsenController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IzinController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\API\RekapanController;

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Tes koneksi
Route::get('/tes', fn () => response()->json(['message' => 'Tes berhasil']));

// Route untuk pegawai biasa
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/absen/masuk', [AbsenController::class, 'checkIn']);
    Route::post('/absen/pulang', [AbsenController::class, 'checkOut']);
    Route::get('/rekap-absensi', [AbsensiController::class, 'rekap']);
    Route::post('/izin', [IzinController::class, 'store']);
});

// Route khusus admin
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/rekapan', [RekapanController::class, 'getRekapan']);
});




