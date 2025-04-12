<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function absen(Request $request)
    {
        $request->validate([
            'qr_code' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();

        // Contoh koordinat kantor
        $kantor_lat = -7.364126; // Sidoarjo
        $kantor_lng = 112.7197931 ;
        $radius_meter = 100; // 100 meter

        // Hitung jarak
    $jarak = $this->hitungJarak($request->latitude, $request->longitude, $kantor_lat, $kantor_lng);

    if ($jarak > $radius_meter) {
        return response()->json([
            'status' => false,
            'message' => 'Absen gagal: Anda tidak berada di lokasi kantor.',
            'jarak' => round($jarak, 2) . ' meter'
        ], 403);
    }


        $absen = Absensi::create([
            'user_id' => $user->id,
            'qr_code' => $request->qr_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'waktu_absen' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Absen berhasil',
            'data' => $absen
        ]);
    }
}
