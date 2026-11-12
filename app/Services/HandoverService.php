<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Console;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HandoverService
{
    /** Serah terima dibuka mulai sekian menit sebelum jadwal mulai. */
    public const JENDELA_SEBELUM_MENIT = 60;

    public function cariByKode(string $kode): ?Booking
    {
        $kode = strtoupper(trim($kode));
        if ($kode === '') {
            return null;
        }

        return Booking::with(['console', 'user', 'payments'])->where('kode_konfirmasi', $kode)->first();
    }

    private function toleransiMenit(): int
    {
        return (int) (DB::table('settings')->where('key', 'toleransi_ambil_menit')->value('value') ?? 30);
    }

    /**
     * Menilai apakah booking boleh diserahterimakan sekarang.
     *
     * @return array{bisa: bool, pesan: ?string, bisa_dipaksa: bool, dipaksa: bool}
     *   bisa_dipaksa = ditolak HANYA karena di luar jendela waktu (admin boleh mengabaikan).
     *   dipaksa      = lolos karena jendela waktu diabaikan.
     */
    public function evaluasi(Booking $b, bool $abaikanJendela = false): array
    {
        $b->loadMissing('console');
        $status = $b->status_booking;

        if ($b->kode_dipakai || $status === 'Sedang Disewa') {
            $kapan = $b->waktu_serah_terima ? ' pada '.$b->waktu_serah_terima->format('d-m-Y H:i') : '';

            return $this->tolak('Kode ini sudah dipakai untuk serah terima'.$kapan.'.');
        }
        if ($status === 'Selesai') {
            return $this->tolak('Pesanan ini sudah selesai.');
        }
        if ($status === 'Dibatalkan') {
            return $this->tolak('Pesanan ini dibatalkan.');
        }
        if ($status === 'Menunggu Pembayaran') {
            return $this->tolak('Pesanan ini belum dibayar.');
        }
        if ($status !== 'Dibayar') {
            return $this->tolak('Status pesanan ('.$status.') tidak dapat diserahterimakan.');
        }

        if ($b->console->status !== 'Tersedia') {
            return $this->tolak("Unit {$b->console->kode_unit} berstatus {$b->console->status} dan belum bisa diserahkan. Selesaikan dulu penyewaan sebelumnya atau ubah status unit.");
        }

        $toleransi = $this->toleransiMenit();
        $buka = $b->waktu_mulai->copy()->subMinutes(self::JENDELA_SEBELUM_MENIT);
        $tutup = $b->waktu_mulai->copy()->addMinutes($toleransi);
        $terlaluAwal = now()->lt($buka);
        $terlaluLambat = now()->gt($tutup);
        $diLuar = $terlaluAwal || $terlaluLambat;

        if ($diLuar && ! $abaikanJendela) {
            $pesan = $terlaluAwal
                ? 'Terlalu awal. Serah terima dibuka mulai '.$buka->format('d-m-Y H:i').' ('.self::JENDELA_SEBELUM_MENIT.' menit sebelum jadwal mulai).'
                : 'Melewati batas pengambilan ('.$tutup->format('d-m-Y H:i').', toleransi '.$toleransi.' menit setelah jadwal mulai).';

            return $this->tolak($pesan, true);
        }

        return ['bisa' => true, 'pesan' => null, 'bisa_dipaksa' => false, 'dipaksa' => $diLuar];
    }

    /** Serah terima unit ke pelanggan. Semua pengecekan diulang di dalam transaksi. */
    public function serahkan(Booking $booking, User $admin, bool $abaikanJendela = false): Booking
    {
        return DB::transaction(function () use ($booking, $admin, $abaikanJendela) {
            // Urutan kunci: booking dulu, lalu unit.
            $b = Booking::with('user')->lockForUpdate()->findOrFail($booking->id);
            $console = Console::lockForUpdate()->findOrFail($b->console_id);
            $b->setRelation('console', $console);

            $hasil = $this->evaluasi($b, $abaikanJendela);
            if (! $hasil['bisa']) {
                throw new BookingException('kode', (string) $hasil['pesan']);
            }

            $lokasi = $b->metode_serah === 'Diantar' ? 'Di alamat pelanggan' : 'Dibawa pelanggan';

            $b->forceFill([
                'status_booking' => 'Sedang Disewa',
                'kode_dipakai' => true,
                'waktu_serah_terima' => now(),
                'admin_konfirmasi_id' => $admin->id,
            ])->save();

            $console->forceFill([
                'status' => 'Disewa',
                'booking_aktif_id' => $b->id,
                'lokasi_saat_ini' => $lokasi,
            ])->save();

            $this->pindah($console, $lokasi, $b, $admin);
            $this->catat($admin, 'serah_terima',
                "Serah terima {$b->kode_booking} unit {$console->kode_unit} ke {$b->user->name}"
                .($hasil['dipaksa'] ? ' (jendela waktu diabaikan)' : ''));

            return $b;
        });
    }

    /** Unit kembali: hitung denda, booking selesai, unit tersedia lagi. */
    public function selesaikan(Booking $booking, User $admin, ?string $catatan = null): Booking
    {
        return DB::transaction(function () use ($booking, $admin, $catatan) {
            $b = Booking::with('user')->lockForUpdate()->findOrFail($booking->id);

            if ($b->status_booking !== 'Sedang Disewa') {
                throw new BookingException('booking', 'Hanya penyewaan yang sedang berjalan yang dapat ditandai selesai.');
            }

            $console = Console::lockForUpdate()->findOrFail($b->console_id);
            $sekarang = now();
            $denda = $this->hitungDenda($b, $sekarang);

            $b->forceFill([
                'status_booking' => 'Selesai',
                'waktu_kembali' => $sekarang,
                'denda' => $denda,
                'catatan_kondisi' => filled($catatan) ? trim($catatan) : null,
            ])->save();

            if ((int) $console->booking_aktif_id === (int) $b->id) {
                $console->forceFill([
                    'status' => 'Tersedia',
                    'booking_aktif_id' => null,
                    'lokasi_saat_ini' => 'Di tempat sewa',
                ])->save();
                $this->pindah($console, 'Di tempat sewa', $b, $admin);
            }

            $this->catat($admin, 'selesai_sewa',
                "Penyewaan {$b->kode_booking} unit {$console->kode_unit} selesai. Denda: Rp ".number_format($denda, 0, ',', '.'));

            $id = $b->id;
            DB::afterCommit(fn () => app(NotifikasiService::class)->sewaSelesai($id));

            return $b;
        });
    }

    /** Denda = jam keterlambatan (dibulatkan ke atas) x tarif yang disalin ke booking. */
    public function hitungDenda(Booking $b, $pada = null): int
    {
        $pada = $pada ?? now();
        $detik = $pada->timestamp - $b->waktu_selesai->timestamp;

        if ($detik <= 0) {
            return 0;
        }

        return (int) ceil($detik / 3600) * (int) $b->denda_per_keterlambatan;
    }

    private function tolak(string $pesan, bool $bisaDipaksa = false): array
    {
        return ['bisa' => false, 'pesan' => $pesan, 'bisa_dipaksa' => $bisaDipaksa, 'dipaksa' => false];
    }

    private function pindah(Console $console, string $lokasi, Booking $b, User $admin): void
    {
        DB::table('console_movements')->insert([
            'console_id' => $console->id,
            'lokasi' => $lokasi,
            'booking_id' => $b->id,
            'admin_id' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function catat(User $admin, string $aksi, string $detail): void
    {
        DB::table('activity_logs')->insert([
            'admin_id' => $admin->id,
            'aksi' => $aksi,
            'detail' => $detail,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}