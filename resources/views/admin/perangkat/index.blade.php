@extends('layouts.admin')
@section('title', 'Perangkat')
@section('heading', 'Perangkat')

@section('content')
@php
    $kelasStatus = fn ($s) => match ($s) {
        'Tersedia' => 'bg-emerald-500/15 text-emerald-300',
        'Disewa' => 'bg-amber-500/15 text-amber-300',
        'Perawatan' => 'bg-primary-container/20 text-primary',
        default => 'bg-surface-container-high text-on-surface-variant',
    };
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $pesan) <li>{{ $pesan }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="flex flex-wrap items-center justify-between gap-3">
    <p class="text-on-surface-variant">{{ $unit->count() }} unit terdaftar. Unit berstatus Nonaktif tidak tampil di katalog dan form booking.</p>
    <a href="{{ route('admin.perangkat.tambah') }}" class="btn-primary !py-2">
        <span class="material-symbols-outlined text-[20px]">add</span> Tambah Unit
    </a>
</div>

<div class="card mt-4 overflow-x-auto !p-0">
    <table class="w-full min-w-[760px] text-left text-sm">
        <thead class="border-b border-surface-variant/40 text-xs uppercase tracking-wider text-outline">
            <tr>
                <th class="px-4 py-3">Unit</th>
                <th class="px-4 py-3">Jenis</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Lokasi</th>
                <th class="px-4 py-3 text-right">Pesanan</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-variant/40">
            @foreach ($unit as $u)
                <tr class="transition hover:bg-surface-container-high/50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-surface-container-lowest">
                                @if ($u->fotoUrl())
                                    <img src="{{ $u->fotoUrl() }}" alt="" class="h-full w-full object-cover" onerror="this.remove()">
                                @else
                                    <span class="material-symbols-outlined text-secondary">sports_esports</span>
                                @endif
                            </div>
                            <div>
                                <p class="font-headline font-semibold tracking-wider">{{ $u->kode_unit }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $u->nama_tampil }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">{{ $u->jenis }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider {{ $kelasStatus($u->status) }}">{{ $u->status }}</span></td>
                    <td class="px-4 py-3 text-on-surface-variant">{{ $u->lokasi_saat_ini }}</td>
                    <td class="px-4 py-3 text-right">{{ $u->bookings_count }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.perangkat.ubah', $u) }}" class="btn-secondary !px-3 !py-1.5 text-xs">Ubah</a>
                            @if ($u->bookings_count === 0 && $u->status !== 'Disewa')
                                <form method="POST" action="{{ route('admin.perangkat.hapus', $u) }}"
                                      onsubmit="return confirm('Hapus unit {{ $u->kode_unit }}? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    <button type="submit" class="btn-secondary !border-error/50 !px-3 !py-1.5 text-xs !text-error">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<p class="mt-4 text-xs text-outline">Tombol Hapus hanya muncul untuk unit yang belum pernah dipakai pada pesanan. Unit lain cukup dinonaktifkan lewat menu Ubah.</p>
@endsection