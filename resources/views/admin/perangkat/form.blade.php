@extends('layouts.admin')
@section('title', $unit->exists ? 'Ubah Unit' : 'Tambah Unit')
@section('heading', $unit->exists ? 'Ubah Unit '.$unit->kode_unit : 'Tambah Unit')

@section('content')
@php
    $terkunci = $unit->exists && $unit->status === 'Disewa';
    $aksi = $unit->exists ? route('admin.perangkat.perbarui', $unit) : route('admin.perangkat.simpan');
@endphp

<div class="mx-auto max-w-3xl">
    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $pesan) <li>{{ $pesan }}</li> @endforeach
            </ul>
        </div>
    @endif

    @if ($terkunci)
        <div class="mb-6 rounded-lg bg-amber-500/10 px-4 py-3 text-sm text-amber-300">
            Unit ini sedang <strong>disewa</strong>. Jenis dan status tidak dapat diubah sampai penyewaan selesai; data lain tetap bisa diperbarui.
        </div>
    @endif

    <form method="POST" action="{{ $aksi }}" enctype="multipart/form-data" class="card space-y-5" novalidate>
        @csrf

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="kode_unit" class="label-field">Kode Unit</label>
                <input id="kode_unit" type="text" name="kode_unit" maxlength="20" value="{{ old('kode_unit', $unit->kode_unit) }}"
                       placeholder="PS5-04" class="input-field uppercase">
            </div>
            <div>
                <label for="jenis" class="label-field">Jenis</label>
                <select id="jenis" name="jenis" class="input-field" @disabled($terkunci)>
                    @foreach (['PS5', 'PS4'] as $j)
                        <option value="{{ $j }}" @selected(old('jenis', $unit->jenis) === $j)>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label for="nama_tampil" class="label-field">Nama Tampil</label>
            <input id="nama_tampil" type="text" name="nama_tampil" maxlength="100" value="{{ old('nama_tampil', $unit->nama_tampil) }}"
                   placeholder="PlayStation 5 Disc Edition" class="input-field">
        </div>

        <div>
            <label for="tag" class="label-field">Tag (opsional)</label>
            <input id="tag" type="text" name="tag" maxlength="100" value="{{ old('tag', $unit->tag) }}"
                   placeholder="PS5 DISC // 4K-120HZ" class="input-field">
        </div>

        <div>
            <label for="deskripsi" class="label-field">Deskripsi (opsional)</label>
            <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="1000" class="input-field">{{ old('deskripsi', $unit->deskripsi) }}</textarea>
        </div>

        <div>
            <label for="game" class="label-field">Daftar Game (opsional)</label>
            <textarea id="game" name="game" rows="2" maxlength="1000" class="input-field" placeholder="FC 24, Spider-Man 2, God of War Ragnarok">{{ old('game', $unit->game) }}</textarea>
            <p class="mt-1.5 text-xs text-outline">Pisahkan tiap game dengan koma.</p>
        </div>

        <div>
            <label for="status" class="label-field">Status</label>
            <select id="status" name="status" class="input-field sm:max-w-xs" @disabled($terkunci)>
                @if ($terkunci)
                    <option selected>Disewa</option>
                @else
                    @foreach (['Tersedia', 'Perawatan', 'Nonaktif'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $unit->status) === $s)>{{ $s }}</option>
                    @endforeach
                @endif
            </select>
            <p class="mt-1.5 text-xs text-outline">Perawatan: tampil di katalog tetapi tidak bisa dibooking. Nonaktif: disembunyikan sepenuhnya.</p>
        </div>

        <div class="border-t border-surface-variant/40 pt-5">
            <p class="label-field">Foto Unit</p>
            @if ($unit->exists && $unit->fotoUrl())
                <div class="mb-3 flex items-center gap-4">
                    <img src="{{ $unit->fotoUrl() }}" alt="Foto {{ $unit->kode_unit }}" class="h-24 w-24 rounded-lg object-cover" onerror="this.remove()">
                    <label class="flex items-center gap-2 text-sm text-on-surface-variant">
                        <input type="checkbox" name="hapus_foto" value="1" class="h-4 w-4 accent-primary-container"> Hapus foto saat ini
                    </label>
                </div>
            @endif
            <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                   class="block w-full text-sm text-on-surface-variant file:mr-4 file:rounded-lg file:border-0 file:bg-primary-container file:px-4 file:py-2 file:font-headline file:font-semibold file:text-on-surface">
            <p class="mt-1.5 text-xs text-outline">JPG, PNG, atau WEBP, maksimal 2 MB. Mengunggah foto baru menggantikan yang lama.</p>
        </div>

        <div class="flex flex-wrap gap-3 border-t border-surface-variant/40 pt-5">
            <button type="submit" class="btn-primary">{{ $unit->exists ? 'Simpan Perubahan' : 'Tambah Unit' }}</button>
            <a href="{{ route('admin.perangkat') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection