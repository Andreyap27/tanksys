<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('jwt_token')) {
            try {
                JWTAuth::setToken(session('jwt_token'))->authenticate();
                return redirect()->route('dashboard');
            } catch (\Exception $e) {
                session()->forget('jwt_token');
            }
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return back()->withErrors(['username' => 'Username atau password salah.'])->withInput();
            }
        } catch (JWTException $e) {
            return back()->withErrors(['username' => 'Tidak dapat membuat token, coba lagi.']);
        }

        session(['jwt_token' => $token]);

        $user = JWTAuth::setToken($token)->authenticate();

        if ($user->reset_password) {
            return redirect()->route('password.reset.form');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        try {
            if ($token = session('jwt_token')) {
                JWTAuth::setToken($token)->invalidate();
            }
        } catch (\Exception $e) {
            // token sudah expired, tidak perlu invalidate
        }

        session()->forget('jwt_token');

        return redirect()->route('login');
    }

    public function showResetForm()
    {
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = auth()->user();
        $user->update([
            'password'       => $request->password,
            'reset_password' => false,
        ]);

        return redirect()->route('dashboard')->with('success', 'Password berhasil diubah.');
    }
}
