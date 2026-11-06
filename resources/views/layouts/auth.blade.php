<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    @include('partials.head')
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
    <div class="pointer-events-none absolute inset-0 opacity-40"
         style="background: radial-gradient(circle at 75% 15%, rgba(47,107,255,.28), transparent 50%), radial-gradient(circle at 15% 85%, rgba(93,230,255,.18), transparent 55%);"></div>

    <div class="relative z-10 w-full max-w-md">
        <a href="{{ route('home') }}" class="mb-6 flex items-center justify-center gap-3">
            <span class="material-symbols-outlined text-4xl text-secondary">sports_esports</span>
            <span class="font-headline text-2xl font-bold uppercase tracking-wider text-secondary">GameBox</span>
        </a>

        <div class="rounded-xl border border-cyan-400/25 bg-[rgba(30,39,73,0.75)] p-8 shadow-[0_0_32px_-4px_rgba(47,107,255,0.35)] backdrop-blur-xl">
            @include('partials.flash')
            @yield('content')
        </div>

        <p class="mt-6 text-center text-sm text-on-surface-variant">@yield('footer')</p>
    </div>
</body>
</html>