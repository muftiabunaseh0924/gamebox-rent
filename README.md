# GameBox Rent

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
- Ringkasan pesanan dengan hitung mundur batas bayar, dan dasbor pelanggan berisi pesanan terbaru

**Dalam pengembangan**

- Pembayaran online Midtrans (sandbox): Snap, webhook, kode konfirmasi setelah pembayaran sukses
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
| Pembayaran | Midtrans Snap (sandbox), direncanakan |
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

Buat database kosong bernama `gamebox_rent`, lalu sesuaikan bagian `DB_*` di `.env` (host, port, nama database, user, dan password MySQL Anda). Opsional, isi `SEED_ADMIN_PASSWORD` di `.env` untuk menentukan password admin awal sebelum seeding.

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
  Models/                  User, Console, Package, Booking
  Services/BookingService  Seluruh aturan bisnis booking
database/
  migrations/              Skema database
  seeders/                 Pengaturan, paket harga, unit, metode bayar, akun admin
resources/
  css/app.css              Token desain (warna, font) dan kelas komponen
  views/                   Layout, partial, beranda, booking, dasbor, auth, admin
routes/web.php             Seluruh route
```

## Keamanan

- Berkas `.env` tidak ikut repository; kunci Midtrans kelak hanya disimpan di `.env`
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
| L6 | Pembayaran Midtrans sandbox | Berikutnya |
| L7 | Invoice PDF dan email | Direncanakan |
| L8 | Admin: konfirmasi kode dan serah terima | Direncanakan |
| L9 | Pelacakan pengantaran | Direncanakan |
| L10 | Admin: dasbor, pesanan, jadwal, perangkat, pengaturan | Direncanakan |
| L11 | Laporan keuangan dan ekspor | Direncanakan |
| L12 | Scheduler (batal otomatis, pengingat) | Direncanakan |
| L13 | Pengujian dan deploy | Direncanakan |

## Penulis

Mufti Abu Naseh ([@muftiabunaseh0924](https://github.com/muftiabunaseh0924)).
