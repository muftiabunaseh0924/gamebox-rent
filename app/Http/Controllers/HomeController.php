<?php

namespace App\Http\Controllers;

use App\Models\Console;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $konten = DB::table('contents')->pluck('value', 'key');

        $unit = Console::urut()->get();

        $paket = Package::aktif()
            ->orderBy('jenis')
            ->orderBy('durasi_jam')
            ->get()
            ->groupBy('jenis');

        // Paket termurah per jenis, untuk "Mulai dari".
        $mulai = $paket->map(fn ($daftar) => $daftar->sortBy('harga')->first());

        return view('home', [
            'judul' => $konten->get('beranda_judul', 'Sewa PS4 & PS5, Main Sepuasnya'),
            'deskripsi' => $konten->get('beranda_deskripsi', ''),
            'badge' => $konten->get('badge_hero', 'Rental PlayStation'),
            'poin' => $this->pasangan($konten->get('hero_poin')),
            'langkah' => $this->pasangan($konten->get('cara_menyewa')),
            'operasional' => $this->pasangan($konten->get('operasional'))[0] ?? null,
            'kelengkapan' => array_values(array_filter(array_map(
                'trim',
                explode(',', (string) $konten->get('kelengkapan_standar'))
            ))),
            'unit' => $unit,
            'paket' => $paket,
            'mulai' => $mulai,
            'jumlahTersedia' => $unit->where('status', 'Tersedia')->count(),
        ]);
    }

    /**
     * "Judul::Isi|Judul::Isi" menjadi [['judul' => ..., 'isi' => ...], ...]
     */
    private function pasangan(?string $teks): array
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
}