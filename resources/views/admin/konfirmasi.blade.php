@extends('layouts.admin')
@section('title', 'Konfirmasi Kode')
@section('heading', 'Konfirmasi Kode & Serah Terima')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">

    <div class="card">
        <h2 class="font-headline text-xl font-bold">Masukkan Kode Konfirmasi</h2>
        <p class="mt-1 text-sm text-on-surface-variant">Kode 6 karakter dari pelanggan (tanpa huruf O/I dan angka 0/1).</p>

        <form method="POST" action="{{ route('admin.konfirmasi.cek') }}" class="mt-4 flex flex-col gap-3 sm:flex-row" novalidate>
            @csrf
            <input type="text" name="kode" value="{{ old('kode', $kode ?? '') }}" maxlength="20" autofocus autocomplete="off"
                   placeholder="ABC123"
                   class="input-field font-headline text-xl uppercase tracking-[0.3em] sm:flex-1">
            <button type="submit" class="btn-primary sm:w-56">
                <span class="material-symbols-outlined text-[20px]">verified</span> Verifikasi Kode
            </button>
        </form>
        @error('kode') <p class="error-text">{{ $message }}</p> @enderror
    </div>

    @isset($booking)
    @php
        $rp = fn ($n) => \App\Models\Booking::rupiah($n);
        $bayar = $booking->payments->firstWhere('status_pembayaran', 'Berhasil');
        $toleransi = (int) (\Illuminate\Support\Facades\DB::table('settings')->where('key', 'toleransi_ambil_menit')->value('value') ?? 30);
    @endphp
    <div class="card space-y-6">
        @if ($hasil['bisa'])
            <div class="flex items-center gap-3 rounded-lg bg-emerald-500/10 px-4 py-3 text-emerald-300">
                <span class="material-symbols-outlined">task_alt</span>
                <div>
                    <p class="font-headline font-semibold uppercase tracking-wider">Valid · Pembayaran terkonfirmasi (Lunas)</p>
                    <p class="text-sm">Siap diserahkan.</p>
                </div>
            </div>
        @else
            <div class="flex items-start gap-3 rounded-lg {{ $hasil['bisa_dipaksa'] ? 'bg-amber-500/10 text-amber-300' : 'bg-error-container/30 text-error' }} px-4 py-3">
                <span class="material-symbols-outlined">{{ $hasil['bisa_dipaksa'] ? 'schedule' : 'error' }}</span>
                <div>
                    <p class="font-headline font-semibold uppercase tracking-wider">{{ $hasil['bisa_dipaksa'] ? 'Di luar jendela waktu' : 'Tidak dapat diserahkan' }}</p>
                    <p class="text-sm">{{ $hasil['pesan'] }}</p>
                </div>
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-3">
                <p class="label-field">Identitas Pelanggan</p>
                <p class="font-headline text-lg font-semibold">{{ $booking->user->name }}</p>
                <p class="text-sm text-on-surface-variant">{{ $booking->user->no_hp ?? '-' }}</p>
                <p class="text-sm text-on-surface-variant">{{ $booking->user->email }}</p>
                <p class="rounded-lg bg-surface-container-high px-3 py-2 text-xs text-on-surface-variant">Minta identitas asli (KTP/SIM) atas nama pemesan untuk verifikasi.</p>
            </div>

            <div class="space-y-3">
                <p class="label-field">Unit</p>
                <p class="font-headline text-lg font-semibold">{{ $booking->console->nama_tampil }}</p>
                <p class="text-sm text-on-surface-variant">{{ $booking->console->kode_unit }} · Status unit: {{ $booking->console->status }}</p>
                <p class="text-sm text-on-surface-variant">Stik tambahan: {{ $booking->jumlah_stik }}</p>
            </div>

            <div class="space-y-3">
                <p class="label-field">Jadwal Rental</p>
                <p class="text-sm"><span class="text-outline">Mulai:</span> <span class="font-semibold">{{ $booking->waktu_mulai->format('d-m-Y H:i') }}</span></p>
                <p class="text-sm"><span class="text-outline">Batas kembali:</span> <span class="font-semibold">{{ $booking->waktu_selesai->format('d-m-Y H:i') }}</span> ({{ $booking->durasi_jam }} jam)</p>
                <p class="text-sm"><span class="text-outline">Jendela serah terima:</span> {{ $booking->waktu_mulai->copy()->subMinutes(\App\Services\HandoverService::JENDELA_SEBELUM_MENIT)->format('d-m-Y H:i') }} s.d. {{ $booking->waktu_mulai->copy()->addMinutes($toleransi)->format('d-m-Y H:i') }}</p>
            </div>

            <div class="space-y-3">
                <p class="label-field">Pesanan & Pembayaran</p>
                <p class="text-sm"><span class="text-outline">Kode booking:</span> <span class="font-semibold">{{ $booking->kode_booking }}</span></p>
                <p class="text-sm"><span class="text-outline">Penerimaan:</span> <span class="font-semibold">{{ $booking->metode_serah }}</span></p>
                @if ($booking->alamat_pengiriman)
                    <p class="text-sm"><span class="text-outline">Alamat:</span> {{ $booking->alamat_pengiriman }}</p>
                @endif
                <p class="text-sm"><span class="text-outline">Total:</span> <span class="font-semibold">{{ $rp($booking->total_harga) }}</span> via {{ $bayar->metode ?? '-' }}</p>
            </div>
        </div>

        @if ($hasil['bisa'] || $hasil['bisa_dipaksa'])
            <form method="POST" action="{{ route('admin.konfirmasi.serahkan') }}" class="space-y-4 border-t border-surface-variant/40 pt-6">
                @csrf
                <input type="hidden" name="kode_booking" value="{{ $booking->kode_booking }}">
                <input type="hidden" name="kode_konfirmasi" value="{{ $booking->kode_konfirmasi }}">

                @if ($hasil['bisa_dipaksa'])
                    <label class="flex items-start gap-3 rounded-lg border border-amber-500/30 bg-amber-500/5 px-4 py-3 text-sm text-amber-200">
                        <input type="checkbox" name="abaikan_jendela" value="1" class="mt-1 h-4 w-4 accent-primary-container">
                        <span>Abaikan jendela waktu (kebijakan admin). Tindakan ini dicatat di log aktivitas.</span>
                    </label>
                @endif

                <button type="submit" class="btn-primary w-full sm:w-auto"
                        onclick="return confirm('Serahkan unit {{ $booking->console->kode_unit }} kepada {{ addslashes($booking->user->name) }}?');">
                    <span class="material-symbols-outlined text-[20px]">key</span> Serahkan Unit
                </button>
            </form>
        @endif
    </div>
    @endisset
</div>
@endsection