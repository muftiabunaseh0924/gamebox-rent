<?php

namespace App\Http\Controllers;

use App\Exceptions\BookingException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Console;
use App\Models\Package;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request, BookingService $service): View
    {
        $pengaturan = $service->pengaturan();

        $unit = Console::where('status', 'Tersedia')->urut()->get();
        $paket = Package::aktif()->orderBy('durasi_jam')->get()->groupBy('jenis');

        $dipilih = $unit->firstWhere('id', (int) $request->query('unit'))?->id ?? $unit->first()?->id;

        $cfg = [
            'paket' => $paket->map(fn ($daftar) => $daftar->map(fn ($p) => [
                'durasi' => $p->durasi_jam,
                'harga' => $p->harga,
            ])->values()),
            'hargaStik' => $pengaturan['harga_stik'],
            'stok' => $pengaturan['stok_stik'],
            'biayaKirim' => $pengaturan['biaya_pengiriman'],
            'pengirimanAktif' => $pengaturan['pengiriman_aktif'],
            'slot' => $pengaturan['slot'],
            'urlSlot' => route('booking.slot', [], false),
        ];

        return view('booking.create', [
            'unit' => $unit,
            'dipilih' => $dipilih,
            'cfg' => $cfg,
            'pengaturan' => $pengaturan,
            'tanggalMin' => now()->toDateString(),
            'tanggalMax' => now()->addDays($pengaturan['maks_hari'])->toDateString(),
        ]);
    }

    public function slot(Request $request, BookingService $service): JsonResponse
    {
        $data = $request->validate([
            'console_id' => ['required', 'integer', 'exists:consoles,id'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'durasi_jam' => ['required', 'integer', 'min:1', 'max:48'],
        ]);

        $console = Console::findOrFail($data['console_id']);
        $tanggal = Carbon::createFromFormat('Y-m-d', $data['tanggal'])->startOfDay();

        return response()->json([
            'slots' => $service->slotUntuk($console, $tanggal, (int) $data['durasi_jam']),
        ]);
    }

    public function store(StoreBookingRequest $request, BookingService $service): RedirectResponse
    {
        try {
            $booking = $service->buat($request->user(), $request->validated());
        } catch (BookingException $e) {
            throw ValidationException::withMessages([$e->field => $e->getMessage()]);
        }

        return redirect()
            ->route('booking.show', $booking)
            ->with('status', 'Booking dibuat. Selesaikan pembayaran sebelum batas waktu.');
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        return view('booking.show', ['booking' => $booking->load('console')]);
    }
}