@extends('layouts.admin')
@section('title', 'Harga Paket')
@section('heading', 'Harga Paket')

@section('content')
@php $rp = fn ($n) => \App\Models\Booking::rupiah($n); @endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $pesan) <li>{{ $pesan }}</li> @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.harga.tambah') }}" class="card grid gap-4 sm:grid-cols-4" novalidate>
    @csrf
    <div>
        <label for="n-jenis" class="label-field">Jenis</label>
        <select id="n-jenis" name="jenis" class="input-field">
            @foreach (['PS5', 'PS4'] as $j)
                <option value="{{ $j }}" @selected(old('jenis') === $j)>{{ $j }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="n-durasi" class="label-field">Durasi (jam)</label>
        <input id="n-durasi" type="number" name="durasi_jam" min="1" max="48" value="{{ old('durasi_jam') }}" placeholder="36" class="input-field">
    </div>
    <div>
        <label for="n-harga" class="label-field">Harga (Rp)</label>
        <input id="n-harga" type="number" name="harga" min="1000" max="10000000" value="{{ old('harga') }}" placeholder="200000" class="input-field">
    </div>
    <div class="flex items-end">
        <button type="submit" class="btn-primary w-full"><span class="material-symbols-outlined text-[20px]">add</span> Tambah Paket</button>
    </div>
</form>

<p class="mt-4 text-sm text-on-surface-variant">
    Perubahan harga hanya berlaku untuk booking baru. Pesanan yang sudah dibuat menyimpan harganya sendiri. Setiap jenis harus punya minimal satu paket aktif.
</p>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    @foreach (['PS5', 'PS4'] as $jenis)
        @php $daftar = $paket->get($jenis, collect()); @endphp
        <section class="card !p-0">
            <div class="flex items-center justify-between border-b border-surface-variant/40 px-5 py-4">
                <h2 class="font-headline text-xl font-bold">{{ $jenis }}</h2>
                <span class="text-sm text-on-surface-variant">{{ $aktifPerJenis[$jenis] ?? 0 }} paket aktif</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[420px] text-left text-sm">
                    <thead class="text-xs uppercase tracking-wider text-outline">
                        <tr>
                            <th class="px-5 py-3">Durasi</th>
                            <th class="px-3 py-3">Harga (Rp)</th>
                            <th class="px-3 py-3 text-center">Aktif</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-variant/40">
                        @forelse ($daftar as $p)
                            <tr class="{{ $p->aktif ? '' : 'opacity-60' }}">
                                <td class="px-5 py-3 font-semibold">{{ $p->durasi_jam }} jam</td>
                                <td class="px-3 py-3">
                                    <input type="number" name="harga" value="{{ $p->harga }}" min="1000" max="10000000"
                                           form="u{{ $p->id }}" class="input-field !py-2" aria-label="Harga {{ $jenis }} {{ $p->durasi_jam }} jam">
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <input type="hidden" name="aktif" value="0" form="u{{ $p->id }}">
                                    <input type="checkbox" name="aktif" value="1" @checked($p->aktif) form="u{{ $p->id }}"
                                           class="h-4 w-4 accent-primary-container" aria-label="Aktif">
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <form id="u{{ $p->id }}" method="POST" action="{{ route('admin.harga.perbarui', $p) }}">
                                            @csrf
                                            <button type="submit" class="btn-primary !px-3 !py-1.5 text-xs">Simpan</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.harga.hapus', $p) }}"
                                              onsubmit="return confirm('Hapus paket {{ $jenis }} {{ $p->durasi_jam }} jam? Pesanan lama tidak terpengaruh.');">
                                            @csrf
                                            <button type="submit" class="btn-secondary !border-error/50 !px-3 !py-1.5 text-xs !text-error" aria-label="Hapus paket">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-on-surface-variant">Belum ada paket untuk {{ $jenis }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach
</div>
@endsection