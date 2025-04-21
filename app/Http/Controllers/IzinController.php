<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Izin;

class IzinController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'alasan' => 'required|string|max:255',
        ]);

        $izin = Izin::create([
            'user_id' => Auth::id(),
            'alasan' => $request->alasan,
            'tanggal' => now()->toDateString(),
            'waktu' => now()->toTimeString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Izin berhasil dikirim',
            'data' => $izin
        ]);
    }


}
