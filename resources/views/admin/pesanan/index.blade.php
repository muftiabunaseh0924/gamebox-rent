@extends('layouts.admin')
@section('title', 'Manajemen Pesanan')
@section('heading', 'Manajemen Pesanan')

@section('content')
@php
    $rp = fn ($n) => \App\Models\Booking::rupiah($n);
    $statusAktif = $filter['status'] ?? '';
    $labelStatus = ['' => 'Semua', 'Menunggu Pembayaran' => 'Menunggu Bayar', 'Dibayar' => 'Dibayar', 'Sedang Disewa' => 'Sedang Disewa', 'Selesai' => 'Selesai', 'Dibatalkan' => 'Dibatalkan', 'Kedaluwarsa' => 'Kedaluwarsa'];
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $pesan) <li>{{ $pesan }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="flex flex-wrap gap-2">
    @foreach ($labelStatus as $kunci => $nama)
        <a href="{{ route('admin.pesanan', array_merge(request()->except('page', 'status'), ['status' => $kunci !== '' ? $kunci : null])) }}"
           class="rounded-lg px-3 py-2 font-headline text-xs font-semibold transition
           {{ $statusAktif === $kunci ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
            {{ $nama }} <span class="ml-1 rounded-full bg-black/20 px-2 py-0.5">{{ $jumlah[$kunci] ?? 0 }}</span>
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.pesanan') }}" class="card mt-4 grid gap-3 !p-4 sm:grid-cols-2 lg:grid-cols-6">
    @if ($statusAktif !== '') <input type="hidden" name="status" value="{{ $statusAktif }}"> @endif
    <input type="text" name="q" value="{{ $filter['q'] ?? '' }}" maxlength="60" placeholder="Cari kode, nama, email, HP" class="input-field lg:col-span-2">
    <input type="date" name="dari" value="{{ $filter['dari'] ?? '' }}" class="input-field" aria-label="Dari tanggal">
    <input type="date" name="sampai" value="{{ $filter['sampai'] ?? '' }}" class="input-field" aria-label="Sampai tanggal">
    <select name="metode" class="input-field" aria-label="Cara penerimaan">
        <option value="">Semua penerimaan</option>
        @foreach (['Ambil Sendiri', 'Diantar'] as $m)
            <option value="{{ $m }}" @selected(($filter['metode'] ?? '') === $m)>{{ $m }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <button type="submit" class="btn-primary flex-1 !px-3"><span class="material-symbols-outlined text-[18px]">search</span> Cari</button>
        <a href="{{ route('admin.pesanan') }}" class="btn-secondary !px-3" title="Atur ulang">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
        </a>
    </div>
</form>

<div class="card mt-4 overflow-x-auto !p-0">
    <table class="w-full min-w-[820px] text-left text-sm">
        <thead class="border-b border-surface-variant/40 text-xs uppercase tracking-wider text-outline">
            <tr>
                <th class="px-4 py-3">Kode</th>
                <th class="px-4 py-3">Pelanggan</th>
                <th class="px-4 py-3">Unit</th>
                <th class="px-4 py-3">Jadwal</th>
                <th class="px-4 py-3">Penerimaan</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-variant/40">
            @forelse ($pesanan as $b)
                <tr class="transition hover:bg-surface-container-high/50">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.pesanan.show', $b) }}" class="font-headline font-semibold tracking-wider text-secondary hover:underline">{{ $b->kode_booking }}</a>
                        <p class="text-xs text-outline">{{ $b->created_at->format('d-m-Y H:i') }}</p>
                    </td>
                    <td class="px-4 py-3"><p class="font-semibold">{{ $b->user->name }}</p><p class="text-xs text-on-surface-variant">{{ $b->user->email }}</p></td>
                    <td class="px-4 py-3">{{ $b->console->kode_unit }}</td>
                    <td class="px-4 py-3"><p>{{ $b->waktu_mulai->format('d-m-Y H:i') }}</p><p class="text-xs text-on-surface-variant">{{ $b->durasi_jam }} jam</p></td>
                    <td class="px-4 py-3">{{ $b->metode_serah }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ $rp($b->total_harga) }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider {{ $b->kelasStatus() }}">{{ $b->statusTampil() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-on-surface-variant">Tidak ada pesanan yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $pesanan->links() }}</div>
@endsection