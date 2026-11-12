<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\HandoverService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HandoverController extends Controller
{
    public function index(): View
    {
        return view('admin.konfirmasi');
    }

    public function cek(Request $request, HandoverService $layanan): View|RedirectResponse
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:20'],
        ], [
            'required' => 'Kode konfirmasi wajib diisi.',
            'max' => 'Kode terlalu panjang.',
        ]);

        $booking = $layanan->cariByKode($data['kode']);
        if (! $booking) {
            return back()->withInput()->withErrors(['kode' => 'Kode tidak ditemukan. Periksa kembali kode dari pelanggan.']);
        }

        return view('admin.konfirmasi', [
            'booking' => $booking,
            'hasil' => $layanan->evaluasi($booking),
            'kode' => strtoupper(trim($data['kode'])),
        ]);
    }

    public function serahkan(Request $request, HandoverService $layanan): RedirectResponse
    {
        $data = $request->validate([
            'kode_booking' => ['required', 'string', 'max:30'],
            'kode_konfirmasi' => ['required', 'string', 'max:20'],
            'abaikan_jendela' => ['nullable', 'boolean'],
        ]);

        // Dicocokkan dengan DUA nilai, supaya tidak bisa menyerahkan hanya dengan kode booking.
        $booking = Booking::where('kode_booking', $data['kode_booking'])
            ->where('kode_konfirmasi', strtoupper(trim($data['kode_konfirmasi'])))
            ->first();

        if (! $booking) {
            throw ValidationException::withMessages(['kode' => 'Data serah terima tidak valid. Verifikasi kode lagi.']);
        }

        try {
            $hasil = $layanan->serahkan($booking, $request->user(), $request->boolean('abaikan_jendela'));
        } catch (BookingException $e) {
            return redirect()->route('admin.konfirmasi')->withErrors(['kode' => $e->getMessage()]);
        }

        return redirect()->route('admin.konfirmasi')->with(
            'status',
            "Unit {$hasil->console->kode_unit} diserahkan ke {$hasil->user->name}. Pesanan {$hasil->kode_booking} kini Sedang Disewa."
        );
    }
}