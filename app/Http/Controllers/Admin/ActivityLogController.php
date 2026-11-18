<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    // Halaman ini hanya baca: log tidak bisa diubah atau dihapus dari panel.
    public function index(Request $request): View
    {
        $f = $request->validate([
            'admin' => ['nullable', 'integer', 'min:1'],
            'aksi' => ['nullable', 'string', 'max:50'],
            'q' => ['nullable', 'string', 'max:60'],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
        ], [
            'date_format' => 'Format tanggal tidak valid.',
            'integer' => 'Pilihan admin tidak valid.',
            'max' => 'Isian terlalu panjang.',
        ]);

        $query = DB::table('activity_logs as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.admin_id')
            ->orderByDesc('a.id')
            ->select('a.id', 'a.aksi', 'a.detail', 'a.created_at', 'u.name as admin');

        if (filled($f['admin'] ?? null)) {
            $query->where('a.admin_id', $f['admin']);
        }
        if (filled($f['aksi'] ?? null)) {
            $query->where('a.aksi', $f['aksi']);
        }
        if (filled($f['q'] ?? null)) {
            $kata = addcslashes(trim($f['q']), '%_\\');
            $query->where('a.detail', 'like', "%{$kata}%");
        }
        if (filled($f['dari'] ?? null)) {
            $query->whereDate('a.created_at', '>=', $f['dari']);
        }
        if (filled($f['sampai'] ?? null)) {
            $query->whereDate('a.created_at', '<=', $f['sampai']);
        }

        return view('admin.log.index', [
            'log' => $query->paginate(25)->withQueryString(),
            'daftarAdmin' => User::where('role', 'admin')->orderBy('name')->get(['id', 'name']),
            'daftarAksi' => DB::table('activity_logs')->distinct()->orderBy('aksi')->pluck('aksi'),
            'filter' => $f,
        ]);
    }
}