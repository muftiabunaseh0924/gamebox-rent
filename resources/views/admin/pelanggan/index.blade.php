@extends('layouts.admin')
@section('title', 'Pelanggan')
@section('heading', 'Pelanggan')

@section('content')
@php
    $rp = fn ($n) => \App\Models\Booking::rupiah($n);
    $statusAktif = $filter['status'] ?? '';
    $labelStatus = ['' => 'Semua', 'Aktif' => 'Aktif', 'Nonaktif' => 'Nonaktif'];
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $pesan) <li>{{ $pesan }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 md:mx-0 md:flex-wrap md:overflow-visible md:px-0 md:pb-0">
    @foreach ($labelStatus as $kunci => $nama)
        <a href="{{ route('admin.pelanggan', array_merge(request()->except('page', 'status'), ['status' => $kunci !== '' ? $kunci : null])) }}"
           class="shrink-0 whitespace-nowrap rounded-lg px-3 py-2 font-headline text-xs font-semibold transition
           {{ $statusAktif === $kunci ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
            {{ $nama }} <span class="ml-1 rounded-full bg-black/20 px-2 py-0.5">{{ $jumlah[$kunci] ?? 0 }}</span>
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.pelanggan') }}" class="card mt-4 grid gap-3 !p-4 sm:grid-cols-3">
    @if ($statusAktif !== '') <input type="hidden" name="status" value="{{ $statusAktif }}"> @endif
    <input type="text" name="q" value="{{ $filter['q'] ?? '' }}" maxlength="60" placeholder="Cari nama, email, HP" class="input-field sm:col-span-2">
    <div class="flex gap-2">
        <button type="submit" class="btn-primary flex-1 !px-3"><span class="material-symbols-outlined text-[18px]">search</span> Cari</button>
        <a href="{{ route('admin.pelanggan') }}" class="btn-secondary !px-3" title="Atur ulang">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
        </a>
    </div>
</form>

{{-- Tampilan HP: daftar kartu --}}
<div class="mt-4 space-y-3 md:hidden">
    @forelse ($pelanggan as $p)
        <a href="{{ route('admin.pelanggan.show', $p) }}"
           class="card block !p-4 transition hover:border-cyan-400/40 active:scale-[0.99]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-secondary">{{ $p->name }}</p>
                    <p class="truncate text-xs text-on-surface-variant">{{ $p->email }}</p>
                </div>
                <span class="shrink-0 rounded-full px-3 py-1 font-headline text-[11px] font-semibold uppercase tracking-wider
                    {{ $p->isActive() ? 'bg-emerald-500/15 text-emerald-300' : 'bg-error-container/40 text-error' }}">
                    {{ $p->isActive() ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 border-t border-surface-variant/40 pt-3 text-sm">
                <div><dt class="text-xs text-outline">No. HP</dt><dd>{{ $p->no_hp ?: '-' }}</dd></div>
                <div><dt class="text-xs text-outline">Terdaftar</dt><dd>{{ $p->created_at->format('d-m-Y') }}</dd></div>
                <div><dt class="text-xs text-outline">Pesanan</dt><dd>{{ $p->bookings_count }}</dd></div>
                <div><dt class="text-xs text-outline">Total belanja</dt><dd class="font-semibold">{{ $rp($p->total_belanja ?? 0) }}</dd></div>
            </dl>
        </a>
    @empty
        <div class="card !p-8 text-center text-sm text-on-surface-variant">Tidak ada pelanggan yang cocok dengan filter.</div>
    @endforelse
</div>

{{-- Tampilan tablet dan desktop: tabel --}}
<div class="card mt-4 hidden overflow-x-auto !p-0 md:block">
    <table class="w-full min-w-[760px] text-left text-sm">
        <thead class="border-b border-surface-variant/40 text-xs uppercase tracking-wider text-outline">
            <tr>
                <th class="px-4 py-3">Pelanggan</th>
                <th class="px-4 py-3">No. HP</th>
                <th class="px-4 py-3">Terdaftar</th>
                <th class="px-4 py-3 text-right">Pesanan</th>
                <th class="px-4 py-3 text-right">Total belanja</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-variant/40">
            @forelse ($pelanggan as $p)
                <tr class="transition hover:bg-surface-container-high/50">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.pelanggan.show', $p) }}" class="font-semibold text-secondary hover:underline">{{ $p->name }}</a>
                        <p class="text-xs text-on-surface-variant">{{ $p->email }}</p>
                    </td>
                    <td class="px-4 py-3">{{ $p->no_hp ?: '-' }}</td>
                    <td class="px-4 py-3">{{ $p->created_at->format('d-m-Y') }}</td>
                    <td class="px-4 py-3 text-right">{{ $p->bookings_count }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ $rp($p->total_belanja ?? 0) }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider
                            {{ $p->isActive() ? 'bg-emerald-500/15 text-emerald-300' : 'bg-error-container/40 text-error' }}">
                            {{ $p->isActive() ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-on-surface-variant">Tidak ada pelanggan yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $pelanggan->links() }}</div>
@endsection