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
    <div class="anim-in flex flex-wrap items-center justify-between gap-4">
        <div class="min-w-0">
            <p class="font-headline text-xs font-semibold uppercase tracking-widest text-outline">Kode Booking</p>
            <h2 class="break-all font-headline text-2xl font-bold tracking-wider sm:text-3xl">{{ $booking->kode_booking }}</h2>
        </div>
        <span class="rounded-full px-4 py-1.5 font-headline text-xs font-semibold uppercase tracking-wider {{ $booking->kelasStatus() }}">{{ $booking->statusTampil() }}</span>
    </div>

    @if ($sudahBayar && $booking->kode_konfirmasi)
        <div class="anim-pop mt-8 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-5 text-center sm:p-6" style="--d: 100ms">
            <p class="font-headline text-xs font-semibold uppercase tracking-widest text-emerald-300">Kode Konfirmasi Serah Terima</p>
            <p class="mt-2 break-all font-headline text-3xl font-bold tracking-[0.2em] text-secondary min-[400px]:text-4xl sm:text-5xl sm:tracking-[0.3em]">{{ $booking->kode_konfirmasi }}</p>
            <p class="mt-3 text-sm text-on-surface-variant">
                Tunjukkan kode ini ke admin saat
                {{ $booking->metode_serah === 'Diantar' ? 'unit diantar ke alamatmu' : 'mengambil unit di tempat sewa' }}.
            </p>
        </div>
    @endif

    <div class="mt-8 grid gap-6 md:grid-cols-5">
        <div class="card anim-in space-y-4 !p-5 sm:!p-6 md:col-span-3" style="--d: 140ms">
            <h3 class="font-headline text-lg font-bold">Detail Sewa</h3>
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-outline">Unit</dt><dd class="font-semibold">{{ $booking->console->kode_unit }} - {{ $booking->console->nama_tampil }}</dd></div>
                <div><dt class="text-outline">Durasi</dt><dd class="font-semibold">{{ $booking->durasi_jam }} jam</dd></div>
                <div><dt class="text-outline">Mulai</dt><dd class="font-semibold">{{ $booking->waktu_mulai->format('d-m-Y H:i') }}</dd></div>
                <div><dt class="text-outline">Selesai</dt><dd class="font-semibold">{{ $booking->waktu_selesai->format('d-m-Y H:i') }}</dd></div>
                <div><dt class="text-outline">Cara Penerimaan</dt><dd class="font-semibold">{{ $booking->metode_serah }}</dd></div>
                <div><dt class="text-outline">Stik Tambahan</dt><dd class="font-semibold">{{ $booking->jumlah_stik }}</dd></div>
                @if ($booking->alamat_pengiriman)
                    <div class="sm:col-span-2"><dt class="text-outline">Alamat Pengiriman</dt><dd class="break-words font-semibold">{{ $booking->alamat_pengiriman }}</dd></div>
                @endif
                @if ($lunas)
                    <div><dt class="text-outline">Metode Bayar</dt><dd class="font-semibold">{{ $lunas->metode ?? '-' }}</dd></div>
                    <div><dt class="text-outline">Dibayar Pada</dt><dd class="font-semibold">{{ $lunas->waktu_bayar?->format('d-m-Y H:i') ?? '-' }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="card anim-in space-y-4 !p-5 sm:!p-6 md:col-span-2" style="--d: 220ms">
            <h3 class="font-headline text-lg font-bold">Rincian Biaya</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between gap-3"><span class="text-on-surface-variant">Paket {{ $booking->durasi_jam }} jam</span><span>{{ $rp($booking->harga_paket) }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-on-surface-variant">Stik ({{ $booking->jumlah_stik }} x {{ $rp($booking->harga_stik) }})</span><span>{{ $rp($booking->jumlah_stik * $booking->harga_stik) }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-on-surface-variant">Pengiriman</span><span>{{ $booking->biaya_pengiriman > 0 ? $rp($booking->biaya_pengiriman) : 'Gratis' }}</span></div>
            </div>
            <div class="flex items-end justify-between gap-3 border-t border-surface-variant/40 pt-4">
                <span class="text-on-surface-variant">Total</span>
                <span class="font-headline text-2xl font-bold text-secondary">{{ $rp($booking->total_harga) }}</span>
            </div>

            @if ($lunas && $sudahBayar)
                <a href="{{ route('booking.invoice', $booking) }}" class="btn-secondary w-full">
                    <span class="material-symbols-outlined text-[20px]">download</span> Unduh Invoice (PDF)
                </a>
            @endif

            @if ($bisaBayar)
                <div id="banner-bayar" class="rounded-lg bg-amber-500/10 px-4 py-3 text-sm text-amber-300">
                    Selesaikan pembayaran dalam
                    <span id="hitung-mundur" data-batas="{{ $booking->batas_bayar->toIso8601String() }}" class="font-headline font-bold">--:--</span>
                </div>
                <button type="button" id="btn-bayar" class="btn-primary w-full disabled:cursor-wait disabled:opacity-60">
                    <span class="material-symbols-outlined text-[20px]">payments</span> Bayar Sekarang
                </button>
                <button type="button" id="btn-cek" class="btn-secondary w-full disabled:cursor-wait disabled:opacity-60">Cek Status Pembayaran</button>
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

    {{-- L9: pelacakan pengantaran (hanya pesanan Diantar yang sudah dibayar) --}}
    @if ($booking->metode_serah === 'Diantar' && $sudahBayar)
        @include('booking._pengiriman')
    @endif

    @if ($riwayat->isNotEmpty())
        <div class="card anim-in mt-6 !p-5 sm:!p-6" style="--d: 300ms">
            <h3 class="font-headline text-lg font-bold">Riwayat Pembayaran</h3>

            {{-- HP: daftar kartu --}}
            <div class="mt-3 space-y-3 md:hidden">
                @foreach ($riwayat as $p)
                    <div class="rounded-lg border border-surface-variant/40 bg-surface-container-lowest p-3 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <p class="min-w-0 break-all font-mono text-xs">{{ $p->order_id }}</p>
                            <span class="shrink-0 text-xs font-semibold">{{ $p->status_pembayaran }}</span>
                        </div>
                        <div class="mt-2 flex justify-between gap-3 text-on-surface-variant">
                            <span>{{ $p->metode ?? '-' }}</span>
                            <span class="font-semibold text-on-surface">{{ $rp($p->nominal) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Tablet dan desktop: tabel --}}
            <div class="mt-3 hidden overflow-x-auto md:block">
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

    <div class="mt-6 flex flex-wrap gap-x-6 gap-y-3 text-sm">
        <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-2 py-1 text-secondary hover:underline">
            <span class="material-symbols-outlined text-[18px]">receipt_long</span> Semua pesanan
        </a>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 py-1 text-secondary hover:underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke dasbor
        </a>
    </div>
</div>

@if ($bisaBayar)
<script src="{{ config('midtrans.snap_js_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
(function () {
    var el = document.getElementById('hitung-mundur');
    var banner = document.getElementById('banner-bayar');
    var batas = new Date(el.dataset.batas).getTime();

    // Hitung mundur: saat sisa waktu 2 menit atau kurang, banner berubah merah dan berdenyut.
    function tick() {
        var s = Math.floor((batas - Date.now()) / 1000);
        if (s <= 0) {
            el.textContent = 'Waktu habis. Muat ulang halaman.';
            banner.classList.remove('animate-pulse');
            return;
        }
        if (s <= 120 && !banner.dataset.mendesak) {
            banner.dataset.mendesak = '1';
            banner.classList.remove('bg-amber-500/10', 'text-amber-300');
            banner.classList.add('bg-error-container/30', 'text-error', 'animate-pulse');
        }
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
    var htmlBayar = tombolBayar.innerHTML;
    var spinner = '<span class="material-symbols-outlined animate-spin text-[20px]">progress_activity</span> Memproses...';

    function tampil(teks, galat, sukses) {
        pesan.textContent = '';
        if (sukses) {
            var ikon = document.createElement('span');
            ikon.className = 'material-symbols-outlined anim-pop mr-2 align-middle text-[22px] text-emerald-300';
            ikon.textContent = 'check_circle';
            pesan.appendChild(ikon);
        }
        pesan.appendChild(document.createTextNode(teks));
        pesan.className = 'rounded-lg px-4 py-3 text-sm ' + (galat ? 'bg-error-container/30 text-error' : 'bg-surface-container-high text-on-surface');
    }

    async function kirim(url) {
        var r = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
        var d = {};
        try { d = await r.json(); } catch (e) { d = {}; }
        return { ok: r.ok, data: d };
    }

    async function cek() {
        tombolCek.disabled = true;
        tampil('Memeriksa status pembayaran...', false);
        try {
            var h = await kirim(urlCek);
            if (!h.ok) { tampil(h.data.message || 'Gagal memeriksa status.', true); return; }
            tampil(h.data.pesan, false, h.data.lunas);
            if (h.data.lunas) { setTimeout(function () { window.location.reload(); }, 1400); }
        } catch (e) {
            tampil('Terjadi kesalahan jaringan. Coba lagi.', true);
        } finally {
            tombolCek.disabled = false;
        }
    }

    tombolBayar.addEventListener('click', async function () {
        if (typeof window.snap === 'undefined') {
            tampil('Popup pembayaran belum termuat. Pastikan koneksi internet aktif dan Client Key terisi, lalu muat ulang halaman.', true);
            return;
        }
        tombolBayar.disabled = true;
        tombolBayar.innerHTML = spinner;
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
            tombolBayar.innerHTML = htmlBayar;
        }
    });

    tombolCek.addEventListener('click', cek);
})();
</script>
@endif
@endsection