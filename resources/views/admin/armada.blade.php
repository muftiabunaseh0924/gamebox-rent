@extends('layouts.admin')
@section('title', 'Jadwal & Lokasi Konsol')
@section('heading', 'Jadwal & Lokasi Konsol')

@section('content')
@php
    $rp = fn ($n) => \App\Models\Booking::rupiah($n);
    $durasi = function (int $menit) {
        $m = abs($menit);
        return intdiv($m, 60).' j '.($m % 60).' m';
    };
    $kartuRingkas = [
        ['Total Terdaftar', $ringkasan['total'], 'text-on-surface'],
        ['Di Tempat (Ready)', $ringkasan['ready'], 'text-emerald-300'],
        ['Dibawa Pelanggan', $ringkasan['disewa'], 'text-amber-300'],
        ['Terlambat Kembali', $ringkasan['terlambat'], 'text-error'],
        ['Perawatan / Nonaktif', $ringkasan['perawatan'], 'text-on-surface-variant'],
    ];
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

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
    @foreach ($kartuRingkas as [$label, $angka, $warna])
        <div class="card !p-5">
            <p class="label-field !mb-1">{{ $label }}</p>
            <p class="font-headline text-3xl font-bold {{ $warna }}">{{ $angka }}<span class="text-base font-medium text-on-surface-variant"> unit</span></p>
        </div>
    @endforeach
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    @foreach ($unit as $u)
        @php
            $b = $u->bookingAktif;
            $i = $info[$u->id] ?? null;
            $lanjut = $lanjutan->get($u->id);
            [$label, $kelas] = match (true) {
                $i && $i['terlambat'] => ['Terlambat', 'bg-error-container/40 text-error'],
                $u->status === 'Disewa' => ['Disewa', 'bg-amber-500/15 text-amber-300'],
                $u->status === 'Tersedia' => ['Ready', 'bg-emerald-500/15 text-emerald-300'],
                default => [$u->status, 'bg-surface-container-high text-on-surface-variant'],
            };
        @endphp
        <article class="card space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-headline text-xs font-semibold uppercase tracking-widest text-outline">{{ $u->jenis }} · {{ $u->kode_unit }}</p>
                    <h3 class="font-headline text-xl font-bold">{{ $u->nama_tampil }}</h3>
                </div>
                <span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider {{ $kelas }}">{{ $label }}</span>
            </div>

            @if ($u->status === 'Disewa' && $b)
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-outline">Pemegang</dt><dd class="font-semibold">{{ $b->user->name }}</dd></div>
                    <div><dt class="text-outline">Telepon</dt><dd class="font-semibold">{{ $b->user->no_hp ?? '-' }}</dd></div>
                    <div><dt class="text-outline">ID Booking</dt><dd class="font-semibold">{{ $b->kode_booking }}</dd></div>
                    <div><dt class="text-outline">Batas Kembali</dt><dd class="font-semibold">{{ $b->waktu_selesai->format('d-m-Y H:i') }}</dd></div>
                    <div class="sm:col-span-2">
                        <dt class="text-outline">Lokasi Tercatat</dt>
                        <dd class="font-semibold">
                            {{ $b->metode_serah === 'Diantar' && $b->alamat_pengiriman ? $b->alamat_pengiriman : $u->lokasi_saat_ini }}
                        </dd>
                    </div>
                </dl>

                @if ($i['terlambat'])
                    <div class="rounded-lg bg-error-container/30 px-4 py-3 text-sm text-error">
                        Terlambat <strong>{{ $durasi($i['menit']) }}</strong>. Denda berjalan: <strong>{{ $rp($i['denda']) }}</strong>
                    </div>
                @else
                    <div class="rounded-lg bg-surface-container-high px-4 py-3 text-sm text-on-surface-variant">
                        Sisa waktu <strong class="text-on-surface">{{ $durasi($i['menit']) }}</strong>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.armada.selesai', $b) }}" class="space-y-2"
                      onsubmit="return confirm('Tandai penyewaan {{ $b->kode_booking }} selesai? Unit akan kembali Tersedia.');">
                    @csrf
                    <textarea name="catatan_kondisi" rows="2" maxlength="500" class="input-field"
                              placeholder="Catatan kondisi unit saat kembali (opsional)"></textarea>
                    <button type="submit" class="btn-primary w-full">
                        <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span> Tandai Selesai
                    </button>
                </form>
            @else
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-outline">Posisi</dt><dd class="font-semibold">{{ $u->lokasi_saat_ini }}</dd></div>
                    <div><dt class="text-outline">Status Unit</dt><dd class="font-semibold">{{ $u->status }}</dd></div>
                </dl>
            @endif

            @if ($lanjut && (! $b || $lanjut->id !== $b->id))
                <div class="rounded-lg border border-primary-container/30 px-4 py-3 text-sm">
                    <p class="label-field !mb-1">Booking Lanjutan</p>
                    <p><strong>{{ $lanjut->kode_booking }}</strong> · {{ $lanjut->user->name }}</p>
                    <p class="text-on-surface-variant">{{ $lanjut->waktu_mulai->format('d-m-Y H:i') }} s.d. {{ $lanjut->waktu_selesai->format('d-m-Y H:i') }}</p>
                </div>
            @endif
        </article>
    @endforeach
</div>
@endsection