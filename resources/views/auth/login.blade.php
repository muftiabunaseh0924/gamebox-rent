@extends('layouts.auth')
@section('title', 'Masuk')

@section('content')
<h1 class="font-headline text-2xl font-bold">Masuk</h1>
<p class="mt-1 text-sm text-on-surface-variant">Masuk untuk menyewa konsol dan melihat riwayat pesanan.</p>

<form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5" novalidate>
    @csrf
    <div>
        <label for="email" class="label-field">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
               autocomplete="email" placeholder="nama@email.com" class="input-field">
        @error('email') <p class="error-text">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password" class="label-field">Kata Sandi</label>
        <input id="password" type="password" name="password" required autocomplete="current-password"
               placeholder="••••••••" class="input-field">
        @error('password') <p class="error-text">{{ $message }}</p> @enderror
    </div>

    <label class="flex items-center gap-2 text-sm text-on-surface-variant">
        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-500 bg-surface-container-lowest accent-primary-container">
        Ingat saya
    </label>

    <button type="submit" class="btn-primary w-full">Masuk</button>
</form>
@endsection

@section('footer')
    Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-secondary hover:underline">Daftar</a>
@endsection