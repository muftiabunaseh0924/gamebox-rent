
@extends('layouts.admin')

@section('title', 'Konten Beranda')
@section('heading', 'Konten Beranda')

@section('content')
@php
    $baris = function (string $nama, array $dariDb) {
        if (old($nama . '.judul') === null) {
            return $dariDb;
        }

        return collect(old($nama . '.judul'))
            ->map(function ($judul, $i) use ($nama) {
                return [
                    'judul' => (string) $judul,
                    'isi' => (string) old($nama . '.isi.' . $i),
                ];
            })
            ->values()
            ->all();
    };

    $v = function (string $kunci) use ($d) {
        return old($kunci, $d[$kunci] ?? '');
    };
@endphp

<div class="mx-auto max-w-4xl">

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
            <p class="mb-1 font-semibold">Periksa isian berikut:</p>

            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="mb-6 rounded-lg bg-surface-container-high px-4 py-3 text-sm text-on-surface-variant">
        Perubahan langsung tampil di halaman beranda. Jangan memakai tanda
        <code>|</code> atau <code>::</code> di dalam teks.
    </p>

    <form method="POST"
          action="{{ route('admin.konten.simpan') }}"
          class="space-y-6"
          novalidate>

        @csrf

        {{-- Bagian Utama --}}
        <section class="card space-y-5">

            <h2 class="font-headline text-xl font-bold">
                Bagian Utama (Hero)
            </h2>

            <div>
                <label for="beranda_judul" class="label-field">
                    Judul
                </label>

                <input
                    id="beranda_judul"
                    type="text"
                    name="beranda_judul"
                    maxlength="120"
                    value="{{ $v('beranda_judul') }}"
                    class="input-field"
                >
            </div>

            <div>
                <label for="beranda_deskripsi" class="label-field">
                    Deskripsi
                </label>

                <textarea
                    id="beranda_deskripsi"
                    name="beranda_deskripsi"
                    rows="2"
                    maxlength="300"
                    class="input-field"
                >{{ $v('beranda_deskripsi') }}</textarea>
            </div>

            <div>
                <label for="badge_hero" class="label-field">
                    Badge kecil di atas judul
                </label>

                <input
                    id="badge_hero"
                    type="text"
                    name="badge_hero"
                    maxlength="60"
                    value="{{ $v('badge_hero') }}"
                    class="input-field sm:max-w-md"
                >
            </div>

            <div>
                <p class="label-field">
                    Poin Keunggulan (maksimal 3)
                </p>

                @include('admin.konten._baris', [
                    'nama' => 'hero_poin',
                    'baris' => $baris('hero_poin', $d['hero_poin']),
                    'maks' => 3,
                    'maksIsi' => 100
                ])
            </div>

        </section>

        {{-- Cara Menyewa dan Informasi Layanan --}}
        <section class="card space-y-5">

            <h2 class="font-headline text-xl font-bold">
                Cara Menyewa dan Info Layanan
            </h2>

            <div>
                <p class="label-field">
                    Langkah Cara Menyewa (maksimal 6)
                </p>

                @include('admin.konten._baris', [
                    'nama' => 'cara_menyewa',
                    'baris' => $baris('cara_menyewa', $d['cara_menyewa']),
                    'maks' => 6,
                    'maksIsi' => 160
                ])
            </div>

            <div class="grid gap-4 sm:grid-cols-2">

                <div>
                    <label for="operasional_judul" class="label-field">
                        Info Operasional: Judul
                    </label>

                    <input
                        id="operasional_judul"
                        type="text"
                        name="operasional_judul"
                        maxlength="40"
                        value="{{ old('operasional_judul', $d['operasional']['judul']) }}"
                        class="input-field"
                        placeholder="Buka 24 Jam"
                    >
                </div>

                <div>
                    <label for="operasional_isi" class="label-field">
                        Info Operasional: Isi
                    </label>

                    <input
                        id="operasional_isi"
                        type="text"
                        name="operasional_isi"
                        maxlength="120"
                        value="{{ old('operasional_isi', $d['operasional']['isi']) }}"
                        class="input-field"
                        placeholder="Layanan sewa dan pengambilan unit siap setiap hari"
                    >
                </div>

            </div>

            <div>
                <label for="kelengkapan_standar" class="label-field">
                    Kelengkapan Standar (pisahkan dengan koma, maksimal 8)
                </label>

                <input
                    id="kelengkapan_standar"
                    type="text"
                    name="kelengkapan_standar"
                    maxlength="300"
                    value="{{ $v('kelengkapan_standar') }}"
                    class="input-field"
                    placeholder="2 Controller, HDMI, Kabel Power"
                >
            </div>

        </section>

        {{-- Catatan dan Ketentuan --}}
        <section class="card space-y-5">

            <h2 class="font-headline text-xl font-bold">
                Catatan dan Ketentuan
            </h2>

            <p class="text-sm text-on-surface-variant">
                Teks ini disimpan untuk dipakai di halaman booking dan ketentuan.
                Bisa memuat <code>@{{buffer_jam}}</code> dan
                <code>@{{denda}}</code>.
                Belum semuanya tampil di halaman depan.
            </p>

            <div>
                <label for="catatan_buffer" class="label-field">
                    Catatan jeda antar penyewa
                </label>

                <textarea
                    id="catatan_buffer"
                    name="catatan_buffer"
                    rows="2"
                    maxlength="300"
                    class="input-field"
                >{{ $v('catatan_buffer') }}</textarea>
            </div>

            <div>
                <label for="catatan_pengiriman" class="label-field">
                    Catatan pengiriman
                </label>

                <textarea
                    id="catatan_pengiriman"
                    name="catatan_pengiriman"
                    rows="2"
                    maxlength="400"
                    class="input-field"
                >{{ $v('catatan_pengiriman') }}</textarea>
            </div>

            <div>
                <label for="catatan_identitas" class="label-field">
                    Catatan identitas
                </label>

                <textarea
                    id="catatan_identitas"
                    name="catatan_identitas"
                    rows="2"
                    maxlength="400"
                    class="input-field"
                >{{ $v('catatan_identitas') }}</textarea>
            </div>

            <div>
                <label for="syarat_ketentuan" class="label-field">
                    Syarat dan ketentuan
                </label>

                <textarea
                    id="syarat_ketentuan"
                    name="syarat_ketentuan"
                    rows="4"
                    maxlength="1000"
                    class="input-field"
                >{{ $v('syarat_ketentuan') }}</textarea>
            </div>

        </section>

        {{-- Tombol Aksi --}}
        <div class="flex flex-wrap items-center gap-3">

            <button type="submit" class="btn-primary">
                <span class="material-symbols-outlined text-[20px]">
                    save
                </span>
                Simpan Konten
            </button>

            <a
                href="{{ route('home') }}"
                target="_blank"
                rel="noopener"
                class="btn-secondary"
            >
                Lihat Beranda
            </a>

        </div>

    </form>

</div>

<script>
document.querySelectorAll('[data-pasangan]').forEach(function (kotak) {
    var daftar = kotak.querySelector('[data-daftar]');
    var templat = kotak.querySelector('[data-templat]');
    var tombolTambah = kotak.querySelector('[data-tambah]');
    var maks = parseInt(kotak.dataset.maks, 10);

    if (!daftar || !templat || !tombolTambah) {
        return;
    }

    tombolTambah.addEventListener('click', function () {
        if (daftar.children.length >= maks) {
            alert('Maksimal ' + maks + ' baris.');
            return;
        }

        daftar.appendChild(templat.content.cloneNode(true));
    });

    kotak.addEventListener('click', function (e) {
        var tombol = e.target.closest('[data-hapus]');

        if (tombol) {
            var baris = tombol.closest('[data-baris]');

            if (baris) {
                baris.remove();
            }
        }
    });
});
</script>
@endsection
