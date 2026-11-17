@php
    $situs = app(\App\Services\SettingsService::class)->situs();
    $ukuran = $ukuran ?? 'text-3xl';
@endphp
@if (filled($situs['logo_url']))
    <img src="{{ $situs['logo_url'] }}" alt="" class="h-9 w-9 shrink-0 rounded object-contain"
         onerror="this.nextElementSibling.classList.remove('hidden'); this.remove();">
    <span class="material-symbols-outlined hidden {{ $ukuran }} text-secondary">sports_esports</span>
@else
    <span class="material-symbols-outlined {{ $ukuran }} text-secondary">sports_esports</span>
@endif