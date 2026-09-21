<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect(Auth::user()->isAdmin() ? route('admin.dashboard') : route('checkin'));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            return back()->withErrors(['username' => 'Akun kamu sudah dinonaktifkan.']);
        }

        $request->session()->regenerate();

        return redirect(Auth::user()->isAdmin() ? route('admin.dashboard') : route('checkin'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
