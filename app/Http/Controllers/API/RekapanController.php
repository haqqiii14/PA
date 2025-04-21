<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;
use App\Models\Rekapan;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\User;



class RekapanController extends Controller
{

    public function getRekapan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'nullable|date',
            'status' => 'nullable|in:presensi,izin,tidak_hadir',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $status = $request->input('status');

        // Ambil semua pegawai
        $users = User::where('role', 'pegawai')->get();
        $report = [];

        foreach ($users as $user) {
            $rekapan = Rekapan::where('user_id', $user->id)
                ->where('tanggal', $tanggal)
                ->first();

            $statusText = $rekapan ? $rekapan->status : 'tidak_hadir';

            if ($status && $statusText !== $status) {
                continue; // Skip jika status tidak cocok dengan filter
            }

            $presensi = Presensi::where('user_id', $user->id)
                ->where('tanggal', $tanggal)
                ->first();

            $report[] = [
                'user_id' => $user->id,
                'nama' => $user->name,
                'email' => $user->email,
                'status' => $statusText,
                'tanggal' => $tanggal,
                'waktu_presensi' => $presensi ? $presensi->waktu_presensi : null,
                'waktu_pulang' => $presensi ? $presensi->waktu_pulang : null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $report,
        ], 200);
    }
}
