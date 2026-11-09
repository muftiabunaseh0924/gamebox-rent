@extends('layouts.app')
@section('title', 'Pesanan '.$booking->kode_booking)

@section('content')
@php
    $kedaluwarsa = $booking->kedaluwarsa();
    $bisaBayar = $booking->status_booking === 'Menunggu Pembayaran' && ! $kedaluwarsa;
    $sudahBayar = in_array($booking->status_booking, ['Dibayar', 'Sedang Disewa', 'Selesai'], true);
    $rp = fn ($n) => \App\Models\Booking::rupiah($n);
    $riwayat = $booking->payments->sortByDesc('id');
    $lunas = $riwayat->firstWhere('status_pembayaran', 'Berhasil');
@endphp

<div class="mx-auto max-w-4xl">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="font-headline text-xs font-semibold uppercase tracking-widest text-outline">Kode Booking</p>
            <h2 class="font-headline text-3xl font-bold tracking-wider">{{ $booking->kode_booking }}</h2>
        </div>
        <span class="rounded-full px-4 py-1.5 font-headline text-xs font-semibold uppercase tracking-wider {{ $booking->kelasStatus() }}">{{ $booking->statusTampil() }}</span>
    </div>

    @if ($sudahBayar && $booking->kode_konfirmasi)
        <div class="mt-8 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-6 text-center">
            <p class="font-headline text-xs font-semibold uppercase tracking-widest text-emerald-300">Kode Konfirmasi Serah Terima</p>
            <p class="mt-2 font-headline text-5xl font-bold tracking-[0.3em] text-secondary">{{ $booking->kode_konfirmasi }}</p>
            <p class="mt-3 text-sm text-on-surface-variant">
                Tunjukkan kode ini ke admin saat
                {{ $booking->metode_serah === 'Diantar' ? 'unit diantar ke alamatmu' : 'mengambil unit di tempat sewa' }}.
            </p>
        </div>
    @endif

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
                @if ($lunas)
                    <div><dt class="text-outline">Metode Bayar</dt><dd class="font-semibold">{{ $lunas->metode ?? '-' }}</dd></div>
                    <div><dt class="text-outline">Dibayar Pada</dt><dd class="font-semibold">{{ $lunas->waktu_bayar?->format('d-m-Y H:i') ?? '-' }}</dd></div>
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

            @if ($bisaBayar)
                <div class="rounded-lg bg-amber-500/10 px-4 py-3 text-sm text-amber-300">
                    Selesaikan pembayaran dalam
                    <span id="hitung-mundur" data-batas="{{ $booking->batas_bayar->toIso8601String() }}" class="font-headline font-bold">--:--</span>
                </div>
                <button type="button" id="btn-bayar" class="btn-primary w-full disabled:cursor-wait disabled:opacity-60">
                    <span class="material-symbols-outlined text-[20px]">payments</span> Bayar Sekarang
                </button>
                <button type="button" id="btn-cek" class="btn-secondary w-full">Cek Status Pembayaran</button>
                <p id="pesan-bayar" class="hidden rounded-lg bg-surface-container-high px-4 py-3 text-sm"></p>

                @unless (config('midtrans.is_production'))
                    <div class="rounded-lg border border-primary-container/30 px-4 py-3 text-xs text-on-surface-variant">
                        <p class="font-headline font-semibold uppercase tracking-wider text-secondary">Mode uji (Sandbox)</p>
                        <p class="mt-1">Tidak ada uang sungguhan. Kartu uji: <span class="font-semibold text-on-surface">4811 1111 1111 1114</span>, CVV <span class="font-semibold text-on-surface">123</span>, kedaluwarsa bulan dan tahun apa saja di masa depan, OTP <span class="font-semibold text-on-surface">112233</span>.</p>
                    </div>
                @endunless
            @elseif ($kedaluwarsa)
                <div class="rounded-lg bg-error-container/30 px-4 py-3 text-sm text-error">
                    Batas waktu pembayaran sudah lewat. Jadwal dilepas, silakan buat booking baru.
                </div>
                <a href="{{ route('booking.create', ['unit' => $booking->console_id]) }}" class="btn-primary w-full">Booking Lagi</a>
            @endif
        </div>
    </div>

    @if ($riwayat->isNotEmpty())
        <div class="card mt-6">
            <h3 class="font-headline text-lg font-bold">Riwayat Pembayaran</h3>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wider text-outline">
                        <tr><th class="py-2 pr-4">Order</th><th class="py-2 pr-4">Metode</th><th class="py-2 pr-4">Nominal</th><th class="py-2">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-surface-variant/40">
                        @foreach ($riwayat as $p)
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">{{ $p->order_id }}</td>
                                <td class="py-2 pr-4">{{ $p->metode ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ $rp($p->nominal) }}</td>
                                <td class="py-2">{{ $p->status_pembayaran }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mt-6 flex flex-wrap gap-4 text-sm">
        <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-2 text-secondary hover:underline">
            <span class="material-symbols-outlined text-[18px]">receipt_long</span> Semua pesanan
        </a>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-secondary hover:underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke dasbor
        </a>
    </div>
</div>

@if ($bisaBayar)
<script src="{{ config('midtrans.snap_js_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
(function () {
    var el = document.getElementById('hitung-mundur');
    var batas = new Date(el.dataset.batas).getTime();
    function tick() {
        var s = Math.floor((batas - Date.now()) / 1000);
        if (s <= 0) { el.textContent = 'Waktu habis. Muat ulang halaman.'; return; }
        el.textContent = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
        setTimeout(tick, 1000);
    }
    tick();

    var tombolBayar = document.getElementById('btn-bayar');
    var tombolCek = document.getElementById('btn-cek');
    var pesan = document.getElementById('pesan-bayar');
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var urlBayar = @json(route('booking.bayar', $booking));
    var urlCek = @json(route('booking.cek-status', $booking));

    function tampil(teks, galat) {
        pesan.textContent = teks;
        pesan.className = 'rounded-lg px-4 py-3 text-sm ' + (galat ? 'bg-error-container/30 text-error' : 'bg-surface-container-high text-on-surface');
    }

    async function kirim(url) {
        var r = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
        var d = {};
        try { d = await r.json(); } catch (e) { d = {}; }
        return { ok: r.ok, data: d };
    }

    async function cek() {
        tampil('Memeriksa status pembayaran...', false);
        try {
            var h = await kirim(urlCek);
            if (!h.ok) { tampil(h.data.message || 'Gagal memeriksa status.', true); return; }
            tampil(h.data.pesan, false);
            if (h.data.lunas) { setTimeout(function () { window.location.reload(); }, 900); }
        } catch (e) {
            tampil('Terjadi kesalahan jaringan. Coba lagi.', true);
        }
    }

    tombolBayar.addEventListener('click', async function () {
        if (typeof window.snap === 'undefined') {
            tampil('Popup pembayaran belum termuat. Pastikan koneksi internet aktif dan Client Key terisi, lalu muat ulang halaman.', true);
            return;
        }
        tombolBayar.disabled = true;
        tampil('Menyiapkan pembayaran...', false);
        try {
            var h = await kirim(urlBayar);
            if (!h.ok || !h.data.token) { tampil(h.data.message || 'Gagal menyiapkan pembayaran.', true); return; }
            window.snap.pay(h.data.token, {
                onSuccess: function () { cek(); },
                onPending: function () { cek(); },
                onError: function () { tampil('Pembayaran gagal. Silakan coba lagi.', true); },
                onClose: function () { tampil('Popup ditutup. Klik "Bayar Sekarang" untuk melanjutkan, atau "Cek Status Pembayaran" bila sudah membayar.', false); }
            });
        } catch (e) {
            tampil('Terjadi kesalahan jaringan. Coba lagi.', true);
        } finally {
            tombolBayar.disabled = false;
        }
    });

    tombolCek.addEventListener('click', cek);
})();
</script>
@endif
@endsection