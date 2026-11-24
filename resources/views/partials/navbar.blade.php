@php
    $situs = app(\App\Services\SettingsService::class)->situs();
    $pengguna = auth()->user();
    $dasbor = $pengguna?->role === 'admin' ? route('admin.dashboard') : route('dashboard');
    $tautan = 'rounded-lg px-4 py-2 font-headline text-sm font-semibold tracking-wide transition-colors text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface';

    // Dipakai oleh panel menu di HP: [label, url, ikon, aktif?]
    $menuHp = [
        ['Beranda', route('home'), 'home', request()->routeIs('home')],
        ['Katalog', route('home').'#katalog', 'sports_esports', false],
        ['Harga', route('home').'#harga', 'sell', false],
        ['Cara Sewa', route('home').'#cara', 'help', false],
    ];
@endphp

<div x-data="{ open: false }"
     @keydown.escape.window="open = false"
     @resize.window="if (window.innerWidth >= 768) open = false">

    <header class="fixed inset-x-0 top-0 z-50 border-b border-surface-variant/40 bg-surface-container-lowest/80 backdrop-blur-xl">
        <div class="mx-auto flex h-20 max-w-[1320px] items-center justify-between gap-6 px-4 md:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                @include('partials.logo', ['ukuran' => 'text-3xl'])
                <span class="flex min-w-0 flex-col leading-none">
                    <span class="truncate font-headline text-lg font-bold uppercase tracking-wider text-secondary">{{ $situs['nama_situs'] }}</span>
                    @if ($situs['tagline'] !== '')
                        <span class="mt-1 truncate font-headline text-[11px] font-semibold uppercase tracking-widest text-outline">{{ \Illuminate\Support\Str::limit($situs['tagline'], 30) }}</span>
                    @endif
                </span>
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Menu utama">
                <a href="{{ route('home') }}"
                   class="rounded-lg px-4 py-2 font-headline text-sm font-semibold tracking-wide transition-colors
                   {{ request()->routeIs('home') ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                    Beranda
                </a>
                <a href="{{ route('home') }}#katalog" class="{{ $tautan }}">Katalog</a>
                <a href="{{ route('home') }}#harga" class="{{ $tautan }}">Harga</a>
                <a href="{{ route('home') }}#cara" class="{{ $tautan }}">Cara Sewa</a>
            </nav>

            <div class="flex shrink-0 items-center gap-3">
                {{-- Desktop: tombol akun --}}
                <div class="hidden items-center gap-3 md:flex">
                    @guest
                        <a href="{{ route('login') }}" class="btn-secondary !py-2">Masuk</a>
                        <a href="{{ route('register') }}" class="btn-primary !py-2">Daftar</a>
                    @else
                        <a href="{{ $dasbor }}" class="flex items-center gap-2 rounded-full border border-surface-variant/40 bg-surface-container-low/70 py-1 pl-1 pr-4">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-container font-headline text-sm font-bold">
                                {{ Str::upper(Str::substr($pengguna->name, 0, 1)) }}
                            </span>
                            <span class="font-headline text-sm font-semibold">{{ Str::words($pengguna->name, 1, '') }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-secondary !py-2">Keluar</button>
                        </form>
                    @endguest
                </div>

                {{-- HP: hamburger, tiga garis berubah menjadi X --}}
                <button type="button"
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                        aria-controls="menu-hp"
                        aria-label="Buka atau tutup menu"
                        class="relative h-11 w-11 shrink-0 rounded-lg transition hover:bg-surface-container-high md:hidden">
                    <span class="absolute left-3 block h-0.5 w-5 rounded bg-on-surface transition-all duration-300"
                          :class="open ? 'top-[21px] rotate-45' : 'top-[13px]'"></span>
                    <span class="absolute left-3 top-[21px] block h-0.5 w-5 rounded bg-on-surface transition-all duration-300"
                          :class="open ? 'scale-x-0 opacity-0' : 'opacity-100'"></span>
                    <span class="absolute left-3 block h-0.5 w-5 rounded bg-on-surface transition-all duration-300"
                          :class="open ? 'top-[21px] -rotate-45' : 'top-[29px]'"></span>
                </button>
            </div>
        </div>
    </header>

    {{-- Panel menu HP. Sengaja di luar <header>: backdrop-blur membuat elemen fixed di dalamnya terkurung di header. --}}
    <div class="md:hidden" x-cloak>
        <div x-show="open"
             x-transition.opacity.duration.300ms
             @click="open = false"
             class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm"
             aria-hidden="true"></div>

        <div id="menu-hp"
             x-show="open"
             x-trap.noscroll="open"
             x-transition:enter="transition duration-300 ease-out"
             x-transition:enter-start="-translate-y-4 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition duration-200 ease-in"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-4 opacity-0"
             class="fixed inset-x-0 top-20 z-40 max-h-[calc(100dvh-5rem)] overflow-y-auto border-b border-surface-variant/40 bg-surface-container-lowest/95 shadow-2xl backdrop-blur-xl">
            <nav class="space-y-1 p-4" aria-label="Menu navigasi">
                @foreach ($menuHp as $i => [$label, $url, $ikon, $aktif])
                    <a href="{{ $url }}"
                       @click="open = false"
                       style="--d: {{ 60 + $i * 45 }}ms"
                       class="menu-item-down flex items-center gap-3 rounded-lg px-4 py-3 font-headline text-sm font-semibold transition-colors
                       {{ $aktif ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                        <span class="material-symbols-outlined text-[20px]">{{ $ikon }}</span> {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="menu-item-down space-y-3 border-t border-surface-variant/40 p-4" style="--d: 260ms">
                @guest
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('login') }}" class="btn-secondary">Masuk</a>
                        <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
                    </div>
                @else
                    <a href="{{ $dasbor }}" class="flex items-center gap-3 rounded-lg border border-surface-variant/40 bg-surface-container-low/70 p-2 pr-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-container font-headline text-base font-bold">
                            {{ Str::upper(Str::substr($pengguna->name, 0, 1)) }}
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate font-headline text-sm font-semibold">{{ $pengguna->name }}</span>
                            <span class="block text-xs text-on-surface-variant">Buka dasbor</span>
                        </span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary w-full">
                            <span class="material-symbols-outlined text-[20px]">logout</span> Keluar
                        </button>
                    </form>
                @endguest
            </div>
        </div>
    </div>
</div>