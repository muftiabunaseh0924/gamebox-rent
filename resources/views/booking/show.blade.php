@extends('layouts.app')
@section('title', 'Pesanan '.$booking->kode_booking)

@section('content')
@php
    $kedaluwarsa = $booking->kedaluwarsa();
    $status = $kedaluwarsa ? 'Kedaluwarsa' : $booking->status_booking;
    $kelas = match ($status) {
        'Menunggu Pembayaran' => 'bg-amber-500/15 text-amber-300',
        'Dibayar', 'Sedang Disewa' => 'bg-emerald-500/15 text-emerald-300',
        'Selesai' => 'bg-primary-container/20 text-primary',
        default => 'bg-error-container/40 text-error',
    };
    $rp = fn ($n) => \App\Models\Booking::rupiah($n);
@endphp

<div class="mx-auto max-w-4xl">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="font-headline text-xs font-semibold uppercase tracking-widest text-outline">Kode Booking</p>
            <h2 class="font-headline text-3xl font-bold tracking-wider">{{ $booking->kode_booking }}</h2>
        </div>
        <span class="rounded-full px-4 py-1.5 font-headline text-xs font-semibold uppercase tracking-wider {{ $kelas }}">{{ $status }}</span>
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-5">
        <div class="card space-y-4 md:col-span-3">
            <h3 class="font-headline text-lg font-bold">Detail Sewa</h3>
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-outline">Unit</dt><dd class="font-semibold">{{ $booking->console->kode_unit }} · {{ $booking->console->nama_tampil }}</dd></div>
                <div><dt class="text-outline">Durasi</dt><dd class="font-semibold">{{ $booking->durasi_jam }} jam</dd></div>
                <div><dt class="text-outline">Mulai</dt><dd class="font-semibold">{{ $booking->waktu_mulai->format('d-m-Y H:i') }}</dd></div>
                <div><dt class="text-outline">Selesai</dt><dd class="font-semibold">{{ $booking->waktu_selesai->format('d-m-Y H:i') }}</dd></div>
                <div><dt class="text-outline">Cara Penerimaan</dt><dd class="font-semibold">{{ $booking->metode_serah }}</dd></div>
                <div><dt class="text-outline">Stik Tambahan</dt><dd class="font-semibold">{{ $booking->jumlah_stik }}</dd></div>
                @if ($booking->alamat_pengiriman)
                    <div class="sm:col-span-2"><dt class="text-outline">Alamat Pengiriman</dt><dd class="font-semibold">{{ $booking->alamat_pengiriman }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="card space-y-4 md:col-span-2">
            <h3 class="font-headline text-lg font-bold">Rincian Biaya</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-on-surface-variant">Paket {{ $booking->durasi_jam }} jam</span><span>{{ $rp($booking->harga_paket) }}</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">Stik ({{ $booking->jumlah_stik }} x {{ $rp($booking->harga_stik) }})</span><span>{{ $rp($booking->jumlah_stik * $booking->harga_stik) }}</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">Pengiriman</span><span>{{ $booking->biaya_pengiriman > 0 ? $rp($booking->biaya_pengiriman) : 'Gratis' }}</span></div>
            </div>
            <div class="flex items-end justify-between border-t border-surface-variant/40 pt-4">
                <span class="text-on-surface-variant">Total</span>
                <span class="font-headline text-2xl font-bold text-secondary">{{ $rp($booking->total_harga) }}</span>
            </div>

            @if ($booking->status_booking === 'Menunggu Pembayaran' && ! $kedaluwarsa)
                <div class="rounded-lg bg-amber-500/10 px-4 py-3 text-sm text-amber-300">
                    Selesaikan pembayaran dalam
                    <span id="hitung-mundur" data-batas="{{ $booking->batas_bayar->toIso8601String() }}" class="font-headline font-bold">--:--</span>
                </div>
                <span class="btn-secondary w-full cursor-not-allowed opacity-70" title="Pembayaran dibuka di tahap berikutnya">Bayar Segera Dibuka</span>
            @elseif ($kedaluwarsa)
                <div class="rounded-lg bg-error-container/30 px-4 py-3 text-sm text-error">
                    Batas waktu pembayaran sudah lewat. Jadwal dilepas, silakan buat booking baru.
                </div>
                <a href="{{ route('booking.create', ['unit' => $booking->console_id]) }}" class="btn-primary w-full">Booking Lagi</a>
            @endif
        </div>
    </div>

    <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center gap-2 text-sm text-secondary hover:underline">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke dasbor
    </a>
</div>

<script>
(function () {
    var el = document.getElementById('hitung-mundur');
    if (!el) { return; }
    var batas = new Date(el.dataset.batas).getTime();
    function tick() {
        var s = Math.floor((batas - Date.now()) / 1000);
        if (s <= 0) { el.textContent = 'Waktu habis. Muat ulang halaman.'; return; }
        el.textContent = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
        setTimeout(tick, 1000);
    }
    tick();
})();
</script>
@endsection