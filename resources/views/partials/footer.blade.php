@php
    $situs = app(\App\Services\SettingsService::class)->situs();
    // Nilai bawaan dari seeder dianggap belum diisi.
    $kontak = $situs['kontak'] !== '' && ! str_contains($situs['kontak'], 'xxxx') ? $situs['kontak'] : null;
    $alamat = $situs['alamat'] !== '' && $situs['alamat'] !== 'Isi alamat tempat sewa' ? $situs['alamat'] : null;
@endphp
<footer class="border-t border-surface-variant/40 bg-surface-container-lowest">
    <div class="mx-auto flex max-w-[1320px] flex-col gap-4 px-4 py-8 text-sm text-on-surface-variant md:px-8">
        <div class="flex flex-col items-center justify-between gap-3 md:flex-row md:items-start">
            <div class="text-center md:text-left">
                <p class="font-headline font-semibold uppercase tracking-wider text-secondary">{{ $situs['nama_situs'] }}</p>
                @if ($situs['tagline'] !== '')
                    <p class="mt-1">{{ $situs['tagline'] }}</p>
                @endif
            </div>
            <div class="space-y-1 text-center md:text-right">
                @if ($alamat)
                    <p class="flex items-center justify-center gap-2 md:justify-end"><span class="material-symbols-outlined text-[18px]">location_on</span> {{ $alamat }}</p>
                @endif
                @if ($kontak)
                    <p class="flex items-center justify-center gap-2 md:justify-end"><span class="material-symbols-outlined text-[18px]">call</span> {{ $kontak }}</p>
                @endif
            </div>
        </div>
        <p class="text-center md:text-left">&copy; {{ date('Y') }} {{ $situs['nama_situs'] }}. Rental PlayStation 4 &amp; 5.</p>
    </div>
</footer>