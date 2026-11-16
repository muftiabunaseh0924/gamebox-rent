@extends('layouts.admin')
@section('title', 'Dasbor Admin')
@section('heading', 'Dasbor Operasional')

@section('content')
@php
    $rp = fn ($n) => \App\Models\Booking::rupiah($n);
    $durasi = function (int $menit) {
        $m = abs($menit);
        return intdiv($m, 60).' j '.($m % 60).' m';
    };
    $tab = ['7' => '7 Hari', 'bulan' => 'Bulan Ini', '30' => '30 Hari'];
    $totalProporsi = max(1, $d['proporsi']['PS5']['n'] + $d['proporsi']['PS4']['n']);
@endphp

@if ($d['telat']->isNotEmpty())
    <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-4 text-error">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined">warning</span>
                <div>
                    <p class="font-headline font-semibold uppercase tracking-wider">Perhatian: {{ $d['telat']->count() }} unit terlambat kembali</p>
                    <ul class="mt-1 space-y-1 text-sm">
                        @foreach ($d['telat'] as $t)
                            <li>
                                {{ $t['booking']->console->kode_unit }} ({{ $t['booking']->user->name }}) terlambat {{ $durasi($t['menit']) }},
                                denda berjalan <strong>{{ $rp($t['denda']) }}</strong>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <a href="{{ route('admin.armada') }}" class="btn-secondary !py-2">Lihat Armada</a>
        </div>
    </div>
@endif

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <div class="card !p-5">
        <p class="label-field !mb-1">Total Pemasukan</p>
        <p class="font-headline text-3xl font-bold text-secondary">{{ $rp($d['pemasukan']) }}</p>
        <p class="mt-1 text-sm text-on-surface-variant">{{ $d['label'] }}</p>
    </div>
    <div class="card !p-5">
        <p class="label-field !mb-1">Total Transaksi</p>
        <p class="font-headline text-3xl font-bold">{{ $d['transaksi'] }} <span class="text-base font-medium text-on-surface-variant">transaksi</span></p>
        <p class="mt-1 text-sm text-on-surface-variant">
            {{ $d['booking']['total'] }} booking dibuat · {{ $d['booking']['persen'] }}% terbayar · {{ $d['booking']['batal'] }} dibatalkan
        </p>
    </div>
    <div class="card !p-5">
        <p class="label-field !mb-1">Rata-rata Order</p>
        <p class="font-headline text-3xl font-bold">{{ $rp($d['rata']) }}</p>
        <p class="mt-1 text-sm text-on-surface-variant">per transaksi berhasil</p>
    </div>
    <div class="card !p-5">
        <p class="label-field !mb-1">Konsol Disewa</p>
        <p class="font-headline text-3xl font-bold text-amber-300">{{ $d['konsol']['disewa'] }} <span class="text-base font-medium text-on-surface-variant">/ {{ $d['konsol']['total'] }}</span></p>
        <p class="mt-1 text-sm text-on-surface-variant">
            {{ $d['konsol']['tersedia'] }} siap sewa · {{ $d['konsol']['perawatan'] }} perawatan
            @if ($d['konsol']['kode_disewa']) · {{ implode(', ', $d['konsol']['kode_disewa']) }} @endif
        </p>
    </div>
    <div class="card !p-5">
        <p class="label-field !mb-1">Serah Terima Hari Ini</p>
        <p class="font-headline text-3xl font-bold">{{ $d['serah']['total'] }} <span class="text-base font-medium text-on-surface-variant">jadwal</span></p>
        <p class="mt-1 text-sm text-on-surface-variant">{{ $d['serah']['sudah'] }} sudah diserahkan · {{ $d['serah']['menunggu'] }} menunggu verifikasi</p>
    </div>
    <div class="card !p-5">
        <p class="label-field !mb-1">Status Keterlambatan</p>
        <p class="font-headline text-3xl font-bold {{ $d['telat']->isNotEmpty() ? 'text-error' : 'text-emerald-300' }}">{{ $d['telat']->count() }} <span class="text-base font-medium text-on-surface-variant">unit</span></p>
        <p class="mt-1 text-sm text-on-surface-variant">Akumulasi denda berjalan: {{ $rp($d['denda_telat']) }}</p>
    </div>
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-3">
    <section class="card space-y-5 lg:col-span-2">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="font-headline text-xl font-bold">Pemasukan Harian</h2>
                <p class="text-sm text-on-surface-variant">{{ $d['label'] }} · batang kuning = akhir pekan</p>
            </div>
            <div class="flex gap-2">
                @foreach ($tab as $kunci => $nama)
                    <a href="{{ route('admin.dashboard', ['periode' => $kunci]) }}"
                       class="rounded-lg px-3 py-1.5 font-headline text-xs font-semibold transition
                       {{ $d['kode'] === (string) $kunci ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high' }}">{{ $nama }}</a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <div class="flex h-52 min-w-[320px] items-end gap-1">
                @foreach ($d['harian'] as $h)
                    <div class="flex h-full flex-1 items-end" title="{{ $h['tanggal'] }}: {{ $rp($h['total']) }}">
                        <div class="w-full rounded-t {{ $h['akhir_pekan'] ? 'bg-amber-400/80' : 'bg-primary-container' }}"
                             style="height: {{ $h['tinggi'] }}%; min-height: {{ $h['total'] > 0 ? '3px' : '0' }};"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex min-w-[320px] gap-1 text-[10px] text-outline">
                @foreach ($d['harian'] as $h)
                    <span class="flex-1 text-center">{{ $h['hari'] }}</span>
                @endforeach
            </div>
        </div>

        <dl class="grid gap-4 border-t border-surface-variant/40 pt-4 text-sm sm:grid-cols-2">
            <div><dt class="text-outline">Rata-rata per hari</dt><dd class="font-headline text-lg font-semibold">{{ $rp($d['rerata_harian']) }}</dd></div>
            <div>
                <dt class="text-outline">Hari tertinggi</dt>
                <dd class="font-headline text-lg font-semibold">{{ $rp($d['puncak']['total']) }}
                    @if ($d['puncak']['tanggal']) <span class="text-sm font-normal text-on-surface-variant">({{ $d['puncak']['tanggal'] }})</span> @endif
                </dd>
            </div>
        </dl>
    </section>

    <section class="card space-y-5">
        <div>
            <h2 class="font-headline text-xl font-bold">Proporsi Unit Sewa</h2>
            <p class="text-sm text-on-surface-variant">PS5 vs PS4 dan stik tambahan · {{ $d['label'] }}</p>
        </div>

        @foreach (['PS5', 'PS4'] as $jenis)
            @php $p = $d['proporsi'][$jenis]; $persen = (int) round($p['n'] / $totalProporsi * 100); @endphp
            <div>
                <div class="flex items-baseline justify-between">
                    <span class="font-headline font-semibold">{{ $jenis }}</span>
                    <span class="text-sm text-on-surface-variant">{{ $p['n'] }} booking · {{ $persen }}%</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-surface-container-high">
                    <div class="h-full rounded-full bg-secondary" style="width: {{ $persen }}%"></div>
                </div>
                <p class="mt-1 text-sm font-semibold text-secondary">{{ $rp($p['total']) }}</p>
            </div>
        @endforeach

        <div class="rounded-lg bg-surface-container-lowest px-4 py-3 text-sm">
            <p class="label-field !mb-1">Stik Tambahan</p>
            <p><strong>{{ $d['stik']['jumlah'] }}</strong> stik · {{ $rp($d['stik']['pendapatan']) }}</p>
        </div>
    </section>
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-3">
    <a href="{{ route('admin.konfirmasi') }}" class="card flex items-center gap-4 !p-5 transition hover:border-cyan-400/40">
        <span class="material-symbols-outlined text-3xl text-secondary">pin</span>
        <div><p class="font-headline font-semibold">Konfirmasi Kode</p><p class="text-sm text-on-surface-variant">{{ $d['serah']['menunggu'] }} menunggu verifikasi hari ini</p></div>
    </a>
    <a href="{{ route('admin.pengiriman') }}" class="card flex items-center gap-4 !p-5 transition hover:border-cyan-400/40">
        <span class="material-symbols-outlined text-3xl text-secondary">local_shipping</span>
        <div><p class="font-headline font-semibold">Pengiriman Aktif</p><p class="text-sm text-on-surface-variant">{{ $d['antrean']['pengiriman'] }} pengantaran berjalan</p></div>
    </a>
    <a href="{{ route('admin.pesanan', ['status' => 'Menunggu Pembayaran']) }}" class="card flex items-center gap-4 !p-5 transition hover:border-cyan-400/40">
        <span class="material-symbols-outlined text-3xl text-secondary">hourglass_top</span>
        <div><p class="font-headline font-semibold">Menunggu Pembayaran</p><p class="text-sm text-on-surface-variant">{{ $d['antrean']['menunggu_bayar'] }} pesanan belum dibayar</p></div>
    </a>
</div>
@endsection