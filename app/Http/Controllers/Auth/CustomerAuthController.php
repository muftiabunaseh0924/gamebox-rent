<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    private const MESSAGES = [
        'required' => ':attribute wajib diisi.',
        'email' => 'Format :attribute tidak valid.',
        'max' => ':attribute maksimal :max karakter.',
        'unique' => ':attribute sudah terdaftar.',
        'confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        'password.min' => 'Kata sandi minimal 8 karakter.',
        'password.regex' => 'Kata sandi harus mengandung huruf dan angka.',
        'no_hp.regex' => 'Nomor HP tidak valid. Contoh: 081234567890.',
    ];

    private const ATTRIBUTES = [
        'name' => 'Nama',
        'email' => 'Email',
        'no_hp' => 'Nomor HP',
        'password' => 'Kata sandi',
    ];

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'no_hp' => preg_replace('/[\s\-]/', '', (string) $request->input('no_hp')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'no_hp' => ['required', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/'],
        ], self::MESSAGES, self::ATTRIBUTES);

        // Seragamkan nomor HP ke format 08xxxxxxxxxx
        $noHp = preg_replace('/^(\+62|62)/', '0', $data['no_hp']);

        $user = new User();
        $user->name = trim($data['name']);
        $user->email = $data['email'];
        $user->no_hp = $noHp;
        $user->password = $data['password']; // di-hash otomatis (cast 'hashed')
        $user->role = 'user';
        $user->status = 'Aktif';
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Akun berhasil dibuat. Selamat datang!');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], self::MESSAGES, self::ATTRIBUTES);

        $attempt = [
            'email' => Str::lower(trim($credentials['email'])),
            'password' => $credentials['password'],
        ];

        if (! Auth::attempt($attempt, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi salah.']);
        }

        $user = Auth::user();

        if ($user->role !== 'user') {
            $this->keluarkan($request);
            throw ValidationException::withMessages([
                'email' => 'Akun ini bukan akun pelanggan. Admin silakan masuk lewat halaman admin.',
            ]);
        }

        if (! $user->isActive()) {
            $this->keluarkan($request);
            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak aktif. Hubungi admin.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->keluarkan($request);

        return redirect()->route('home')->with('status', 'Anda sudah keluar.');
    }

    private function keluarkan(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}