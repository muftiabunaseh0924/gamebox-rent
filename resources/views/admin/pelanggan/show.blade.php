@extends('layouts.admin')
@section('title', 'Detail Pelanggan')
@section('heading', 'Detail Pelanggan')

@section('content')
@php
    $rp = fn ($n) => \App\Models\Booking::rupiah($n);
    $aktif = $pelanggan->isActive();
@endphp

<a href="{{ route('admin.pelanggan') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-on-surface">
    <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar
</a>

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $pesan) <li>{{ $pesan }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="card !p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="font-headline text-2xl font-bold">{{ $pelanggan->name }}</h2>
            <p class="mt-1 text-sm text-on-surface-variant">{{ $pelanggan->email }}</p>
            <p class="text-sm text-on-surface-variant">{{ $pelanggan->no_hp ?: 'No. HP belum diisi' }}</p>
            <p class="mt-2 text-xs text-outline">Terdaftar {{ $pelanggan->created_at->format('d-m-Y H:i') }}</p>
        </div>
        <div class="flex flex-col items-end gap-3">
            <span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider
                {{ $aktif ? 'bg-emerald-500/15 text-emerald-300' : 'bg-error-container/40 text-error' }}">
                {{ $aktif ? 'Aktif' : 'Nonaktif' }}
            </span>

            <form method="POST" action="{{ route('admin.pelanggan.status', $pelanggan) }}"
                  onsubmit="return confirm('{{ $aktif ? 'Nonaktifkan akun ini? Pelanggan langsung keluar dan tidak bisa masuk lagi.' : 'Aktifkan kembali akun ini?' }}')">
                @csrf
                <input type="hidden" name="status" value="{{ $aktif ? 'Nonaktif' : 'Aktif' }}">
                @if ($aktif)
                    <button type="submit" class="btn-secondary !py-2 text-error">
                        <span class="material-symbols-outlined text-[18px]">block</span> Nonaktifkan akun
                    </button>
                @else
                    <button type="submit" class="btn-primary !py-2">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span> Aktifkan akun
                    </button>
                @endif
            </form>
        </div>
    </div>
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="card !p-4"><p class="text-xs uppercase tracking-wider text-outline">Total pesanan</p><p class="mt-1 font-headline text-2xl font-bold">{{ $ringkasan['pesanan'] }}</p></div>
    <div class="card !p-4"><p class="text-xs uppercase tracking-wider text-outline">Pesanan dibayar</p><p class="mt-1 font-headline text-2xl font-bold">{{ $ringkasan['berhasil'] }}</p></div>
    <div class="card !p-4"><p class="text-xs uppercase tracking-wider text-outline">Total belanja</p><p class="mt-1 font-headline text-2xl font-bold">{{ $rp($ringkasan['belanja']) }}</p></div>
    <div class="card !p-4"><p class="text-xs uppercase tracking-wider text-outline">Pesan terakhir</p>
        <p class="mt-1 font-headline text-2xl font-bold">{{ $ringkasan['terakhir'] ? \Illuminate\Support\Carbon::parse($ringkasan['terakhir'])->format('d-m-Y') : '-' }}</p></div>
</div>

<h3 class="mb-3 mt-8 font-headline text-lg font-bold">Riwayat pesanan</h3>
<div class="card overflow-x-auto !p-0">
    <table class="w-full min-w-[700px] text-left text-sm">
        <thead class="border-b border-surface-variant/40 text-xs uppercase tracking-wider text-outline">
            <tr>
                <th class="px-4 py-3">Kode</th>
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
                    <td class="px-4 py-3">{{ $b->console->kode_unit }}</td>
                    <td class="px-4 py-3"><p>{{ $b->waktu_mulai->format('d-m-Y H:i') }}</p><p class="text-xs text-on-surface-variant">{{ $b->durasi_jam }} jam</p></td>
                    <td class="px-4 py-3">{{ $b->metode_serah }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ $rp($b->total_harga) }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider {{ $b->kelasStatus() }}">{{ $b->statusTampil() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-on-surface-variant">Pelanggan ini belum pernah memesan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $pesanan->links() }}</div>
@endsection