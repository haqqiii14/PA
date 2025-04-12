<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'device_id' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'device_id' => $request->device_id
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'user' => $user,
            'token' => $token
        ]);
    }

    public function login(Request $request)
{
    // Cari user berdasarkan email
    $user = User::where('email', $request->email)->first();

    // Jika user tidak ditemukan atau password salah
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'status' => false,
            'message' => 'Email atau password salah.'
        ], 401);
    }

    // Jika user belum punya device_id, set sekarang
    if ($user->device_id === null) {
        $user->device_id = $request->device_id;
        $user->save();
    }

    // Jika device_id tidak cocok
    if ($user->device_id !== $request->device_id) {
        return response()->json([
            'status' => false,
            'message' => 'Login hanya dari perangkat yang terdaftar.'
        ], 403);
    }

    // Buat token baru
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => true,
        'user' => $user,
        'token' => $token
    ]);
}
}
