<?php

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExcelService
{
    private const RUPIAH = '"Rp" #,##0';
    private const RUPIAH_STRIP = '"Rp" #,##0;-"Rp" #,##0;"-"';
    private const WAKTU = 'dd-mm-yyyy hh:mm';
    private const TANGGAL = 'dd-mm-yyyy';

    /**
     * @param  iterable<object>  $rincian  baris dari ReportService::rincian()
     */
    public function buat(array $lap, iterable $rincian, string $label, array $situs, Carbon $dicetak): Spreadsheet
    {
        $ss = new Spreadsheet();
        $ss->getProperties()
            ->setCreator($situs['nama_situs'])
            ->setTitle('Laporan Keuangan '.$label);

        $this->lembarRingkasan($ss->getActiveSheet(), $lap, $label, $situs, $dicetak);

        $harian = $ss->createSheet();
        $this->lembarHarian($harian, $lap);

        $unit = $ss->createSheet();
        $this->lembarUnit($unit, $lap);

        $detail = $ss->createSheet();
        $this->lembarRincian($detail, $rincian);

        $ss->setActiveSheetIndex(0);

        return $ss;
    }

    // ---------- Lembar ----------

    private function lembarRingkasan(Worksheet $s, array $lap, string $label, array $situs, Carbon $dicetak): void
    {
        $s->setTitle('Ringkasan');

        $this->teks($s, 1, 1, 'Laporan Keuangan');
        $s->getStyle([1, 1])->getFont()->setBold(true)->setSize(14);
        $this->teks($s, 1, 2, $situs['nama_situs']);
        $this->teks($s, 1, 3, 'Periode');
        $this->teks($s, 2, 3, $label);
        $this->teks($s, 1, 4, 'Dicetak');
        $this->teks($s, 2, 4, $dicetak->format('d-m-Y H:i'));

        $baris = 6;
        $this->kepala($s, $baris, ['Ringkasan', 'Nilai']);
        $item = [
            ['Total pemasukan', $lap['pemasukan'], self::RUPIAH],
            ['Total transaksi', $lap['transaksi'], null],
            ['Rata-rata order', $lap['rata'], self::RUPIAH],
            ['Denda keterlambatan (tidak masuk pemasukan)', $lap['denda']['total'], self::RUPIAH],
            ['Jumlah pesanan berdenda', $lap['denda']['n'], null],
            ['Stik tambahan (jumlah)', $lap['stik']['jumlah'], null],
            ['Stik tambahan (pendapatan)', $lap['stik']['pendapatan'], self::RUPIAH],
            ['Dibatalkan, sudah dibayar (jumlah pesanan)', $lap['batal_dibayar']['n'], null],
            ['Dibatalkan, sudah dibayar (nilai, tidak masuk pemasukan)', $lap['batal_dibayar']['total'], self::RUPIAH],
        ];
        foreach ($item as [$nama, $nilai, $format]) {
            $baris++;
            $this->teks($s, 1, $baris, $nama);
            $this->angka($s, 2, $baris, $nilai, $format);
        }

        $baris += 2;
        $this->kepala($s, $baris, ['Jenis unit', 'Transaksi', 'Pemasukan']);
        foreach (['PS5', 'PS4'] as $jenis) {
            $baris++;
            $this->teks($s, 1, $baris, $jenis);
            $this->angka($s, 2, $baris, $lap['jenis'][$jenis]['n']);
            $this->angka($s, 3, $baris, $lap['jenis'][$jenis]['total'], self::RUPIAH);
        }

        $baris += 2;
        $this->kepala($s, $baris, ['Penerimaan', 'Transaksi', 'Pemasukan']);
        foreach (['Ambil Sendiri', 'Diantar'] as $metode) {
            $baris++;
            $this->teks($s, 1, $baris, $metode);
            $this->angka($s, 2, $baris, $lap['metode'][$metode]['n']);
            $this->angka($s, 3, $baris, $lap['metode'][$metode]['total'], self::RUPIAH);
        }

        $baris += 2;
        $this->teks($s, 1, $baris, 'Catatan: pesanan dibatalkan tidak dihitung sebagai pemasukan; refund dilakukan manual di dasbor Midtrans.');

        $s->getColumnDimensionByColumn(1)->setWidth(58);
        $s->getColumnDimensionByColumn(2)->setWidth(24);
        $s->getColumnDimensionByColumn(3)->setWidth(18);
    }

    private function lembarHarian(Worksheet $s, array $lap): void
    {
        $s->setTitle('Harian');
        $this->kepala($s, 1, ['Tanggal', 'Transaksi', 'Pemasukan']);

        $baris = 1;
        foreach ($lap['harian'] as $h) {
            $baris++;
            $this->tanggal($s, 1, $baris, $h->hari, self::TANGGAL);
            $this->angka($s, 2, $baris, (int) $h->n);
            $this->angka($s, 3, $baris, (int) $h->total, self::RUPIAH);
        }

        if ($baris > 1) {
            $baris++;
            $this->teks($s, 1, $baris, 'Total');
            $s->setCellValue([2, $baris], '=SUM(B2:B'.($baris - 1).')');
            $s->setCellValue([3, $baris], '=SUM(C2:C'.($baris - 1).')');
            $s->getStyle([3, $baris])->getNumberFormat()->setFormatCode(self::RUPIAH);
            $s->getStyle([1, $baris, 3, $baris])->getFont()->setBold(true);
        }

        $s->freezePane('A2');
        $this->lebar($s, 3);
    }

    private function lembarUnit(Worksheet $s, array $lap): void
    {
        $s->setTitle('Per Unit');
        $this->kepala($s, 1, ['Unit', 'Jenis', 'Transaksi', 'Pemasukan']);

        $baris = 1;
        foreach ($lap['unit'] as $u) {
            $baris++;
            $this->teks($s, 1, $baris, $u->kode_unit);
            $this->teks($s, 2, $baris, $u->jenis);
            $this->angka($s, 3, $baris, (int) $u->n);
            $this->angka($s, 4, $baris, (int) $u->total, self::RUPIAH);
        }

        if ($baris > 1) {
            $baris++;
            $this->teks($s, 1, $baris, 'Total');
            $s->setCellValue([3, $baris], '=SUM(C2:C'.($baris - 1).')');
            $s->setCellValue([4, $baris], '=SUM(D2:D'.($baris - 1).')');
            $s->getStyle([4, $baris])->getNumberFormat()->setFormatCode(self::RUPIAH);
            $s->getStyle([1, $baris, 4, $baris])->getFont()->setBold(true);
        }

        $s->freezePane('A2');
        $this->lebar($s, 4);
    }

    private function lembarRincian(Worksheet $s, iterable $rincian): void
    {
        $s->setTitle('Rincian');
        $this->kepala($s, 1, [
            'Waktu bayar', 'Kode booking', 'Pelanggan', 'Unit', 'Jenis', 'Jadwal mulai',
            'Durasi (jam)', 'Penerimaan', 'Stik', 'Status', 'Nominal', 'Denda',
        ]);

        $baris = 1;
        foreach ($rincian as $r) {
            $baris++;
            $this->waktu($s, 1, $baris, $r->waktu_bayar);
            $this->teks($s, 2, $baris, $r->kode_booking);
            $this->teks($s, 3, $baris, $r->pelanggan);
            $this->teks($s, 4, $baris, $r->kode_unit);
            $this->teks($s, 5, $baris, $r->jenis);
            $this->waktu($s, 6, $baris, $r->waktu_mulai);
            $this->angka($s, 7, $baris, (int) $r->durasi_jam);
            $this->teks($s, 8, $baris, $r->metode_serah);
            $this->angka($s, 9, $baris, (int) $r->jumlah_stik);
            $this->teks($s, 10, $baris, $r->status_booking);
            $this->angka($s, 11, $baris, (int) $r->nominal, self::RUPIAH);
            $this->angka($s, 12, $baris, (int) ($r->denda ?? 0), self::RUPIAH_STRIP);
        }

        if ($baris > 1) {
            $akhir = $baris;
            $baris++;
            $this->teks($s, 1, $baris, 'Total');
            $s->setCellValue([11, $baris], '=SUM(K2:K'.$akhir.')');
            $s->setCellValue([12, $baris], '=SUM(L2:L'.$akhir.')');
            $s->getStyle([11, $baris, 12, $baris])->getNumberFormat()->setFormatCode(self::RUPIAH);
            $s->getStyle([1, $baris, 12, $baris])->getFont()->setBold(true);
            $s->setAutoFilter('A1:L'.$akhir);
        }

        $s->freezePane('A2');
        $this->lebar($s, 12);
    }

    // ---------- Pembantu ----------

    /** Teks selalu ditulis sebagai string agar isi seperti "=1+1" tidak dianggap rumus. */
    private function teks(Worksheet $s, int $kolom, int $baris, ?string $nilai): void
    {
        $s->setCellValueExplicit([$kolom, $baris], (string) ($nilai ?? ''), DataType::TYPE_STRING);
    }

    private function angka(Worksheet $s, int $kolom, int $baris, int|float $nilai, ?string $format = null): void
    {
        $s->setCellValueExplicit([$kolom, $baris], $nilai, DataType::TYPE_NUMERIC);
        if ($format !== null) {
            $s->getStyle([$kolom, $baris])->getNumberFormat()->setFormatCode($format);
        }
    }

    private function waktu(Worksheet $s, int $kolom, int $baris, mixed $nilai): void
    {
        $this->tanggal($s, $kolom, $baris, $nilai, self::WAKTU);
    }

    private function tanggal(Worksheet $s, int $kolom, int $baris, mixed $nilai, string $format): void
    {
        $s->setCellValueExplicit([$kolom, $baris], Date::dateTimeToExcel(Carbon::parse($nilai)), DataType::TYPE_NUMERIC);
        $s->getStyle([$kolom, $baris])->getNumberFormat()->setFormatCode($format);
    }

    /** @param  list<string>  $judul */
    private function kepala(Worksheet $s, int $baris, array $judul): void
    {
        foreach ($judul as $i => $nama) {
            $this->teks($s, $i + 1, $baris, $nama);
        }
        $rentang = $s->getStyle([1, $baris, count($judul), $baris]);
        $rentang->getFont()->setBold(true);
        $rentang->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEF1F5');
    }

    private function lebar(Worksheet $s, int $jumlahKolom): void
    {
        for ($k = 1; $k <= $jumlahKolom; $k++) {
            $s->getColumnDimensionByColumn($k)->setAutoSize(true);
        }
    }
}