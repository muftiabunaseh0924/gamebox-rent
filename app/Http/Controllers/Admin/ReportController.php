<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\ReportExcelService;
use App\Services\ReportService;
use App\Services\SettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const MAKS_HARI = 366;
    private const MAKS_BARIS_PDF = 2000;

    /**
     * Menentukan periode dari bulan (Y-m) atau rentang tanggal; dipakai halaman dan ekspor.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: ?string}
     */
    private function periode(Request $request): array
    {
        $f = $request->validate([
            'bulan' => ['nullable', 'date_format:Y-m'],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
        ], [
            'date_format' => 'Format tanggal atau bulan tidak valid.',
        ]);

        $dari = $f['dari'] ?? null;
        $sampai = $f['sampai'] ?? null;
        $kembali = route('admin.laporan');

        if (filled($dari) xor filled($sampai)) {
            throw ValidationException::withMessages(['dari' => 'Isi tanggal "dari" dan "sampai" sekaligus.'])
                ->redirectTo($kembali);
        }

        if (filled($dari)) {
            $mulai = Carbon::createFromFormat('!Y-m-d', $dari)->startOfDay();
            $akhir = Carbon::createFromFormat('!Y-m-d', $sampai)->endOfDay();

            if ($akhir->lt($mulai)) {
                throw ValidationException::withMessages(['sampai' => 'Tanggal "sampai" tidak boleh sebelum tanggal "dari".'])
                    ->redirectTo($kembali);
            }
            if ($mulai->diffInDays($akhir) > self::MAKS_HARI) {
                throw ValidationException::withMessages(['sampai' => 'Rentang maksimal '.self::MAKS_HARI.' hari.'])
                    ->redirectTo($kembali);
            }

            return [$mulai, $akhir, $mulai->format('d-m-Y').' s/d '.$akhir->format('d-m-Y'), null];
        }

        $bulanAktif = $f['bulan'] ?? now()->format('Y-m');
        $mulai = Carbon::createFromFormat('!Y-m', $bulanAktif)->startOfMonth();

        return [$mulai, $mulai->copy()->endOfMonth(), 'Bulan '.ReportService::labelBulan($mulai), $bulanAktif];
    }

    public function index(Request $request, ReportService $laporan): View
    {
        [$mulai, $akhir, $label, $bulanAktif] = $this->periode($request);

        // Pilihan bulan: 12 bulan terakhir.
        $opsiBulan = [];
        for ($i = 0; $i < 12; $i++) {
            $b = now()->startOfMonth()->subMonthsNoOverflow($i);
            $opsiBulan[$b->format('Y-m')] = ReportService::labelBulan($b);
        }
        if ($bulanAktif !== null && ! isset($opsiBulan[$bulanAktif])) {
            $opsiBulan[$bulanAktif] = ReportService::labelBulan($mulai);
        }

        return view('admin.laporan.index', [
            'lap' => $laporan->ringkasan($mulai, $akhir),
            'rincian' => $laporan->rincian($mulai, $akhir)->paginate(20)->withQueryString(),
            'label' => $label,
            'opsiBulan' => $opsiBulan,
            'bulanAktif' => $bulanAktif,
            'filter' => ['dari' => $request->input('dari'), 'sampai' => $request->input('sampai')],
            // Parameter periode yang aktif, diteruskan ke tombol ekspor.
            'query' => $bulanAktif !== null
                ? ['bulan' => $bulanAktif]
                : ['dari' => $mulai->format('Y-m-d'), 'sampai' => $akhir->format('Y-m-d')],
        ]);
    }

    public function pdf(Request $request, ReportService $laporan, SettingsService $pengaturan): Response
    {
        [$mulai, $akhir, $label] = $this->periode($request);

        $rincian = $laporan->rincian($mulai, $akhir);
        $jumlah = (clone $rincian)->reorder()->count();
        $baris = $rincian->limit(self::MAKS_BARIS_PDF)->get();

        ActivityLogger::catat($request->user(), 'Ekspor Laporan', "Ekspor PDF laporan keuangan {$label}.");

        $pdf = Pdf::loadView('admin.laporan.pdf', [
            'lap' => $laporan->ringkasan($mulai, $akhir),
            'rincian' => $baris,
            'jumlah' => $jumlah,
            'dipotong' => $jumlah > $baris->count(),
            'label' => $label,
            'situs' => $pengaturan->situs(),
            'dicetak' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan-Keuangan-'.$mulai->format('Ymd').'-'.$akhir->format('Ymd').'.pdf');
    }

    public function excel(
        Request $request,
        ReportService $laporan,
        ReportExcelService $excel,
        SettingsService $pengaturan
    ): StreamedResponse {
        [$mulai, $akhir, $label] = $this->periode($request);

        // Semua transaksi periode ini (tanpa batas baris), dibaca bertahap agar hemat memori.
        $spreadsheet = $excel->buat(
            $laporan->ringkasan($mulai, $akhir),
            $laporan->rincian($mulai, $akhir)->cursor(),
            $label,
            $pengaturan->situs(),
            now()
        );

        ActivityLogger::catat($request->user(), 'Ekspor Laporan', "Ekspor Excel laporan keuangan {$label}.");

        $nama = 'Laporan-Keuangan-'.$mulai->format('Ymd').'-'.$akhir->format('Ymd').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $nama, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}