<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingsService
{
    /** Kunci tema dari desain Stitch: hanya ditampilkan, tidak diubah lewat panel. */
    public const TEMA = ['warna_latar', 'warna_kartu', 'warna_utama', 'warna_aksen', 'warna_aksen2', 'font_judul', 'font_isi'];

    private static ?array $memoSitus = null;

    /** @return array<int, array{judul: string, ikon: string, kolom: array<string, array<string, mixed>>}> */
    public static function kelompok(): array
    {
        return [
            [
                'judul' => 'Operasional Booking',
                'ikon' => 'schedule',
                'kolom' => [
                    'buffer_jam' => ['label' => 'Jeda antar penyewa', 'tipe' => 'int', 'min' => 0, 'max' => 12, 'satuan' => 'jam', 'default' => '1',
                        'bantuan' => 'Waktu pembersihan unit di antara dua penyewa. Dipakai saat mengecek jadwal bentrok.'],
                    'batas_bayar_menit' => ['label' => 'Batas waktu pembayaran', 'tipe' => 'int', 'min' => 5, 'max' => 1440, 'satuan' => 'menit', 'default' => '15',
                        'bantuan' => 'Booking yang belum dibayar sampai batas ini dilepas jadwalnya.'],
                    'toleransi_ambil_menit' => ['label' => 'Toleransi pengambilan', 'tipe' => 'int', 'min' => 0, 'max' => 240, 'satuan' => 'menit', 'default' => '30',
                        'bantuan' => 'Serah terima masih diterima sampai sekian menit setelah jadwal mulai.'],
                    'maks_hari_ke_depan' => ['label' => 'Booking maksimal ke depan', 'tipe' => 'int', 'min' => 1, 'max' => 60, 'satuan' => 'hari', 'default' => '7',
                        'bantuan' => 'Seberapa jauh pelanggan boleh memesan dari hari ini.'],
                    'maks_booking_menunggu' => ['label' => 'Maksimal booking menunggu bayar', 'tipe' => 'int', 'min' => 1, 'max' => 10, 'satuan' => 'booking', 'default' => '2',
                        'bantuan' => 'Per akun, untuk mencegah satu orang menahan banyak jadwal.'],
                    'jam_mulai_slot' => ['label' => 'Slot jam mulai', 'tipe' => 'slot', 'satuan' => '', 'default' => '0,2,4,6,8,10,12,14,16,18,20,22', 'lebar' => true,
                        'bantuan' => 'Jam yang boleh dipilih sebagai jam mulai (0 sampai 23), dipisah koma. Contoh: 8,10,12,14,16,18,20.'],
                ],
            ],
            [
                'judul' => 'Harga dan Layanan',
                'ikon' => 'payments',
                'kolom' => [
                    'harga_stik' => ['label' => 'Harga stik tambahan', 'tipe' => 'int', 'min' => 0, 'max' => 1000000, 'satuan' => 'Rp per stik', 'default' => '10000',
                        'bantuan' => 'Berlaku untuk booking baru. Pesanan lama menyimpan harganya sendiri.'],
                    'stok_stik' => ['label' => 'Stok stik tambahan', 'tipe' => 'int', 'min' => 0, 'max' => 50, 'satuan' => 'stik', 'default' => '6',
                        'bantuan' => 'Jumlah stik yang bisa disewa bersamaan pada jadwal yang tumpang tindih.'],
                    'denda_keterlambatan' => ['label' => 'Denda keterlambatan', 'tipe' => 'int', 'min' => 0, 'max' => 10000000, 'satuan' => 'Rp per jam', 'default' => '20000',
                        'bantuan' => 'Dihitung per jam keterlambatan (dibulatkan ke atas). Disalin ke setiap booking saat dibuat.'],
                    'biaya_pengiriman' => ['label' => 'Biaya pengiriman', 'tipe' => 'int', 'min' => 0, 'max' => 1000000, 'satuan' => 'Rp per sewa', 'default' => '20000',
                        'bantuan' => 'Dikenakan pada pesanan berstatus Diantar.'],
                    'pengiriman_aktif' => ['label' => 'Layanan pengantaran', 'tipe' => 'bool', 'satuan' => '', 'default' => '1',
                        'bantuan' => 'Bila Tidak aktif, pelanggan tidak bisa memilih Diantar.'],
                ],
            ],
            [
                'judul' => 'Identitas Situs',
                'ikon' => 'storefront',
                'kolom' => [
                    'nama_situs' => ['label' => 'Nama situs', 'tipe' => 'teks', 'min' => 2, 'max' => 60, 'satuan' => '', 'default' => 'GameBox Rent',
                        'bantuan' => 'Tampil di navbar, footer, judul tab, email, dan invoice.'],
                    'tagline' => ['label' => 'Tagline', 'tipe' => 'teks', 'min' => 0, 'max' => 100, 'satuan' => '', 'default' => '',
                        'bantuan' => 'Kalimat singkat di bawah nama situs (navbar menampilkan 30 karakter pertama).'],
                    'kontak' => ['label' => 'Kontak', 'tipe' => 'teks', 'min' => 0, 'max' => 60, 'satuan' => '', 'default' => '',
                        'bantuan' => 'Nomor telepon atau WhatsApp yang tampil di footer.'],
                    'alamat' => ['label' => 'Alamat tempat sewa', 'tipe' => 'teks', 'min' => 0, 'max' => 200, 'satuan' => '', 'default' => '', 'lebar' => true,
                        'bantuan' => 'Tampil di footer. Kosongkan bila belum ada.'],
                    'logo_url' => ['label' => 'Alamat gambar logo', 'tipe' => 'url', 'satuan' => '', 'default' => '', 'lebar' => true,
                        'bantuan' => 'Alamat lengkap gambar (https://...). Kosongkan untuk memakai ikon bawaan. Bila gambar gagal dimuat, ikon bawaan dipakai.'],
                    'prefix_invoice' => ['label' => 'Awalan nomor invoice', 'tipe' => 'awalan', 'satuan' => '', 'default' => 'INV',
                        'bantuan' => '2 sampai 6 huruf atau angka. Perhatian: nomor invoice dibentuk saat diunduh, jadi mengubah awalan juga mengubah nomor invoice pesanan lama.'],
                    'panjang_kode' => ['label' => 'Panjang kode booking dan konfirmasi', 'tipe' => 'int', 'min' => 4, 'max' => 10, 'satuan' => 'karakter', 'default' => '6',
                        'bantuan' => 'Berlaku untuk kode yang dibuat setelah ini.'],
                ],
            ],
        ];
    }

    /** @return array<string, string> semua nilai pengaturan yang bisa diubah, lengkap dengan nilai bawaan */
    public function nilai(): array
    {
        $db = DB::table('settings')->pluck('value', 'key');
        $hasil = [];

        foreach (self::kelompok() as $grup) {
            foreach ($grup['kolom'] as $kunci => $d) {
                $hasil[$kunci] = (string) ($db[$kunci] ?? $d['default']);
            }
        }

        return $hasil;
    }

    /** @return array{0: array<string, string>, 1: array<string, string>} [nilai bersih, galat per kunci] */
    public function validasi(array $input): array
    {
        $bersih = [];
        $galat = [];

        foreach (self::kelompok() as $grup) {
            foreach ($grup['kolom'] as $kunci => $d) {
                $mentah = $input[$kunci] ?? '';
                $teks = is_string($mentah) ? trim($mentah) : '';

                [$nilai, $pesan] = $this->periksa($d['tipe'], $d, $teks);

                if ($pesan !== null) {
                    $galat[$kunci] = $pesan;
                } else {
                    $bersih[$kunci] = (string) $nilai;
                }
            }
        }

        return [$bersih, $galat];
    }

    /** @return array<string, array{0: ?string, 1: string}> kunci => [nilai lama, nilai baru] */
    public function simpan(array $bersih, User $admin): array
    {
        $diubah = $this->terapkan('settings', $bersih);
        self::$memoSitus = null;

        if ($diubah !== []) {
            $ringkas = collect($diubah)->map(fn ($p, $k) => $k.': '.Str::limit($p[0] ?? '(kosong)', 40).' -> '.Str::limit($p[1], 40))->implode('; ');
            ActivityLogger::catat($admin, 'pengaturan_ubah', 'Pengaturan diubah: '.$ringkas);
        }

        return $diubah;
    }

    /** @return array<string, string> semua isi tabel contents */
    public function konten(): array
    {
        return DB::table('contents')->pluck('value', 'key')->map(fn ($v) => (string) $v)->all();
    }

    public function simpanKonten(array $nilai, User $admin): array
    {
        $diubah = $this->terapkan('contents', $nilai);

        if ($diubah !== []) {
            ActivityLogger::catat($admin, 'konten_ubah', 'Konten beranda diubah: '.implode(', ', array_keys($diubah)));
        }

        return $diubah;
    }

    /** Identitas situs untuk tampilan. Aman dipanggil berulang (di-cache selama satu permintaan). */
    public function situs(): array
    {
        if (self::$memoSitus !== null) {
            return self::$memoSitus;
        }

        $baku = ['nama_situs' => 'GameBox Rent', 'tagline' => '', 'kontak' => '', 'alamat' => '', 'logo_url' => ''];

        try {
            $db = DB::table('settings')->whereIn('key', array_keys($baku))->pluck('value', 'key');
        } catch (\Throwable $e) {
            return $baku; // mis. database belum siap: jangan merusak halaman
        }

        $hasil = [];
        foreach ($baku as $kunci => $bawaan) {
            $nilai = trim((string) ($db[$kunci] ?? ''));
            $hasil[$kunci] = $nilai !== '' ? $nilai : $bawaan;
        }

        return self::$memoSitus = $hasil;
    }

    /** "Judul::Isi|Judul::Isi" menjadi [['judul' => ..., 'isi' => ...], ...] */
    public static function pasangan(?string $teks): array
    {
        if (blank($teks)) {
            return [];
        }

        return collect(explode('|', $teks))
            ->map(function ($bagian) {
                [$judul, $isi] = array_pad(explode('::', $bagian, 2), 2, '');

                return ['judul' => trim($judul), 'isi' => trim($isi)];
            })
            ->filter(fn ($p) => $p['judul'] !== '')
            ->values()
            ->all();
    }

    public static function bangunPasangan(array $baris): string
    {
        return implode('|', array_map(fn ($b) => $b['judul'].'::'.$b['isi'], $baris));
    }

    // ------------------------------------------------------------------

    /** @return array{0: ?string, 1: ?string} [nilai, pesan galat] */
    private function periksa(string $tipe, array $d, string $teks): array
    {
        $label = $d['label'];

        switch ($tipe) {
            case 'int':
                if (! preg_match('/^\d{1,9}$/', $teks) || (int) $teks < $d['min'] || (int) $teks > $d['max']) {
                    return [null, "{$label} harus berupa angka bulat {$d['min']} sampai {$d['max']}."];
                }

                return [(string) (int) $teks, null];

            case 'bool':
                return in_array($teks, ['0', '1'], true) ? [$teks, null] : [null, "{$label} tidak valid."];

            case 'teks':
                if (mb_strlen($teks) < $d['min']) {
                    return [null, $d['min'] === 1 ? "{$label} wajib diisi." : "{$label} minimal {$d['min']} karakter."];
                }
                if (mb_strlen($teks) > $d['max']) {
                    return [null, "{$label} maksimal {$d['max']} karakter."];
                }

                return [$teks, null];

            case 'url':
                if ($teks === '') {
                    return ['', null];
                }
                $skema = strtolower((string) parse_url($teks, PHP_URL_SCHEME));
                if (mb_strlen($teks) > 255 || ! in_array($skema, ['http', 'https'], true) || filter_var($teks, FILTER_VALIDATE_URL) === false) {
                    return [null, "{$label} harus berupa alamat lengkap yang diawali http:// atau https:// (maksimal 255 karakter)."];
                }

                return [$teks, null];

            case 'slot':
                $jam = [];
                foreach (explode(',', $teks) as $bagian) {
                    $bagian = trim($bagian);
                    if ($bagian === '') {
                        continue;
                    }
                    if (! ctype_digit($bagian) || (int) $bagian > 23) {
                        return [null, "{$label}: \"{$bagian}\" bukan jam yang valid (0 sampai 23)."];
                    }
                    $jam[(int) $bagian] = (int) $bagian;
                }
                if ($jam === []) {
                    return [null, "{$label} minimal berisi satu jam."];
                }
                ksort($jam);

                return [implode(',', $jam), null];

            case 'awalan':
                $teks = strtoupper($teks);

                return preg_match('/^[A-Z0-9]{2,6}$/', $teks)
                    ? [$teks, null]
                    : [null, "{$label} harus 2 sampai 6 huruf atau angka tanpa spasi."];
        }

        return [null, "{$label} tidak dapat divalidasi."];
    }

    /** Menulis hanya nilai yang berubah. @return array<string, array{0: ?string, 1: string}> */
    private function terapkan(string $tabel, array $nilai): array
    {
        $lama = DB::table($tabel)->whereIn('key', array_keys($nilai))->pluck('value', 'key')->all();
        $diubah = [];

        DB::transaction(function () use ($tabel, $nilai, $lama, &$diubah) {
            foreach ($nilai as $kunci => $baru) {
                $sebelum = array_key_exists($kunci, $lama) ? (string) $lama[$kunci] : null;
                if ($sebelum === $baru) {
                    continue;
                }

                if (array_key_exists($kunci, $lama)) {
                    DB::table($tabel)->where('key', $kunci)->update(['value' => $baru, 'updated_at' => now()]);
                } else {
                    DB::table($tabel)->insert(['key' => $kunci, 'value' => $baru, 'created_at' => now(), 'updated_at' => now()]);
                }

                $diubah[$kunci] = [$sebelum, $baru];
            }
        });

        return $diubah;
    }
}