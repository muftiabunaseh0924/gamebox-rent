@php
    $pengguna = auth()->user();
    $dasbor = $pengguna?->role === 'admin' ? route('admin.dashboard') : route('dashboard');
@endphp
<header class="fixed inset-x-0 top-0 z-50 border-b border-surface-variant/40 bg-surface-container-lowest/80 backdrop-blur-xl">
    <div class="mx-auto flex h-20 max-w-[1320px] items-center justify-between gap-6 px-4 md:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <span class="material-symbols-outlined text-3xl text-secondary">sports_esports</span>
            <span class="flex flex-col leading-none">
                <span class="font-headline text-lg font-bold uppercase tracking-wider text-secondary">GameBox</span>
                <span class="mt-1 font-headline text-[11px] font-semibold uppercase tracking-widest text-outline">PlayStation Rental</span>
            </span>
        </a>

        <nav class="hidden items-center gap-2 lg:flex">
            <a href="{{ route('home') }}"
               class="rounded-lg px-4 py-2 font-headline text-sm font-semibold tracking-wide transition-colors
               {{ request()->routeIs('home') ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                Beranda
            </a>
        </nav>

        <div class="flex items-center gap-3">
            @guest
                <a href="{{ route('login') }}" class="btn-secondary !py-2">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary !py-2">Daftar</a>
            @else
                <a href="{{ $dasbor }}" class="hidden items-center gap-2 rounded-full border border-surface-variant/40 bg-surface-container-low/70 py-1 pl-1 pr-4 sm:flex">
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
    </div>
</header>