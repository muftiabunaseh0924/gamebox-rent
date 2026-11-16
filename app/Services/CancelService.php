<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Transaction;

class CancelService
{
    /**
     * Admin membatalkan pesanan yang belum diserahkan (Menunggu Pembayaran atau Dibayar).
     * Jadwal dilepas otomatis (pesanan Dibatalkan tidak memegang jadwal).
     * Refund dana untuk pesanan yang sudah dibayar dilakukan MANUAL di dasbor Midtrans.
     */
    public function batalkan(Booking $booking, User $admin, string $alasan): Booking
    {
        $alasan = trim($alasan);

        return DB::transaction(function () use ($booking, $admin, $alasan) {
            $b = Booking::with('user')->lockForUpdate()->findOrFail($booking->id);

            if (! in_array($b->status_booking, ['Menunggu Pembayaran', 'Dibayar'], true) || $b->kode_dipakai) {
                throw new BookingException('booking', 'Pesanan dengan status "'.$b->status_booking.'" tidak dapat dibatalkan.');
            }

            $sudahBayar = $b->status_booking === 'Dibayar';

            $b->forceFill([
                'status_booking' => 'Dibatalkan',
                'alasan_batal' => Str::limit($alasan, 255, ''),
                'dibatalkan_at' => now(),
            ])->save();

            // Percobaan bayar yang masih menunggu ikut dibatalkan.
            $orderPending = [];
            foreach (Payment::where('booking_id', $b->id)->where('status_pembayaran', 'Pending')->lockForUpdate()->get() as $p) {
                $orderPending[] = $p->order_id;
                $p->forceFill(['status_pembayaran' => 'Dibatalkan'])->save();
            }

            // Pengantaran yang belum selesai ditutup.
            $d = Delivery::where('booking_id', $b->id)->lockForUpdate()->first();
            if ($d && $d->status_kirim !== 'Telah Diterima') {
                $d->forceFill(['status_kirim' => 'Dibatalkan', 'catatan' => 'Pesanan dibatalkan admin.', 'admin_id' => $admin->id])->save();
                DB::table('delivery_events')->insert([
                    'booking_id' => $b->id,
                    'status_kirim' => 'Dibatalkan',
                    'catatan' => 'Pesanan dibatalkan admin: '.Str::limit($alasan, 200, ''),
                    'admin_id' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('activity_logs')->insert([
                'admin_id' => $admin->id,
                'aksi' => 'batal_pesanan',
                'detail' => "Pesanan {$b->kode_booking} dibatalkan: {$alasan}".($sudahBayar ? ' (sudah dibayar, refund manual)' : ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $id = $b->id;
            DB::afterCommit(function () use ($id, $alasan, $sudahBayar, $orderPending) {
                app(NotifikasiService::class)->dibatalkan($id, $alasan, $sudahBayar);
                $this->batalkanDiMidtrans($orderPending);
            });

            return $b;
        });
    }

    /** Transaksi yang belum dibayar dibatalkan di Midtrans agar tidak bisa dibayar lagi. Kegagalan hanya dicatat. */
    private function batalkanDiMidtrans(array $orders): void
    {
        if ($orders === [] || blank(config('midtrans.server_key'))) {
            return;
        }

        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');

        foreach ($orders as $order) {
            try {
                Transaction::cancel($order);
            } catch (\Throwable $e) {
                // Mis. 404: pelanggan belum pernah memilih metode di popup. Aman diabaikan.
                Log::info('Pembatalan transaksi Midtrans dilewati: '.$e->getMessage(), ['order_id' => $order]);
            }
        }
    }
}