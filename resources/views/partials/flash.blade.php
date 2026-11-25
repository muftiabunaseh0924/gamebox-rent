@php
    $pesanSukses = session('status');
    $pesanGalat = session('error');
@endphp

@if ($pesanSukses || $pesanGalat)
    @php $galat = ! $pesanSukses; @endphp
    <div x-data="{ tampil: true }"
         x-init="setTimeout(() => tampil = false, 6000)"
         x-show="tampil"
         x-transition:enter="transition duration-300 ease-out"
         x-transition:enter-start="translate-x-8 opacity-0"
         x-transition:enter-end="translate-x-0 opacity-100"
         x-transition:leave="transition duration-200 ease-in"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="translate-x-8 opacity-0"
         role="{{ $galat ? 'alert' : 'status' }}"
         class="fixed inset-x-4 top-24 z-[60] overflow-hidden rounded-lg border shadow-2xl backdrop-blur-xl sm:left-auto sm:right-6 sm:w-96
         {{ $galat ? 'border-error/40 bg-error-container/80 text-error' : 'border-emerald-500/30 bg-surface-container-lowest/95 text-emerald-300' }}">
        <div class="flex items-start gap-3 px-4 py-3 text-sm">
            <span class="material-symbols-outlined mt-0.5 shrink-0 text-[20px]">{{ $galat ? 'error' : 'check_circle' }}</span>
            <p class="min-w-0 flex-1 break-words">{{ $pesanSukses ?: $pesanGalat }}</p>
            <button type="button" @click="tampil = false" aria-label="Tutup notifikasi"
                    class="-mr-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition hover:bg-white/10">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div class="susut-x h-0.5 {{ $galat ? 'bg-error' : 'bg-emerald-400' }}" style="--dur: 6000ms" aria-hidden="true"></div>
    </div>
@endif