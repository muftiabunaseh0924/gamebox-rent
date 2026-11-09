@extends('layouts.app')
@section('title', 'Dasbor')

@section('content')
<h2 class="font-headline text-3xl font-bold">Halo, {{ auth()->user()->name }} 👋</h2>
<p class="mt-1 text-on-surface-variant">Dasbor pelanggan. Pembayaran dan riwayat lengkap menyusul di L6.</p>

<div class="mt-8 grid gap-6 md:grid-cols-3">
    <div class="card">
        <p class="label-field">Email</p>
        <p class="font-headline text-lg">{{ auth()->user()->email }}</p>
    </div>
    <div class="card">
        <p class="label-field">Nomor HP</p>
        <p class="font-headline text-lg">{{ auth()->user()->no_hp ?? '-' }}</p>
    </div>
    <div class="card">
        <p class="label-field">Status Akun</p>
        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider text-emerald-300">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span> {{ auth()->user()->status }}
        </span>
    </div>
</div>

<div class="mt-10 flex items-center justify-between">
    <h3 class="font-headline text-2xl font-bold">Pesanan Terbaru</h3>
        <div class="flex flex-wrap gap-3">
        <a href="{{ route('booking.index') }}" class="btn-secondary !py-2">Semua Pesanan</a>
        <a href="{{ route('booking.create') }}" class="btn-primary !py-2">Booking Baru</a>
    </div>
</div>

<div class="mt-4 space-y-3">
    @forelse ($pesanan as $b)
        @php
            $status = $b->kedaluwarsa() ? 'Kedaluwarsa' : $b->status_booking;
            $kelas = match ($status) {
                'Menunggu Pembayaran' => 'bg-amber-500/15 text-amber-300',
                'Dibayar', 'Sedang Disewa' => 'bg-emerald-500/15 text-emerald-300',
                'Selesai' => 'bg-primary-container/20 text-primary',
                default => 'bg-error-container/40 text-error',
            };
        @endphp
        <a href="{{ route('booking.show', $b) }}" class="card flex flex-wrap items-center justify-between gap-4 transition hover:border-cyan-400/40">
            <div>
                <p class="font-headline font-semibold tracking-wider">{{ $b->kode_booking }}</p>
                <p class="text-sm text-on-surface-variant">{{ $b->console->nama_tampil }} · {{ $b->waktu_mulai->format('d-m-Y H:i') }} ({{ $b->durasi_jam }} jam)</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="font-headline font-semibold text-secondary">{{ \App\Models\Booking::rupiah($b->total_harga) }}</span>
                <span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider {{ $kelas }}">{{ $status }}</span>
            </div>
        </a>
    @empty
        <div class="card text-center text-on-surface-variant">Belum ada pesanan. Mulai dengan memilih unit di beranda.</div>
    @endforelse
</div>
@endsection