<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class EmailTemplateService
{
    /** Cadangan bila baris templat di database hilang. */
    private const CADANGAN = [
        'pembayaran_berhasil' => [
            'Pembayaran berhasil - Kode {{kode}}',
            'Halo {{nama}}, kode konfirmasi kamu: {{kode}}. Jadwal: {{jadwal}}. Invoice terlampir.',
        ],
        'pengingat_mulai' => [
            'Pengingat: sewa dimulai {{waktu_mulai}} - {{kode_booking}}',
            'Halo {{nama}}, sewa {{unit}} kamu akan dimulai pada {{waktu_mulai}}. Siapkan kode konfirmasi serah terima: {{kode}}. Kode booking: {{kode_booking}}.',
        ],
        'pengingat_selesai' => [
            'Pengingat: sewa berakhir {{waktu_selesai}} - {{kode_booking}}',
            'Halo {{nama}}, sewa {{unit}} kamu akan berakhir pada {{waktu_selesai}}. Mohon kembalikan unit tepat waktu agar tidak terkena denda keterlambatan.',
        ],
    ];

    /** @return array{0: string, 1: string} [subjek, isi] dengan placeholder {{...}} terisi */
    public function render(string $jenis, array $data): array
    {
        $templat = DB::table('email_templates')->where('jenis_email', $jenis)->first();

        $subjek = $templat->subjek ?? (self::CADANGAN[$jenis][0] ?? $jenis);
        $isi = $templat->isi ?? (self::CADANGAN[$jenis][1] ?? '');

        $peta = [];
        foreach ($data as $kunci => $nilai) {
            $peta['{{'.$kunci.'}}'] = (string) $nilai;
        }

        return [strtr($subjek, $peta), strtr($isi, $peta)];
    }

    public function dataBooking(Booking $b): array
    {
        $b->loadMissing(['console', 'user']);

        return [
            'nama' => $b->user->name,
            'kode' => (string) $b->kode_konfirmasi,
            'kode_booking' => $b->kode_booking,
            'unit' => $b->console->nama_tampil,
            'jadwal' => $b->waktu_mulai->format('d-m-Y H:i').' s.d. '.$b->waktu_selesai->format('d-m-Y H:i'),
            'waktu_mulai' => $b->waktu_mulai->format('d-m-Y H:i'),
            'waktu_selesai' => $b->waktu_selesai->format('d-m-Y H:i'),
            'batas_bayar' => $b->batas_bayar?->format('d-m-Y H:i') ?? '-',
            'total' => Booking::rupiah($b->total_harga),
            'denda' => Booking::rupiah($b->denda_per_keterlambatan),
            'alasan' => '-',
        ];
    }
}