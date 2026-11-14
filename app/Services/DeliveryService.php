<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Delivery;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeliveryService
{
    public function __construct(private readonly HandoverService $handover)
    {
    }

    /** Membuat catatan pengantaran untuk booking Diantar. Aman dipanggil berulang. */
    public function mulai(Booking $b, ?string $statusAwal = null): ?Delivery
    {
        if ($b->metode_serah !== 'Diantar') {
            return null;
        }

        $ada = Delivery::where('booking_id', $b->id)->first();
        if ($ada) {
            return $ada;
        }

        $status = $statusAwal ?? 'Menunggu Dikirim';

        try {
            $d = new Delivery();
            $d->forceFill(['booking_id' => $b->id, 'status_kirim' => $status])->save();
        } catch (QueryException $e) {
            // Dibuat bersamaan oleh proses lain (booking_id unik).
            return Delivery::where('booking_id', $b->id)->first();
        }

        $this->catatEvent($b->id, $status, $status === 'Telah Diterima'
            ? 'Unit sudah diserahkan sebelum fitur pelacakan pengantaran tersedia.'
            : 'Pembayaran berhasil. Menunggu penjadwalan kurir.', null);

        return $d;
    }

    /**
     * Merapikan data pengantaran:
     * 1) melengkapi catatan yang terlewat (mis. dibayar sebelum fitur ini ada);
     * 2) menutup catatan yang statusnya tertinggal padahal unit sudah diserahkan atau selesai.
     */
    public function sinkronkan(): void
    {
        Booking::where('metode_serah', 'Diantar')
            ->whereIn('status_booking', ['Dibayar', 'Sedang Disewa', 'Selesai'])
            ->whereDoesntHave('delivery')
            ->get()
            ->each(fn (Booking $b) => $this->mulai($b, $b->status_booking === 'Dibayar' ? null : 'Telah Diterima'));

        Delivery::whereIn('status_kirim', ['Menunggu Dikirim', 'Kurir Ditugaskan', 'Dalam Perjalanan', 'Gagal Dikirim'])
            ->whereHas('booking', fn ($q) => $q->whereIn('status_booking', ['Sedang Disewa', 'Selesai']))
            ->get()
            ->each(function (Delivery $d) {
                $dari = $d->status_kirim;
                $d->forceFill([
                    'status_kirim' => 'Telah Diterima',
                    'catatan' => 'Disesuaikan otomatis: unit sudah diserahkan.',
                ])->save();

                $this->catatEvent($d->booking_id, 'Telah Diterima',
                    "Disesuaikan otomatis: unit sudah diserahkan (status pengantaran sebelumnya: {$dari}).", null);
            });
    }

    /**
     * Mengubah status pengantaran.
     * aksi: tugaskan | berangkat | gagal | terima
     */
    public function ubah(Booking $booking, string $aksi, User $admin, array $data = []): Delivery
    {
        return DB::transaction(function () use ($booking, $aksi, $admin, $data) {
            $b = Booking::with(['console', 'user'])->lockForUpdate()->findOrFail($booking->id);
            $d = Delivery::where('booking_id', $b->id)->lockForUpdate()->first();

            if (! $d || $b->metode_serah !== 'Diantar') {
                throw new BookingException('pengiriman', 'Pesanan ini tidak memiliki catatan pengantaran.');
            }
            if ($b->status_booking !== 'Dibayar') {
                throw new BookingException('pengiriman', 'Pengantaran hanya dapat diproses untuk pesanan berstatus Dibayar.');
            }

            $dari = $d->status_kirim;
            $catatan = trim((string) ($data['catatan'] ?? ''));
            $attrs = [];

            switch ($aksi) {
                case 'tugaskan':
                    $this->wajibDari($dari, ['Menunggu Dikirim', 'Gagal Dikirim'], 'menugaskan kurir');
                    $baru = 'Kurir Ditugaskan';
                    $attrs = [
                        'nama_kurir' => trim((string) $data['nama_kurir']),
                        'hp_kurir' => preg_replace('/[\s\-]/', '', (string) $data['hp_kurir']),
                    ];
                    $teks = "Kurir {$attrs['nama_kurir']} ({$attrs['hp_kurir']}) ditugaskan."
                        .($catatan !== '' ? " {$catatan}" : '');
                    break;

                case 'berangkat':
                    $this->wajibDari($dari, ['Kurir Ditugaskan'], 'memberangkatkan kurir');
                    $baru = 'Dalam Perjalanan';
                    $teks = 'Kurir berangkat menuju alamat pelanggan.'.($catatan !== '' ? " {$catatan}" : '');
                    break;

                case 'gagal':
                    $this->wajibDari($dari, ['Kurir Ditugaskan', 'Dalam Perjalanan'], 'menandai gagal');
                    if ($catatan === '') {
                        throw new BookingException('catatan', 'Alasan pengantaran gagal wajib diisi.');
                    }
                    $baru = 'Gagal Dikirim';
                    $teks = "Pengantaran gagal: {$catatan}";
                    break;

                case 'terima':
                    $this->wajibDari($dari, ['Dalam Perjalanan'], 'mengonfirmasi penerimaan');
                    $kode = strtoupper(trim((string) ($data['kode_konfirmasi'] ?? '')));
                    if ($kode === '' || ! hash_equals((string) $b->kode_konfirmasi, $kode)) {
                        throw new BookingException('kode_konfirmasi', 'Kode konfirmasi tidak cocok. Minta kode dari pelanggan.');
                    }
                    // Serah terima unit lewat aturan yang sama dengan L8 (jendela waktu, status unit, log).
                    // false: status pengantaran diurus di sini, bukan oleh HandoverService.
                    $this->handover->serahkan($b, $admin, (bool) ($data['abaikan_jendela'] ?? false), false);
                    $baru = 'Telah Diterima';
                    $teks = 'Unit diterima pelanggan. Kode konfirmasi terverifikasi.';
                    break;

                default:
                    throw new BookingException('pengiriman', 'Aksi tidak dikenal.');
            }

            $d->forceFill(array_merge($attrs, [
                'status_kirim' => $baru,
                'catatan' => Str::limit($teks, 200, ''),
                'admin_id' => $admin->id,
            ]))->save();

            $this->catatEvent($b->id, $baru, Str::limit($teks, 255, ''), $admin->id);

            DB::table('activity_logs')->insert([
                'admin_id' => $admin->id,
                'aksi' => 'pengantaran',
                'detail' => "Pengantaran {$b->kode_booking}: {$dari} -> {$baru}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $d;
        });
    }

    private function wajibDari(string $sekarang, array $boleh, string $tindakan): void
    {
        if (! in_array($sekarang, $boleh, true)) {
            throw new BookingException('pengiriman', "Tidak dapat {$tindakan} saat status pengantaran \"{$sekarang}\".");
        }
    }

    private function catatEvent(int $bookingId, string $status, string $catatan, ?int $adminId): void
    {
        DB::table('delivery_events')->insert([
            'booking_id' => $bookingId,
            'status_kirim' => $status,
            'catatan' => Str::limit($catatan, 255, ''),
            'admin_id' => $adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}