<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Izin;

class AdminController extends Controller
{
    public function rekapAbsensi(Request $request)
    {
        $tanggal = $request->input('tanggal') ?? date('Y-m-d');

        $absenMasuk = Presensi::whereDate('waktu_presensi', $tanggal)->with('user')->get();
        $absenPulang = Presensi::whereDate('waktu_pulang', $tanggal)->with('user')->get();
        $izin = Izin::whereDate('tanggal', $tanggal)->with('user')->get();

        return response()->json([
            'tanggal' => $tanggal,
            'absen_masuk' => $absenMasuk,
            'absen_pulang' => $absenPulang,
            'izin' => $izin
        ]);
    }
}
