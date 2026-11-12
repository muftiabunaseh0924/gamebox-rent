<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Console;
use App\Services\HandoverService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FleetController extends Controller
{
    public function index(HandoverService $layanan): View
    {
        $unit = Console::with(['bookingAktif.user'])->urut()->get();

        // Booking berikutnya (sudah dibayar, belum dimulai) per unit.
        $lanjutan = Booking::with('user')
            ->where('status_booking', 'Dibayar')
            ->where('waktu_selesai', '>', now())
            ->orderBy('waktu_mulai')
            ->get()
            ->groupBy('console_id')
            ->map(fn ($grup) => $grup->first());

        $info = [];
        $terlambat = 0;
        foreach ($unit as $u) {
            $b = $u->bookingAktif;
            if ($u->status === 'Disewa' && $b) {
                $menit = intdiv($b->waktu_selesai->timestamp - now()->timestamp, 60);
                $telat = $b->waktu_selesai->isPast();
                $info[$u->id] = [
                    'menit' => $menit,
                    'terlambat' => $telat,
                    'denda' => $layanan->hitungDenda($b, now()),
                ];
                if ($telat) {
                    $terlambat++;
                }
            }
        }

        $ringkasan = [
            'total' => $unit->count(),
            'ready' => $unit->where('status', 'Tersedia')->count(),
            'disewa' => $unit->where('status', 'Disewa')->count() - $terlambat,
            'terlambat' => $terlambat,
            'perawatan' => $unit->whereIn('status', ['Perawatan', 'Nonaktif'])->count(),
        ];

        return view('admin.armada', compact('unit', 'lanjutan', 'info', 'ringkasan'));
    }

    public function selesai(Request $request, Booking $booking, HandoverService $layanan): RedirectResponse
    {
        $data = $request->validate([
            'catatan_kondisi' => ['nullable', 'string', 'max:500'],
        ], [
            'max' => 'Catatan maksimal :max karakter.',
        ]);

        try {
            $hasil = $layanan->selesaikan($booking, $request->user(), $data['catatan_kondisi'] ?? null);
        } catch (BookingException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }

        $pesan = "Penyewaan {$hasil->kode_booking} selesai. "
            .($hasil->denda > 0 ? 'Denda keterlambatan: '.Booking::rupiah($hasil->denda).'.' : 'Tanpa denda.');

        return redirect()->route('admin.armada')->with('status', $pesan);
    }
}