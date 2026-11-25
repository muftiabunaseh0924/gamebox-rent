# 🎮 GameBox Rent

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<h1 align="center">GameBox Rent</h1>

<p align="center">
  Sistem rental <b>PlayStation 4 &amp; 5</b> berbasis web: booking jadwal, cek bentrok, dan pembayaran online.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS v4">
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Pembayaran-Midtrans_Sandbox-0A4DA2?style=flat-square" alt="Midtrans Sandbox">
  <img src="https://img.shields.io/badge/Status-L12_selesai_(prototipe)-2F6BFF?style=flat-square" alt="Status pengembangan">
  <img src="https://img.shields.io/badge/Platform-Windows_%2B_Laragon-5DE6FF?style=flat-square" alt="Windows dan Laragon">
</p>

---

## Deskripsi

GameBox Rent merupakan sistem rental **PlayStation 4 & 5** berbasis web. Pelanggan dapat memilih unit, jadwal, dan durasi sewa. Sistem memastikan jadwal tidak bentrok, menghitung harga di server, dan memproses pembayaran online melalui Midtrans (mode sandbox).

Aplikasi dibangun menggunakan **Laravel 13**, **Tailwind CSS v4**, dan **MySQL**.

> Proyek ini dimigrasikan dari prototipe Google Apps Script ke Laravel, dengan desain antarmuka dari prototipe Stitch. Pengembangan berjalan bertahap; lihat bagian [Status Pengembangan](#status-pengembangan).

## Fitur

### Sudah Berjalan

- Beranda dan katalog unit (PS4/PS5) dengan filter jenis, status ketersediaan, dan harga "mulai dari".
- Konten beranda (judul, poin keunggulan, cara menyewa) dibaca dari database.
- Registrasi dan login pelanggan, serta login admin di alamat terpisah (`/admin/masuk`).
- Hak akses berbasis peran: pelanggan tidak bisa membuka area admin dan sebaliknya; akun nonaktif langsung dikeluarkan.
- Booking dengan pilihan unit, tanggal, jam mulai, durasi, stik tambahan, dan cara penerimaan (Ambil Sendiri atau Diantar).
- Cek jadwal bentrok beserta jeda (buffer) pembersihan unit, batas jumlah booking yang menunggu pembayaran, dan stok stik.
- Harga dihitung ulang di server, bukan dari data browser.
- Pencegahan dobel booking pada slot yang sama (transaksi database + penguncian baris).
- Ringkasan pesanan dengan hitung mundur batas bayar, dasbor pelanggan, dan halaman riwayat pesanan.
- Pembayaran online lewat Midtrans Snap (mode sandbox): popup pembayaran, cek status pembayaran, dan kode konfirmasi serah terima yang terbit setelah pembayaran sukses.
- Invoice PDF yang bisa diunduh dari halaman pesanan, dan email konfirmasi pembayaran (dengan lampiran invoice) yang terkirim sekali per pembayaran.
- Panel admin: verifikasi kode konfirmasi dan serah terima unit (sekali per kode, dengan jendela waktu pengambilan), pantauan armada dan lokasi konsol, serta penandaan selesai dengan perhitungan denda keterlambatan.
- Log aktivitas admin dan riwayat perpindahan unit.
- Dasbor admin dengan KPI (pemasukan, transaksi, rata-rata order, armada, keterlambatan dan denda berjalan), grafik pemasukan harian, dan proporsi PS5 vs PS4.
- Manajemen pesanan admin: daftar dengan filter dan pencarian, detail lengkap, unduh invoice, dan pembatalan pesanan dengan alasan (jadwal dilepas, pelanggan diberi tahu lewat email).
- Pengelolaan perangkat (tambah, ubah, nonaktifkan, hapus, unggah foto yang tampil di katalog), harga paket per jenis dan durasi, serta tanggal libur (semua unit atau satu unit) yang menutup booking baru.
- Pelacakan pengantaran untuk pesanan Diantar: status berurutan (kurir ditugaskan, dalam perjalanan, diterima, gagal), linimasa yang dilihat pelanggan, dan penerimaan unit yang diverifikasi dengan kode konfirmasi.
- Pengaturan sistem dari panel admin (identitas situs dan parameter bisnis seperti jeda buffer dan batas booking), tanpa perlu mengubah kode atau database langsung.
- Pengelolaan konten beranda dari panel admin (judul, poin keunggulan, cara menyewa).
- Templat email yang bisa disunting admin, lengkap dengan pratinjau dan pengiriman email uji.
- Manajemen pelanggan admin: daftar dengan pencarian (nama, email, HP) dan filter status, detail pelanggan beserta ringkasan belanja dan riwayat pesanan, serta aktif/nonaktifkan akun (sesi pelanggan langsung diputus dan setiap perubahan tercatat di log).
- Halaman log aktivitas admin (hanya baca) dengan filter admin, jenis aksi, rentang tanggal, dan pencarian keterangan.
- Laporan keuangan admin: pilih bulan atau rentang tanggal bebas, ringkasan pemasukan (per jenis unit, penerimaan, unit, dan harian), rincian per transaksi, serta ekspor PDF dan Excel (angka sama dengan dasbor).
- Pembatalan otomatis pesanan yang melewati batas bayar (dengan pengecekan ke Midtrans agar pembayaran yang sudah masuk tidak ikut dibatalkan) dan pengingat email sebelum sewa mulai (1 jam) dan selesai (30 menit), dijalankan scheduler tiap 5 menit.

### Dalam Pengembangan

- Webhook Midtrans untuk notifikasi pembayaran otomatis (butuh alamat publik; di lokal memakai tombol cek status).

### Di Luar Cakupan Prototipe

- Pengujian kriteria penerimaan dan deploy ke hosting (L13).
- Email lewat SMTP sungguhan (prototipe memakai `MAIL_MAILER=log`, email tercatat di `storage/logs/laravel.log`).

---

## Tampilan dan Antarmuka

Tampilan aplikasi dirancang responsif dengan pendekatan **mobile-first** untuk sisi pelanggan maupun admin. Antarmuka dibangun menggunakan Blade, Tailwind CSS v4, dan Alpine.js tanpa menggunakan pustaka animasi yang berat.

### Responsif

- Layout menggunakan satu kolom pada perangkat HP, kemudian menyesuaikan menjadi lebih lebar pada tablet (768 px) dan desktop (1024 px).
- Navbar pelanggan berubah menjadi menu hamburger pada layar di bawah 768 px, dilengkapi panel menu dengan animasi.
- Sidebar admin berubah menjadi drawer dari sisi kiri pada perangkat mobile, dilengkapi navigasi bawah yang berisi Dasbor, Pesanan, Konfirmasi, Pengiriman, dan Menu.
- Tabel Pesanan, Pelanggan, dan Log Aktivitas berubah menjadi daftar kartu pada perangkat HP.
- Formulir booking pada perangkat HP menggunakan bar bawah yang menampilkan total harga dan tombol Buat Booking.

### Animasi

- Animasi elemen saat muncul ketika halaman beranda di-scroll.
- Efek kartu terangkat ketika diarahkan kursor.
- Animasi ringan pada filter katalog.
- Angka KPI admin menggunakan animasi menghitung naik.
- Batang grafik menggunakan animasi pertumbuhan dari bawah.
- Hitung mundur pembayaran memberikan efek denyut ketika waktu tersisa 2 menit.
- Notifikasi flash menggunakan animasi masuk, dapat ditutup, dan menghilang secara otomatis.
- Dialog konfirmasi menggantikan fungsi `confirm()` bawaan browser.
- Seluruh animasi mengikuti pengaturan Reduce Motion pada perangkat pengguna.

### Dialog Konfirmasi

Konfirmasi tindakan seperti menghapus data, mengubah status, dan memproses pesanan menggunakan dialog konfirmasi khusus sebagai pengganti `confirm()` bawaan browser.

Dialog dapat dipanggil dengan menambahkan atribut pada elemen `<form>` atau tombol submit di halaman admin.

Contoh penggunaan:

```blade
<form method="POST" action="..."
      data-konfirmasi="Hapus data ini?"
      data-label="Ya, hapus"
      data-bahaya="1">
```

Keterangan atribut:

- `data-konfirmasi`: pesan yang ditampilkan pada dialog konfirmasi.
- `data-label`: teks tombol konfirmasi, bersifat opsional.
- `data-bahaya`: menentukan apakah tombol konfirmasi menggunakan warna merah untuk tindakan berisiko, bersifat opsional.

### Pustaka Frontend

Pustaka frontend dipasang melalui npm dengan versi tetap untuk menjaga konsistensi dependensi.

Pustaka yang digunakan:

- `alpinejs` versi 3.17.5.
- `@alpinejs/focus` versi 3.17.5.
- `@alpinejs/intersect` versi 3.17.5.

Setelah melakukan perubahan pada kelas Tailwind CSS atau JavaScript, jalankan proses build agar perubahan diterapkan.

```bash
npm run build
```

Selama pengembangan, dapat menggunakan:

```bash
npm run dev
```
---

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | PHP 8.3, Laravel 13 |
| Tampilan | Blade, Tailwind CSS v4, Alpine.js |
| Database | MySQL |
| Pembayaran | Midtrans Snap (sandbox) |
| Ekspor laporan | DomPDF (PDF), PhpSpreadsheet (Excel) |
| Penjadwalan | Laravel Scheduler (perintah artisan) |
| Lingkungan pengembangan | Windows + Laragon |
| Build frontend | Vite, npm |

## Aturan Bisnis Utama

- Slot jam mulai dan durasi sewa berasal dari tabel `settings` dan `packages`.
- Jadwal dianggap bentrok bila tumpang tindih setelah ditambah jeda buffer di kedua sisi.
- Booking `Menunggu Pembayaran` memegang jadwal hanya sampai batas bayar; setelah lewat, jadwal otomatis terbuka lagi.
- Total harga = harga paket + (jumlah stik x harga stik) + biaya pengiriman, seluruhnya dihitung di server.
- Setiap booking menyimpan salinan harga, buffer, dan denda saat dibuat, sehingga perubahan pengaturan tidak mengubah pesanan lama.
- Total belanja pelanggan dihitung dari pesanan berstatus `Dibayar`, `Sedang Disewa`, dan `Selesai`.
- Akun pelanggan yang dinonaktifkan tidak bisa masuk dan sesi yang sedang berjalan langsung diputus.
- Pemasukan laporan = pembayaran berhasil dari pesanan yang tidak dibatalkan (sama dengan dasbor); denda keterlambatan dilaporkan terpisah dan tidak dijumlahkan ke pemasukan.
- Pesanan `Menunggu Pembayaran` yang melewati batas bayar (ditambah tenggang 2 menit) dibatalkan otomatis; pesanan yang pembayarannya sudah masuk di Midtrans dilewati.
- Pengingat email dikirim sekali per pesanan (penanda di database mencegah email ganda).

## Menjalankan Secara Lokal

### Prasyarat

Pastikan perangkat sudah memiliki:

- PHP 8.3+
- Composer
- Node.js dan npm
- MySQL
- Git

### Instalasi

Clone repository:

```bash
git clone https://github.com/muftiabunaseh0924/gamebox-rent.git
cd gamebox-rent
```

Install dependency Laravel dan frontend:

```bash
composer install
npm install
```

Salin file konfigurasi:

```bash
cp .env.example .env
php artisan key:generate
```

Buat database kosong bernama `gamebox_rent`, lalu sesuaikan bagian `DB_*` di `.env` (host, port, nama database, user, dan password MySQL Anda).

Opsional, isi `SEED_ADMIN_PASSWORD` di `.env` untuk menentukan password admin awal sebelum seeding.

Untuk mencoba pembayaran, buat akun [Midtrans Sandbox](https://dashboard.sandbox.midtrans.com), lalu isi konfigurasi berikut di `.env`:

```env
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
```

Jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

Jalankan aplikasi:

```bash
composer run dev
```

Buka aplikasi melalui:

```text
http://localhost:8000
```

### Menjalankan Scheduler

Untuk mencoba pembatalan otomatis dan pengingat email, jalankan scheduler di terminal terpisah:

```bash
php artisan schedule:work
```

Atau jalankan perintah secara manual:

```bash
php artisan booking:batal-kedaluwarsa
php artisan booking:kirim-pengingat
```

Opsi `--simulasi` hanya menampilkan daftar tanpa mengubah data.

### Akun Aplikasi

| Peran | Alamat Masuk | Akun |
|---|---|---|
| Pelanggan | `/masuk` (daftar di `/daftar`) | Buat sendiri lewat halaman daftar |
| Admin | `/admin/masuk` | `admin@gamebox.test` (password awal dari seeder atau `SEED_ADMIN_PASSWORD`) |

Ganti password admin awal sebelum aplikasi dipakai di luar komputer lokal.

## Struktur Penting

```text
app/
  Http/Controllers/        Home, Booking, Dashboard, dan Auth (pelanggan + admin)
  Http/Middleware/         EnsureRole (pembatas akses pelanggan/admin)
  Http/Requests/           Validasi form booking
  Models/                  User, Console, Package, Booking, Payment, Delivery, DeliveryEvent
  Http/Controllers/Admin/  Dasbor, pesanan, pelanggan, laporan, konfirmasi kode (serah terima), armada, pengiriman,
                           perangkat, harga, tanggal libur, pengaturan, konten beranda, templat email, log aktivitas
  Services/                BookingService, PaymentService (Midtrans), InvoiceService (PDF), HandoverService (serah terima, denda),
                           DeliveryService (pengantaran), DashboardService (KPI), CancelService (pembatalan),
                           NotifikasiService + EmailTemplateService (email), SettingsService (pengaturan situs),
                           ActivityLogger (pencatat log admin), ReportService + ReportExcelService (laporan keuangan dan ekspor),
                           AutoCancelService (batal otomatis), PengingatService (pengingat email)
  Console/Commands/        booking:batal-kedaluwarsa, booking:kirim-pengingat
  Mail/                    Email pembayaran berhasil (lampiran invoice)
database/
  migrations/              Skema database
  seeders/                 Pengaturan, paket harga, unit, metode bayar, akun admin
resources/
  css/app.css              Token desain (warna, font) dan kelas komponen
  views/                   Layout, partial, beranda, booking, dasbor, auth, admin
config/midtrans.php        Konfigurasi Midtrans (dibaca dari .env)
routes/web.php             Seluruh route
routes/console.php         Jadwal scheduler (tiap 5 menit)
```

## Keamanan

- Berkas `.env` tidak ikut repository; kunci Midtrans hanya disimpan di `.env`.
- Jumlah pembayaran selalu diambil dari database, bukan dari browser; notifikasi Midtrans diverifikasi dengan tanda tangan.
- Password di-hash, session diregenerasi saat login, dan login dibatasi lewat rate limiting.
- Kolom sensitif (peran, status, harga) tidak bisa diisi lewat input pengguna.
- Pelanggan hanya dapat melihat pesanan miliknya sendiri.
- Halaman pelanggan di panel admin hanya menampilkan akun berperan pelanggan; akun admin tidak bisa dibuka atau diubah dari sana.
- Log aktivitas admin bersifat hanya baca dari panel (tidak bisa diubah atau dihapus).
- Ekspor Excel menulis semua teks sebagai string, sehingga isi seperti `=1+1` tidak dieksekusi sebagai rumus; setiap ekspor tercatat di log aktivitas.

## Status Pengembangan

| Tahap | Isi | Status |
|---|---|---|
| L1 | Setup lingkungan dan proyek Laravel | Selesai |
| L2 | Migrasi database dan seeder | Selesai |
| L3 | Layout, autentikasi pelanggan dan admin, peran | Selesai |
| L4 | Beranda dan katalog unit | Selesai |
| L5 | Booking (jadwal, bentrok + buffer, harga server) | Selesai |
| L6 | Pembayaran Midtrans sandbox | Selesai |
| L7 | Invoice PDF dan email | Selesai |
| L8 | Admin: konfirmasi kode dan serah terima | Selesai |
| L9 | Pelacakan pengantaran | Selesai |
| L10a | Admin: dasbor KPI dan manajemen pesanan | Selesai |
| L10b | Admin: perangkat dan foto, harga paket, tanggal libur | Selesai |
| L10c | Admin: pengaturan sistem, konten, templat email | Selesai |
| L10d | Admin: pelanggan dan log aktivitas | Selesai |
| L11 | Laporan keuangan dan ekspor (PDF dan Excel) | Selesai |
| L12 | Scheduler (batal otomatis, pengingat) | Selesai |
| L13 | Pengujian dan deploy | Tidak dikerjakan (prototipe) |

## Penulis

Mufti Abu Naseh ([@muftiabunaseh0924](https://github.com/muftiabunaseh0924)).

---

<p align="center">
  Dibangun di atas <a href="https://laravel.com">Laravel</a>, framework open-source berlisensi MIT.
</p>
