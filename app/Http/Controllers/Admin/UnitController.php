<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Console;
use App\Services\ActivityLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UnitController extends Controller
{
    private const STATUS_FORM = ['Tersedia', 'Perawatan', 'Nonaktif'];

    public function index(): View
    {
        return view('admin.perangkat.index', [
            'unit' => Console::withCount('bookings')->urut()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.perangkat.form', [
            'unit' => new Console(['status' => 'Tersedia', 'jenis' => 'PS5']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $this->validasi($request, null);

        $unit = new Console();
        $unit->fill($this->atribut($v, null));
        $unit->lokasi_saat_ini = 'Di tempat sewa';

        if ($berkas = $request->file('foto')) {
            $unit->foto = $berkas->store('unit', 'public');
        }
        $unit->save();

        ActivityLogger::catat($request->user(), 'unit_tambah', "Unit {$unit->kode_unit} ditambahkan ({$unit->jenis}, {$unit->nama_tampil})");

        return redirect()->route('admin.perangkat')->with('status', "Unit {$unit->kode_unit} ditambahkan.");
    }

    public function edit(Console $console): View
    {
        return view('admin.perangkat.form', ['unit' => $console]);
    }

    public function update(Request $request, Console $console): RedirectResponse
    {
        $v = $this->validasi($request, $console);

        $console->fill($this->atribut($v, $console));

        if ($request->boolean('hapus_foto') && $console->foto) {
            $this->hapusBerkas($console->foto);
            $console->foto = null;
        }
        if ($berkas = $request->file('foto')) {
            $this->hapusBerkas($console->foto);
            $console->foto = $berkas->store('unit', 'public');
        }

        $perubahan = array_keys($console->getDirty());
        $console->save();

        ActivityLogger::catat($request->user(), 'unit_ubah',
            "Unit {$console->kode_unit} diubah (".($perubahan ? implode(', ', $perubahan) : 'tanpa perubahan data').')');

        return redirect()->route('admin.perangkat')->with('status', "Unit {$console->kode_unit} diperbarui.");
    }

    public function destroy(Request $request, Console $console): RedirectResponse
    {
        if ($console->status === 'Disewa') {
            return back()->withErrors(['unit' => "Unit {$console->kode_unit} sedang disewa dan tidak dapat dihapus."]);
        }
        if ($console->bookings()->exists()) {
            return back()->withErrors(['unit' => "Unit {$console->kode_unit} pernah dipakai pada pesanan, jadi tidak bisa dihapus. Ubah statusnya menjadi Nonaktif."]);
        }

        $kode = $console->kode_unit;

        try {
            DB::transaction(function () use ($console) {
                DB::table('blocked_dates')->where('console_id', $console->id)->delete();
                $this->hapusBerkas($console->foto);
                $console->delete();
            });
        } catch (QueryException $e) {
            report($e);

            return back()->withErrors(['unit' => "Unit {$kode} masih dipakai data lain dan tidak dapat dihapus. Nonaktifkan saja."]);
        }

        ActivityLogger::catat($request->user(), 'unit_hapus', "Unit {$kode} dihapus");

        return redirect()->route('admin.perangkat')->with('status', "Unit {$kode} dihapus.");
    }

    // ------------------------------------------------------------------

    /** @return array<string, mixed> data tervalidasi */
    private function validasi(Request $request, ?Console $unit): array
    {
        $request->merge(['kode_unit' => strtoupper(trim((string) $request->input('kode_unit')))]);

        $terkunci = $unit && $unit->status === 'Disewa'; // jenis dan status tidak bisa diubah saat disewa

        $aturan = [
            'kode_unit' => ['required', 'regex:/^[A-Z0-9][A-Z0-9-]{1,18}[A-Z0-9]$/', Rule::unique('consoles', 'kode_unit')->ignore($unit?->id)],
            'nama_tampil' => ['required', 'string', 'max:100'],
            'tag' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'game' => ['nullable', 'string', 'max:1000'],
            'foto' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_foto' => ['nullable', 'boolean'],
        ];
        if (! $terkunci) {
            $aturan['jenis'] = ['required', 'in:PS4,PS5'];
            $aturan['status'] = ['required', Rule::in(self::STATUS_FORM)];
        }

        $v = $request->validate($aturan, [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang (maksimal :max karakter).',
            'unique' => ':attribute sudah dipakai unit lain.',
            'in' => ':attribute tidak valid.',
            'kode_unit.regex' => 'Kode unit hanya boleh huruf besar, angka, dan tanda hubung (3 sampai 20 karakter), contoh PS5-04.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
            'foto.image' => 'Berkas harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'foto.uploaded' => 'Foto gagal diunggah. Ukuran maksimal 2 MB.',
        ], [
            'kode_unit' => 'Kode unit',
            'nama_tampil' => 'Nama tampil',
            'tag' => 'Tag',
            'deskripsi' => 'Deskripsi',
            'game' => 'Daftar game',
            'foto' => 'Foto',
            'jenis' => 'Jenis',
            'status' => 'Status',
        ]);

        if ($unit && ! $terkunci) {
            if ($v['jenis'] !== $unit->jenis && $unit->bookings()->exists()) {
                throw ValidationException::withMessages(['jenis' => 'Jenis tidak dapat diubah karena unit ini sudah punya pesanan.']);
            }

            $keluarDariTersedia = $unit->status === 'Tersedia' && in_array($v['status'], ['Perawatan', 'Nonaktif'], true);
            if ($keluarDariTersedia) {
                $aktif = Booking::where('console_id', $unit->id)
                    ->where('waktu_selesai', '>', now())
                    ->where(function ($q) {
                        $q->where('status_booking', 'Dibayar')
                            ->orWhere(fn ($m) => $m->where('status_booking', 'Menunggu Pembayaran')->where('batas_bayar', '>', now()));
                    })
                    ->count();

                if ($aktif > 0) {
                    throw ValidationException::withMessages([
                        'status' => "Masih ada {$aktif} pesanan aktif atau mendatang untuk unit ini. Batalkan atau selesaikan dulu sebelum mengubah status menjadi {$v['status']}.",
                    ]);
                }
            }
        }

        return $v;
    }

    private function atribut(array $v, ?Console $unit): array
    {
        $atribut = [
            'kode_unit' => $v['kode_unit'],
            'nama_tampil' => trim($v['nama_tampil']),
            'tag' => $v['tag'] ?? null,
            'deskripsi' => $v['deskripsi'] ?? null,
            'game' => $v['game'] ?? null,
        ];

        if (isset($v['jenis'])) {
            $atribut['jenis'] = $v['jenis'];
        }
        if (isset($v['status'])) {
            $atribut['status'] = $v['status'];
        }

        return $atribut;
    }

    /** Hanya berkas yang diunggah lewat panel ini (folder unit/) yang dihapus, bukan alamat luar. */
    private function hapusBerkas(?string $jalur): void
    {
        if ($jalur && str_starts_with($jalur, 'unit/')) {
            Storage::disk('public')->delete($jalur);
        }
    }
}