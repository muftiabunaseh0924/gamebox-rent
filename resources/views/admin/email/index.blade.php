
@extends('layouts.admin')

@section('title', 'Templat Email')
@section('heading', 'Templat Email')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">

    {{-- Placeholder yang tersedia --}}
    <section class="card space-y-3">
        <h2 class="font-headline text-lg font-bold">
            Placeholder yang tersedia
        </h2>

        <p class="text-sm text-on-surface-variant">
            Tulis dalam kurung kurawal ganda, misalnya
            <code>@{{nama}}</code>.
            Nilainya diisi otomatis saat email dikirim.
        </p>

        <dl class="grid gap-2 text-sm sm:grid-cols-2">
            @foreach ($placeholder as $kunci => $arti)
                <div class="flex items-center justify-between gap-3 rounded-lg bg-surface-container-lowest px-3 py-2">
                    <dt>
                        <code>@php echo e('{' . '{' . $kunci . '}' . '}'); @endphp</code>
                    </dt>

                    <dd class="text-on-surface-variant">
                        {{ $arti }}
                    </dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Daftar template email --}}
    @foreach ($jenis as $kunci => [$nama, $dipakai, $keterangan])

        @php
            $bag = 'tpl_' . $kunci;
            $adaGalat = $errors->hasBag($bag);
            $galat = $errors->getBag($bag);
            $baris = $templat->get($kunci);

            $subjek = $adaGalat
                ? old('subjek')
                : ($baris->subjek ?? '');

            $isi = $adaGalat
                ? old('isi')
                : ($baris->isi ?? '');
        @endphp

        <form
            method="POST"
            action="{{ route('admin.email.simpan', $kunci) }}"
            class="card space-y-4"
            novalidate
            data-templat-email
            data-pratinjau="{{ route('admin.email.pratinjau', $kunci) }}"
            data-uji="{{ route('admin.email.uji', $kunci) }}"
        >
            @csrf

            {{-- Header template --}}
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="font-headline text-lg font-bold">
                        {{ $nama }}
                    </h2>

                    <p class="text-xs text-outline">
                        {{ $keterangan }}
                    </p>
                </div>

                <span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider {{ $dipakai ? 'bg-emerald-500/15 text-emerald-300' : 'bg-surface-container-high text-on-surface-variant' }}">
                    {{ $dipakai ? 'Dipakai sekarang' : 'Belum aktif' }}
                </span>
            </div>

            {{-- Pesan validasi --}}
            @if ($adaGalat)
                <div class="rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($galat->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Subjek email --}}
            <div>
                <label class="label-field" for="subjek_{{ $kunci }}">
                    Subjek
                </label>

                <input
                    type="text"
                    id="subjek_{{ $kunci }}"
                    name="subjek"
                    maxlength="200"
                    value="{{ $subjek }}"
                    class="input-field"
                >
            </div>

            {{-- Isi email --}}
            <div>
                <label class="label-field" for="isi_{{ $kunci }}">
                    Isi Email
                </label>

                <textarea
                    id="isi_{{ $kunci }}"
                    name="isi"
                    rows="4"
                    maxlength="2000"
                    class="input-field"
                >{{ $isi }}</textarea>
            </div>

            {{-- Tombol aksi --}}
            <div class="flex flex-wrap gap-3">

                <button
                    type="submit"
                    class="btn-primary !py-2"
                >
                    <span class="material-symbols-outlined text-[18px]">
                        save
                    </span>
                    Simpan
                </button>

                <button
                    type="button"
                    data-aksi="pratinjau"
                    class="btn-secondary !py-2"
                >
                    Pratinjau
                </button>

                <button
                    type="button"
                    data-aksi="uji"
                    class="btn-secondary !py-2"
                >
                    Kirim Uji ke Email Saya
                </button>

            </div>

            {{-- Pesan status --}}
            <p
                data-pesan
                class="hidden rounded-lg bg-surface-container-high px-4 py-3 text-sm"
            ></p>

            {{-- Hasil pratinjau --}}
            <div
                data-hasil
                class="hidden rounded-lg border border-primary-container/30 bg-surface-container-lowest px-4 py-3 text-sm"
            >
                <p class="label-field !mb-1">
                    Pratinjau (data contoh)
                </p>

                <p
                    data-hasil-subjek
                    class="font-semibold"
                ></p>

                <p
                    data-hasil-isi
                    class="mt-2 whitespace-pre-line text-on-surface-variant"
                ></p>
            </div>

        </form>
    @endforeach

</div>

{{-- JavaScript --}}
<script>
(function () {
    var csrfElement = document.querySelector('meta[name="csrf-token"]');
    var csrf = csrfElement ? csrfElement.content : '';

    document.querySelectorAll('[data-templat-email]').forEach(function (form) {
        var pesan = form.querySelector('[data-pesan]');
        var hasil = form.querySelector('[data-hasil]');

        function tampil(teks, galat) {
            pesan.textContent = teks;

            pesan.className =
                'rounded-lg px-4 py-3 text-sm ' +
                (galat
                    ? 'bg-error-container/30 text-error'
                    : 'bg-surface-container-high text-on-surface');
        }

        async function kirim(url) {
            var r = await fetch(url, {
                method: 'POST',

                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    subjek: form.elements.subjek.value,
                    isi: form.elements.isi.value
                })
            });

            var data = {};

            try {
                data = await r.json();
            } catch (e) {
                data = {};
            }

            return {
                ok: r.ok,
                data: data
            };
        }

        function galatPertama(data) {
            if (data.errors) {
                return Object.values(data.errors)[0][0];
            }

            return data.message || 'Terjadi kesalahan.';
        }

        form.querySelectorAll('[data-aksi]').forEach(function (tombol) {
            tombol.addEventListener('click', async function () {
                tombol.disabled = true;

                try {
                    if (tombol.dataset.aksi === 'pratinjau') {
                        var p = await kirim(form.dataset.pratinjau);

                        if (!p.ok) {
                            tampil(galatPertama(p.data), true);
                            return;
                        }

                        pesan.className = 'hidden';

                        form.querySelector('[data-hasil-subjek]').textContent =
                            p.data.subjek;

                        form.querySelector('[data-hasil-isi]').textContent =
                            p.data.isi;

                        hasil.classList.remove('hidden');

                    } else {
                        tampil('Mengirim email uji...', false);

                        var u = await kirim(form.dataset.uji);

                        tampil(
                            u.ok
                                ? (u.data.pesan || 'Email uji berhasil dikirim.')
                                : galatPertama(u.data),
                            !u.ok
                        );
                    }

                } catch (e) {
                    tampil(
                        'Terjadi kesalahan jaringan. Coba lagi.',
                        true
                    );

                } finally {
                    tombol.disabled = false;
                }
            });
        });
    });
})();
</script>
@endsection
