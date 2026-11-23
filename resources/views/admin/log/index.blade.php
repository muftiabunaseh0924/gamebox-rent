@extends('layouts.admin')
@section('title', 'Log Aktivitas')
@section('heading', 'Log Aktivitas')

@section('content')

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $pesan) <li>{{ $pesan }}</li> @endforeach
        </ul>
    </div>
@endif

<form method="GET" action="{{ route('admin.log') }}" class="card grid gap-3 !p-4 sm:grid-cols-2 lg:grid-cols-6">
    <input type="text" name="q" value="{{ $filter['q'] ?? '' }}" maxlength="60" placeholder="Cari isi keterangan" class="input-field lg:col-span-2">

    <select name="admin" class="input-field" aria-label="Admin">
        <option value="">Semua admin</option>
        @foreach ($daftarAdmin as $a)
            <option value="{{ $a->id }}" @selected((string) ($filter['admin'] ?? '') === (string) $a->id)>{{ $a->name }}</option>
        @endforeach
    </select>

    <select name="aksi" class="input-field" aria-label="Jenis aksi">
        <option value="">Semua aksi</option>
        @foreach ($daftarAksi as $k)
            <option value="{{ $k }}" @selected(($filter['aksi'] ?? '') === $k)>{{ $k }}</option>
        @endforeach
    </select>

    <input type="date" name="dari" value="{{ $filter['dari'] ?? '' }}" class="input-field" aria-label="Dari tanggal">
    <input type="date" name="sampai" value="{{ $filter['sampai'] ?? '' }}" class="input-field" aria-label="Sampai tanggal">

    <div class="flex gap-2 sm:col-span-2 lg:col-span-6 lg:justify-end">
        <button type="submit" class="btn-primary !px-4"><span class="material-symbols-outlined text-[18px]">search</span> Cari</button>
        <a href="{{ route('admin.log') }}" class="btn-secondary !px-3" title="Atur ulang">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
        </a>
    </div>
</form>

<div class="card mt-4 overflow-x-auto !p-0">
    <table class="w-full min-w-[760px] text-left text-sm">
        <thead class="border-b border-surface-variant/40 text-xs uppercase tracking-wider text-outline">
            <tr>
                <th class="px-4 py-3">Waktu</th>
                <th class="px-4 py-3">Pelaku</th>
                <th class="px-4 py-3">Aksi</th>
                <th class="px-4 py-3">Keterangan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-variant/40">
            @forelse ($log as $l)
                <tr class="transition hover:bg-surface-container-high/50">
                    <td class="whitespace-nowrap px-4 py-3">
                        <p>{{ \Illuminate\Support\Carbon::parse($l->created_at)->format('d-m-Y') }}</p>
                        <p class="text-xs text-outline">{{ \Illuminate\Support\Carbon::parse($l->created_at)->format('H:i:s') }}</p>
                    </td>
                    <td class="px-4 py-3">{{ $l->admin ?: 'Sistem' }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full bg-primary-container/20 px-3 py-1 font-headline text-xs font-semibold tracking-wider text-primary">{{ $l->aksi }}</span>
                    </td>
                    <td class="px-4 py-3 text-on-surface-variant">{{ $l->detail ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-on-surface-variant">Tidak ada log yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $log->links() }}</div>
@endsection