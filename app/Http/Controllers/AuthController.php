<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
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

        $adminEmail = config('app.admin_email');
        $adminPassword = config('app.admin_password');

        if (!$adminEmail || !$adminPassword) {
            return back()->withErrors(['email' => 'Konfigurasi admin belum diatur.'])->withInput();
        }

        if (
            $credentials['email'] !== $adminEmail ||
            !Hash::check($credentials['password'], $adminPassword)
        ) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
        }

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
