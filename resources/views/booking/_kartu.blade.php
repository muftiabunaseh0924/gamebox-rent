{{-- Kartu satu pesanan, dipakai di dasbor dan halaman Pesanan Saya. Variabel: $b (Booking), $i (urutan untuk animasi) --}}
<a href="{{ route('booking.show', $b) }}"
   style="--d: {{ min(($i ?? 0) * 70, 420) }}ms"
   class="card anim-in block !p-4 transition duration-300 hover:-translate-y-0.5 hover:border-cyan-400/40 hover:shadow-[0_8px_28px_-10px_rgba(34,211,238,0.25)] active:scale-[0.99] sm:!p-5">
    <div class="flex items-start justify-between gap-3">
        <p class="min-w-0 break-all font-headline font-semibold tracking-wider">{{ $b->kode_booking }}</p>
        <span class="shrink-0 rounded-full px-3 py-1 font-headline text-[11px] font-semibold uppercase tracking-wider sm:text-xs {{ $b->kelasStatus() }}">{{ $b->statusTampil() }}</span>
    </div>

    <p class="mt-2 text-sm text-on-surface-variant">{{ $b->console->nama_tampil }}</p>
    <p class="text-sm text-on-surface-variant">{{ $b->waktu_mulai->format('d-m-Y H:i') }} ({{ $b->durasi_jam }} jam)</p>

    <div class="mt-3 flex items-center justify-between gap-3 border-t border-surface-variant/40 pt-3">
        <span class="text-xs text-outline">Total</span>
        <span class="font-headline text-lg font-semibold text-secondary">{{ \App\Models\Booking::rupiah($b->total_harga) }}</span>
    </div>
</a>