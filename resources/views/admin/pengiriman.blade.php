@extends('layouts.admin')
@section('title', 'Pengiriman')
@section('heading', 'Pelacakan Pengantaran')

@section('content')
@php
    $tabs = ['aktif' => 'Aktif', 'gagal' => 'Gagal', 'selesai' => 'Selesai'];
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="flex flex-wrap gap-2" role="tablist">
    @foreach ($tabs as $kunci => $label)
        <a href="{{ route('admin.pengiriman', ['tab' => $kunci]) }}"
           class="rounded-lg px-4 py-2 font-headline text-sm font-semibold transition
           {{ $tab === $kunci ? 'bg-primary-container text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
            {{ $label }} <span class="ml-1 rounded-full bg-black/20 px-2 py-0.5 text-xs">{{ $jumlah[$kunci] ?? 0 }}</span>
        </a>
    @endforeach
</div>

<div class="mt-6 space-y-6">
    @forelse ($daftar as $d)
        @php
            $b = $d->booking;
            $status = $d->status_kirim;
        @endphp
        <article class="card space-y-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-headline text-xs font-semibold uppercase tracking-widest text-outline">Booking</p>
                    <h3 class="font-headline text-xl font-bold tracking-wider">{{ $b->kode_booking }}</h3>
                </div>
                <span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider {{ $d->kelasStatus() }}">{{ $status }}</span>
            </div>

            <dl class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                <div><dt class="text-outline">Pelanggan</dt><dd class="font-semibold">{{ $b->user->name }}</dd><dd class="text-on-surface-variant">{{ $b->user->no_hp ?? '-' }}</dd></div>
                <div><dt class="text-outline">Unit</dt><dd class="font-semibold">{{ $b->console->kode_unit }} · {{ $b->console->nama_tampil }}</dd><dd class="text-on-surface-variant">Stik tambahan: {{ $b->jumlah_stik }}</dd></div>
                <div><dt class="text-outline">Jadwal Mulai</dt><dd class="font-semibold">{{ $b->waktu_mulai->format('d-m-Y H:i') }}</dd><dd class="text-on-surface-variant">Selesai {{ $b->waktu_selesai->format('d-m-Y H:i') }}</dd></div>
                <div class="sm:col-span-2 lg:col-span-2"><dt class="text-outline">Alamat Pengantaran</dt><dd class="font-semibold">{{ $b->alamat_pengiriman ?? '-' }}</dd></div>
                <div>
                    <dt class="text-outline">Kurir</dt>
                    <dd class="font-semibold">{{ $d->nama_kurir ?? '-' }}</dd>
                    <dd class="text-on-surface-variant">{{ $d->hp_kurir ?? '' }}</dd>
                </div>
            </dl>

            {{-- Linimasa --}}
            <div class="rounded-lg bg-surface-container-lowest px-4 py-3">
                <p class="label-field !mb-2">Linimasa</p>
                <ol class="space-y-2 text-sm">
                    @foreach ($b->deliveryEvents as $e)
                        <li class="flex flex-wrap gap-x-3">
                            <span class="text-outline">{{ $e->created_at->format('d-m H:i') }}</span>
                            <span class="font-semibold">{{ $e->status_kirim }}</span>
                            <span class="text-on-surface-variant">{{ $e->catatan }}{{ $e->admin ? ' (oleh '.$e->admin->name.')' : '' }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- Aksi sesuai status --}}
            @if (in_array($status, ['Menunggu Dikirim', 'Gagal Dikirim'], true))
                <form method="POST" action="{{ route('admin.pengiriman.ubah', $b) }}" class="space-y-3 border-t border-surface-variant/40 pt-5">
                    @csrf
                    <input type="hidden" name="aksi" value="tugaskan">
                    <p class="font-headline text-sm font-semibold">{{ $status === 'Gagal Dikirim' ? 'Tugaskan ulang kurir' : 'Tugaskan kurir' }}</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <input type="text" name="nama_kurir" maxlength="60" placeholder="Nama kurir" value="{{ $d->nama_kurir }}" class="input-field">
                        <input type="text" name="hp_kurir" maxlength="20" placeholder="08xxxxxxxxxx" value="{{ $d->hp_kurir }}" class="input-field">
                        <input type="text" name="catatan" maxlength="120" placeholder="Catatan (opsional)" class="input-field">
                    </div>
                    <button type="submit" class="btn-primary"><span class="material-symbols-outlined text-[20px]">person_add</span> Tugaskan Kurir</button>
                </form>
            @elseif ($status === 'Kurir Ditugaskan')
                <div class="grid gap-4 border-t border-surface-variant/40 pt-5 lg:grid-cols-2">
                    <form method="POST" action="{{ route('admin.pengiriman.ubah', $b) }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="aksi" value="berangkat">
                        <input type="text" name="catatan" maxlength="200" placeholder="Catatan (opsional)" class="input-field">
                        <button type="submit" class="btn-primary w-full"><span class="material-symbols-outlined text-[20px]">local_shipping</span> Berangkat</button>
                    </form>
                    <form method="POST" action="{{ route('admin.pengiriman.ubah', $b) }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="aksi" value="gagal">
                        <input type="text" name="catatan" maxlength="200" placeholder="Alasan gagal (wajib)" class="input-field">
                        <button type="submit" class="btn-secondary w-full">Tandai Gagal Dikirim</button>
                    </form>
                </div>
            @elseif ($status === 'Dalam Perjalanan')
                <div class="grid gap-4 border-t border-surface-variant/40 pt-5 lg:grid-cols-2">
                    <form method="POST" action="{{ route('admin.pengiriman.ubah', $b) }}" class="space-y-3"
                          onsubmit="return confirm('Konfirmasi unit sudah diterima {{ addslashes($b->user->name) }}? Unit akan tercatat Disewa.');">
                        @csrf
                        <input type="hidden" name="aksi" value="terima">
                        <input type="text" name="kode_konfirmasi" maxlength="20" autocomplete="off" placeholder="Kode konfirmasi dari pelanggan"
                               class="input-field font-headline uppercase tracking-[0.2em]">
                        <label class="flex items-start gap-2 text-xs text-on-surface-variant">
                            <input type="checkbox" name="abaikan_jendela" value="1" class="mt-0.5 h-4 w-4 accent-primary-container">
                            Abaikan jendela waktu serah terima (dicatat di log aktivitas)
                        </label>
                        <button type="submit" class="btn-primary w-full"><span class="material-symbols-outlined text-[20px]">key</span> Konfirmasi Diterima</button>
                    </form>
                    <form method="POST" action="{{ route('admin.pengiriman.ubah', $b) }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="aksi" value="gagal">
                        <input type="text" name="catatan" maxlength="200" placeholder="Alasan gagal (wajib)" class="input-field">
                        <button type="submit" class="btn-secondary w-full">Tandai Gagal Dikirim</button>
                    </form>
                </div>
            @endif
        </article>
    @empty
        <div class="card text-center text-on-surface-variant">Tidak ada pengantaran di tab ini.</div>
    @endforelse
</div>
@endsection