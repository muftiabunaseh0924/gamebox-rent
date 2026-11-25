@php
    $langkah = \App\Models\Delivery::URUTAN;
    $kirim = $booking->delivery;
    $kejadian = $booking->deliveryEvents;
    $status = $kirim->status_kirim ?? 'Menunggu Dikirim';
    $gagal = $status === 'Gagal Dikirim';
    $dicapai = (int) ($kejadian
        ->map(fn ($e) => array_search($e->status_kirim, $langkah, true))
        ->filter(fn ($i) => $i !== false)
        ->max() ?? 0);
    $jumlahLangkah = count($langkah);
    $persen = $jumlahLangkah > 1 ? (int) round($dicapai / ($jumlahLangkah - 1) * 100) : 0;
@endphp
<div class="card anim-in mt-6 !p-5 sm:!p-6" style="--d: 260ms">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h3 class="font-headline text-lg font-bold">Pelacakan Pengantaran</h3>
        <span class="rounded-full px-3 py-1 font-headline text-xs font-semibold uppercase tracking-wider {{ $kirim ? $kirim->kelasStatus() : 'bg-surface-container-high text-on-surface-variant' }}">{{ $status }}</span>
    </div>

    {{-- Bilah progres (tablet dan desktop): terisi dari kiri --}}
    <div class="mt-5 hidden h-1.5 overflow-hidden rounded-full bg-surface-container-high sm:block" aria-hidden="true">
        <div class="tumbuh-x h-full rounded-full bg-primary-container" style="width: {{ $persen }}%; --d: 250ms"></div>
    </div>

    <ol class="mt-5 grid gap-4 sm:mt-4 sm:grid-cols-4">
        @foreach ($langkah as $i => $nama)
            @php $sudah = $i <= $dicapai; @endphp
            <li class="anim-in relative flex items-start gap-3 sm:flex-col sm:items-center sm:text-center" style="--d: {{ 300 + $i * 120 }}ms">
                {{-- Garis penghubung (HP, tampilan vertikal) --}}
                @unless ($loop->last)
                    <span class="absolute -bottom-4 left-[17px] top-9 w-0.5 sm:hidden {{ $i < $dicapai ? 'bg-primary-container' : 'bg-surface-container-high' }}" aria-hidden="true"></span>
                @endunless

                <span @if ($sudah) style="--d: {{ 400 + $i * 120 }}ms" @endif
                      class="{{ $sudah ? 'anim-pop bg-primary-container text-on-surface' : 'bg-surface-container-high text-outline' }} relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full font-headline text-sm font-bold">
                    @if ($sudah) <span class="material-symbols-outlined text-[18px]">check</span> @else {{ $i + 1 }} @endif
                </span>
                <span class="pt-1.5 text-sm sm:pt-0 {{ $sudah ? 'font-semibold' : 'text-outline' }}">{{ $nama }}</span>
            </li>
        @endforeach
    </ol>

    @if ($gagal)
        <div class="mt-4 rounded-lg bg-error-container/30 px-4 py-3 text-sm text-error">
            Pengantaran sempat gagal. Admin akan menjadwalkan ulang. Hubungi kami bila perlu.
        </div>
    @endif

    @if ($kirim && $kirim->nama_kurir)
        <p class="mt-4 text-sm text-on-surface-variant">
            Kurir: <span class="font-semibold text-on-surface">{{ $kirim->nama_kurir }}</span>
            @if ($kirim->hp_kurir) - {{ $kirim->hp_kurir }} @endif
        </p>
    @endif

    @if ($kejadian->isNotEmpty())
        <ol class="mt-4 space-y-3 border-t border-surface-variant/40 pt-4 text-sm">
            @foreach ($kejadian->reverse() as $e)
                <li class="flex flex-col gap-x-3 sm:flex-row sm:flex-wrap">
                    <span class="text-xs text-outline sm:text-sm">{{ $e->created_at->format('d-m-Y H:i') }}</span>
                    <span class="font-semibold">{{ $e->status_kirim }}</span>
                    <span class="text-on-surface-variant">{{ $e->catatan }}</span>
                </li>
            @endforeach
        </ol>
    @endif

    <p class="mt-4 text-xs text-outline">Tunjukkan kode konfirmasi di atas kepada kurir saat unit tiba.</p>
</div>