<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public static function labelBulan(Carbon $tgl): string
    {
        return self::BULAN[$tgl->month].' '.$tgl->year;
    }

    /** Pembayaran berhasil pada periode (belum menyaring status pesanan). */
    private function dasar(Carbon $mulai, Carbon $akhir): Builder
    {
        return DB::table('payments as p')
            ->join('bookings as b', 'b.id', '=', 'p.booking_id')
            ->join('consoles as c', 'c.id', '=', 'b.console_id')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->where('p.status_pembayaran', 'Berhasil')
            ->whereBetween('p.waktu_bayar', [$mulai->copy()->startOfDay(), $akhir->copy()->endOfDay()]);
    }

    /** Dasar semua angka pemasukan: sama dengan dasbor (pesanan dibatalkan tidak dihitung). */
    private function aktif(Carbon $mulai, Carbon $akhir): Builder
    {
        return $this->dasar($mulai, $akhir)->where('b.status_booking', '<>', 'Dibatalkan');
    }

    public function ringkasan(Carbon $mulai, Carbon $akhir): array
    {
        $aktif = fn (): Builder => $this->aktif($mulai, $akhir);

        $pemasukan = (int) $aktif()->sum('p.nominal');
        $transaksi = (int) $aktif()->distinct()->count('b.id');

        $perJenis = $aktif()
            ->selectRaw('c.jenis, COUNT(DISTINCT b.id) as n, SUM(p.nominal) as total')
            ->groupBy('c.jenis')
            ->get()
            ->keyBy('jenis');

        $perMetode = $aktif()
            ->selectRaw('b.metode_serah, COUNT(DISTINCT b.id) as n, SUM(p.nominal) as total')
            ->groupBy('b.metode_serah')
            ->get()
            ->keyBy('metode_serah');

        $perUnit = $aktif()
            ->selectRaw('c.kode_unit, c.jenis, COUNT(DISTINCT b.id) as n, SUM(p.nominal) as total')
            ->groupBy('c.kode_unit', 'c.jenis')
            ->orderByDesc('total')
            ->get();

        $harian = $aktif()
            ->selectRaw('DATE(p.waktu_bayar) as hari, COUNT(DISTINCT b.id) as n, SUM(p.nominal) as total')
            ->groupByRaw('DATE(p.waktu_bayar)')
            ->orderBy('hari')
            ->get();

        $stik = $aktif()
            ->selectRaw('COALESCE(SUM(b.jumlah_stik), 0) as jumlah, COALESCE(SUM(b.jumlah_stik * b.harga_stik), 0) as pendapatan')
            ->first();

        $batalDibayar = $this->dasar($mulai, $akhir)
            ->where('b.status_booking', 'Dibatalkan')
            ->selectRaw('COUNT(DISTINCT b.id) as n, COALESCE(SUM(p.nominal), 0) as total')
            ->first();

        $denda = DB::table('bookings')
            ->where('status_booking', 'Selesai')
            ->whereBetween('waktu_kembali', [$mulai->copy()->startOfDay(), $akhir->copy()->endOfDay()])
            ->where('denda', '>', 0)
            ->selectRaw('COUNT(*) as n, COALESCE(SUM(denda), 0) as total')
            ->first();

        return [
            'pemasukan' => $pemasukan,
            'transaksi' => $transaksi,
            'rata' => $transaksi > 0 ? intdiv($pemasukan, $transaksi) : 0,
            'jenis' => [
                'PS5' => ['n' => (int) ($perJenis['PS5']->n ?? 0), 'total' => (int) ($perJenis['PS5']->total ?? 0)],
                'PS4' => ['n' => (int) ($perJenis['PS4']->n ?? 0), 'total' => (int) ($perJenis['PS4']->total ?? 0)],
            ],
            'metode' => [
                'Ambil Sendiri' => ['n' => (int) ($perMetode['Ambil Sendiri']->n ?? 0), 'total' => (int) ($perMetode['Ambil Sendiri']->total ?? 0)],
                'Diantar' => ['n' => (int) ($perMetode['Diantar']->n ?? 0), 'total' => (int) ($perMetode['Diantar']->total ?? 0)],
            ],
            'unit' => $perUnit,
            'harian' => $harian,
            'stik' => ['jumlah' => (int) ($stik->jumlah ?? 0), 'pendapatan' => (int) ($stik->pendapatan ?? 0)],
            'batal_dibayar' => ['n' => (int) ($batalDibayar->n ?? 0), 'total' => (int) ($batalDibayar->total ?? 0)],
            'denda' => ['n' => (int) ($denda->n ?? 0), 'total' => (int) ($denda->total ?? 0)],
        ];
    }

    /** Rincian per transaksi; halaman memakai paginate(), ekspor memakai get(). */
    public function rincian(Carbon $mulai, Carbon $akhir): Builder
    {
        return $this->aktif($mulai, $akhir)
            ->orderByDesc('p.waktu_bayar')
            ->orderByDesc('p.id')
            ->select(
                'p.waktu_bayar', 'b.kode_booking', 'u.name as pelanggan', 'c.kode_unit', 'c.jenis',
                'b.waktu_mulai', 'b.durasi_jam', 'b.metode_serah', 'b.jumlah_stik', 'b.status_booking',
                'b.denda', 'p.nominal'
            );
    }
}