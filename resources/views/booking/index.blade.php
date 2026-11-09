@extends('layouts.app')
@section('title', 'Pesanan Saya')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="font-headline text-3xl font-bold">Pesanan Saya</h2>
            <p class="mt-1 text-on-surface-variant">Riwayat seluruh booking dan status pembayarannya.</p>
        </div>
        <a href="{{ route('booking.create') }}" class="btn-primary !py-2">Booking Baru</a>
    </div>

    <div class="mt-8 space-y-3">
        @forelse ($pesanan as $b)
            <a href="{{ route('booking.show', $b) }}" class="card flex flex-wrap items-center justify-between gap-4 transition hover:border-cyan-400/40">
                <div>
                    <p class="font-headline font-semibold tracking-wider">{{ $b->kode_booking }}</p>
                    <p class="text-sm text-on-surface-variant">{{ $b->console->nama_tampil }} · {{ $b->waktu_mulai->format('d-m-Y H:i') }} ({{ $b->durasi_jam }} jam)</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="font-headline font-semibold text-secondary">{{ \App\Models\Booking::rupiah($b->total_harga) }}</span>
                    <span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider {{ $b->kelasStatus() }}">{{ $b->statusTampil() }}</span>
                </div>
            </a>
        @empty
            <div class="card text-center text-on-surface-variant">Belum ada pesanan.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $pesanan->links() }}</div>
</div>
@endsection