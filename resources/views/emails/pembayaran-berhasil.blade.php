<!DOCTYPE html>
<html lang="id">
<body style="margin:0; padding:24px; background:#f3f4f8; font-family:Arial, Helvetica, sans-serif; color:#1a1f30;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden;">
        <tr>
            <td style="background:#0e1323; padding:18px 24px; color:#5de6ff; font-size:18px; font-weight:bold;">{{ $namaSitus }}</td>
        </tr>
        <tr>
            <td style="padding:24px; font-size:14px; line-height:1.6;">
                {!! nl2br(e($isi)) !!}

                <div style="margin:24px 0; padding:16px; background:#f3f4f8; border-radius:6px; text-align:center;">
                    <div style="font-size:11px; letter-spacing:1px; color:#6b7080; text-transform:uppercase;">Kode konfirmasi</div>
                    <div style="font-size:30px; font-weight:bold; letter-spacing:6px; color:#2f6bff;">{{ $booking->kode_konfirmasi }}</div>
                </div>

                <table width="100%" cellpadding="4" cellspacing="0" style="font-size:13px;">
                    <tr><td style="color:#6b7080;">Kode booking</td><td align="right"><strong>{{ $booking->kode_booking }}</strong></td></tr>
                    <tr><td style="color:#6b7080;">Unit</td><td align="right">{{ $booking->console->nama_tampil }}</td></tr>
                    <tr><td style="color:#6b7080;">Jadwal</td><td align="right">{{ $booking->waktu_mulai->format('d-m-Y H:i') }} s.d. {{ $booking->waktu_selesai->format('d-m-Y H:i') }}</td></tr>
                    <tr><td style="color:#6b7080;">Total</td><td align="right"><strong>{{ \App\Models\Booking::rupiah($booking->total_harga) }}</strong></td></tr>
                </table>

                <p style="margin-top:24px;">
                    <a href="{{ route('booking.show', $booking) }}" style="color:#2f6bff;">Lihat pesanan</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>