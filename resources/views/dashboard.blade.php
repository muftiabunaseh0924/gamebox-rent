@extends('layouts.app')
@section('title', 'Dasbor')

@section('content')
<h2 class="font-headline text-3xl font-bold">Halo, {{ auth()->user()->name }} 👋</h2>
<p class="mt-1 text-on-surface-variant">Dasbor pelanggan. Riwayat sewa menyusul di L5 dan L6.</p>

<div class="mt-8 grid gap-6 md:grid-cols-3">
    <div class="card">
        <p class="label-field">Email</p>
        <p class="font-headline text-lg">{{ auth()->user()->email }}</p>
    </div>
    <div class="card">
        <p class="label-field">Nomor HP</p>
        <p class="font-headline text-lg">{{ auth()->user()->no_hp ?? '-' }}</p>
    </div>
    <div class="card">
        <p class="label-field">Status Akun</p>
        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider text-emerald-300">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span> {{ auth()->user()->status }}
        </span>
    </div>
</div>
@endsection