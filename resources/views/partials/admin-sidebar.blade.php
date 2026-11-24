@php $drawer = $drawer ?? false; @endphp

@unless ($drawer)
    <div class="flex h-20 shrink-0 items-center gap-3 border-b border-surface-variant/40 px-6">
        @include('partials.logo', ['ukuran' => 'text-3xl'])
        <span class="flex flex-col leading-none">
            <span class="font-headline text-lg font-bold uppercase tracking-wider text-secondary">{{ \Illuminate\Support\Str::limit($situs['nama_situs'], 16, '') }}</span>
            <span class="mt-1 font-headline text-[11px] font-semibold uppercase tracking-widest text-outline">Panel Admin</span>
        </span>
    </div>
@endunless

<nav class="flex-1 space-y-1 overflow-y-auto p-4" aria-label="Menu admin">
    @foreach ($menu as $i => [$pola, $rute, $ikon, $label])
        <a href="{{ route($rute) }}"
           @if ($drawer) style="--d: {{ 60 + $i * 30 }}ms" @endif
           class="{{ $drawer ? 'menu-item-in' : '' }} flex items-center gap-3 rounded-lg px-4 py-3 font-headline text-sm font-semibold transition-colors
           {{ request()->routeIs($pola) ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
            <span class="material-symbols-outlined text-[20px]">{{ $ikon }}</span> {{ $label }}
        </a>
    @endforeach
</nav>

<div class="shrink-0 space-y-3 border-t border-surface-variant/40 p-4">
    @if ($drawer)
        <p class="truncate px-1 text-sm text-on-surface-variant">{{ auth()->user()->name }}</p>
    @endif
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="btn-secondary w-full">
            <span class="material-symbols-outlined text-[20px]">logout</span> Keluar
        </button>
    </form>
</div>