<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'required' => ':attribute wajib diisi.',
            'email' => 'Format :attribute tidak valid.',
        ], [
            'email' => 'Email',
            'password' => 'Kata sandi',
        ]);

        $attempt = [
            'email' => Str::lower(trim($credentials['email'])),
            'password' => $credentials['password'],
        ];

        if (! Auth::attempt($attempt, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi salah.']);
        }

        $user = Auth::user();

        if ($user->role !== 'admin') {
            $this->keluarkan($request);
            throw ValidationException::withMessages(['email' => 'Akun ini tidak memiliki akses admin.']);
        }

        if (! $user->isActive()) {
            $this->keluarkan($request);
            throw ValidationException::withMessages(['email' => 'Akun Anda tidak aktif.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->keluarkan($request);

        return redirect()->route('admin.login')->with('status', 'Anda sudah keluar.');
    }

    private function keluarkan(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}