@extends('layouts.admin')
@section('title', 'Laporan Keuangan')
@section('heading', 'Laporan Keuangan')

@section('content')
@php
    $rp = fn ($n) => \App\Models\Booking::rupiah($n);
    $totalJenis = max(1, $lap['jenis']['PS5']['total'] + $lap['jenis']['PS4']['total']);
    $totalMetode = max(1, $lap['metode']['Ambil Sendiri']['total'] + $lap['metode']['Diantar']['total']);
    $bulanIni = now()->format('Y-m');
    $bulanLalu = now()->startOfMonth()->subMonthNoOverflow()->format('Y-m');
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $pesan) <li>{{ $pesan }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="flex flex-wrap gap-2">
    <a href="{{ route('admin.laporan', ['bulan' => $bulanIni]) }}"
       class="rounded-lg px-3 py-2 font-headline text-xs font-semibold transition {{ $bulanAktif === $bulanIni ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high' }}">Bulan Ini</a>
    <a href="{{ route('admin.laporan', ['bulan' => $bulanLalu]) }}"
       class="rounded-lg px-3 py-2 font-headline text-xs font-semibold transition {{ $bulanAktif === $bulanLalu ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high' }}">Bulan Lalu</a>
</div>

<form method="GET" action="{{ route('admin.laporan') }}" class="card mt-4 grid gap-3 !p-4 sm:grid-cols-2 lg:grid-cols-5">
    <select name="bulan" class="input-field" aria-label="Bulan">
        @foreach ($opsiBulan as $nilai => $nama)
            <option value="{{ $nilai }}" @selected($bulanAktif === $nilai)>{{ $nama }}</option>
        @endforeach
    </select>
    <input type="date" name="dari" value="{{ $filter['dari'] ?? '' }}" class="input-field" aria-label="Dari tanggal">
    <input type="date" name="sampai" value="{{ $filter['sampai'] ?? '' }}" class="input-field" aria-label="Sampai tanggal">
    <div class="flex gap-2 lg:col-span-2">
        <button type="submit" class="btn-primary flex-1 !px-3"><span class="material-symbols-outlined text-[18px]">search</span> Tampilkan</button>
        <a href="{{ route('admin.laporan') }}" class="btn-secondary !px-3" title="Atur ulang">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
        </a>
    </div>
    <p class="text-xs text-outline sm:col-span-2 lg:col-span-5">Bila tanggal "dari" dan "sampai" diisi, tanggal dipakai dan pilihan bulan diabaikan (maksimal 366 hari).</p>
</form>

<div class="mt-6 flex flex-wrap items-center justify-between gap-3">
    <p class="font-headline text-sm font-semibold uppercase tracking-wider text-outline">Periode: {{ $label }}</p>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.laporan.pdf', $query) }}" class="btn-secondary !py-2">
            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span> Unduh PDF
        </a>
        @if (\Illuminate\Support\Facades\Route::has('admin.laporan.excel'))
            <a href="{{ route('admin.laporan.excel', $query) }}" class="btn-secondary !py-2">
                <span class="material-symbols-outlined text-[18px]">table_view</span> Unduh Excel
            </a>
        @endif
    </div>
</div>

<div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="card !p-5">
        <p class="label-field !mb-1">Total Pemasukan</p>
        <p class="font-headline text-3xl font-bold text-secondary">{{ $rp($lap['pemasukan']) }}</p>
        <p class="mt-1 text-sm text-on-surface-variant">pembayaran berhasil, tanpa pesanan dibatalkan</p>
    </div>
    <div class="card !p-5">
        <p class="label-field !mb-1">Total Transaksi</p>
        <p class="font-headline text-3xl font-bold">{{ $lap['transaksi'] }}</p>
        <p class="mt-1 text-sm text-on-surface-variant">pesanan yang dibayar</p>
    </div>
    <div class="card !p-5">
        <p class="label-field !mb-1">Rata-rata Order</p>
        <p class="font-headline text-3xl font-bold">{{ $rp($lap['rata']) }}</p>
        <p class="mt-1 text-sm text-on-surface-variant">per transaksi</p>
    </div>
    <div class="card !p-5">
        <p class="label-field !mb-1">Denda Keterlambatan</p>
        <p class="font-headline text-3xl font-bold text-amber-300">{{ $rp($lap['denda']['total']) }}</p>
        <p class="mt-1 text-sm text-on-surface-variant">{{ $lap['denda']['n'] }} pesanan selesai | tidak masuk pemasukan</p>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <section class="card space-y-4">
        <h2 class="font-headline text-lg font-bold">Pemasukan per Jenis Unit</h2>
        @foreach (['PS5', 'PS4'] as $jenis)
            @php $p = $lap['jenis'][$jenis]; $persen = (int) round($p['total'] / $totalJenis * 100); @endphp
            <div>
                <div class="flex items-baseline justify-between">
                    <span class="font-headline font-semibold">{{ $jenis }}</span>
                    <span class="text-sm text-on-surface-variant">{{ $p['n'] }} transaksi | {{ $persen }}%</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-surface-container-high">
                    <div class="h-full rounded-full bg-secondary" style="width: {{ $persen }}%"></div>
                </div>
                <p class="mt-1 text-sm font-semibold text-secondary">{{ $rp($p['total']) }}</p>
            </div>
        @endforeach
    </section>

    <section class="card space-y-4">
        <h2 class="font-headline text-lg font-bold">Pemasukan per Penerimaan</h2>
        @foreach (['Ambil Sendiri', 'Diantar'] as $metode)
            @php $p = $lap['metode'][$metode]; $persen = (int) round($p['total'] / $totalMetode * 100); @endphp
            <div>
                <div class="flex items-baseline justify-between">
                    <span class="font-headline font-semibold">{{ $metode }}</span>
                    <span class="text-sm text-on-surface-variant">{{ $p['n'] }} transaksi | {{ $persen }}%</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-surface-container-high">
                    <div class="h-full rounded-full bg-primary-container" style="width: {{ $persen }}%"></div>
                </div>
                <p class="mt-1 text-sm font-semibold text-secondary">{{ $rp($p['total']) }}</p>
            </div>
        @endforeach
    </section>

    <section class="card space-y-4">
        <h2 class="font-headline text-lg font-bold">Catatan Lain</h2>
        <div class="rounded-lg bg-surface-container-lowest px-4 py-3 text-sm">
            <p class="label-field !mb-1">Stik Tambahan</p>
            <p><strong>{{ $lap['stik']['jumlah'] }}</strong> stik | {{ $rp($lap['stik']['pendapatan']) }}</p>
        </div>
        <div class="rounded-lg bg-surface-container-lowest px-4 py-3 text-sm">
            <p class="label-field !mb-1">Dibatalkan, Sudah Dibayar</p>
            <p><strong>{{ $lap['batal_dibayar']['n'] }}</strong> pesanan | {{ $rp($lap['batal_dibayar']['total']) }}</p>
            <p class="mt-1 text-xs text-outline">Tidak dihitung sebagai pemasukan. Refund dilakukan manual di dasbor Midtrans.</p>
        </div>
    </section>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section>
        <h2 class="mb-3 font-headline text-lg font-bold">Pemasukan Harian</h2>
        <div class="card overflow-x-auto !p-0">
            <table class="w-full min-w-[360px] text-left text-sm">
                <thead class="border-b border-surface-variant/40 text-xs uppercase tracking-wider text-outline">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-right">Transaksi</th>
                        <th class="px-4 py-3 text-right">Pemasukan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-variant/40">
                    @forelse ($lap['harian'] as $h)
                        <tr>
                            <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($h->hari)->format('d-m-Y') }}</td>
                            <td class="px-4 py-3 text-right">{{ $h->n }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ $rp($h->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-on-surface-variant">Belum ada pemasukan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if ($lap['harian']->isNotEmpty())
                    <tfoot class="border-t border-surface-variant/40 font-semibold">
                        <tr>
                            <td class="px-4 py-3">Total</td>
                            <td class="px-4 py-3 text-right">{{ $lap['transaksi'] }}</td>
                            <td class="px-4 py-3 text-right text-secondary">{{ $rp($lap['pemasukan']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>

    <section>
        <h2 class="mb-3 font-headline text-lg font-bold">Pemasukan per Unit</h2>
        <div class="card overflow-x-auto !p-0">
            <table class="w-full min-w-[360px] text-left text-sm">
                <thead class="border-b border-surface-variant/40 text-xs uppercase tracking-wider text-outline">
                    <tr>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3 text-right">Transaksi</th>
                        <th class="px-4 py-3 text-right">Pemasukan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-variant/40">
                    @forelse ($lap['unit'] as $u)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $u->kode_unit }}</td>
                            <td class="px-4 py-3">{{ $u->jenis }}</td>
                            <td class="px-4 py-3 text-right">{{ $u->n }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ $rp($u->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-on-surface-variant">Belum ada pemasukan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<h2 class="mb-3 mt-8 font-headline text-lg font-bold">Rincian Transaksi</h2>
<div class="card overflow-x-auto !p-0">
    <table class="w-full min-w-[1000px] text-left text-sm">
        <thead class="border-b border-surface-variant/40 text-xs uppercase tracking-wider text-outline">
            <tr>
                <th class="px-4 py-3">Waktu bayar</th>
                <th class="px-4 py-3">Kode</th>
                <th class="px-4 py-3">Pelanggan</th>
                <th class="px-4 py-3">Unit</th>
                <th class="px-4 py-3">Jadwal</th>
                <th class="px-4 py-3">Penerimaan</th>
                <th class="px-4 py-3 text-right">Stik</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Nominal</th>
                <th class="px-4 py-3 text-right">Denda</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-variant/40">
            @forelse ($rincian as $r)
                <tr class="transition hover:bg-surface-container-high/50">
                    <td class="whitespace-nowrap px-4 py-3">{{ \Illuminate\Support\Carbon::parse($r->waktu_bayar)->format('d-m-Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.pesanan.show', $r->kode_booking) }}" class="font-headline font-semibold tracking-wider text-secondary hover:underline">{{ $r->kode_booking }}</a>
                    </td>
                    <td class="px-4 py-3">{{ $r->pelanggan }}</td>
                    <td class="px-4 py-3">{{ $r->kode_unit }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <p>{{ \Illuminate\Support\Carbon::parse($r->waktu_mulai)->format('d-m-Y H:i') }}</p>
                        <p class="text-xs text-on-surface-variant">{{ $r->durasi_jam }} jam</p>
                    </td>
                    <td class="px-4 py-3">{{ $r->metode_serah }}</td>
                    <td class="px-4 py-3 text-right">{{ $r->jumlah_stik }}</td>
                    <td class="px-4 py-3">{{ $r->status_booking }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ $rp($r->nominal) }}</td>
                    <td class="px-4 py-3 text-right">{{ ($r->denda ?? 0) > 0 ? $rp($r->denda) : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="px-4 py-10 text-center text-on-surface-variant">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $rincian->links() }}</div>
@endsection