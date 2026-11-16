@extends('layouts.admin')
@section('title', 'Pesanan '.$booking->kode_booking)
@section('heading', 'Detail Pesanan')

@section('content')
@php
    $rp = fn ($n) => \App\Models\Booking::rupiah($n);
    $b = $booking;
    $lunas = $b->payments->firstWhere('status_pembayaran', 'Berhasil');
    $bisaBatal = in_array($b->status_booking, ['Menunggu Pembayaran', 'Dibayar'], true) && ! $b->kode_dipakai;
    $kirim = $b->delivery;
@endphp

<div class="mx-auto max-w-5xl space-y-6">
    @if ($errors->any())
        <div class="rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $pesan) <li>{{ $pesan }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="font-headline text-xs font-semibold uppercase tracking-widest text-outline">Kode Booking</p>
            <h2 class="font-headline text-3xl font-bold tracking-wider">{{ $b->kode_booking }}</h2>
            <p class="text-sm text-on-surface-variant">Dibuat {{ $b->created_at->format('d-m-Y H:i') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if ($bisaInvoice)
                <a href="{{ route('admin.pesanan.invoice', $b) }}" class="btn-secondary !py-2"><span class="material-symbols-outlined text-[18px]">download</span> Invoice PDF</a>
            @endif
            <span class="rounded-full px-4 py-1.5 font-headline text-xs font-semibold uppercase tracking-wider {{ $b->kelasStatus() }}">{{ $b->statusTampil() }}</span>
        </div>
    </div>

    @if ($b->status_booking === 'Dibatalkan')
        <div class="rounded-lg bg-error-container/30 px-4 py-3 text-sm text-error">
            <p class="font-headline font-semibold uppercase tracking-wider">Pesanan dibatalkan</p>
            <p class="mt-1">{{ $b->alasan_batal ?? '-' }}
                @if ($b->dibatalkan_at) · {{ \Illuminate\Support\Carbon::parse($b->dibatalkan_at)->format('d-m-Y H:i') }} @endif
            </p>
            @if ($lunas) <p class="mt-1 text-xs">Pesanan ini sudah dibayar. Pastikan refund diproses manual di dasbor Midtrans.</p> @endif
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <div class="card space-y-3">
            <h3 class="font-headline text-lg font-bold">Pelanggan</h3>
            <p class="font-semibold">{{ $b->user->name }}</p>
            <p class="text-sm text-on-surface-variant">{{ $b->user->email }}</p>
            <p class="text-sm text-on-surface-variant">{{ $b->user->no_hp ?? '-' }}</p>
            <p class="text-xs text-outline">Status akun: {{ $b->user->status }}</p>
        </div>

        <div class="card space-y-3">
            <h3 class="font-headline text-lg font-bold">Sewa</h3>
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-outline">Unit</dt><dd class="font-semibold">{{ $b->console->kode_unit }} · {{ $b->console->nama_tampil }}</dd></div>
                <div><dt class="text-outline">Durasi</dt><dd class="font-semibold">{{ $b->durasi_jam }} jam</dd></div>
                <div><dt class="text-outline">Mulai</dt><dd class="font-semibold">{{ $b->waktu_mulai->format('d-m-Y H:i') }}</dd></div>
                <div><dt class="text-outline">Selesai</dt><dd class="font-semibold">{{ $b->waktu_selesai->format('d-m-Y H:i') }}</dd></div>
                <div><dt class="text-outline">Penerimaan</dt><dd class="font-semibold">{{ $b->metode_serah }}</dd></div>
                <div><dt class="text-outline">Stik Tambahan</dt><dd class="font-semibold">{{ $b->jumlah_stik }}</dd></div>
                @if ($b->alamat_pengiriman) <div class="sm:col-span-2"><dt class="text-outline">Alamat</dt><dd class="font-semibold">{{ $b->alamat_pengiriman }}</dd></div> @endif
                @if ($b->kode_konfirmasi) <div><dt class="text-outline">Kode Konfirmasi</dt><dd class="font-headline font-semibold tracking-widest text-secondary">{{ $b->kode_konfirmasi }}</dd></div> @endif
                @if ($b->waktu_serah_terima) <div><dt class="text-outline">Serah Terima</dt><dd class="font-semibold">{{ $b->waktu_serah_terima->format('d-m-Y H:i') }}</dd></div> @endif
                @if ($b->waktu_kembali) <div><dt class="text-outline">Dikembalikan</dt><dd class="font-semibold">{{ $b->waktu_kembali->format('d-m-Y H:i') }}</dd></div> @endif
                @if ($b->denda > 0) <div><dt class="text-outline">Denda</dt><dd class="font-semibold text-error">{{ $rp($b->denda) }}</dd></div> @endif
                @if ($b->catatan_kondisi) <div class="sm:col-span-2"><dt class="text-outline">Catatan Kondisi</dt><dd class="font-semibold">{{ $b->catatan_kondisi }}</dd></div> @endif
            </dl>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="card space-y-3">
            <h3 class="font-headline text-lg font-bold">Rincian Biaya</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-on-surface-variant">Paket {{ $b->durasi_jam }} jam</span><span>{{ $rp($b->harga_paket) }}</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">Stik ({{ $b->jumlah_stik }} x {{ $rp($b->harga_stik) }})</span><span>{{ $rp($b->jumlah_stik * $b->harga_stik) }}</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">Pengiriman</span><span>{{ $b->biaya_pengiriman > 0 ? $rp($b->biaya_pengiriman) : 'Gratis' }}</span></div>
            </div>
            <div class="flex items-end justify-between border-t border-surface-variant/40 pt-3">
                <span class="text-on-surface-variant">Total</span>
                <span class="font-headline text-2xl font-bold text-secondary">{{ $rp($b->total_harga) }}</span>
            </div>
        </div>

        <div class="card space-y-3">
            <h3 class="font-headline text-lg font-bold">Pembayaran</h3>
            @forelse ($b->payments->sortByDesc('id') as $p)
                <div class="rounded-lg bg-surface-container-lowest px-3 py-2 text-sm">
                    <p class="font-mono text-xs text-outline">{{ $p->order_id }}</p>
                    <p>{{ $p->metode ?? '-' }} · {{ $rp($p->nominal) }} · <strong>{{ $p->status_pembayaran }}</strong></p>
                    @if ($p->waktu_bayar) <p class="text-xs text-on-surface-variant">{{ $p->waktu_bayar->format('d-m-Y H:i') }}</p> @endif
                </div>
            @empty
                <p class="text-sm text-on-surface-variant">Belum ada percobaan pembayaran.</p>
            @endforelse
        </div>
    </div>

    @if ($kirim)
        <div class="card space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-headline text-lg font-bold">Pengantaran</h3>
                <span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider {{ $kirim->kelasStatus() }}">{{ $kirim->status_kirim }}</span>
            </div>
            @if ($kirim->nama_kurir) <p class="text-sm">Kurir: <strong>{{ $kirim->nama_kurir }}</strong> · {{ $kirim->hp_kurir }}</p> @endif
            <ol class="space-y-1 text-sm">
                @foreach ($b->deliveryEvents as $e)
                    <li class="flex flex-wrap gap-x-3">
                        <span class="text-outline">{{ $e->created_at->format('d-m H:i') }}</span>
                        <span class="font-semibold">{{ $e->status_kirim }}</span>
                        <span class="text-on-surface-variant">{{ $e->catatan }}{{ $e->admin ? ' (oleh '.$e->admin->name.')' : '' }}</span>
                    </li>
                @endforeach
            </ol>
            <a href="{{ route('admin.pengiriman') }}" class="text-sm text-secondary hover:underline">Buka halaman Pengiriman</a>
        </div>
    @endif

    @if ($log->isNotEmpty())
        <div class="card space-y-3">
            <h3 class="font-headline text-lg font-bold">Log Aktivitas Terkait</h3>
            <ol class="space-y-1 text-sm">
                @foreach ($log as $l)
                    <li class="flex flex-wrap gap-x-3">
                        <span class="text-outline">{{ \Illuminate\Support\Carbon::parse($l->created_at)->format('d-m-Y H:i') }}</span>
                        <span class="font-semibold">{{ $l->aksi }}</span>
                        <span class="text-on-surface-variant">{{ $l->detail }}{{ $l->admin ? ' (oleh '.$l->admin.')' : '' }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    @if ($bisaBatal)
        <form method="POST" action="{{ route('admin.pesanan.batal', $b) }}" class="card space-y-3 border-error/30"
              onsubmit="return confirm('Batalkan pesanan {{ $b->kode_booking }}? Jadwal dilepas dan pelanggan diberi tahu lewat email.');">
            @csrf
            <h3 class="font-headline text-lg font-bold text-error">Batalkan Pesanan</h3>
            @if ($b->status_booking === 'Dibayar')
                <p class="rounded-lg bg-amber-500/10 px-4 py-3 text-sm text-amber-300">
                    Pesanan ini <strong>sudah dibayar</strong>. Pembatalan tidak mengembalikan dana secara otomatis. Proses refund di dasbor Midtrans secara manual.
                </p>
            @endif
            <textarea name="alasan" rows="2" maxlength="200" class="input-field" placeholder="Alasan pembatalan (wajib, minimal 5 karakter)">{{ old('alasan') }}</textarea>
            @error('alasan') <p class="error-text">{{ $message }}</p> @enderror
            <button type="submit" class="btn-secondary !border-error/50 !text-error">
                <span class="material-symbols-outlined text-[20px]">cancel</span> Batalkan Pesanan
            </button>
        </form>
    @endif

    <a href="{{ route('admin.pesanan') }}" class="inline-flex items-center gap-2 text-sm text-secondary hover:underline">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar pesanan
    </a>
</div>
@endsection