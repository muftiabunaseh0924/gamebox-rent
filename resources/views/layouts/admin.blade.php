<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen">
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col overflow-y-auto border-r border-surface-variant/40 bg-surface-container-lowest md:flex">
        <div class="flex h-20 shrink-0 items-center gap-3 border-b border-surface-variant/40 px-6">
            <span class="material-symbols-outlined text-3xl text-secondary">admin_panel_settings</span>
            <span class="flex flex-col leading-none">
                <span class="font-headline text-lg font-bold uppercase tracking-wider text-secondary">GameBox</span>
                <span class="mt-1 font-headline text-[11px] font-semibold uppercase tracking-widest text-outline">Panel Admin</span>
            </span>
        </div>

        @php
            $menu = [
                ['admin.dashboard', 'admin.dashboard', 'dashboard', 'Dasbor'],
                ['admin.pesanan*', 'admin.pesanan', 'receipt_long', 'Pesanan'],
                ['admin.konfirmasi*', 'admin.konfirmasi', 'pin', 'Konfirmasi Kode'],
                ['admin.pengiriman*', 'admin.pengiriman', 'local_shipping', 'Pengiriman'],
                ['admin.armada*', 'admin.armada', 'videogame_asset', 'Jadwal & Lokasi'],
                ['admin.perangkat*', 'admin.perangkat', 'sports_esports', 'Perangkat'],
                ['admin.harga*', 'admin.harga', 'sell', 'Harga Paket'],
                ['admin.libur*', 'admin.libur', 'event_busy', 'Tanggal Libur'],
            ];
            $menuNanti = [['payments', 'Laporan'], ['tune', 'Pengaturan']];
        @endphp
        <nav class="flex-1 space-y-1 p-4">
            @foreach ($menu as [$pola, $rute, $ikon, $label])
                <a href="{{ route($rute) }}"
                   class="flex items-center gap-3 rounded-lg px-4 py-3 font-headline text-sm font-semibold
                   {{ request()->routeIs($pola) ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                    <span class="material-symbols-outlined text-[20px]">{{ $ikon }}</span> {{ $label }}
                </a>
            @endforeach
            @foreach ($menuNanti as [$ikon, $label])
                <span class="flex cursor-not-allowed items-center justify-between rounded-lg px-4 py-3 font-headline text-sm font-semibold text-outline/70">
                    <span class="flex items-center gap-3"><span class="material-symbols-outlined text-[20px]">{{ $ikon }}</span> {{ $label }}</span>
                    <span class="rounded-full bg-surface-container-high px-2 py-0.5 text-[10px] uppercase tracking-wider">Segera</span>
                </span>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0 border-t border-surface-variant/40 p-4">
            @csrf
            <button type="submit" class="btn-secondary w-full">
                <span class="material-symbols-outlined text-[20px]">logout</span> Keluar
            </button>
        </form>
    </aside>

    <div class="md:pl-64">
        <header class="flex h-20 items-center justify-between gap-3 border-b border-surface-variant/40 bg-surface-container-lowest/80 px-4 backdrop-blur-xl md:px-8">
            <h1 class="shrink-0 font-headline text-xl font-bold">@yield('heading', 'Dasbor')</h1>
            <div class="flex min-w-0 items-center gap-3">
                <nav class="flex gap-2 overflow-x-auto md:hidden">
                    @foreach ($menu as [$pola, $rute, $ikon, $label])
                        <a href="{{ route($rute) }}" class="btn-secondary !px-3 !py-2" aria-label="{{ $label }}"><span class="material-symbols-outlined text-[18px]">{{ $ikon }}</span></a>
                    @endforeach
                </nav>
                <span class="hidden text-sm text-on-surface-variant sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('admin.logout') }}" class="md:hidden">
                    @csrf
                    <button type="submit" class="btn-secondary !py-2">Keluar</button>
                </form>
            </div>
        </header>

        <main class="p-4 md:p-8">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</body>
</html>