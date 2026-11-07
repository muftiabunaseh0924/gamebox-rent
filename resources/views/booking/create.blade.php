@extends('layouts.app')
@section('title', 'Booking Unit')

@section('content')
<div class="mx-auto max-w-5xl">
    <h2 class="font-headline text-3xl font-bold">Booking Unit</h2>
    <p class="mt-1 text-on-surface-variant">Pilih unit, jadwal, dan durasi sewa. Harga final dihitung ulang oleh server.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-error/40 bg-error-container/20 px-4 py-3 text-sm text-error">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($unit->isEmpty())
        <div class="card mt-8 text-center text-on-surface-variant">Saat ini belum ada unit yang tersedia untuk disewa.</div>
    @else
    <form method="POST" action="{{ route('booking.store') }}" class="mt-8 grid gap-8 lg:grid-cols-3" novalidate>
        @csrf

        <div class="space-y-6 lg:col-span-2">
            <div class="card space-y-5">
                <div>
                    <label for="console_id" class="label-field">Unit</label>
                    <select id="console_id" name="console_id" class="input-field">
                        @foreach ($unit as $u)
                            <option value="{{ $u->id }}" data-jenis="{{ $u->jenis }}"
                                @selected((string) old('console_id', $dipilih) === (string) $u->id)>
                                {{ $u->kode_unit }} · {{ $u->nama_tampil }}
                            </option>
                        @endforeach
                    </select>
                    @error('console_id') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label for="tanggal" class="label-field">Tanggal</label>
                        <input id="tanggal" type="date" name="tanggal" class="input-field"
                               value="{{ old('tanggal', $tanggalMin) }}" min="{{ $tanggalMin }}" max="{{ $tanggalMax }}">
                        @error('tanggal') <p class="error-text">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="durasi_jam" class="label-field">Durasi</label>
                        <select id="durasi_jam" name="durasi_jam" class="input-field"></select>
                        @error('durasi_jam') <p class="error-text">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="jam_mulai" class="label-field">Jam Mulai</label>
                        <select id="jam_mulai" name="jam_mulai" class="input-field"></select>
                        @error('jam_mulai') <p class="error-text">{{ $message }}</p> @enderror
                    </div>
                </div>
                <p class="text-xs text-outline">
                    Jam yang bentrok ditandai (Terisi). Setiap jeda antar penyewa diberi buffer {{ $pengaturan['buffer_jam'] }} jam untuk pembersihan unit.
                </p>
            </div>

            <div class="card space-y-5">
                <div>
                    <label for="jumlah_stik" class="label-field">Stik Tambahan (opsional)</label>
                    <input id="jumlah_stik" type="number" name="jumlah_stik" min="0" max="{{ $pengaturan['stok_stik'] }}"
                           value="{{ old('jumlah_stik', 0) }}" class="input-field sm:max-w-[10rem]">
                   <p class="mt-1.5 text-xs text-outline">{{ \App\Models\Booking::rupiah($pengaturan['harga_stik']) }} per stik. Sudah termasuk 2 controller.</p>
                    @error('jumlah_stik') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <p class="label-field">Cara Penerimaan</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-400/20 bg-surface-container-lowest px-4 py-3">
                            <input type="radio" name="metode_serah" value="Ambil Sendiri" class="accent-primary-container"
                                   @checked(old('metode_serah', 'Ambil Sendiri') === 'Ambil Sendiri')>
                            <span>Ambil Sendiri <span class="block text-xs text-outline">Gratis, ambil di tempat sewa</span></span>
                        </label>
                        <label class="flex items-center gap-3 rounded-lg border border-slate-400/20 bg-surface-container-lowest px-4 py-3 {{ $pengaturan['pengiriman_aktif'] ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}">
                            <input type="radio" name="metode_serah" value="Diantar" class="accent-primary-container"
                                   @disabled(! $pengaturan['pengiriman_aktif']) @checked(old('metode_serah') === 'Diantar')>
                            <span>Diantar <span class="block text-xs text-outline">+ {{ \App\Models\Booking::rupiah($pengaturan['biaya_pengiriman']) }} per sewa</span></span>
                        </label>
                    </div>
                    @error('metode_serah') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div id="alamat_box" class="hidden">
                    <label for="alamat_pengiriman" class="label-field">Alamat Pengiriman</label>
                    <textarea id="alamat_pengiriman" name="alamat_pengiriman" rows="3" class="input-field"
                              placeholder="Alamat lengkap tujuan pengantaran">{{ old('alamat_pengiriman') }}</textarea>
                    @error('alamat_pengiriman') <p class="error-text">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <aside class="lg:col-span-1">
            <div class="card sticky top-28 space-y-4">
                <h3 class="font-headline text-xl font-bold">Ringkasan</h3>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-outline">Unit</dt><dd id="r-unit" class="font-semibold">-</dd></div>
                    <div><dt class="text-outline">Mulai</dt><dd id="r-mulai" class="font-semibold">-</dd></div>
                    <div><dt class="text-outline">Selesai</dt><dd id="r-selesai" class="font-semibold">-</dd></div>
                </dl>
                <div class="space-y-2 border-t border-surface-variant/40 pt-4 text-sm">
                    <div class="flex justify-between"><span class="text-on-surface-variant">Paket sewa</span><span id="r-paket">-</span></div>
                    <div class="flex justify-between"><span class="text-on-surface-variant">Stik tambahan</span><span id="r-stik">-</span></div>
                    <div class="flex justify-between"><span class="text-on-surface-variant">Pengiriman</span><span id="r-kirim">-</span></div>
                </div>
                <div class="flex items-end justify-between border-t border-surface-variant/40 pt-4">
                    <span class="text-on-surface-variant">Total</span>
                    <span id="r-total" class="font-headline text-2xl font-bold text-secondary">-</span>
                </div>
                <button type="submit" class="btn-primary w-full">Buat Booking</button>
                <p class="text-xs text-outline">Pembayaran dibuka setelah booking dibuat. Batas bayar {{ $pengaturan['batas_bayar_menit'] }} menit.</p>
            </div>
        </aside>
    </form>
    @endif
</div>

<script>
(function () {
    var CFG = @json($cfg);
    var OLD = { durasi: @json(old('durasi_jam')), jam: @json(old('jam_mulai')) };
    var $ = function (id) { return document.getElementById(id); };
    var unit = $('console_id'), tanggal = $('tanggal'), durasi = $('durasi_jam'), jam = $('jam_mulai'), stik = $('jumlah_stik');
    if (!unit) { return; }
    var token = 0;

    function rupiah(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }
    function pad(n) { return String(n).padStart(2, '0'); }
    function jenis() { var o = unit.options[unit.selectedIndex]; return o ? o.dataset.jenis : null; }
    function metode() { var r = document.querySelector('input[name=metode_serah]:checked'); return r ? r.value : 'Ambil Sendiri'; }

    function isiDurasi() {
        var daftar = CFG.paket[jenis()] || [];
        var pilih = durasi.value || OLD.durasi;
        durasi.innerHTML = daftar.map(function (p) {
            return '<option value="' + p.durasi + '">' + p.durasi + ' jam · ' + rupiah(p.harga) + '</option>';
        }).join('');
        if (pilih && daftar.some(function (p) { return String(p.durasi) === String(pilih); })) { durasi.value = pilih; }
    }

    async function muatSlot() {
        var saya = ++token;
        var pilih = jam.value || OLD.jam;
        if (!unit.value || !tanggal.value || !durasi.value) {
            jam.innerHTML = '<option value="">Pilih unit dan tanggal</option>';
            ringkas();
            return;
        }
        try {
            var url = new URL(CFG.urlSlot, window.location.origin);
            url.search = new URLSearchParams({ console_id: unit.value, tanggal: tanggal.value, durasi_jam: durasi.value }).toString();
            var r = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!r.ok) { throw new Error('slot'); }
            var data = await r.json();
            if (saya !== token) { return; }
            jam.innerHTML = data.slots.map(function (s) {
                return '<option value="' + s.jam + '"' + (s.tersedia ? '' : ' disabled') + '>' + pad(s.jam) + ':00' + (s.tersedia ? '' : ' (' + s.alasan + ')') + '</option>';
            }).join('');
            var cocok = data.slots.find(function (s) { return String(s.jam) === String(pilih) && s.tersedia; })
                || data.slots.find(function (s) { return s.tersedia; });
            if (cocok) { jam.value = cocok.jam; }
        } catch (e) {
            jam.innerHTML = CFG.slot.map(function (j) { return '<option value="' + j + '">' + pad(j) + ':00</option>'; }).join('');
            if (pilih) { jam.value = pilih; }
        }
        ringkas();
    }

    function ringkas() {
        var daftar = CFG.paket[jenis()] || [];
        var p = daftar.find(function (x) { return String(x.durasi) === String(durasi.value); });
        var hargaPaket = p ? p.harga : 0;
        var jml = Math.max(0, Math.min(CFG.stok, parseInt(stik.value || '0', 10) || 0));
        var diantar = metode() === 'Diantar';
        var kirim = diantar ? CFG.biayaKirim : 0;

        $('alamat_box').classList.toggle('hidden', !diantar);
        $('r-unit').textContent = unit.options[unit.selectedIndex] ? unit.options[unit.selectedIndex].text : '-';
        $('r-paket').textContent = p ? rupiah(hargaPaket) : '-';
        $('r-stik').textContent = jml + ' x ' + rupiah(CFG.hargaStik) + ' = ' + rupiah(jml * CFG.hargaStik);
        $('r-kirim').textContent = diantar ? rupiah(kirim) : 'Gratis';
        $('r-total').textContent = p ? rupiah(hargaPaket + jml * CFG.hargaStik + kirim) : '-';

        if (tanggal.value && jam.value !== '' && p) {
            var m = new Date(tanggal.value + 'T' + pad(jam.value) + ':00');
            var s = new Date(m.getTime() + p.durasi * 3600000);
            var opsi = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
            $('r-mulai').textContent = m.toLocaleString('id-ID', opsi);
            $('r-selesai').textContent = s.toLocaleString('id-ID', opsi);
        } else {
            $('r-mulai').textContent = '-';
            $('r-selesai').textContent = '-';
        }
    }

    unit.addEventListener('change', function () { isiDurasi(); muatSlot(); });
    tanggal.addEventListener('change', muatSlot);
    durasi.addEventListener('change', muatSlot);
    jam.addEventListener('change', ringkas);
    stik.addEventListener('input', ringkas);
    document.querySelectorAll('input[name=metode_serah]').forEach(function (r) { r.addEventListener('change', ringkas); });

    isiDurasi();
    muatSlot();
})();
</script>
@endsection