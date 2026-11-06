@extends('layouts.auth')
@section('title', 'Daftar')

@section('content')
<h1 class="font-headline text-2xl font-bold">Buat Akun</h1>
<p class="mt-1 text-sm text-on-surface-variant">Daftar gratis untuk mulai menyewa PS4 &amp; PS5.</p>

<form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5" novalidate>
    @csrf
    <div>
        <label for="name" class="label-field">Nama Lengkap</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
               autocomplete="name" placeholder="Nama Anda" class="input-field">
        @error('name') <p class="error-text">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="label-field">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required
               autocomplete="email" placeholder="nama@email.com" class="input-field">
        @error('email') <p class="error-text">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="no_hp" class="label-field">Nomor HP</label>
        <input id="no_hp" type="tel" name="no_hp" value="{{ old('no_hp') }}" required
               autocomplete="tel" placeholder="081234567890" class="input-field">
        @error('no_hp') <p class="error-text">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password" class="label-field">Kata Sandi</label>
        <input id="password" type="password" name="password" required autocomplete="new-password"
               placeholder="Minimal 8 karakter, huruf + angka" class="input-field">
        @error('password') <p class="error-text">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password_confirmation" class="label-field">Ulangi Kata Sandi</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required
               autocomplete="new-password" placeholder="Ketik ulang kata sandi" class="input-field">
    </div>

    <button type="submit" class="btn-primary w-full">Daftar</button>
</form>
@endsection

@section('footer')
    Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-secondary hover:underline">Masuk</a>
@endsection