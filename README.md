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
  <img src="https://img.shields.io/badge/Status-L6_selesai,_L7_berikutnya-2F6BFF?style=flat-square" alt="Status pengembangan">
  <img src="https://img.shields.io/badge/Platform-Windows_%2B_Laragon-5DE6FF?style=flat-square" alt="Windows dan Laragon">
</p>

---

Sistem rental **PlayStation 4 & 5** berbasis web. Pelanggan memilih unit, jadwal, dan durasi sewa; sistem memastikan jadwal tidak bentrok, menghitung harga di server, dan (dalam pengembangan) memproses pembayaran online lewat Midtrans. Dibangun dengan **Laravel 13**, **Tailwind CSS**, dan **MySQL**.

> Proyek ini dimigrasikan dari prototipe Google Apps Script ke Laravel, dengan desain antarmuka dari prototipe Stitch. Pengembangan berjalan bertahap; lihat [Status pengembangan](#status-pengembangan).

## Fitur

**Sudah berjalan**

- Beranda dan katalog unit (PS4/PS5) dengan filter jenis, status ketersediaan, dan harga "mulai dari"
- Konten beranda (judul, poin keunggulan, cara menyewa) dibaca dari database
- Registrasi dan login pelanggan, serta login admin di alamat terpisah (`/admin/masuk`)
- Hak akses berbasis peran: pelanggan tidak bisa membuka area admin dan sebaliknya; akun nonaktif langsung dikeluarkan
- Booking dengan pilihan unit, tanggal, jam mulai, durasi, stik tambahan, dan cara penerimaan (Ambil Sendiri atau Diantar)
- Cek jadwal bentrok beserta jeda (buffer) pembersihan unit, batas jumlah booking yang menunggu pembayaran, dan stok stik
- Harga dihitung ulang di server, bukan dari data browser
- Pencegahan dobel booking pada slot yang sama (transaksi database + penguncian baris)
- Ringkasan pesanan dengan hitung mundur batas bayar, dasbor pelanggan, dan halaman riwayat pesanan
- Pembayaran online lewat Midtrans Snap (mode sandbox): popup pembayaran, cek status pembayaran, dan kode konfirmasi serah terima yang terbit setelah pembayaran sukses

**Dalam pengembangan**

- Webhook Midtrans untuk notifikasi pembayaran otomatis (butuh alamat publik; di lokal memakai tombol cek status)
- Invoice PDF dan notifikasi email
- Panel admin: konfirmasi kode serah terima, jadwal, perangkat, harga, pengaturan, pelanggan
- Laporan keuangan dengan ekspor PDF dan Excel
- Pelacakan pengantaran, serta pembatalan otomatis dan pengingat lewat scheduler

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | PHP 8.3, Laravel 13 |
| Tampilan | Blade, Tailwind CSS v4, Vite |
| Database | MySQL |
| Pembayaran | Midtrans Snap (sandbox) |
| Lingkungan pengembangan | Windows + Laragon |

## Aturan bisnis utama

- Slot jam mulai dan durasi sewa berasal dari tabel `settings` dan `packages`
- Jadwal dianggap bentrok bila tumpang tindih setelah ditambah jeda buffer di kedua sisi
- Booking `Menunggu Pembayaran` memegang jadwal hanya sampai batas bayar; setelah lewat, jadwal otomatis terbuka lagi
- Total harga = harga paket + (jumlah stik x harga stik) + biaya pengiriman, seluruhnya dihitung di server
- Setiap booking menyimpan salinan harga, buffer, dan denda saat dibuat, sehingga perubahan pengaturan tidak mengubah pesanan lama

## Menjalankan secara lokal

Prasyarat: PHP 8.3+, Composer, Node.js dan npm, MySQL.

```bash
git clone https://github.com/muftiabunaseh0924/gamebox-rent.git
cd gamebox-rent

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Buat database kosong bernama `gamebox_rent`, lalu sesuaikan bagian `DB_*` di `.env` (host, port, nama database, user, dan password MySQL Anda). Opsional, isi `SEED_ADMIN_PASSWORD` di `.env` untuk menentukan password admin awal sebelum seeding. Untuk mencoba pembayaran, buat akun [Midtrans](https://dashboard.sandbox.midtrans.com) (lingkungan **Sandbox**), lalu isi `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, dan `MIDTRANS_IS_PRODUCTION=false` di `.env`.

```bash
php artisan migrate --seed
composer run dev
```

Buka `http://localhost:8000`.

| Peran | Alamat masuk | Akun |
|---|---|---|
| Pelanggan | `/masuk` (daftar di `/daftar`) | Buat sendiri lewat halaman daftar |
| Admin | `/admin/masuk` | `admin@gamebox.test` (password awal dari seeder atau `SEED_ADMIN_PASSWORD`) |

Ganti password admin awal sebelum aplikasi dipakai di luar komputer lokal.

## Struktur penting

```text
app/
  Http/Controllers/        Home, Booking, Dashboard, dan Auth (pelanggan + admin)
  Http/Middleware/         EnsureRole (pembatas akses pelanggan/admin)
  Http/Requests/           Validasi form booking
  Models/                  User, Console, Package, Booking, Payment
  Services/                BookingService (aturan booking), PaymentService (Midtrans)
database/
  migrations/              Skema database
  seeders/                 Pengaturan, paket harga, unit, metode bayar, akun admin
resources/
  css/app.css              Token desain (warna, font) dan kelas komponen
  views/                   Layout, partial, beranda, booking, dasbor, auth, admin
config/midtrans.php        Konfigurasi Midtrans (dibaca dari .env)
routes/web.php             Seluruh route
```

## Keamanan

- Berkas `.env` tidak ikut repository; kunci Midtrans hanya disimpan di `.env`
- Jumlah pembayaran selalu diambil dari database, bukan dari browser; notifikasi Midtrans diverifikasi dengan tanda tangan
- Password di-hash, session diregenerasi saat login, dan login dibatasi lewat rate limiting
- Kolom sensitif (peran, status, harga) tidak bisa diisi lewat input pengguna
- Pelanggan hanya dapat melihat pesanan miliknya sendiri

## Status pengembangan

| Tahap | Isi | Status |
|---|---|---|
| L1 | Setup lingkungan dan proyek Laravel | Selesai |
| L2 | Migrasi database dan seeder | Selesai |
| L3 | Layout, autentikasi pelanggan dan admin, peran | Selesai |
| L4 | Beranda dan katalog unit | Selesai |
| L5 | Booking (jadwal, bentrok + buffer, harga server) | Selesai |
| L6 | Pembayaran Midtrans sandbox | Selesai |
| L7 | Invoice PDF dan email | Berikutnya |
| L8 | Admin: konfirmasi kode dan serah terima | Direncanakan |
| L9 | Pelacakan pengantaran | Direncanakan |
| L10 | Admin: dasbor, pesanan, jadwal, perangkat, pengaturan | Direncanakan |
| L11 | Laporan keuangan dan ekspor | Direncanakan |
| L12 | Scheduler (batal otomatis, pengingat) | Direncanakan |
| L13 | Pengujian dan deploy | Direncanakan |

## Penulis

Mufti Abu Naseh ([@muftiabunaseh0924](https://github.com/muftiabunaseh0924)).

---

<p align="center">
  Dibangun di atas <a href="https://laravel.com">Laravel</a>, framework open-source berlisensi MIT.
</p>
