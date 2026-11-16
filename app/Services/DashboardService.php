<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Console;
use App\Models\Delivery;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function __construct(private readonly HandoverService $handover)
    {
    }

    /** @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon, 2: string, 3: string} */
    private function periode(string $kode): array
    {
        $sekarang = now();

        return match ($kode) {
            '7' => [$sekarang->copy()->subDays(6)->startOfDay(), $sekarang->copy()->endOfDay(), '7 hari terakhir', '7'],
            '30' => [$sekarang->copy()->subDays(29)->startOfDay(), $sekarang->copy()->endOfDay(), '30 hari terakhir', '30'],
            default => [
                $sekarang->copy()->startOfMonth(),
                $sekarang->copy()->endOfDay(),
                'Bulan '.self::BULAN[$sekarang->month].' '.$sekarang->year,
                'bulan',
            ],
        };
    }

    public function ambil(string $kodePeriode): array
    {
        [$mulai, $akhir, $label, $kode] = $this->periode($kodePeriode);

        // Dasar semua angka pemasukan: pembayaran berhasil dari pesanan yang tidak dibatalkan.
        $dasar = fn (): Builder => DB::table('payments as p')
            ->join('bookings as b', 'b.id', '=', 'p.booking_id')
            ->join('consoles as c', 'c.id', '=', 'b.console_id')
            ->where('p.status_pembayaran', 'Berhasil')
            ->where('b.status_booking', '<>', 'Dibatalkan')
            ->whereBetween('p.waktu_bayar', [$mulai, $akhir]);

        $pemasukan = (int) $dasar()->sum('p.nominal');
        $transaksi = (int) $dasar()->distinct()->count('b.id');
        $rata = $transaksi > 0 ? intdiv($pemasukan, $transaksi) : 0;

        // Grafik harian
        $perHari = $dasar()
            ->selectRaw('DATE(p.waktu_bayar) as hari, SUM(p.nominal) as total')
            ->groupByRaw('DATE(p.waktu_bayar)')
            ->pluck('total', 'hari');

        $maks = (int) $perHari->max();
        $harian = [];
        $puncak = ['tanggal' => null, 'total' => 0];

        foreach (CarbonPeriod::create($mulai->copy()->startOfDay(), $akhir->copy()->startOfDay()) as $tgl) {
            $total = (int) ($perHari[$tgl->format('Y-m-d')] ?? 0);
            $harian[] = [
                'tanggal' => $tgl->format('d-m-Y'),
                'hari' => $tgl->format('d'),
                'total' => $total,
                'tinggi' => $maks > 0 && $total > 0 ? max(3, (int) round($total / $maks * 100)) : 0,
                'akhir_pekan' => $tgl->isWeekend(),
            ];
            if ($total > $puncak['total']) {
                $puncak = ['tanggal' => $tgl->format('d-m-Y'), 'total' => $total];
            }
        }
        $jumlahHari = max(1, count($harian));

        // Proporsi PS5 vs PS4 dan stik tambahan
        $proporsi = $dasar()
            ->selectRaw('c.jenis, COUNT(DISTINCT b.id) as n, SUM(p.nominal) as total')
            ->groupBy('c.jenis')
            ->get()
            ->keyBy('jenis');
        $stik = $dasar()
            ->selectRaw('COALESCE(SUM(b.jumlah_stik), 0) as jumlah, COALESCE(SUM(b.jumlah_stik * b.harga_stik), 0) as pendapatan')
            ->first();

        // Statistik booking pada periode
        $periodeBooking = fn () => Booking::whereBetween('created_at', [$mulai, $akhir]);
        $totalBooking = $periodeBooking()->count();
        $sukses = $periodeBooking()->whereIn('status_booking', ['Dibayar', 'Sedang Disewa', 'Selesai'])->count();
        $batal = $periodeBooking()->where('status_booking', 'Dibatalkan')->count();

        // Armada
        $unit = Console::with('bookingAktif.user')->urut()->get();
        $disewa = $unit->where('status', 'Disewa');

        // Serah terima hari ini
        $hariIni = Booking::whereBetween('waktu_mulai', [now()->startOfDay(), now()->endOfDay()])
            ->whereIn('status_booking', ['Dibayar', 'Sedang Disewa', 'Selesai'])
            ->get();

        // Keterlambatan
        $telat = Booking::with(['console', 'user'])
            ->where('status_booking', 'Sedang Disewa')
            ->where('waktu_selesai', '<', now())
            ->orderBy('waktu_selesai')
            ->get()
            ->map(fn (Booking $b) => [
                'booking' => $b,
                'menit' => (int) abs(intdiv($b->waktu_selesai->timestamp - now()->timestamp, 60)),
                'denda' => $this->handover->hitungDenda($b, now()),
            ]);

        return [
            'kode' => $kode,
            'label' => $label,
            'pemasukan' => $pemasukan,
            'transaksi' => $transaksi,
            'rata' => $rata,
            'booking' => [
                'total' => $totalBooking,
                'sukses' => $sukses,
                'batal' => $batal,
                'persen' => $totalBooking > 0 ? (int) round($sukses / $totalBooking * 100) : 0,
            ],
            'konsol' => [
                'total' => $unit->count(),
                'disewa' => $disewa->count(),
                'tersedia' => $unit->where('status', 'Tersedia')->count(),
                'perawatan' => $unit->whereNotIn('status', ['Tersedia', 'Disewa'])->count(),
                'kode_disewa' => $disewa->pluck('kode_unit')->all(),
            ],
            'serah' => [
                'total' => $hariIni->count(),
                'sudah' => $hariIni->filter(fn (Booking $b) => $b->kode_dipakai)->count(),
                'menunggu' => $hariIni->where('status_booking', 'Dibayar')->count(),
            ],
            'telat' => $telat,
            'denda_telat' => (int) $telat->sum('denda'),
            'harian' => $harian,
            'rerata_harian' => intdiv($pemasukan, $jumlahHari),
            'puncak' => $puncak,
            'proporsi' => [
                'PS5' => ['n' => (int) ($proporsi['PS5']->n ?? 0), 'total' => (int) ($proporsi['PS5']->total ?? 0)],
                'PS4' => ['n' => (int) ($proporsi['PS4']->n ?? 0), 'total' => (int) ($proporsi['PS4']->total ?? 0)],
            ],
            'stik' => ['jumlah' => (int) ($stik->jumlah ?? 0), 'pendapatan' => (int) ($stik->pendapatan ?? 0)],
            'antrean' => [
                'pengiriman' => Delivery::whereIn('status_kirim', ['Menunggu Dikirim', 'Kurir Ditugaskan', 'Dalam Perjalanan'])->count(),
                'menunggu_bayar' => Booking::where('status_booking', 'Menunggu Pembayaran')->where('batas_bayar', '>', now())->count(),
            ],
        ];
    }
}