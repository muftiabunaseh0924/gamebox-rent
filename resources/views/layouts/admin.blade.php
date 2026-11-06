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

        <nav class="flex-1 space-y-1 p-4">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 rounded-lg px-4 py-3 font-headline text-sm font-semibold
               {{ request()->routeIs('admin.dashboard') ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                <span class="material-symbols-outlined text-[20px]">dashboard</span> Dasbor
            </a>
            @foreach ([['receipt_long', 'Pesanan'], ['videogame_asset', 'Perangkat'], ['payments', 'Laporan']] as [$ikon, $label])
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