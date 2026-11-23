<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TemplateMail;
use App\Models\Booking;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    /** jenis_email => [nama, dipakai sekarang?, keterangan] */
    private const JENIS = [
        'booking_dibuat' => ['Booking dibuat', false, 'Belum dikirim otomatis (disiapkan, belum dipakai di versi prototipe).'],
        'pembayaran_berhasil' => ['Pembayaran berhasil', true, 'Dikirim sekali setelah pembayaran sukses, dengan lampiran invoice PDF.'],
        'pengingat_mulai' => ['Pengingat mulai sewa', true, 'Dikirim otomatis oleh scheduler sekitar 1 jam sebelum sewa mulai, sekali per pesanan.'],
        'pengingat_selesai' => ['Pengingat selesai sewa', true, 'Dikirim otomatis oleh scheduler sekitar 30 menit sebelum sewa selesai, sekali per pesanan.'],
        'terlambat' => ['Keterlambatan', false, 'Belum dikirim otomatis (disiapkan, belum dipakai di versi prototipe).'],
        'dibatalkan' => ['Pesanan dibatalkan', true, 'Dikirim saat admin membatalkan pesanan.'],
        'terima_kasih' => ['Terima kasih', true, 'Dikirim saat penyewaan ditandai selesai.'],
    ];

    private const PLACEHOLDER = [
        'nama' => 'Nama pelanggan',
        'kode' => 'Kode konfirmasi serah terima',
        'kode_booking' => 'Kode booking',
        'unit' => 'Nama unit',
        'jadwal' => 'Jadwal mulai sampai selesai',
        'waktu_mulai' => 'Waktu mulai',
        'waktu_selesai' => 'Waktu selesai',
        'batas_bayar' => 'Batas waktu pembayaran',
        'total' => 'Total pembayaran',
        'denda' => 'Denda (nominal)',
        'alasan' => 'Alasan pembatalan',
    ];

    public function index(Request $request): View
    {
        return view('admin.email.index', [
            'jenis' => self::JENIS,
            'placeholder' => self::PLACEHOLDER,
            'templat' => DB::table('email_templates')->get()->keyBy('jenis_email'),
            'penerimaBawaan' => $request->user()->email,
            'mailer' => config('mail.default'),
        ]);
    }

    public function update(Request $request, string $jenis): RedirectResponse
    {
        abort_unless(isset(self::JENIS[$jenis]), 404);
        $bag = 'tpl_'.$jenis;

        $v = $request->validateWithBag($bag, [
            'subjek' => ['required', 'string', 'max:200'],
            'isi' => ['required', 'string', 'max:2000'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang (maksimal :max karakter).',
        ], ['subjek' => 'Subjek', 'isi' => 'Isi email']);

        $subjek = $this->rapikan($v['subjek']);
        $isi = $this->rapikan($v['isi']);

        foreach (['subjek' => $subjek, 'isi' => $isi] as $kolom => $teks) {
            $asing = $this->placeholderAsing($teks);
            if ($asing !== []) {
                throw ValidationException::withMessages([
                    $kolom => 'Placeholder '.implode(', ', array_map(fn ($p) => '{{'.$p.'}}', $asing)).' tidak dikenal. Yang tersedia: '
                        .implode(', ', array_map(fn ($p) => '{{'.$p.'}}', array_keys(self::PLACEHOLDER))).'.',
                ])->errorBag($bag);
            }
        }

        $ada = DB::table('email_templates')->where('jenis_email', $jenis)->exists();
        if ($ada) {
            DB::table('email_templates')->where('jenis_email', $jenis)->update(['subjek' => $subjek, 'isi' => $isi, 'updated_at' => now()]);
        } else {
            DB::table('email_templates')->insert(['jenis_email' => $jenis, 'subjek' => $subjek, 'isi' => $isi, 'created_at' => now(), 'updated_at' => now()]);
        }

        ActivityLogger::catat($request->user(), 'templat_email_ubah', 'Templat email "'.self::JENIS[$jenis][0]."\" diubah ({$jenis})");

        return redirect()->route('admin.email')->with('status', 'Templat "'.self::JENIS[$jenis][0].'" disimpan. Berlaku untuk email berikutnya.');
    }

    public function pratinjau(Request $request, string $jenis): JsonResponse
    {
        abort_unless(isset(self::JENIS[$jenis]), 404);

        [$subjek, $isi] = $this->rendah($request);

        return response()->json(['subjek' => $subjek, 'isi' => $isi]);
    }

    public function uji(Request $request, string $jenis): JsonResponse
    {
        abort_unless(isset(self::JENIS[$jenis]), 404);

        // Penerima hanya boleh email yang terdaftar di aplikasi (admin atau pelanggan).
        $request->merge(['penerima' => strtolower(trim((string) $request->input('penerima')))]);
        $request->validate([
            'penerima' => ['nullable', 'email:rfc', 'max:150', 'exists:users,email'],
        ], [
            'email' => 'Alamat penerima tidak valid.',
            'exists' => 'Penerima uji harus email yang terdaftar di aplikasi (akun admin atau pelanggan).',
            'max' => 'Alamat penerima terlalu panjang.',
        ]);

        [$subjek, $isi] = $this->rendah($request);
        $admin = $request->user();
        $penerima = $request->input('penerima') ?: $admin->email;

        try {
            Mail::to($penerima)->send(new TemplateMail(new Booking(), '[UJI] '.$subjek, $isi));
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Email uji gagal dikirim: '.$e->getMessage()], 500);
        }

        ActivityLogger::catat($admin, 'email_uji', "Email uji templat {$jenis} dikirim ke {$penerima}");

        $catatan = config('mail.default') === 'log'
            ? ' Karena MAIL_MAILER=log, email hanya tercatat di storage/logs/laravel.log dan tidak masuk ke kotak masuk.'
            : '';

        return response()->json(['pesan' => "Email uji dikirim ke {$penerima}.{$catatan}"]);
    }

    // ------------------------------------------------------------------

    /** Memvalidasi teks dari formulir lalu mengisi placeholder dengan data contoh. @return array{0: string, 1: string} */
    private function rendah(Request $request): array
    {
        $v = $request->validate([
            'subjek' => ['required', 'string', 'max:200'],
            'isi' => ['required', 'string', 'max:2000'],
        ], ['required' => ':attribute wajib diisi.', 'max' => ':attribute terlalu panjang.'], ['subjek' => 'Subjek', 'isi' => 'Isi email']);

        $data = $this->contoh($request->user());
        $peta = [];
        foreach ($data as $kunci => $nilai) {
            $peta['{{'.$kunci.'}}'] = $nilai;
        }

        return [strtr($this->rapikan($v['subjek']), $peta), strtr($this->rapikan($v['isi']), $peta)];
    }

    private function contoh(User $admin): array
    {
        return [
            'nama' => $admin->name,
            'kode' => 'ABC123',
            'kode_booking' => 'PS5-ABC123',
            'unit' => 'PlayStation 5 Disc Edition',
            'jadwal' => '20-11-2026 12:00 s.d. 20-11-2026 18:00',
            'waktu_mulai' => '20-11-2026 12:00',
            'waktu_selesai' => '20-11-2026 18:00',
            'batas_bayar' => '20-11-2026 11:45',
            'total' => Booking::rupiah(100000),
            'denda' => Booking::rupiah(20000),
            'alasan' => 'Contoh alasan pembatalan',
        ];
    }

    /** "{{ nama }}" menjadi "{{nama}}" agar terbaca oleh pengisi placeholder. */
    private function rapikan(string $teks): string
    {
        return trim(preg_replace('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', '{{$1}}', $teks));
    }

    /** @return array<int, string> placeholder yang tidak dikenal */
    private function placeholderAsing(string $teks): array
    {
        preg_match_all('/\{\{([A-Za-z0-9_]+)\}\}/', $teks, $cocok);

        return array_values(array_unique(array_diff($cocok[1], array_keys(self::PLACEHOLDER))));
    }
}