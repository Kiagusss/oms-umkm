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

        $user = \App\Models\User::where('email', $credentials['email'])->first();
        $authenticated = false;

        if ($user) {
            if ($user->is_active && Hash::check($credentials['password'], $user->password)) {
                auth()->login($user);
                session([
                    'admin_authenticated' => true,
                    'admin_user_id'       => $user->id,
                    'selected_branch_id'  => $user->branch_id,
                ]);
                $authenticated = true;
            }
        } else {
            // Fallback to config admin credentials
            $adminEmail = config('app.admin_email');
            $adminPassword = config('app.admin_password');

            if ($adminEmail && $adminPassword && $credentials['email'] === $adminEmail && Hash::check($credentials['password'], $adminPassword)) {
                $owner = \App\Models\User::where('email', $adminEmail)->first();
                if ($owner) {
                    auth()->login($owner);
                    session(['admin_user_id' => $owner->id, 'selected_branch_id' => $owner->branch_id]);
                }
                session(['admin_authenticated' => true]);
                $authenticated = true;
            }
        }

        if (! $authenticated) {
            RateLimiter::hit($throttleKey, self::DECAY_MINUTES * 60);
            return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
        }

        // Sukses — bersihkan rate limit counter
        RateLimiter::clear($throttleKey);
        session()->regenerate();

        \App\Models\AuditLog::log(
            auth()->user() ?? $user,
            'auth.login',
            "User {$credentials['email']} logged in successfully."
        );

        return redirect()->intended('/admin');
    }

    public function switchBranch(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        session(['selected_branch_id' => $validated['branch_id'] ?? null]);

        return back()->with('success', 'Cabang aktif berhasil diubah.');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->forget(['admin_authenticated', 'admin_user_id', 'selected_branch_id']);
        $request->session()->regenerate();

        return redirect('/admin/login');
    }
}
