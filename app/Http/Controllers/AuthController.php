<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /**
     * Limit login attempt: 5 gagal per 15 menit per IP+email.
     * Anti brute force sederhana — pakai Laravel built-in RateLimiter.
     */
    private const MAX_ATTEMPTS = 5;
    private const DECAY_MINUTES = 15;

    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Rate limit key: IP + email (supaya 1 IP tidak lockout akun lain)
        $throttleKey = 'login:' . $request->ip() . ':' . strtolower($credentials['email']);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ])->withInput();
        }

        $adminEmail = config('app.admin_email');
        $adminPassword = config('app.admin_password');

        if (!$adminEmail || !$adminPassword) {
            return back()->withErrors(['email' => 'Konfigurasi admin belum diatur.'])->withInput();
        }

        $valid = $credentials['email'] === $adminEmail
            && Hash::check($credentials['password'], $adminPassword);

        if (! $valid) {
            RateLimiter::hit($throttleKey, self::DECAY_MINUTES * 60);
            return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
        }

        // Sukses — bersihkan rate limit counter
        RateLimiter::clear($throttleKey);

        session(['admin_authenticated' => true]);
        session()->regenerate();

        return redirect()->intended('/admin');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_authenticated');
        $request->session()->regenerate();

        return redirect('/admin/login');
    }
}
