<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(SettingsService $layanan): View
    {
        $k = $layanan->konten();

        return view('admin.konten.index', [
            'd' => [
                'beranda_judul' => $k['beranda_judul'] ?? '',
                'beranda_deskripsi' => $k['beranda_deskripsi'] ?? '',
                'badge_hero' => $k['badge_hero'] ?? '',
                'hero_poin' => SettingsService::pasangan($k['hero_poin'] ?? ''),
                'cara_menyewa' => SettingsService::pasangan($k['cara_menyewa'] ?? ''),
                'operasional' => SettingsService::pasangan($k['operasional'] ?? '')[0] ?? ['judul' => '', 'isi' => ''],
                'kelengkapan_standar' => $k['kelengkapan_standar'] ?? '',
                'catatan_buffer' => $k['catatan_buffer'] ?? '',
                'catatan_pengiriman' => $k['catatan_pengiriman'] ?? '',
                'catatan_identitas' => $k['catatan_identitas'] ?? '',
                'syarat_ketentuan' => $k['syarat_ketentuan'] ?? '',
            ],
        ]);
    }

    public function update(Request $request, SettingsService $layanan): RedirectResponse
    {
        $tanpaPemisah = 'not_regex:/\||::/';

        $v = $request->validate([
            'beranda_judul' => ['required', 'string', 'max:120'],
            'beranda_deskripsi' => ['nullable', 'string', 'max:300'],
            'badge_hero' => ['nullable', 'string', 'max:60'],
            'hero_poin' => ['nullable', 'array'],
            'hero_poin.judul' => ['nullable', 'array', 'max:3'],
            'hero_poin.judul.*' => ['nullable', 'string', 'max:40', $tanpaPemisah],
            'hero_poin.isi' => ['nullable', 'array', 'max:3'],
            'hero_poin.isi.*' => ['nullable', 'string', 'max:100', $tanpaPemisah],
            'cara_menyewa' => ['nullable', 'array'],
            'cara_menyewa.judul' => ['nullable', 'array', 'max:6'],
            'cara_menyewa.judul.*' => ['nullable', 'string', 'max:40', $tanpaPemisah],
            'cara_menyewa.isi' => ['nullable', 'array', 'max:6'],
            'cara_menyewa.isi.*' => ['nullable', 'string', 'max:160', $tanpaPemisah],
            'operasional_judul' => ['nullable', 'string', 'max:40', $tanpaPemisah],
            'operasional_isi' => ['nullable', 'string', 'max:120', $tanpaPemisah],
            'kelengkapan_standar' => ['nullable', 'string', 'max:300'],
            'catatan_buffer' => ['nullable', 'string', 'max:300'],
            'catatan_pengiriman' => ['nullable', 'string', 'max:400'],
            'catatan_identitas' => ['nullable', 'string', 'max:400'],
            'syarat_ketentuan' => ['nullable', 'string', 'max:1000'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang (maksimal :max).',
            'not_regex' => ':attribute tidak boleh memuat tanda | atau ::.',
        ], [
            'beranda_judul' => 'Judul beranda',
            'beranda_deskripsi' => 'Deskripsi beranda',
            'badge_hero' => 'Badge hero',
            'hero_poin.judul' => 'Jumlah poin keunggulan',
            'hero_poin.isi' => 'Jumlah poin keunggulan',
            'hero_poin.judul.*' => 'Judul poin keunggulan',
            'hero_poin.isi.*' => 'Isi poin keunggulan',
            'cara_menyewa.judul' => 'Jumlah langkah',
            'cara_menyewa.isi' => 'Jumlah langkah',
            'cara_menyewa.judul.*' => 'Judul langkah',
            'cara_menyewa.isi.*' => 'Isi langkah',
            'operasional_judul' => 'Judul info operasional',
            'operasional_isi' => 'Isi info operasional',
            'kelengkapan_standar' => 'Kelengkapan standar',
            'catatan_buffer' => 'Catatan jeda',
            'catatan_pengiriman' => 'Catatan pengiriman',
            'catatan_identitas' => 'Catatan identitas',
            'syarat_ketentuan' => 'Syarat dan ketentuan',
        ]);

        $poin = $this->baris($v['hero_poin'] ?? [], 'hero_poin', 'poin keunggulan');
        $langkah = $this->baris($v['cara_menyewa'] ?? [], 'cara_menyewa', 'langkah');

        $opJudul = trim((string) ($v['operasional_judul'] ?? ''));
        $opIsi = trim((string) ($v['operasional_isi'] ?? ''));
        if ($opJudul === '' && $opIsi !== '') {
            throw ValidationException::withMessages(['operasional_judul' => 'Judul info operasional wajib diisi bila isinya terisi.']);
        }

        $item = array_values(array_filter(array_map('trim', explode(',', (string) ($v['kelengkapan_standar'] ?? '')))));
        if (count($item) > 8 || collect($item)->contains(fn ($x) => mb_strlen($x) > 40)) {
            throw ValidationException::withMessages(['kelengkapan_standar' => 'Kelengkapan standar maksimal 8 item, masing-masing paling banyak 40 karakter.']);
        }

        $teks = fn (string $kunci) => trim((string) ($v[$kunci] ?? ''));

        $diubah = $layanan->simpanKonten([
            'beranda_judul' => trim($v['beranda_judul']),
            'beranda_deskripsi' => $teks('beranda_deskripsi'),
            'badge_hero' => $teks('badge_hero'),
            'hero_poin' => SettingsService::bangunPasangan($poin),
            'cara_menyewa' => SettingsService::bangunPasangan($langkah),
            'operasional' => $opJudul !== '' ? $opJudul.'::'.$opIsi : '',
            'kelengkapan_standar' => implode(', ', $item),
            'catatan_buffer' => $teks('catatan_buffer'),
            'catatan_pengiriman' => $teks('catatan_pengiriman'),
            'catatan_identitas' => $teks('catatan_identitas'),
            'syarat_ketentuan' => $teks('syarat_ketentuan'),
        ], $request->user());

        return redirect()->route('admin.konten')->with('status', $diubah === []
            ? 'Tidak ada perubahan.'
            : count($diubah).' bagian konten diperbarui dan langsung tampil di beranda.');
    }

    /** @return array<int, array{judul: string, isi: string}> baris pasangan, baris kosong dibuang */
    private function baris(array $kolom, string $nama, string $sebutan): array
    {
        $judul = (array) ($kolom['judul'] ?? []);
        $isi = (array) ($kolom['isi'] ?? []);
        $hasil = [];

        foreach ($judul + $isi as $i => $_) {
            $j = trim((string) ($judul[$i] ?? ''));
            $s = trim((string) ($isi[$i] ?? ''));

            if ($j === '' && $s === '') {
                continue;
            }
            if ($j === '') {
                throw ValidationException::withMessages(["{$nama}.judul.{$i}" => "Judul {$sebutan} wajib diisi bila isinya terisi."]);
            }

            $hasil[] = ['judul' => $j, 'isi' => $s];
        }

        return $hasil;
    }
}