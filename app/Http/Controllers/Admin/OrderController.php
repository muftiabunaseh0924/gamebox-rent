<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\CancelService;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const STATUS = ['Menunggu Pembayaran', 'Dibayar', 'Sedang Disewa', 'Selesai', 'Dibatalkan', 'Kedaluwarsa'];

    public function index(Request $request): View
    {
        $f = $request->validate([
            'status' => ['nullable', 'in:'.implode(',', self::STATUS)],
            'q' => ['nullable', 'string', 'max:60'],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
            'metode' => ['nullable', 'in:Ambil Sendiri,Diantar'],
        ], [
            'date_format' => 'Format tanggal tidak valid.',
            'in' => 'Pilihan tidak valid.',
            'max' => 'Kata kunci terlalu panjang.',
        ]);

        $query = Booking::with(['user', 'console'])->latest('id');

        $status = $f['status'] ?? null;
        if ($status === 'Kedaluwarsa') {
            $query->where('status_booking', 'Menunggu Pembayaran')->where('batas_bayar', '<', now());
        } elseif ($status === 'Menunggu Pembayaran') {
            $query->where('status_booking', 'Menunggu Pembayaran')
                ->where(fn ($w) => $w->whereNull('batas_bayar')->orWhere('batas_bayar', '>=', now()));
        } elseif ($status) {
            $query->where('status_booking', $status);
        }

        if (filled($f['q'] ?? null)) {
            $kata = addcslashes(trim($f['q']), '%_\\');
            $query->where(function ($w) use ($kata) {
                $w->where('kode_booking', 'like', "%{$kata}%")
                    ->orWhere('kode_konfirmasi', strtoupper($kata))
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('name', 'like', "%{$kata}%")
                        ->orWhere('email', 'like', "%{$kata}%")
                        ->orWhere('no_hp', 'like', "%{$kata}%"));
            });
        }
        if (filled($f['dari'] ?? null)) {
            $query->whereDate('created_at', '>=', $f['dari']);
        }
        if (filled($f['sampai'] ?? null)) {
            $query->whereDate('created_at', '<=', $f['sampai']);
        }
        if (filled($f['metode'] ?? null)) {
            $query->where('metode_serah', $f['metode']);
        }

        $hitung = Booking::select('status_booking', DB::raw('count(*) as n'))->groupBy('status_booking')->pluck('n', 'status_booking');
        $kedaluwarsa = Booking::where('status_booking', 'Menunggu Pembayaran')->where('batas_bayar', '<', now())->count();

        $jumlah = [
            '' => (int) $hitung->sum(),
            'Menunggu Pembayaran' => max(0, (int) ($hitung['Menunggu Pembayaran'] ?? 0) - $kedaluwarsa),
            'Dibayar' => (int) ($hitung['Dibayar'] ?? 0),
            'Sedang Disewa' => (int) ($hitung['Sedang Disewa'] ?? 0),
            'Selesai' => (int) ($hitung['Selesai'] ?? 0),
            'Dibatalkan' => (int) ($hitung['Dibatalkan'] ?? 0),
            'Kedaluwarsa' => $kedaluwarsa,
        ];

        return view('admin.pesanan.index', [
            'pesanan' => $query->paginate(15)->withQueryString(),
            'jumlah' => $jumlah,
            'filter' => $f,
        ]);
    }

    public function show(Booking $booking, InvoiceService $invoice): View
    {
        $booking->load(['user', 'console', 'payments', 'delivery', 'deliveryEvents.admin']);

        $log = DB::table('activity_logs as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.admin_id')
            ->where('a.detail', 'like', '%'.$booking->kode_booking.'%')
            ->orderByDesc('a.id')
            ->limit(15)
            ->get(['a.aksi', 'a.detail', 'a.created_at', 'u.name as admin']);

        return view('admin.pesanan.show', [
            'booking' => $booking,
            'log' => $log,
            'bisaInvoice' => $invoice->bisaDibuat($booking),
        ]);
    }

    public function batal(Request $request, Booking $booking, CancelService $layanan): RedirectResponse
    {
        $data = $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:200'],
        ], [
            'required' => 'Alasan pembatalan wajib diisi.',
            'min' => 'Alasan terlalu pendek (minimal :min karakter).',
            'max' => 'Alasan terlalu panjang (maksimal :max karakter).',
        ]);

        try {
            $layanan->batalkan($booking, $request->user(), $data['alasan']);
        } catch (BookingException $e) {
            return back()->withErrors([$e->field => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.pesanan.show', $booking)
            ->with('status', "Pesanan {$booking->kode_booking} dibatalkan dan pelanggan diberi tahu lewat email.");
    }

    public function invoice(Booking $booking, InvoiceService $invoice)
    {
        abort_unless($invoice->bisaDibuat($booking), 404, 'Invoice tersedia setelah pembayaran berhasil.');

        return $invoice->pdf($booking)->download($invoice->namaBerkas($booking));
    }
}