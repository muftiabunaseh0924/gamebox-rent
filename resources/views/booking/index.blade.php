@extends('layouts.app')
@section('title', 'Pesanan Saya')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="anim-in flex flex-wrap items-center justify-between gap-4">
        <div class="min-w-0">
            <h2 class="font-headline text-2xl font-bold sm:text-3xl">Pesanan Saya</h2>
            <p class="mt-1 text-on-surface-variant">Riwayat seluruh booking dan status pembayarannya.</p>
        </div>
        <a href="{{ route('booking.create') }}" class="btn-primary !py-2">Booking Baru</a>
    </div>

    <div class="mt-8 space-y-3">
        @forelse ($pesanan as $b)
            @include('booking._kartu', ['b' => $b, 'i' => $loop->index])
        @empty
            <div class="card anim-in text-center text-on-surface-variant">Belum ada pesanan.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $pesanan->links() }}</div>
</div>
@endsection