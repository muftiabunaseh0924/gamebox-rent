<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Keuangan - {{ $label }}</title>
<style>
    @page { margin: 28px 32px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1b1f24; }
    h1 { font-size: 18px; margin: 0; }
    h2 { font-size: 12px; margin: 16px 0 6px; border-bottom: 1px solid #c9ced6; padding-bottom: 3px; }
    .muted { color: #6b7280; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #eef1f5; text-align: left; font-size: 9px; text-transform: uppercase; padding: 5px 6px; border-bottom: 1px solid #c9ced6; }
    td { padding: 5px 6px; border-bottom: 1px solid #e5e8ed; vertical-align: top; }
    .r { text-align: right; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
    .kpi td { border: 1px solid #c9ced6; padding: 8px 10px; width: 25%; }
    .kpi .v { font-size: 15px; font-weight: bold; }
    .kpi .k { font-size: 9px; text-transform: uppercase; color: #6b7280; }
    .total td { font-weight: bold; border-top: 1px solid #1b1f24; }
    .note { margin-top: 8px; padding: 6px 8px; background: #f6f7f9; border: 1px solid #e5e8ed; }
    .dua td { border-bottom: none; padding: 0 8px 0 0; width: 50%; }
</style>
</head>
<body>
@php
    $rp = fn ($n) => \App\Models\Booking::rupiah($n);
    $tgl = fn ($t, $f = 'd-m-Y H:i') => \Illuminate\Support\Carbon::parse($t)->format($f);
@endphp

<table>
    <tr>
        <td style="border:none;padding:0;">
            <h1>Laporan Keuangan</h1>
            <p class="muted" style="margin:3px 0 0;">{{ $situs['nama_situs'] }}</p>
        </td>
        <td class="r" style="border:none;padding:0;">
            <p style="margin:0;font-weight:bold;">Periode: {{ $label }}</p>
            <p class="muted" style="margin:3px 0 0;">Dicetak {{ $dicetak->format('d-m-Y H:i') }}</p>
        </td>
    </tr>
</table>

<h2>Ringkasan</h2>
<table class="kpi">
    <tr>
        <td><div class="k">Total pemasukan</div><div class="v">{{ $rp($lap['pemasukan']) }}</div></td>
        <td><div class="k">Total transaksi</div><div class="v">{{ $lap['transaksi'] }}</div></td>
        <td><div class="k">Rata-rata order</div><div class="v">{{ $rp($lap['rata']) }}</div></td>
        <td><div class="k">Denda keterlambatan</div><div class="v">{{ $rp($lap['denda']['total']) }}</div><div class="muted">{{ $lap['denda']['n'] }} pesanan, tidak masuk pemasukan</div></td>
    </tr>
</table>

<table class="dua" style="margin-top:6px;">
    <tr>
        <td>
            <h2>Pemasukan per jenis unit</h2>
            <table>
                <thead><tr><th>Jenis</th><th class="r">Transaksi</th><th class="r">Pemasukan</th></tr></thead>
                <tbody>
                @foreach (['PS5', 'PS4'] as $j)
                    <tr><td>{{ $j }}</td><td class="r">{{ $lap['jenis'][$j]['n'] }}</td><td class="r">{{ $rp($lap['jenis'][$j]['total']) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </td>
        <td>
            <h2>Pemasukan per penerimaan</h2>
            <table>
                <thead><tr><th>Penerimaan</th><th class="r">Transaksi</th><th class="r">Pemasukan</th></tr></thead>
                <tbody>
                @foreach (['Ambil Sendiri', 'Diantar'] as $m)
                    <tr><td>{{ $m }}</td><td class="r">{{ $lap['metode'][$m]['n'] }}</td><td class="r">{{ $rp($lap['metode'][$m]['total']) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </td>
    </tr>
</table>

<div class="note">
    Stik tambahan: <strong>{{ $lap['stik']['jumlah'] }}</strong> stik ({{ $rp($lap['stik']['pendapatan']) }}).
    Dibatalkan tetapi sudah dibayar: <strong>{{ $lap['batal_dibayar']['n'] }}</strong> pesanan ({{ $rp($lap['batal_dibayar']['total']) }}),
    tidak dihitung sebagai pemasukan; refund dilakukan manual di dasbor Midtrans.
</div>

<table class="dua" style="margin-top:6px;">
    <tr>
        <td>
            <h2>Pemasukan per unit</h2>
            <table>
                <thead><tr><th>Unit</th><th>Jenis</th><th class="r">Transaksi</th><th class="r">Pemasukan</th></tr></thead>
                <tbody>
                @forelse ($lap['unit'] as $u)
                    <tr><td>{{ $u->kode_unit }}</td><td>{{ $u->jenis }}</td><td class="r">{{ $u->n }}</td><td class="r">{{ $rp($u->total) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="muted">Belum ada pemasukan pada periode ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </td>
        <td>
            <h2>Pemasukan harian</h2>
            <table>
                <thead><tr><th>Tanggal</th><th class="r">Transaksi</th><th class="r">Pemasukan</th></tr></thead>
                <tbody>
                @forelse ($lap['harian'] as $h)
                    <tr><td>{{ $tgl($h->hari, 'd-m-Y') }}</td><td class="r">{{ $h->n }}</td><td class="r">{{ $rp($h->total) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="muted">Belum ada pemasukan pada periode ini.</td></tr>
                @endforelse
                </tbody>
                @if ($lap['harian']->isNotEmpty())
                    <tfoot><tr class="total"><td>Total</td><td class="r">{{ $lap['transaksi'] }}</td><td class="r">{{ $rp($lap['pemasukan']) }}</td></tr></tfoot>
                @endif
            </table>
        </td>
    </tr>
</table>

<h2 style="page-break-before: always;">Rincian transaksi ({{ $jumlah }})</h2>
@if ($dipotong)
    <p class="muted">Menampilkan {{ $rincian->count() }} transaksi terbaru dari {{ $jumlah }}. Gunakan ekspor Excel untuk data selengkapnya.</p>
@endif
<table>
    <thead>
        <tr>
            <th>Waktu bayar</th>
            <th>Kode</th>
            <th>Pelanggan</th>
            <th>Unit</th>
            <th>Jadwal</th>
            <th>Penerimaan</th>
            <th class="r">Stik</th>
            <th>Status</th>
            <th class="r">Nominal</th>
            <th class="r">Denda</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($rincian as $r)
        <tr>
            <td>{{ $tgl($r->waktu_bayar) }}</td>
            <td>{{ $r->kode_booking }}</td>
            <td>{{ $r->pelanggan }}</td>
            <td>{{ $r->kode_unit }}</td>
            <td>{{ $tgl($r->waktu_mulai) }} ({{ $r->durasi_jam }} jam)</td>
            <td>{{ $r->metode_serah }}</td>
            <td class="r">{{ $r->jumlah_stik }}</td>
            <td>{{ $r->status_booking }}</td>
            <td class="r">{{ $rp($r->nominal) }}</td>
            <td class="r">{{ ($r->denda ?? 0) > 0 ? $rp($r->denda) : '-' }}</td>
        </tr>
    @empty
        <tr><td colspan="10" class="muted">Tidak ada transaksi pada periode ini.</td></tr>
    @endforelse
    </tbody>
</table>
</body>
</html>