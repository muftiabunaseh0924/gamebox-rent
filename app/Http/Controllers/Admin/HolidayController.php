<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Console;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(): View
    {
        $dasar = fn () => DB::table('blocked_dates as d')
            ->leftJoin('consoles as c', 'c.id', '=', 'd.console_id')
            ->select('d.id', 'd.tanggal', 'd.alasan', 'd.console_id', 'c.kode_unit');

        return view('admin.libur.index', [
            'mendatang' => $dasar()->where('d.tanggal', '>=', today()->toDateString())->orderBy('d.tanggal')->get(),
            'lampau' => $dasar()->where('d.tanggal', '<', today()->toDateString())->orderByDesc('d.tanggal')->limit(10)->get(),
            'unit' => Console::urut()->get(['id', 'kode_unit', 'nama_tampil']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'console_id' => ['nullable', 'integer', 'exists:consoles,id'],
            'alasan' => ['nullable', 'string', 'max:200'],
        ], [
            'required' => ':attribute wajib diisi.',
            'date_format' => 'Format :attribute tidak valid.',
            'after_or_equal' => ':attribute tidak boleh sebelum hari ini.',
            'exists' => 'Unit tidak ditemukan.',
            'max' => ':attribute terlalu panjang (maksimal :max karakter).',
        ], [
            'tanggal' => 'Tanggal',
            'console_id' => 'Unit',
            'alasan' => 'Alasan',
        ]);

        $consoleId = $data['console_id'] ?? null;

        $sudahAda = DB::table('blocked_dates')
            ->where('tanggal', $data['tanggal'])
            ->when($consoleId === null, fn ($q) => $q->whereNull('console_id'), fn ($q) => $q->where('console_id', $consoleId))
            ->exists();

        if ($sudahAda) {
            return back()->withInput()->withErrors(['tanggal' => 'Tanggal libur untuk pilihan unit tersebut sudah ada.']);
        }

        DB::table('blocked_dates')->insert([
            'console_id' => $consoleId,
            'tanggal' => $data['tanggal'],
            'alasan' => $data['alasan'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $namaUnit = $consoleId ? Console::find($consoleId)?->kode_unit : 'semua unit';
        $tanggal = Carbon::parse($data['tanggal']);

        ActivityLogger::catat($request->user(), 'libur_tambah',
            "Tanggal libur {$tanggal->format('d-m-Y')} ({$namaUnit})".(filled($data['alasan'] ?? null) ? ": {$data['alasan']}" : ''));

        // Tanggal libur tidak membatalkan pesanan yang sudah ada: beri peringatan bila ada yang terdampak.
        $terdampak = Booking::whereDate('waktu_mulai', $tanggal->toDateString())
            ->when($consoleId !== null, fn ($q) => $q->where('console_id', $consoleId))
            ->where(function ($q) {
                $q->whereIn('status_booking', ['Dibayar', 'Sedang Disewa'])
                    ->orWhere(fn ($m) => $m->where('status_booking', 'Menunggu Pembayaran')->where('batas_bayar', '>', now()));
            })
            ->count();

        $pesan = "Tanggal libur {$tanggal->format('d-m-Y')} ({$namaUnit}) ditambahkan.";
        if ($terdampak > 0) {
            $pesan .= " Perhatian: sudah ada {$terdampak} pesanan aktif pada tanggal itu. Tanggal libur hanya menutup booking baru dan tidak membatalkan pesanan yang ada.";
        }

        return redirect()->route('admin.libur')->with('status', $pesan);
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $baris = DB::table('blocked_dates')->where('id', $id)->first();
        if (! $baris) {
            return back()->withErrors(['tanggal' => 'Tanggal libur tidak ditemukan.']);
        }

        DB::table('blocked_dates')->where('id', $id)->delete();

        ActivityLogger::catat($request->user(), 'libur_hapus',
            'Tanggal libur '.Carbon::parse($baris->tanggal)->format('d-m-Y').' dihapus');

        return redirect()->route('admin.libur')->with('status', 'Tanggal libur dihapus. Booking pada tanggal itu dibuka kembali.');
    }
}