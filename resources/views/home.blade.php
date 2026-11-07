@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
<div class="space-y-20">

    {{-- ===== HERO ===== --}}
    <section class="relative overflow-hidden rounded-xl bg-surface-container-lowest p-8 md:p-12">
        <div class="pointer-events-none absolute inset-0 opacity-40"
             style="background: radial-gradient(circle at 70% 20%, rgba(47,107,255,.25), transparent 50%), radial-gradient(circle at 20% 80%, rgba(93,230,255,.18), transparent 55%);"></div>

        <div class="relative z-10 grid items-center gap-10 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-surface-container-high/80 px-4 py-1 font-headline text-xs font-semibold uppercase tracking-widest text-secondary">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-secondary"></span> {{ $badge }}
                </span>
                <h2 class="mt-4 font-headline text-4xl font-bold leading-tight tracking-tight md:text-5xl">{{ $judul }}</h2>
                @if ($deskripsi)
                    <p class="mt-4 max-w-2xl text-lg text-on-surface-variant">{{ $deskripsi }}</p>
                @endif
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#katalog" class="btn-primary">
                        <span class="material-symbols-outlined text-[20px]">sports_esports</span> Lihat Katalog
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn-secondary">Daftar Gratis</a>
                    @endguest
                </div>
            </div>

            <div class="grid gap-4 lg:col-span-2">
                @foreach ($poin as $i => $p)
                    <div class="card flex items-start gap-4 !p-5">
                        <span class="material-symbols-outlined rounded-lg bg-primary-container/20 p-2 text-secondary">
                            {{ ['event_available', 'verified_user', 'bolt'][$i] ?? 'check_circle' }}
                        </span>
                        <div>
                            <p class="font-headline font-semibold">{{ $p['judul'] }}</p>
                            <p class="text-sm text-on-surface-variant">{{ $p['isi'] }}</p>
                        </div>
                    </div>
                @endforeach
                <div class="card flex items-center justify-between !p-5">
                    <span class="text-sm text-on-surface-variant">Unit tersedia sekarang</span>
                    <span class="font-headline text-2xl font-bold text-secondary">{{ $jumlahTersedia }}<span class="text-base font-medium text-on-surface-variant"> / {{ $unit->count() }}</span></span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== KATALOG ===== --}}
    <section id="katalog" class="scroll-mt-28">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h3 class="font-headline text-3xl font-bold">Katalog Unit</h3>
                <p class="mt-1 text-on-surface-variant">Pilih konsol yang siap kamu sewa.</p>
            </div>
            <div class="flex gap-2" role="group" aria-label="Filter jenis konsol">
                @foreach (['semua' => 'Semua', 'PS5' => 'PS5', 'PS4' => 'PS4'] as $nilai => $label)
                    <button type="button" data-filter="{{ $nilai }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                            class="rounded-lg px-4 py-2 font-headline text-sm font-semibold transition {{ $loop->first ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($unit as $u)
                @php
                    $tersedia = $u->status === 'Tersedia';
                    $kelasStatus = match ($u->status) {
                        'Tersedia' => 'bg-emerald-500/15 text-emerald-300',
                        'Disewa' => 'bg-amber-500/15 text-amber-300',
                        'Perawatan' => 'bg-error-container/40 text-error',
                        default => 'bg-surface-container-high text-on-surface-variant',
                    };
                    $game = $u->daftarGame();
                    $paketMulai = $mulai->get($u->jenis);
                @endphp
                <article data-unit="{{ $u->jenis }}"
                         class="flex flex-col overflow-hidden rounded-xl border border-primary-container/15 bg-surface-container-low transition hover:border-cyan-400/40">
                    <div class="relative aspect-[16/10] bg-surface-container-lowest">
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-2"
                             style="background: radial-gradient(circle at 70% 20%, rgba(47,107,255,.35), transparent 55%), radial-gradient(circle at 20% 90%, rgba(93,230,255,.2), transparent 60%);">
                            <span class="material-symbols-outlined text-5xl text-secondary">sports_esports</span>
                            <span class="font-headline text-2xl font-bold tracking-wider">{{ $u->jenis }}</span>
                        </div>
                        @if ($u->fotoUrl())
                            <img src="{{ $u->fotoUrl() }}" alt="{{ $u->nama_tampil }}" loading="lazy"
                                 class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
                        @endif
                        <span class="absolute left-3 top-3 rounded-full bg-surface/80 px-3 py-1 font-headline text-xs font-bold uppercase tracking-wider text-secondary backdrop-blur">{{ $u->jenis }}</span>
                        <span class="absolute right-3 top-3 rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider backdrop-blur {{ $kelasStatus }}">{{ $u->status }}</span>
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <p class="font-headline text-xs font-semibold uppercase tracking-widest text-outline">{{ $u->kode_unit }}</p>
                        <h4 class="mt-1 font-headline text-xl font-bold">{{ $u->nama_tampil }}</h4>
                        @if ($u->tag)
                            <p class="mt-1 text-sm text-on-surface-variant">{{ $u->tag }}</p>
                        @endif

                        @if (count($game))
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach (array_slice($game, 0, 3) as $g)
                                    <span class="rounded-full bg-surface-container-high px-3 py-1 text-xs text-on-surface-variant">{{ $g }}</span>
                                @endforeach
                                @if (count($game) > 3)
                                    <span class="rounded-full bg-surface-container-high px-3 py-1 text-xs text-outline">+{{ count($game) - 3 }} game</span>
                                @endif
                            </div>
                        @endif

                        <div class="mt-auto pt-6">
                            @if ($paketMulai)
                                <p class="text-xs uppercase tracking-wider text-outline">Mulai dari</p>
                                <p class="font-headline text-2xl font-bold text-secondary">
                                    {{ $paketMulai->hargaRupiah() }}
                                    <span class="text-sm font-medium text-on-surface-variant">/ {{ $paketMulai->durasi_jam }} jam</span>
                                </p>
                            @endif

                            <div class="mt-4">
                                @if (! $tersedia)
                                    <span class="btn-secondary w-full cursor-not-allowed opacity-50">Tidak Tersedia</span>
                                @elseguest
                                    <a href="{{ route('login') }}" class="btn-primary w-full">Masuk untuk Menyewa</a>
                                @else
                                    <span class="btn-secondary w-full cursor-not-allowed opacity-70" title="Booking dibuka di tahap berikutnya">Booking Segera Dibuka</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="card col-span-full text-center text-on-surface-variant">Belum ada unit yang ditampilkan.</div>
            @endforelse
        </div>

        <div id="katalog-kosong" class="card mt-6 hidden text-center text-on-surface-variant">
            Tidak ada unit untuk jenis ini.
        </div>
    </section>

    {{-- ===== HARGA ===== --}}
    <section id="harga" class="scroll-mt-28">
        <h3 class="font-headline text-3xl font-bold">Harga Paket</h3>
        <p class="mt-1 text-on-surface-variant">Pilih durasi sesuai kebutuhan mainmu.</p>

        <div class="mt-8 grid gap-6 md:grid-cols-2">
            @forelse ($paket as $jenis => $daftar)
                <div class="card">
                    <div class="flex items-center gap-3 border-b border-surface-variant/40 pb-4">
                        <span class="material-symbols-outlined text-secondary">sports_esports</span>
                        <h4 class="font-headline text-xl font-bold">{{ $jenis }}</h4>
                    </div>
                    <ul class="divide-y divide-surface-variant/40">
                        @foreach ($daftar as $p)
                            <li class="flex items-center justify-between py-3">
                                <span class="text-on-surface-variant">{{ $p->durasi_jam }} jam</span>
                                <span class="font-headline font-semibold text-secondary">{{ $p->hargaRupiah() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <div class="card col-span-full text-center text-on-surface-variant">Daftar harga belum tersedia.</div>
            @endforelse
        </div>

        @if (count($kelengkapan))
            <div class="mt-6 flex flex-wrap items-center gap-3">
                <span class="font-headline text-sm font-semibold uppercase tracking-wider text-outline">Sudah termasuk</span>
                @foreach ($kelengkapan as $item)
                    <span class="rounded-full border border-primary-container/30 px-3 py-1 text-sm">{{ $item }}</span>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ===== CARA MENYEWA ===== --}}
    @if (count($langkah))
        <section id="cara" class="scroll-mt-28">
            <h3 class="font-headline text-3xl font-bold">Cara Menyewa</h3>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($langkah as $i => $l)
                    <div class="card">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-container font-headline font-bold">{{ $i + 1 }}</span>
                        <h4 class="mt-4 font-headline text-lg font-semibold">{{ $l['judul'] }}</h4>
                        <p class="mt-1 text-sm text-on-surface-variant">{{ $l['isi'] }}</p>
                    </div>
                @endforeach
            </div>

            @if ($operasional)
                <div class="card mt-6 flex items-center gap-4">
                    <span class="material-symbols-outlined text-3xl text-secondary">schedule</span>
                    <div>
                        <p class="font-headline text-lg font-semibold">{{ $operasional['judul'] }}</p>
                        <p class="text-sm text-on-surface-variant">{{ $operasional['isi'] }}</p>
                    </div>
                </div>
            @endif
        </section>
    @endif
</div>

<script>
    (function () {
        var tombol = document.querySelectorAll('[data-filter]');
        var kartu = document.querySelectorAll('[data-unit]');
        var kosong = document.getElementById('katalog-kosong');
        var aktifKelas = ['bg-primary-container', 'text-on-surface'];
        var pasifKelas = ['text-on-surface-variant', 'hover:bg-surface-container-high'];

        tombol.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var filter = btn.dataset.filter;
                var tampil = 0;

                kartu.forEach(function (k) {
                    var cocok = filter === 'semua' || k.dataset.unit === filter;
                    k.classList.toggle('hidden', !cocok);
                    if (cocok) { tampil++; }
                });
                kosong.classList.toggle('hidden', tampil !== 0);

                tombol.forEach(function (b) {
                    var aktif = b === btn;
                    b.setAttribute('aria-pressed', aktif ? 'true' : 'false');
                    aktifKelas.forEach(function (c) { b.classList.toggle(c, aktif); });
                    pasifKelas.forEach(function (c) { b.classList.toggle(c, !aktif); });
                });
            });
        });
    })();
</script>
@endsection