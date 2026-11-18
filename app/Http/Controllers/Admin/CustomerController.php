<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    // Status pesanan yang dihitung sebagai belanja nyata (sudah dibayar).
    private const BERHASIL = ['Dibayar', 'Sedang Disewa', 'Selesai'];

    public function index(Request $request): View
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:60'],
            'status' => ['nullable', 'in:Aktif,Nonaktif'],
        ], [
            'in' => 'Pilihan tidak valid.',
            'max' => 'Kata kunci terlalu panjang.',
        ]);

        $query = User::where('role', 'user')
            ->withCount('bookings')
            ->withSum(
                ['bookings as total_belanja' => fn ($b) => $b->whereIn('status_booking', self::BERHASIL)],
                'total_harga'
            )
            ->latest('id');

        $status = $f['status'] ?? null;
        if ($status === 'Aktif') {
            $query->where('status', 'Aktif');
        } elseif ($status === 'Nonaktif') {
            $query->where('status', '<>', 'Aktif');
        }

        if (filled($f['q'] ?? null)) {
            $kata = addcslashes(trim($f['q']), '%_\\');
            $query->where(function ($w) use ($kata) {
                $w->where('name', 'like', "%{$kata}%")
                    ->orWhere('email', 'like', "%{$kata}%")
                    ->orWhere('no_hp', 'like', "%{$kata}%");
            });
        }

        $semua = User::where('role', 'user')->count();
        $aktif = User::where('role', 'user')->where('status', 'Aktif')->count();

        return view('admin.pelanggan.index', [
            'pelanggan' => $query->paginate(15)->withQueryString(),
            'jumlah' => ['' => $semua, 'Aktif' => $aktif, 'Nonaktif' => $semua - $aktif],
            'filter' => $f,
        ]);
    }

    public function show(User $user): View
    {
        // Hanya akun pelanggan; akun admin tidak ditampilkan di sini.
        abort_unless($user->role === 'user', 404);

        $berhasil = $user->bookings()->whereIn('status_booking', self::BERHASIL);

        return view('admin.pelanggan.show', [
            'pelanggan' => $user,
            'ringkasan' => [
                'pesanan' => $user->bookings()->count(),
                'berhasil' => (clone $berhasil)->count(),
                'belanja' => (int) (clone $berhasil)->sum('total_harga'),
                'terakhir' => $user->bookings()->max('created_at'),
            ],
            'pesanan' => $user->bookings()->with('console')->latest('id')->paginate(10),
        ]);
    }

    public function ubahStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'user', 404);

        $data = $request->validate([
            'status' => ['required', 'in:Aktif,Nonaktif'],
        ], [
            'required' => 'Status baru wajib dipilih.',
            'in' => 'Status tidak valid.',
        ]);

        $baru = $data['status'];
        $sekarang = $user->isActive() ? 'Aktif' : 'Nonaktif';

        if ($baru === $sekarang) {
            return back()->withErrors(['status' => "Akun {$user->name} sudah berstatus {$baru}."]);
        }

        DB::transaction(function () use ($request, $user, $baru) {
            // status sengaja tidak ada di $fillable, jadi diisi lewat forceFill().
            $user->forceFill(['status' => $baru])->save();

            ActivityLogger::catat(
                $request->user(),
                $baru === 'Aktif' ? 'Aktifkan Pelanggan' : 'Nonaktifkan Pelanggan',
                "Akun pelanggan {$user->name} ({$user->email}) diubah menjadi {$baru}."
            );

            // Putuskan sesi yang masih berjalan agar langsung keluar.
            if ($baru === 'Nonaktif' && config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }
        });

        return redirect()
            ->route('admin.pelanggan.show', $user)
            ->with('status', $baru === 'Aktif'
                ? "Akun {$user->name} diaktifkan kembali."
                : "Akun {$user->name} dinonaktifkan dan tidak bisa masuk lagi.");
    }
}