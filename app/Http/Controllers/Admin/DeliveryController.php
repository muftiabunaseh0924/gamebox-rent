<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Delivery;
use App\Services\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    private const TAB = [
        'aktif' => ['Menunggu Dikirim', 'Kurir Ditugaskan', 'Dalam Perjalanan'],
        'gagal' => ['Gagal Dikirim'],
        'selesai' => ['Telah Diterima'],
    ];

    public function index(Request $request, DeliveryService $layanan): View
    {
        $layanan->sinkronkan();

        $tab = array_key_exists((string) $request->query('tab'), self::TAB) ? $request->query('tab') : 'aktif';

        $hitung = Delivery::selectRaw('status_kirim, count(*) as n')->groupBy('status_kirim')->pluck('n', 'status_kirim');
        $jumlah = collect(self::TAB)->map(fn ($status) => collect($status)->sum(fn ($s) => (int) ($hitung[$s] ?? 0)));

        $daftar = Delivery::with(['booking.user', 'booking.console', 'booking.deliveryEvents.admin'])
            ->whereIn('status_kirim', self::TAB[$tab])
            ->get();

        $daftar = $tab === 'aktif'
            ? $daftar->sortBy(fn (Delivery $d) => $d->booking->waktu_mulai->timestamp)->values()
            : $daftar->sortByDesc('updated_at')->values();

        return view('admin.pengiriman', compact('daftar', 'tab', 'jumlah'));
    }

    public function ubah(Request $request, Booking $booking, DeliveryService $layanan): RedirectResponse
    {
        $aksi = $request->validate([
            'aksi' => ['required', 'in:tugaskan,berangkat,gagal,terima'],
        ])['aksi'];

        $aturan = match ($aksi) {
            'tugaskan' => [
                'nama_kurir' => ['required', 'string', 'max:60'],
                'hp_kurir' => ['required', 'regex:/^(\+62|62|0)8[0-9\s\-]{8,16}$/'],
                'catatan' => ['nullable', 'string', 'max:120'],
            ],
            'gagal' => [
                'catatan' => ['required', 'string', 'min:5', 'max:200'],
            ],
            'terima' => [
                'kode_konfirmasi' => ['required', 'string', 'max:20'],
                'abaikan_jendela' => ['nullable', 'boolean'],
            ],
            default => [
                'catatan' => ['nullable', 'string', 'max:200'],
            ],
        };

        $data = $request->validate($aturan, [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang (maksimal :max karakter).',
            'min' => ':attribute terlalu pendek (minimal :min karakter).',
            'hp_kurir.regex' => 'Nomor HP kurir tidak valid. Contoh: 081234567890.',
        ], [
            'nama_kurir' => 'Nama kurir',
            'hp_kurir' => 'Nomor HP kurir',
            'catatan' => 'Catatan',
            'kode_konfirmasi' => 'Kode konfirmasi',
        ]);

        try {
            $layanan->ubah($booking, $aksi, $request->user(), $data);
        } catch (BookingException $e) {
            return back()->withErrors([$e->field => $e->getMessage()]);
        }

        $pesan = match ($aksi) {
            'tugaskan' => "Kurir ditugaskan untuk {$booking->kode_booking}.",
            'berangkat' => "Pengantaran {$booking->kode_booking} sedang dalam perjalanan.",
            'gagal' => "Pengantaran {$booking->kode_booking} ditandai gagal.",
            'terima' => "Unit diterima pelanggan. Pesanan {$booking->kode_booking} kini Sedang Disewa.",
        };

        return redirect()->route('admin.pengiriman', [
            'tab' => $aksi === 'gagal' ? 'gagal' : ($aksi === 'terima' ? 'selesai' : 'aktif'),
        ])->with('status', $pesan);
    }
}