@extends('layouts.auth')
@section('title', 'Masuk Admin')

@section('content')
<span class="inline-flex items-center gap-2 rounded-full bg-tertiary/10 px-3 py-1 font-headline text-xs font-semibold uppercase tracking-widest text-tertiary">
    <span class="material-symbols-outlined text-[16px]">admin_panel_settings</span> Panel Admin
</span>
<h1 class="mt-3 font-headline text-2xl font-bold">Masuk Admin</h1>
<p class="mt-1 text-sm text-on-surface-variant">Halaman ini khusus pengelola GameBox Rent.</p>

<form method="POST" action="{{ route('admin.login') }}" class="mt-6 space-y-5" novalidate>
    @csrf
    <div>
        <label for="email" class="label-field">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
               autocomplete="username" placeholder="admin@gamebox.test" class="input-field">
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
    <a href="{{ route('home') }}" class="font-semibold text-secondary hover:underline">&larr; Kembali ke beranda</a>
@endsection