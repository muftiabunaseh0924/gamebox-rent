<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PackageController extends Controller
{
    private const PESAN = [
        'required' => ':attribute wajib diisi.',
        'integer' => ':attribute harus berupa angka bulat.',
        'between' => ':attribute di luar batas yang diizinkan.',
        'in' => ':attribute tidak valid.',
        'durasi_jam.unique' => 'Paket dengan jenis dan durasi tersebut sudah ada.',
    ];

    private const ATRIBUT = [
        'jenis' => 'Jenis',
        'durasi_jam' => 'Durasi (jam, 1 sampai 48)',
        'harga' => 'Harga (Rp 1.000 sampai Rp 10.000.000)',
    ];

    public function index(): View
    {
        $paket = Package::orderBy('jenis')->orderBy('durasi_jam')->get()->groupBy('jenis');

        return view('admin.harga.index', [
            'paket' => $paket,
            'aktifPerJenis' => $paket->map(fn ($daftar) => $daftar->where('aktif', true)->count()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jenis' => ['required', 'in:PS4,PS5'],
            'durasi_jam' => ['required', 'integer', 'between:1,48',
                Rule::unique('packages', 'durasi_jam')->where('jenis', $request->input('jenis'))],
            'harga' => ['required', 'integer', 'between:1000,10000000'],
        ], self::PESAN, self::ATRIBUT);

        $paket = Package::create([
            'jenis' => $data['jenis'],
            'durasi_jam' => $data['durasi_jam'],
            'harga' => $data['harga'],
            'aktif' => true,
        ]);

        ActivityLogger::catat($request->user(), 'paket_tambah',
            "Paket {$paket->jenis} {$paket->durasi_jam} jam ditambahkan: {$paket->hargaRupiah()}");

        return redirect()->route('admin.harga')->with('status', "Paket {$paket->jenis} {$paket->durasi_jam} jam ditambahkan.");
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $data = $request->validate([
            'harga' => ['required', 'integer', 'between:1000,10000000'],
            'aktif' => ['nullable', 'boolean'],
        ], self::PESAN, self::ATRIBUT);

        $aktif = $request->boolean('aktif');

        if ($package->aktif && ! $aktif && $this->aktifLain($package) === 0) {
            return back()->withErrors(['aktif' => "Paket {$package->jenis} {$package->durasi_jam} jam adalah satu-satunya paket aktif untuk {$package->jenis}. Tambahkan paket lain dulu sebelum menonaktifkannya."]);
        }

        $hargaLama = $package->harga;
        $aktifLama = $package->aktif;

        $package->update(['harga' => (int) $data['harga'], 'aktif' => $aktif]);

        $ubah = [];
        if ($hargaLama !== $package->harga) {
            $ubah[] = 'harga Rp '.number_format($hargaLama, 0, ',', '.').' menjadi '.$package->hargaRupiah();
        }
        if ($aktifLama !== $package->aktif) {
            $ubah[] = $package->aktif ? 'diaktifkan' : 'dinonaktifkan';
        }

        if ($ubah === []) {
            return back()->with('status', 'Tidak ada perubahan.');
        }

        ActivityLogger::catat($request->user(), 'paket_ubah',
            "Paket {$package->jenis} {$package->durasi_jam} jam: ".implode(', ', $ubah));

        return redirect()->route('admin.harga')->with('status', "Paket {$package->jenis} {$package->durasi_jam} jam diperbarui.");
    }

    public function destroy(Request $request, Package $package): RedirectResponse
    {
        if ($package->aktif && $this->aktifLain($package) === 0) {
            return back()->withErrors(['aktif' => "Paket {$package->jenis} {$package->durasi_jam} jam adalah satu-satunya paket aktif untuk {$package->jenis} dan tidak dapat dihapus."]);
        }

        $nama = "{$package->jenis} {$package->durasi_jam} jam";
        $package->delete(); // pesanan lama tidak terpengaruh: harga sudah disalin ke setiap booking

        ActivityLogger::catat($request->user(), 'paket_hapus', "Paket {$nama} dihapus");

        return redirect()->route('admin.harga')->with('status', "Paket {$nama} dihapus.");
    }

    private function aktifLain(Package $package): int
    {
        return Package::aktif()->where('jenis', $package->jenis)->where('id', '<>', $package->id)->count();
    }
}