<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(SettingsService $layanan): View
    {
        return view('admin.pengaturan.index', [
            'kelompok' => SettingsService::kelompok(),
            'nilai' => $layanan->nilai(),
            'tema' => DB::table('settings')->whereIn('key', SettingsService::TEMA)->pluck('value', 'key'),
        ]);
    }

    public function update(Request $request, SettingsService $layanan): RedirectResponse
    {
        [$bersih, $galat] = $layanan->validasi((array) $request->input('s', []));

        if ($galat !== []) {
            throw ValidationException::withMessages(
                collect($galat)->mapWithKeys(fn ($pesan, $kunci) => ['s.'.$kunci => $pesan])->all()
            );
        }

        $diubah = $layanan->simpan($bersih, $request->user());

        $pesan = $diubah === []
            ? 'Tidak ada perubahan.'
            : count($diubah).' pengaturan diperbarui. Pengaturan operasional dan harga berlaku untuk booking baru; pesanan yang sudah dibuat tidak berubah.';

        return redirect()->route('admin.pengaturan')->with('status', $pesan);
    }
}