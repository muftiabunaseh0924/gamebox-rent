@extends('layouts.app')
@section('title', 'Dasbor')

@section('content')
<h2 class="anim-in font-headline text-2xl font-bold sm:text-3xl">Halo, {{ auth()->user()->name }} &#128075;</h2>
<p class="anim-in mt-1 text-on-surface-variant" style="--d: 60ms">Ringkasan akun dan pesanan terbaru kamu.</p>

<div class="mt-8 grid gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
    <div class="card anim-in min-w-0 !p-5 sm:!p-6" style="--d: 100ms">
        <p class="label-field">Email</p>
        <p class="break-all font-headline text-lg">{{ auth()->user()->email }}</p>
    </div>
    <div class="card anim-in min-w-0 !p-5 sm:!p-6" style="--d: 170ms">
        <p class="label-field">Nomor HP</p>
        <p class="break-words font-headline text-lg">{{ auth()->user()->no_hp ?? '-' }}</p>
    </div>
    <div class="card anim-in min-w-0 !p-5 sm:col-span-2 sm:!p-6 lg:col-span-1" style="--d: 240ms">
        <p class="label-field">Status Akun</p>
        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider text-emerald-300">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span> {{ auth()->user()->status }}
        </span>
    </div>
</div>

<div class="mt-10 flex flex-wrap items-center justify-between gap-3">
    <h3 class="font-headline text-xl font-bold sm:text-2xl">Pesanan Terbaru</h3>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('booking.index') }}" class="btn-secondary !py-2">Semua Pesanan</a>
        <a href="{{ route('booking.create') }}" class="btn-primary !py-2">Booking Baru</a>
    </div>
</div>

<div class="mt-4 space-y-3">
    @forelse ($pesanan as $b)
        @include('booking._kartu', ['b' => $b, 'i' => $loop->index])
    @empty
        <div class="card anim-in text-center text-on-surface-variant">Belum ada pesanan. Mulai dengan memilih unit di beranda.</div>
    @endforelse
</div>
@endsection