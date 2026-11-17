@extends('layouts.admin')
@section('title', 'Tanggal Libur')
@section('heading', 'Tanggal Libur')

@section('content')
@if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $pesan) <li>{{ $pesan }}</li> @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.libur.tambah') }}" class="card grid gap-4 sm:grid-cols-2 lg:grid-cols-4" novalidate>
    @csrf
    <div>
        <label for="tanggal" class="label-field">Tanggal</label>
        <input id="tanggal" type="date" name="tanggal" min="{{ now()->toDateString() }}" value="{{ old('tanggal') }}" class="input-field">
    </div>
    <div>
        <label for="console_id" class="label-field">Berlaku untuk</label>
        <select id="console_id" name="console_id" class="input-field">
            <option value="">Semua unit</option>
            @foreach ($unit as $u)
                <option value="{{ $u->id }}" @selected((string) old('console_id') === (string) $u->id)>{{ $u->kode_unit }} · {{ $u->nama_tampil }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="alasan" class="label-field">Alasan (opsional)</label>
        <input id="alasan" type="text" name="alasan" maxlength="200" value="{{ old('alasan') }}" placeholder="Hari libur nasional" class="input-field">
    </div>
    <div class="flex items-end">
        <button type="submit" class="btn-primary w-full"><span class="material-symbols-outlined text-[20px]">event_busy</span> Tambah Libur</button>
    </div>
</form>

<p class="mt-4 text-sm text-on-surface-variant">
    Tanggal libur menutup <strong>booking baru</strong> pada tanggal tersebut (jam tampil "Libur" di form booking). Pesanan yang sudah ada tidak dibatalkan otomatis.
</p>

<section class="card mt-6 !p-0">
    <div class="border-b border-surface-variant/40 px-5 py-4">
        <h2 class="font-headline text-xl font-bold">Mendatang</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[520px] text-left text-sm">
            <thead class="text-xs uppercase tracking-wider text-outline">
                <tr><th class="px-5 py-3">Tanggal</th><th class="px-3 py-3">Berlaku untuk</th><th class="px-3 py-3">Alasan</th><th class="px-5 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-surface-variant/40">
                @forelse ($mendatang as $l)
                    <tr>
                        <td class="px-5 py-3 font-semibold">{{ \Illuminate\Support\Carbon::parse($l->tanggal)->format('d-m-Y') }}</td>
                        <td class="px-3 py-3">{{ $l->kode_unit ?? 'Semua unit' }}</td>
                        <td class="px-3 py-3 text-on-surface-variant">{{ $l->alasan ?? '-' }}</td>
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="{{ route('admin.libur.hapus', $l->id) }}"
                                  onsubmit="return confirm('Hapus tanggal libur ini? Booking pada tanggal itu dibuka kembali.');">
                                @csrf
                                <button type="submit" class="btn-secondary !border-error/50 !px-3 !py-1.5 text-xs !text-error">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-on-surface-variant">Belum ada tanggal libur mendatang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if ($lampau->isNotEmpty())
    <section class="card mt-6 !p-0 opacity-80">
        <div class="border-b border-surface-variant/40 px-5 py-4">
            <h2 class="font-headline text-lg font-bold">Sudah Lewat (10 terakhir)</h2>
        </div>
        <ul class="divide-y divide-surface-variant/40 text-sm">
            @foreach ($lampau as $l)
                <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                    <span><strong>{{ \Illuminate\Support\Carbon::parse($l->tanggal)->format('d-m-Y') }}</strong> · {{ $l->kode_unit ?? 'Semua unit' }} · <span class="text-on-surface-variant">{{ $l->alasan ?? '-' }}</span></span>
                    <form method="POST" action="{{ route('admin.libur.hapus', $l->id) }}" onsubmit="return confirm('Hapus catatan ini?');">
                        @csrf
                        <button type="submit" class="text-xs text-error hover:underline">Hapus</button>
                    </form>
                </li>
            @endforeach
        </ul>
    </section>
@endif
@endsection