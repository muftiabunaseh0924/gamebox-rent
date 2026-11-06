@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
<section class="relative overflow-hidden rounded-xl bg-surface-container-lowest p-8 md:p-12">
    <div class="pointer-events-none absolute inset-0 opacity-40"
         style="background: radial-gradient(circle at 70% 20%, rgba(47,107,255,.25), transparent 50%), radial-gradient(circle at 20% 80%, rgba(93,230,255,.18), transparent 55%);"></div>
    <div class="relative z-10 max-w-3xl">
        <span class="inline-flex items-center gap-2 rounded-full bg-surface-container-high/80 px-4 py-1 font-headline text-xs font-semibold uppercase tracking-widest text-secondary">
            <span class="h-2 w-2 animate-pulse rounded-full bg-secondary"></span> Rental PlayStation
        </span>
        <h2 class="mt-4 font-headline text-4xl font-bold leading-tight tracking-tight md:text-5xl">
            Sewa PS4 &amp; PS5 tanpa ribet, booking instan.
        </h2>
        <p class="mt-4 text-lg text-on-surface-variant">
            Pilih durasi 6 sampai 24 jam, bayar online, lalu ambil unit dengan kode konfirmasi.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            @guest
                <a href="{{ route('register') }}" class="btn-primary">Daftar Sekarang</a>
                <a href="{{ route('login') }}" class="btn-secondary">Masuk</a>
            @else
                <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('dashboard') }}" class="btn-primary">Ke Dasbor</a>
            @endguest
        </div>
        <p class="mt-6 text-sm text-outline">Katalog unit menyusul di langkah L4.</p>
    </div>
</section>
@endsection