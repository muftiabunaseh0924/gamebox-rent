@extends('layouts.admin')
@section('title', 'Dasbor Admin')
@section('heading', 'Dasbor Admin')

@section('content')
<div class="card">
    <h2 class="font-headline text-2xl font-bold">Selamat datang, {{ auth()->user()->name }}</h2>
    <p class="mt-2 text-on-surface-variant">
        Anda masuk sebagai <strong class="text-secondary">{{ auth()->user()->role }}</strong>.
        Isi dasbor (KPI, pesanan, jadwal) dibuat di L10.
    </p>
</div>
@endsection