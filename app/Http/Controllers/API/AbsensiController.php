<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Izin;
use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Rekapan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AbsensiController extends Controller
{

    public function absen(Request $request)
    {
        $request->validate([
            'qr_code' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $validator = Validator::make($request->all(), [
            'qr_code' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // Cek apakah sudah check-in hari ini
        $existing = Presensi::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah check-in hari ini.'
            ], 422);
        }

        // Cek apakah ada izin untuk hari ini
        $izin = Izin::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        if ($izin) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sedang dalam status izin.'
            ], 422);
        }

        // Lokasi kantor (contoh koordinat)
        $kantor_lat = -7.364126;
        $kantor_lng = 112.7197931;
        $radius_meter = 100; // Jarak maksimal 100 meter

        // Hitung jarak dari lokasi user ke kantor
        $jarak = $this->hitungJarak($request->latitude, $request->longitude, $kantor_lat, $kantor_lng);

        if ($jarak > $radius_meter) {
            return response()->json([
                'status' => false,
                'message' => 'Absen gagal: Anda tidak berada di lokasi kantor.',
                'jarak' => round($jarak, 2) . ' meter'
            ], 403);
        }

        $presensi = Presensi::create([
            'user_id' => $user->id,
            'qr_code' => $request->qr_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'waktu_presensi' => Carbon::now(),
            'tanggal' => $today,
        ]);

        Rekapan::create([
            'user_id' => $user->id,
            'tanggal' => $today,
            'status' => 'presensi',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil.',
            'data' => $presensi,
        ], 201);
    }

    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // Cek apakah sudah check-in hari ini
        $presensi = Presensi::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->whereNotNull('waktu_presensi')
            ->first();

        if (!$presensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum check-in hari ini.'
            ], 422);
        }

        if ($presensi->waktu_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah check-out hari ini.'
            ], 422);
        }

        $presensi->update([
            'waktu_pulang' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil.',
            'data' => $presensi,
        ], 200);
    }

    public function submitIzin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'alasan' => 'nullable|string',
            'waktu' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $tanggal = $request->tanggal;

        // Cek apakah sudah ada presensi pada tanggal tersebut
        $presensi = Presensi::where('user_id', $user->id)
            ->where('tanggal', $tanggal)
            ->first();

        if ($presensi) {
            return response()->json([
                'success' => false,
                'message' => "Anda sudah memiliki presensi pada tanggal $tanggal."
            ], 422);
        }

        // Cek apakah sudah ada izin pada tanggal tersebut
        $izin = Izin::where('user_id', $user->id)
            ->where('tanggal', $tanggal)
            ->first();

        if ($izin) {
            return response()->json([
                'success' => false,
                'message' => "Anda sudah memiliki izin pada tanggal $tanggal."
            ], 422);
        }

        $izin = Izin::create([
            'user_id' => $user->id,
            'tanggal' => $tanggal,
            'alasan' => $request->alasan,
            'waktu' => $request->waktu,
        ]);

        Rekapan::create([
            'user_id' => $user->id,
            'tanggal' => $tanggal,
            'status' => 'izin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin berhasil.',
            'data' => $izin,
        ], 201);

        // Simpan data presensi
        $absen = Presensi::create([
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

    // Fungsi untuk menghitung jarak menggunakan rumus haversine
    private function hitungJarak($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance; // dalam meter
    }
}
