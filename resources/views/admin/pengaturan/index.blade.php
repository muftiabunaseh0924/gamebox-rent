@extends('layouts.admin')
@section('title', 'Pengaturan')
@section('heading', 'Pengaturan Sistem')

@section('content')
<div class="mx-auto max-w-5xl">
    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
            <p class="mb-1 font-semibold">Periksa isian berikut:</p>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $pesan) <li>{{ $pesan }}</li> @endforeach
            </ul>
        </div>
    @endif

    <p class="mb-6 rounded-lg bg-surface-container-high px-4 py-3 text-sm text-on-surface-variant">
        Pengaturan operasional dan harga berlaku untuk <strong>booking baru</strong>. Pesanan yang sudah dibuat menyimpan nilainya sendiri (harga, jeda, denda), jadi tidak berubah.
    </p>

    <form method="POST" action="{{ route('admin.pengaturan.simpan') }}" class="space-y-6" novalidate>
        @csrf

        @foreach ($kelompok as $grup)
            <section class="card space-y-5">
                <h2 class="flex items-center gap-2 font-headline text-xl font-bold">
                    <span class="material-symbols-outlined text-secondary">{{ $grup['ikon'] }}</span> {{ $grup['judul'] }}
                </h2>

                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach ($grup['kolom'] as $kunci => $d)
                        @php
                            $nama = 's['.$kunci.']';
                            $isi = old('s.'.$kunci, $nilai[$kunci]);
                            $galat = $errors->first('s.'.$kunci);
                        @endphp
                        <div class="{{ ($d['lebar'] ?? false) ? 'sm:col-span-2' : '' }}">
                            <label for="s-{{ $kunci }}" class="label-field">{{ $d['label'] }}</label>

                            @if ($d['tipe'] === 'bool')
                                <select id="s-{{ $kunci }}" name="{{ $nama }}" class="input-field sm:max-w-xs">
                                    <option value="1" @selected((string) $isi === '1')>Aktif</option>
                                    <option value="0" @selected((string) $isi === '0')>Tidak aktif</option>
                                </select>
                            @else
                                <div class="flex items-center gap-3">
                                    <input id="s-{{ $kunci }}" name="{{ $nama }}" value="{{ $isi }}"
                                           type="{{ $d['tipe'] === 'int' ? 'number' : 'text' }}"
                                           @if ($d['tipe'] === 'int') min="{{ $d['min'] }}" max="{{ $d['max'] }}" @endif
                                           @if ($d['tipe'] === 'teks') maxlength="{{ $d['max'] }}" @endif
                                           class="input-field {{ $galat ? '!border-error' : '' }}">
                                    @if ($d['satuan'] !== '')
                                        <span class="shrink-0 text-sm text-on-surface-variant">{{ $d['satuan'] }}</span>
                                    @endif
                                </div>
                            @endif

                            <p class="mt-1.5 text-xs text-outline">{{ $d['bantuan'] }}</p>
                            @if ($galat) <p class="error-text">{{ $galat }}</p> @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="btn-primary"><span class="material-symbols-outlined text-[20px]">save</span> Simpan Pengaturan</button>
            <a href="{{ route('admin.pengaturan') }}" class="btn-secondary">Muat Ulang</a>
        </div>
    </form>

    <section class="card mt-8 space-y-3">
        <h2 class="flex items-center gap-2 font-headline text-lg font-bold">
            <span class="material-symbols-outlined text-secondary">palette</span> Tema (tetap dari desain Stitch)
        </h2>
        <p class="text-sm text-on-surface-variant">Warna dan font mengikuti tema desain dan tidak diubah dari panel ini. Nilai di bawah hanya untuk informasi.</p>
        <dl class="grid gap-2 text-sm sm:grid-cols-2">
            @foreach ($tema as $kunci => $nilaiTema)
                <div class="flex items-center justify-between rounded-lg bg-surface-container-lowest px-3 py-2">
                    <dt class="text-outline">{{ $kunci }}</dt>
                    <dd class="flex items-center gap-2 font-semibold">
                        @if (str_starts_with((string) $nilaiTema, '#'))
                            <span class="inline-block h-4 w-4 rounded border border-white/20" style="background: {{ $nilaiTema }}"></span>
                        @endif
                        {{ $nilaiTema }}
                    </dd>
                </div>
            @endforeach
        </dl>
    </section>
</div>
@endsection