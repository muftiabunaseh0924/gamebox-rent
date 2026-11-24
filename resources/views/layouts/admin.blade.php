<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen"
      x-data="{ open: false }"
      @keydown.escape.window="open = false"
      @resize.window="if (window.innerWidth >= 768) open = false">
    @php
        $situs = app(\App\Services\SettingsService::class)->situs();

        $menu = [
            ['admin.dashboard', 'admin.dashboard', 'dashboard', 'Dasbor'],
            ['admin.pesanan*', 'admin.pesanan', 'receipt_long', 'Pesanan'],
            ['admin.pelanggan*', 'admin.pelanggan', 'group', 'Pelanggan'],
            ['admin.laporan*', 'admin.laporan', 'payments', 'Laporan'],
            ['admin.konfirmasi*', 'admin.konfirmasi', 'pin', 'Konfirmasi Kode'],
            ['admin.pengiriman*', 'admin.pengiriman', 'local_shipping', 'Pengiriman'],
            ['admin.armada*', 'admin.armada', 'videogame_asset', 'Jadwal & Lokasi'],
            ['admin.perangkat*', 'admin.perangkat', 'sports_esports', 'Perangkat'],
            ['admin.harga*', 'admin.harga', 'sell', 'Harga Paket'],
            ['admin.libur*', 'admin.libur', 'event_busy', 'Tanggal Libur'],
            ['admin.pengaturan*', 'admin.pengaturan', 'tune', 'Pengaturan'],
            ['admin.konten*', 'admin.konten', 'edit_note', 'Konten Beranda'],
            ['admin.email*', 'admin.email', 'mail', 'Templat Email'],
            ['admin.log', 'admin.log', 'history', 'Log Aktivitas'],
        ];

        // Menu yang paling sering dipakai di HP, tampil di bar bawah.
        $utama = collect($menu)
            ->filter(fn ($m) => in_array($m[1], ['admin.dashboard', 'admin.pesanan', 'admin.konfirmasi', 'admin.pengiriman'], true))
            ->values();
    @endphp

    {{-- Sidebar desktop --}}
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-surface-variant/40 bg-surface-container-lowest md:flex">
        @include('partials.admin-sidebar', ['menu' => $menu, 'situs' => $situs, 'drawer' => false])
    </aside>

    {{-- Drawer HP: muncul di bawah header agar tombol hamburger yang berubah jadi X tetap terlihat --}}
    <div class="md:hidden" x-cloak>
        <div x-show="open"
             x-transition.opacity.duration.300ms
             @click="open = false"
             class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm"
             aria-hidden="true"></div>

        <aside x-show="open"
               x-trap.noscroll="open"
               x-transition:enter="transition-transform duration-300 ease-out"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition-transform duration-200 ease-in"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed bottom-0 left-0 top-20 z-40 flex w-72 max-w-[85vw] flex-col border-r border-surface-variant/40 bg-surface-container-lowest shadow-2xl"
               aria-label="Menu navigasi">
            @include('partials.admin-sidebar', ['menu' => $menu, 'situs' => $situs, 'drawer' => true])
        </aside>
    </div>

    <div class="md:pl-64">
        <header class="sticky top-0 z-50 flex h-20 items-center justify-between gap-3 border-b border-surface-variant/40 bg-surface-container-lowest/90 px-4 backdrop-blur-xl md:px-8">
            <div class="flex min-w-0 items-center gap-3">
                {{-- Hamburger: tiga garis berubah menjadi X --}}
                <button type="button"
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                        aria-label="Buka atau tutup menu"
                        class="relative h-11 w-11 shrink-0 rounded-lg transition hover:bg-surface-container-high md:hidden">
                    <span class="absolute left-3 block h-0.5 w-5 rounded bg-on-surface transition-all duration-300"
                          :class="open ? 'top-[21px] rotate-45' : 'top-[13px]'"></span>
                    <span class="absolute left-3 top-[21px] block h-0.5 w-5 rounded bg-on-surface transition-all duration-300"
                          :class="open ? 'scale-x-0 opacity-0' : 'opacity-100'"></span>
                    <span class="absolute left-3 block h-0.5 w-5 rounded bg-on-surface transition-all duration-300"
                          :class="open ? 'top-[21px] -rotate-45' : 'top-[29px]'"></span>
                </button>
                <h1 class="truncate font-headline text-xl font-bold">@yield('heading', 'Dasbor')</h1>
            </div>
            <span class="hidden text-sm text-on-surface-variant sm:inline">{{ auth()->user()->name }}</span>
        </header>

        <main class="p-4 pb-24 md:p-8 md:pb-8">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>

    {{-- Bar bawah HP --}}
    <nav class="fixed inset-x-0 bottom-0 z-20 border-t border-surface-variant/40 bg-surface-container-lowest/90 pb-[env(safe-area-inset-bottom)] backdrop-blur-xl md:hidden"
         aria-label="Menu utama">
        <div class="grid grid-cols-5">
            @foreach ($utama as [$pola, $rute, $ikon, $label])
                @php $aktif = request()->routeIs($pola); @endphp
                <a href="{{ route($rute) }}"
                   class="flex flex-col items-center gap-1 px-1 py-2.5 text-[11px] font-semibold transition
                   {{ $aktif ? 'text-secondary' : 'text-on-surface-variant active:text-on-surface' }}">
                    <span class="material-symbols-outlined text-[22px] transition-transform duration-200 {{ $aktif ? 'scale-110' : '' }}">{{ $ikon }}</span>
                    <span class="max-w-full truncate">{{ \Illuminate\Support\Str::before($label, ' ') }}</span>
                </a>
            @endforeach
            <button type="button"
                    @click="open = !open"
                    class="flex flex-col items-center gap-1 px-1 py-2.5 text-[11px] font-semibold text-on-surface-variant transition active:text-on-surface">
                <span class="material-symbols-outlined text-[22px]">menu</span>
                <span>Menu</span>
            </button>
        </div>
    </nav>
</body>
</html>