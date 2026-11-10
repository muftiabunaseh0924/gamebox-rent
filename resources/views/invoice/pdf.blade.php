@php $rp = fn ($n) => \App\Models\Booking::rupiah($n); @endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>{{ $nomor }}</title>
<style>
    @page { margin: 34px 38px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1f30; }
    h1, h2, h3, p { margin: 0; }
    table { width: 100%; border-collapse: collapse; }
    .muted { color: #6b7080; }
    .brand { font-size: 20px; font-weight: bold; color: #2f6bff; }
    .judul { font-size: 22px; font-weight: bold; text-align: right; }
    .kotak { border: 1px solid #d8dbe6; padding: 10px 12px; vertical-align: top; }
    .label { font-size: 9px; text-transform: uppercase; color: #6b7080; margin-bottom: 4px; }
    .th { background: #1a1f30; color: #ffffff; font-size: 10px; text-transform: uppercase; padding: 7px 8px; text-align: left; }
    .td { padding: 7px 8px; border-bottom: 1px solid #e6e8f0; }
    .kanan { text-align: right; }
    .total { font-size: 14px; font-weight: bold; color: #2f6bff; }
    .lunas { width: 76px; text-align: center; border: 2px solid #12a150; color: #12a150; font-weight: bold; font-size: 13px; padding: 3px 0; }
    .kode { font-size: 22px; font-weight: bold; letter-spacing: 4px; color: #2f6bff; }
</style>
</head>
<body>

<table>
    <tr>
        <td style="width:55%; vertical-align: top;">
            <div class="brand">{{ $situs['nama_situs'] ?? config('app.name') }}</div>
            <p class="muted">{{ $situs['tagline'] ?? '' }}</p>
            <p class="muted" style="margin-top:6px;">{{ $situs['alamat'] ?? '' }}</p>
            <p class="muted">{{ $situs['kontak'] ?? '' }}</p>
        </td>
        <td style="width:45%; vertical-align: top;">
            <div class="judul">INVOICE</div>
            <p class="kanan" style="margin-top:6px;"><strong>{{ $nomor }}</strong></p>
            <p class="kanan muted">{{ $tanggal }}</p>
        </td>
    </tr>
</table>

<table style="margin-top:18px;">
    <tr>
        <td class="kotak" style="width:50%;">
            <div class="label">Ditagihkan kepada</div>
            <p><strong>{{ $booking->user->name }}</strong></p>
            <p>{{ $booking->user->email }}</p>
            <p>{{ $booking->user->no_hp ?? '-' }}</p>
        </td>
        <td class="kotak" style="width:50%;">
            <div class="label">Detail pesanan</div>
            <p>Kode booking: <strong>{{ $booking->kode_booking }}</strong></p>
            <p>Unit: {{ $booking->console->kode_unit }} - {{ $booking->console->nama_tampil }}</p>
            <p>Mulai: {{ $booking->waktu_mulai->format('d-m-Y H:i') }}</p>
            <p>Selesai: {{ $booking->waktu_selesai->format('d-m-Y H:i') }}</p>
            <p>Penerimaan: {{ $booking->metode_serah }}</p>
            @if ($booking->alamat_pengiriman)
                <p>Alamat: {{ $booking->alamat_pengiriman }}</p>
            @endif
        </td>
    </tr>
</table>

<table style="margin-top:18px;">
    <tr>
        <th class="th">Deskripsi</th>
        <th class="th kanan" style="width:50px;">Jumlah</th>
        <th class="th kanan" style="width:90px;">Harga</th>
        <th class="th kanan" style="width:100px;">Subtotal</th>
    </tr>
    <tr>
        <td class="td">Sewa {{ $booking->console->nama_tampil }} ({{ $booking->durasi_jam }} jam)</td>
        <td class="td kanan">1</td>
        <td class="td kanan">{{ $rp($booking->harga_paket) }}</td>
        <td class="td kanan">{{ $rp($booking->harga_paket) }}</td>
    </tr>
    @if ($booking->jumlah_stik > 0)
        <tr>
            <td class="td">Stik tambahan</td>
            <td class="td kanan">{{ $booking->jumlah_stik }}</td>
            <td class="td kanan">{{ $rp($booking->harga_stik) }}</td>
            <td class="td kanan">{{ $rp($booking->jumlah_stik * $booking->harga_stik) }}</td>
        </tr>
    @endif
    @if ($booking->biaya_pengiriman > 0)
        <tr>
            <td class="td">Biaya pengiriman</td>
            <td class="td kanan">1</td>
            <td class="td kanan">{{ $rp($booking->biaya_pengiriman) }}</td>
            <td class="td kanan">{{ $rp($booking->biaya_pengiriman) }}</td>
        </tr>
    @endif
    <tr>
        <td colspan="3" class="td kanan"><strong>TOTAL</strong></td>
        <td class="td kanan total">{{ $rp($booking->total_harga) }}</td>
    </tr>
</table>

<table style="margin-top:18px;">
    <tr>
        <td class="kotak" style="width:50%;">
            <div class="label">Pembayaran</div>
            <div class="lunas">LUNAS</div>
            <p style="margin-top:8px;">Metode: {{ $pembayaran?->metode ?? '-' }}</p>
            <p>Dibayar: {{ $pembayaran?->waktu_bayar?->format('d-m-Y H:i') ?? '-' }}</p>
            <p class="muted">Order: {{ $pembayaran?->order_id ?? '-' }}</p>
        </td>
        <td class="kotak" style="width:50%; text-align:center;">
            <div class="label">Kode konfirmasi serah terima</div>
            <div class="kode">{{ $booking->kode_konfirmasi }}</div>
            <p class="muted" style="margin-top:6px;">Tunjukkan kode ini ke admin saat serah terima unit.</p>
        </td>
    </tr>
</table>

<p class="muted" style="margin-top:20px; font-size:9px;">
    Keterlambatan pengembalian dikenakan denda {{ $rp($booking->denda_per_keterlambatan) }}.
    Invoice ini dibuat otomatis oleh sistem dan sah tanpa tanda tangan.
</p>

</body>
</html>