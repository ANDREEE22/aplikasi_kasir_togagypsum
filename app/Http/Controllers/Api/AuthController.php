<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi input dari Flutter
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Cek apakah email & password benar
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Login gagal, cek email/password'
            ], 401);
        }

        // 3. Ambil data user
        $user = User::where('email', $request->email)->firstOrFail();

        // 4. BUAT TOKEN (Ini kuncinya!)
        // 'auth_token' bisa diganti nama apa saja
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Kirim respon JSON ke Flutter
        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil!',
            'user'    => $user,
            'token'   => $token, // <-- Token ini yang nanti disimpan di HP
        ]);
    }
    
    // Fungsi Logout untuk Flutter
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Token dihapus, berhasil logout']);
    }
}