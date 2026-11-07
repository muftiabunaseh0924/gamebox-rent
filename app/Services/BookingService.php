<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Console;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingService
{
    private const ALFABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // tanpa I, O, 0, 1

    /** Pengaturan dari tabel settings, sudah dalam tipe yang benar. */
    public function pengaturan(): array
    {
        $s = DB::table('settings')->pluck('value', 'key');

        $slot = collect(explode(',', (string) $s->get('jam_mulai_slot', '0,2,4,6,8,10,12,14,16,18,20,22')))
            ->map(fn ($j) => trim($j))
            ->filter(fn ($j) => $j !== '' && ctype_digit($j) && (int) $j <= 23)
            ->map(fn ($j) => (int) $j)
            ->unique()->sort()->values()->all();

        return [
            'harga_stik' => (int) $s->get('harga_stik', 0),
            'denda' => (int) $s->get('denda_keterlambatan', 0),
            'buffer_jam' => (int) $s->get('buffer_jam', 0),
            'batas_bayar_menit' => (int) $s->get('batas_bayar_menit', 15),
            'stok_stik' => (int) $s->get('stok_stik', 0),
            'slot' => $slot,
            'maks_hari' => (int) $s->get('maks_hari_ke_depan', 7),
            'maks_menunggu' => (int) $s->get('maks_booking_menunggu', 2),
            'biaya_pengiriman' => (int) $s->get('biaya_pengiriman', 0),
            'pengiriman_aktif' => (bool) (int) $s->get('pengiriman_aktif', 0),
            'panjang_kode' => max(4, (int) $s->get('panjang_kode', 6)),
        ];
    }

    /**
     * Membuat booking. Seluruh pengecekan dilakukan DI DALAM transaksi setelah
     * semua baris unit dikunci, sehingga dua permintaan bersamaan tidak bisa
     * sama-sama lolos ke slot (atau stok stik) yang sama.
     */
    public function buat(User $user, array $data): Booking
    {
        $cfg = $this->pengaturan();

        return DB::transaction(function () use ($user, $data, $cfg) {
            // PENTING: query pertama di transaksi harus kunci ini (baca ulang setelah kunci).
            $units = Console::orderBy('id')->lockForUpdate()->get()->keyBy('id');

            $console = $units->get((int) $data['console_id']);
            if (! $console) {
                throw new BookingException('console_id', 'Unit tidak ditemukan.');
            }
            if ($console->status !== 'Tersedia') {
                throw new BookingException('console_id', 'Unit ini sedang tidak tersedia untuk disewa.');
            }

            $jam = (int) $data['jam_mulai'];
            if (! in_array($jam, $cfg['slot'], true)) {
                throw new BookingException('jam_mulai', 'Jam mulai tidak termasuk slot yang tersedia.');
            }

            $mulai = Carbon::createFromFormat('Y-m-d H:i', $data['tanggal'].' '.sprintf('%02d:00', $jam));
            if ($mulai->lte(now())) {
                throw new BookingException('tanggal', 'Jadwal mulai sudah lewat. Pilih waktu yang akan datang.');
            }
            if ($mulai->gt(now()->addDays($cfg['maks_hari'])->endOfDay())) {
                throw new BookingException('tanggal', "Booking maksimal {$cfg['maks_hari']} hari ke depan.");
            }
            if ($this->tanggalDiblokir($mulai)) {
                throw new BookingException('tanggal', 'Tanggal tersebut tidak tersedia untuk penyewaan.');
            }

            $durasi = (int) $data['durasi_jam'];
            $paket = Package::aktif()
                ->where('jenis', $console->jenis)
                ->where('durasi_jam', $durasi)
                ->first();
            if (! $paket) {
                throw new BookingException('durasi_jam', 'Durasi sewa tidak tersedia untuk jenis konsol ini.');
            }
            $selesai = $mulai->copy()->addHours($durasi);

            $menunggu = Booking::where('user_id', $user->id)
                ->where('status_booking', 'Menunggu Pembayaran')
                ->where('batas_bayar', '>', now())
                ->count();
            if ($menunggu >= $cfg['maks_menunggu']) {
                throw new BookingException('booking', "Anda masih punya {$menunggu} booking yang menunggu pembayaran. Selesaikan atau tunggu kedaluwarsa dulu (maksimal {$cfg['maks_menunggu']}).");
            }

            if ($this->adaBentrok($console->id, $mulai, $selesai, $cfg['buffer_jam'])) {
                throw new BookingException('jam_mulai', 'Jadwal bentrok dengan booking lain (termasuk jeda '.$cfg['buffer_jam'].' jam untuk pembersihan unit). Pilih jam lain.');
            }

            $jumlahStik = (int) ($data['jumlah_stik'] ?? 0);
            if ($jumlahStik > 0) {
                $terpakai = (int) $this->bookingAktif()
                    ->where('waktu_mulai', '<', $selesai->copy()->addHours($cfg['buffer_jam']))
                    ->where('waktu_selesai', '>', $mulai->copy()->subHours($cfg['buffer_jam']))
                    ->sum('jumlah_stik');
                $sisa = max(0, $cfg['stok_stik'] - $terpakai);
                if ($jumlahStik > $sisa) {
                    throw new BookingException('jumlah_stik', "Stik tambahan tidak cukup. Tersisa {$sisa} untuk jadwal ini.");
                }
            }

            $metode = $data['metode_serah'];
            $biayaKirim = 0;
            $alamat = null;
            if ($metode === 'Diantar') {
                if (! $cfg['pengiriman_aktif']) {
                    throw new BookingException('metode_serah', 'Layanan pengantaran sedang tidak aktif.');
                }
                $alamat = trim((string) ($data['alamat_pengiriman'] ?? ''));
                $biayaKirim = $cfg['biaya_pengiriman'];
            }

            $total = $paket->harga + ($jumlahStik * $cfg['harga_stik']) + $biayaKirim;

            $booking = new Booking();
            $booking->forceFill([
                'kode_booking' => $this->kodeBooking($cfg['panjang_kode']),
                'user_id' => $user->id,
                'console_id' => $console->id,
                'waktu_mulai' => $mulai,
                'waktu_selesai' => $selesai,
                'durasi_jam' => $durasi,
                'jumlah_stik' => $jumlahStik,
                'harga_paket' => $paket->harga,
                'harga_stik' => $cfg['harga_stik'],          // harga PER stik (salinan)
                'biaya_pengiriman' => $biayaKirim,
                'total_harga' => $total,
                'buffer_jam' => $cfg['buffer_jam'],
                'denda_per_keterlambatan' => $cfg['denda'],
                'metode_serah' => $metode,
                'alamat_pengiriman' => $alamat,
                'status_booking' => 'Menunggu Pembayaran',
                'batas_bayar' => now()->addMinutes($cfg['batas_bayar_menit']),
            ])->save();

            return $booking->load('console');
        });
    }

    /** Daftar slot jam untuk satu unit, satu tanggal, dan durasi tertentu. */
    public function slotUntuk(Console $console, Carbon $tanggal, int $durasi): array
    {
        $cfg = $this->pengaturan();
        $hasil = [];

        foreach ($cfg['slot'] as $jam) {
            $mulai = $tanggal->copy()->setTime($jam, 0);
            $selesai = $mulai->copy()->addHours($durasi);
            $alasan = null;

            if ($mulai->lte(now())) {
                $alasan = 'Lewat';
            } elseif ($mulai->gt(now()->addDays($cfg['maks_hari'])->endOfDay())) {
                $alasan = 'Di luar batas';
            } elseif ($this->tanggalDiblokir($mulai)) {
                $alasan = 'Libur';
            } elseif ($this->adaBentrok($console->id, $mulai, $selesai, $cfg['buffer_jam'])) {
                $alasan = 'Terisi';
            }

            $hasil[] = ['jam' => $jam, 'tersedia' => $alasan === null, 'alasan' => $alasan];
        }

        return $hasil;
    }

    /**
     * Bentrok bila: mulaiBaru < selesaiLama + buffer  DAN  selesaiBaru + buffer > mulaiLama
     */
    public function adaBentrok(int $consoleId, Carbon $mulai, Carbon $selesai, int $buffer): bool
    {
        return $this->bookingAktif()
            ->where('console_id', $consoleId)
            ->where('waktu_mulai', '<', $selesai->copy()->addHours($buffer))
            ->where('waktu_selesai', '>', $mulai->copy()->subHours($buffer))
            ->exists();
    }

    /** Booking yang masih memegang jadwal (menunggu yang belum kedaluwarsa, dibayar, disewa). */
    private function bookingAktif(): Builder
    {
        return Booking::query()->where(function ($q) {
            $q->whereIn('status_booking', ['Dibayar', 'Sedang Disewa'])
                ->orWhere(function ($m) {
                    $m->where('status_booking', 'Menunggu Pembayaran')
                        ->where('batas_bayar', '>', now());
                });
        });
    }

    private function tanggalDiblokir(Carbon $mulai): bool
    {
        if (! Schema::hasTable('blocked_dates')) {
            return false;
        }

        foreach (['tanggal', 'tanggal_blokir', 'tgl'] as $kolom) {
            if (Schema::hasColumn('blocked_dates', $kolom)) {
                return DB::table('blocked_dates')->whereDate($kolom, $mulai->toDateString())->exists();
            }
        }

        return false; // kolom belum dikenali: pengecekan dilewati
    }

    private function kodeBooking(int $panjang): string
    {
        $maks = strlen(self::ALFABET) - 1;

        do {
            $acak = '';
            for ($i = 0; $i < $panjang; $i++) {
                $acak .= self::ALFABET[random_int(0, $maks)];
            }
            $kode = 'PS5-'.$acak;
        } while (Booking::where('kode_booking', $kode)->exists());

        return $kode;
    }
}