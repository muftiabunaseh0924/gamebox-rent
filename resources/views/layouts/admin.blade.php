<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen">
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-surface-variant/40 bg-surface-container-lowest md:flex">
        <div class="flex h-20 items-center gap-3 border-b border-surface-variant/40 px-6">
            <span class="material-symbols-outlined text-3xl text-secondary">admin_panel_settings</span>
            <span class="flex flex-col leading-none">
                <span class="font-headline text-lg font-bold uppercase tracking-wider text-secondary">GameBox</span>
                <span class="mt-1 font-headline text-[11px] font-semibold uppercase tracking-widest text-outline">Panel Admin</span>
            </span>
        </div>

        @php
            $menu = [
                ['admin.dashboard', 'admin.dashboard', 'dashboard', 'Dasbor'],
                ['admin.konfirmasi*', 'admin.konfirmasi', 'pin', 'Konfirmasi Kode'],
                ['admin.pengiriman*', 'admin.pengiriman', 'local_shipping', 'Pengiriman'],
                ['admin.armada*', 'admin.armada', 'videogame_asset', 'Jadwal & Lokasi'],
            ];
            $menuNanti = [['receipt_long', 'Pesanan'], ['payments', 'Laporan']];
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

        <form method="POST" action="{{ route('admin.logout') }}" class="border-t border-surface-variant/40 p-4">
            @csrf
            <button type="submit" class="btn-secondary w-full">
                <span class="material-symbols-outlined text-[20px]">logout</span> Keluar
            </button>
        </form>
    </aside>

    <div class="md:pl-64">
        <header class="flex h-20 items-center justify-between border-b border-surface-variant/40 bg-surface-container-lowest/80 px-4 backdrop-blur-xl md:px-8">
            <h1 class="font-headline text-xl font-bold">@yield('heading', 'Dasbor')</h1>
            <div class="flex items-center gap-3">
                <nav class="flex gap-2 md:hidden">
                    <a href="{{ route('admin.konfirmasi') }}" class="btn-secondary !px-3 !py-2" aria-label="Konfirmasi Kode"><span class="material-symbols-outlined text-[18px]">pin</span></a>
                    <a href="{{ route('admin.pengiriman') }}" class="btn-secondary !px-3 !py-2" aria-label="Pengiriman"><span class="material-symbols-outlined text-[18px]">local_shipping</span></a>
                    <a href="{{ route('admin.armada') }}" class="btn-secondary !px-3 !py-2" aria-label="Jadwal dan Lokasi"><span class="material-symbols-outlined text-[18px]">videogame_asset</span></a>
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