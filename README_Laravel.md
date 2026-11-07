# GameBox Rent (Laravel): Panduan Proyek

Pemindahan proyek **GameBox Rent** dari Google Apps Script + Google Sheets ke **Laravel**, dengan pembayaran lewat **gateway sungguhan (mode sandbox)**. Dokumen ini diperbarui setiap selesai satu langkah.

Proyek lama (Apps Script, Langkah 1 sampai 6 selesai dan lolos tes) **tetap disimpan sebagai acuan** aturan bisnis, daftar tes, dan desain. Jangan dihapus.

**Posisi sekarang: L1 sampai L5 selesai dan lolos tes. Berikutnya L6.**

---

## 1. Keputusan dan lingkungan

| Hal | Nilai |
|---|---|
| Alasan pindah | Dosen menyarankan pembayaran lewat gateway (Midtrans/Xendit); Apps Script tidak cocok untuk webhook, kuota, dan skala |
| Backend | **Laravel 13.40.0**, **PHP 8.3.36**, Composer 2.10.3 |
| Frontend | **Blade + Tailwind CSS (Vite)** + sedikit JavaScript/Alpine. Desain Stitch (HTML + Tailwind) dipakai ulang |
| Database | **MySQL 8.4.3** lewat Laragon, nama database **`gamebox_rent`** |
| Pembayaran | **Midtrans Snap, sandbox** (Xendit alternatif; dipastikan di L6). Di PRD ini tercantum *opsional*, jadi tidak bertentangan |
| Sistem | Windows + **Laragon** di `D:\laragon` |
| Lokasi proyek | `D:\File Coding Random\GameBox-Rent\gamebox_rent_project by laravel` |
| Node / Git | Node v22.20.0, Git 2.53 |
| Cara kerja | Per langkah: kode + README. Zip hanya jika diminta. Selalu sebutkan file mana yang **dibuat baru** dan mana yang **diubah** |

---

## 2. Roadmap pemindahan

| Langkah | Isi | Status |
|---|---|---|
| L1 | Pasang lingkungan + buat proyek Laravel | **Selesai** (bagian 3) |
| L2 | Migrasi database + seeder (pengaturan, paket harga, unit, metode bayar, admin) | **Selesai, lolos tes, sudah di-commit** (bagian 4) |
| L3 | Layout dari Stitch, registrasi/login pelanggan dan admin (terpisah), role + middleware | **Selesai, lolos tes 11 skenario, di-commit** (bagian 10) |
| L4 | Beranda + katalog unit | **Selesai, lolos tes, di-commit** (bagian 10.7) |
| L5 | Booking: jadwal, durasi, bentrok + buffer, transaksi DB + penguncian baris, Ambil Sendiri/Diantar | **Selesai, lolos semua tes** (bagian 10.8) |
| **L6** | **Pembayaran Midtrans (sandbox)**: Snap, webhook, kode konfirmasi setelah `settlement`, riwayat, detail pesanan | **Berikutnya** (bagian 10.9) |
| L7 | Invoice PDF + email (DomPDF + Laravel Mail) | Belum |
| L8 | Admin: konfirmasi kode, serah terima, tandai selesai, lokasi konsol | Belum |
| L9 | Pelacakan kurir (berbasis status) | Belum |
| L10 | Admin: dashboard, pesanan, jadwal, perangkat, harga, pengaturan, pelanggan, log | Belum |
| L11 | Laporan keuangan + ekspor PDF dan Excel | Belum |
| L12 | Scheduler (batal otomatis, pengingat email) | Belum |
| L13 | Pengujian (kriteria penerimaan PRD) + deploy | Belum |

---

## 3. Status L1 (selesai)

**Terkonfirmasi benar:**
- Proyek aktif di `D:\File Coding Random\GameBox-Rent\gamebox_rent_project by laravel`
- Laravel 13.40.0, PHP 8.3.36, Composer 2.10.3, Vite 8.3.6; `composer run dev` berjalan (server `http://127.0.0.1:8000`, queue, Vite)
- Terhubung ke MySQL 8.4.3, database `gamebox_rent`
- PATH PHP, Composer, MySQL sudah permanen
- `php artisan about`: zona waktu **Asia/Jakarta**, locale **id**, database **mysql**
- Folder sisa `D:\laragon\www\gamebox-rent` sudah tidak ada
- Git berjalan; branch utama sekarang `main` (sebelumnya `master`)

**Yang sengaja dilewati / belum:**
- Halaman `/tes` tidak pernah dicoba dan sudah dihapus di L3 (Vite + Tailwind terbukti jalan lewat layout L3).
- Repository GitHub sudah dibuat dan di-push (lihat bagian 7)

---

## 4. Status L2 (selesai)

**Hasil yang sudah dibuktikan:**
- `php artisan migrate:fresh --seed` berjalan tanpa error (9 migrasi dan 7 seeder)
- `php artisan db:show` menampilkan **24 tabel**
- Jumlah data sesuai: paket **20**, unit **5**, pengaturan **26**, metode bayar **4**, konten **11**, templat email **7**, pengguna **1**
- Satu akun admin: `admin@gamebox.test`, role `admin`, status `Aktif`
- Lima unit: PS4-01, PS4-02, PS5-01, PS5-02, PS5-03, semuanya `Tersedia`
- Commit: `dc40521`, pesan `L2: migrasi dan seeder` (16 berkas, working tree bersih)

**Berkas L2 di proyek:**

| Berkas | Lokasi |
|---|---|
| `2026_11_06_000001_add_gamebox_columns_to_users_table.php` (menambah `no_hp`, `role`, `status`) | `database/migrations/` |
| `2026_11_06_000002_create_master_tables.php` (consoles, packages, settings, contents, email_templates, payment_methods, blocked_dates) | `database/migrations/` |
| `2026_11_06_000003_create_bookings_table.php` | `database/migrations/` |
| `2026_11_06_000004_create_payments_and_invoices_tables.php` | `database/migrations/` |
| `2026_11_06_000005_create_delivery_tables.php` (deliveries, delivery_events) | `database/migrations/` |
| `2026_11_06_000006_create_log_tables.php` (activity_logs, console_movements, email_logs) | `database/migrations/` |
| `DatabaseSeeder.php` (menimpa bawaan), `SettingSeeder`, `PackageSeeder`, `ConsoleSeeder`, `PaymentMethodSeeder`, `ContentSeeder`, `EmailTemplateSeeder`, `AdminSeeder` | `database/seeders/` |
| `Console.php`, `Package.php` (model kosong hasil `make:model`, diisi di L4) | `app/Models/` |

**Fakta penting tentang tabel `users`:**
- Kolom tambahan: `no_hp` (boleh kosong), `role` (bawaan **`user`**, nilai lain **`admin`**), `status` (bawaan **`Aktif`**).
- Kolom nama tetap `name` (konvensi Laravel). Admin tidak punya tabel sendiri; dibedakan lewat `role`.
- Password admin awal: `Admin123!`, kecuali `SEED_ADMIN_PASSWORD` diisi di `.env` **sebelum** seeding. **Wajib diganti sebelum dipakai di luar komputer lokal.**

**Catatan kejadian saat L2 (agar tidak terulang):**
- Jangan menjalankan `php artisan make:model ... -m` untuk tabel yang sudah dibuat migrasi L2 (`consoles`, `packages`, dst.). Itu membuat migrasi kosong ganda yang bentrok dan membuat database setengah jadi.
- `migrate` hanya menjalankan berkas yang ada di `database/migrations/`. Jika tabel tidak muncul, cek dulu isi folder itu dengan `Get-ChildItem database\migrations`.
- Sisa migrasi percobaan lama (`add_role_to_users_table`) sudah dihapus.

### Perintah cek data (kutip tunggal di PowerShell)

```powershell
mysql -u root gamebox_rent -e 'SELECT (SELECT COUNT(*) FROM packages) AS paket, (SELECT COUNT(*) FROM consoles) AS unit, (SELECT COUNT(*) FROM settings) AS pengaturan, (SELECT COUNT(*) FROM payment_methods) AS metode, (SELECT COUNT(*) FROM contents) AS konten, (SELECT COUNT(*) FROM email_templates) AS templat, (SELECT COUNT(*) FROM users) AS pengguna;'
mysql -u root gamebox_rent -e 'SELECT id, name, email, role, status FROM users;'
mysql -u root gamebox_rent -e 'SELECT kode_unit, jenis, nama_tampil, status FROM consoles;'
```

Seeder aman dijalankan ulang (`php artisan db:seed`): data yang sudah ada tidak ditimpa dan admin tidak dibuat dua kali. `migrate:fresh` **menghapus semua tabel**; hanya aman selama belum ada data sungguhan.

---

## 5. Konfigurasi yang benar

### `.env` (nilai yang harus sesuai)

```dotenv
APP_NAME="GameBox Rent"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gamebox_rent
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=log
```

Aturan `.env`:
- Nilai yang mengandung spasi **wajib diberi tanda kutip** (`APP_NAME="GameBox Rent"`), kalau tidak Laravel gagal membaca `.env`.
- File yang diedit bernama persis `.env`, bukan `.env.example`.
- **Jangan membagikan `.env`** (isinya `APP_KEY`, password, kunci API). Kalau perlu dikirim untuk dicek, hapus dulu nilai rahasianya. Kunci Midtrans nanti hanya di `.env`, tidak pernah di kode atau di Git.
- Setelah mengubah `.env`: `php artisan config:clear`.

### `config/app.php`

Zona waktu sudah diubah (baris tidak memakai `env()`):

```php
'timezone' => 'Asia/Jakarta',
```

### Memperbaiki database yang salah (`laravel` → `gamebox_rent`)

Jalankan satu per satu, **satu perintah per baris**:

```powershell
mysql -u root -e "CREATE DATABASE IF NOT EXISTS gamebox_rent CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan config:clear
php artisan migrate
php artisan db:show
```

`migrate` tidak boleh menanyakan "create database". Setelah memastikan, boleh menghapus database salah: `mysql -u root -e "DROP DATABASE laravel;"`.

---

## 6. Tes Blade + Vite + Tailwind (opsional, dihapus di L3)

Jalankan server: `composer run dev` (server `http://127.0.0.1:8000`, Vite, dan log). Jika gagal, pakai dua terminal: `php artisan serve` dan `npm run dev`.

Tambahkan di akhir `routes/web.php`:

```php
Route::get('/tes', fn () => view('tes'));
```

Buat `resources/views/tes.blade.php`:

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tes GameBox Rent</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-4xl font-bold text-cyan-300">GameBox Rent</h1>
        <p class="mt-2 text-slate-400">Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}</p>
    </div>
</body>
</html>
```

Buka `http://127.0.0.1:8000/tes`. Yang benar: latar gelap, judul cyan besar, baris versi Laravel 13 + PHP 8.3. Jika teks tanpa warna, Vite belum berjalan.

---

## 7. Git

Riwayat saat ini:

| Commit | Pesan |
|---|---|
| `987113a` | `L1: setup Laravel` |
| `dc40521` | `L2: migrasi dan seeder` |
| `38890b1` | `L3: layout, autentikasi, role` |
| (L4) | `L4: beranda dan katalog` |
| (L5) | `L5: booking` |

Format commit tiap langkah: `Lx: ringkasan`. Sebelum `git add .`, cek `git status` dan pastikan `.env` tidak muncul (sudah ada di `.gitignore` bawaan).

**GitHub:** repository `https://github.com/muftiabunaseh0924/gamebox-rent` (branch `main`, remote `origin`). Email commit sama dengan email akun GitHub agar terhitung di grafik kontribusi. `.env` tidak ikut terkirim (`git ls-files .env` kosong). Setelah tiap langkah selesai: `git add .`, `git commit -m "Lx: ringkasan"`, `git push`.

Jika diminta identitas: `git config --global user.name "Nama Kamu"` dan `git config --global user.email "emailkamu@example.com"`. Disarankan membuat repository **private** di GitHub (cadangan + kerja kelompok).

---

## 8. Aturan terminal (sumber error terbanyak)

| Aturan | Penjelasan |
|---|---|
| **Satu perintah per baris** | Menempel beberapa perintah dalam satu baris (`cd ... php -v composer -V`) menyebabkan *"The system cannot find the path specified"* |
| **Kutip untuk jalur bersepasi** | `cd "D:\File Coding Random\GameBox-Rent\gamebox_rent_project by laravel"` |
| **Pindah drive** | cmd/terminal Laragon: `cd /d "D:\..."`; PowerShell: `cd "D:\..."` |
| **Terminal Laragon** | Klik kanan ikon Laragon → Terminal: `php`, `composer`, `mysql` pasti dikenali |
| **PowerShell/VS Code** | Butuh PATH permanen (sudah dipasang). Jika `php` tidak dikenali: tutup VS Code sepenuhnya lalu buka lagi |
| **MySQL harus menyala** | Laragon → Start All, sebelum `migrate`/`mysql` |
| **Mengganti versi PHP di Laragon** | Folder PHP berubah nama; ulangi pemasangan PATH dengan nama folder baru |
| **SQL di PowerShell** | Pakai kutip **tunggal** untuk perintah `mysql -e '...'`; kutip ganda dan backtick merusak SQL |

PATH PHP saat ini: `D:\laragon\bin\php\php-8.3.36-Win32-vs16-x64`.

---

## 9. Masalah umum

| Gejala | Penyebab | Solusi |
|---|---|---|
| `php`/`composer`/`mysql` tidak dikenali | PATH belum terbaca | Pakai Terminal Laragon, atau pasang PATH (bagian 8), tutup-buka VS Code |
| `Failed to parse dotenv file ... unexpected whitespace` | Nilai `.env` berspasi tanpa kutip | `APP_NAME="GameBox Rent"` |
| Laravel membuat database `laravel` / `Unknown database` | `DB_DATABASE` salah atau `.env` belum tersimpan | Bagian 5 |
| Pertanyaan *"Would you like to create it?"* muncul | Nama database di `.env` tidak ada di MySQL | Perbaiki `.env`, atau buat database dulu |
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL belum menyala | Laragon → Start All |
| `Access denied for user 'root'` | Password MySQL bukan kosong | Sesuaikan `DB_PASSWORD` |
| `Table '...' doesn't exist` setelah migrate | Berkas migrasi belum ada di `database/migrations/` | `Get-ChildItem database\migrations`, tempatkan berkas, ulangi `migrate:fresh --seed` |
| `Table '...' already exists` saat migrate | Ada migrasi ganda (mis. dari `make:model -m`) | Hapus migrasi ganda, lalu `migrate:fresh --seed` |
| `Duplicate column` saat migrate | Migrasi percobaan lama menambah kolom yang sama | Hapus migrasi lama itu, lalu `migrate:fresh --seed` |
| Halaman `/tes` tanpa gaya | Vite tidak berjalan | `npm run dev` atau `composer run dev` |
| `Vite manifest not found` | Aset belum dibangun | `npm run dev` (dev) atau `npm run build` |
| `Project directory ... is not empty` | Folder `create-project` tidak kosong | Kosongkan atau pakai folder baru |
| `filename too long` saat `npm install` | Batas jalur Windows | `git config --global core.longpaths true` |
| `Storage ... NOT LINKED` di `about` | `storage:link` belum dijalankan | Abaikan dulu; dijalankan nanti untuk foto unit |
| `View [layouts.admin] not found` (atau view lain tidak ditemukan) | Nama berkas salah atau ekstensi bukan `.blade.php` (Windows menyembunyikan ekstensi) | `Get-ChildItem resources\views\layouts`, ganti nama, lalu `php artisan view:clear` |
| Layar login/dasbor tanpa gaya setelah ganti `app.css` | Vite tidak berjalan atau cache lama | `composer run dev`, `php artisan optimize:clear` |
| Login ditolak padahal password benar | Role atau status salah, atau salah halaman login | Pelanggan: `/masuk`. Admin: `/admin/masuk`. Cek `SELECT email, role, status FROM users;` |
| `route:list : The term 'route:list' is not recognized` | Lupa awalan `php artisan` | `php artisan route:list --except-vendor` |
| `Call to undefined function Booking_rupiah()` | Fungsi tidak ada | Pakai `\App\Models\Booking::rupiah(...)`, lalu `php artisan view:clear` |
| `Invalid route action: [App\Http\Controllers\HomeController]` | Berkas controller dibuat dengan `make:controller` tetapi isinya belum ditimpa (tanpa `__invoke`) | Timpa isi berkas dengan kode lengkap, simpan, `php artisan optimize:clear` |

---

## 10. Status L3, L4, L5 (selesai) dan rancangan L6

### 10.1 Keputusan L3 yang dipakai

- Autentikasi ditulis **manual** (tanpa Breeze), supaya sesuai desain dan pemisahan login admin.
- **Tanpa verifikasi email** di tahap ini (`MAIL_MAILER=log`).
- Login admin di alamat terpisah: **`/admin/masuk`**.
- Zip Stitch (`stitch_prd_visual_interface_prototype.zip`) berisi 8 layar: katalog beranda, form booking, pembayaran simulasi, detail pesanan + kode serah terima, jadwal + lokasi konsol, dasbor admin KPI, konfirmasi kode serah terima, laporan keuangan. **Tidak ada layar login/registrasi**, jadi form login/daftar dibuat dari nol memakai token desain Stitch (`gamebox_rent_system/DESIGN.md`). Layar lain dipakai di L4 sampai L11.

### 10.2 Hasil yang sudah dibuktikan (11 tes lolos)

| # | Tes | Hasil |
|---|---|---|
| 1 | Daftar pelanggan | Masuk ke `/dashboard` |
| 2 | Cek DB | Akun baru `role=user`, `status=Aktif`, `no_hp` berawalan `08` |
| 3 | Tanpa login buka `/dashboard` | Diarahkan ke `/masuk` |
| 4 | Pelanggan buka `/admin` | 403 |
| 5 | Tanpa login buka `/admin` | Diarahkan ke `/admin/masuk` |
| 6 | Admin login lewat `/masuk` | Ditolak dengan pesan |
| 7 | Pelanggan login lewat `/admin/masuk` | Ditolak dengan pesan |
| 8 | Admin login lewat `/admin/masuk` | Masuk ke `/admin` |
| 9 | Admin yang login buka `/dashboard` | Dialihkan ke `/admin` |
| 10 | Salah password 7 kali | 429 Too Many Requests |
| 11 | Akun `Nonaktif` | Dikeluarkan saat sesi aktif dan ditolak saat login |

### 10.3 Berkas L3 di proyek

| Berkas | Lokasi | Catatan |
|---|---|---|
| `app.css` | `resources/css/` | Diubah. Tailwind v4: token warna dan font di blok `@theme`; kelas komponen `.input-field`, `.label-field`, `.error-text`, `.btn-primary`, `.btn-secondary`, `.card` |
| `User.php` | `app/Models/` | Diubah. `fillable` hanya `name, email, password, no_hp` (**`role` dan `status` sengaja tidak di fillable**); cast `password => hashed`; helper `isAdmin()`, `isActive()` |
| `EnsureRole.php` | `app/Http/Middleware/` | Baru. Alias `role`; mengeluarkan akun non-aktif; admin yang salah halaman dialihkan ke `/admin`, pelanggan yang membuka area admin mendapat 403 |
| `CustomerAuthController.php`, `AdminAuthController.php` | `app/Http/Controllers/Auth/` | Baru |
| `app.php` | `bootstrap/` | Diubah. Alias `role`, `redirectGuestsTo` (`/admin*` ke `admin.login`, lainnya ke `login`), `redirectUsersTo` (admin ke `admin.dashboard`, pelanggan ke `dashboard`) |
| `web.php` | `routes/` | Diubah. Route `/tes` sudah hilang |
| `head`, `flash`, `navbar`, `footer` (`.blade.php`) | `resources/views/partials/` | Baru |
| `app`, `auth`, `admin` (`.blade.php`) | `resources/views/layouts/` | Baru |
| `home`, `dashboard` (`.blade.php`) | `resources/views/` | Baru, placeholder (isi sebenarnya L4 dan L5/L6) |
| `login`, `register` (`.blade.php`) | `resources/views/auth/` | Baru |
| `login`, `dashboard` (`.blade.php`) | `resources/views/admin/` | Baru, `dashboard` placeholder (isi di L10) |
| `tes.blade.php` | `resources/views/` | Dihapus |

### 10.4 Route L3

| URL | Nama | Akses |
|---|---|---|
| `GET /` | `home` | Publik |
| `GET/POST /daftar` | `register` | Tamu (throttle 10/menit untuk POST) |
| `GET/POST /masuk` | `login` | Tamu (throttle 6/menit untuk POST) |
| `POST /keluar` | `logout` | `auth` |
| `GET /dashboard` | `dashboard` | `auth` + `role:user` |
| `GET/POST /admin/masuk` | `admin.login` | Tamu (throttle 6/menit untuk POST) |
| `GET /admin` | `admin.dashboard` | `auth` + `role:admin` |
| `POST /admin/keluar` | `admin.logout` | `auth` + `role:admin` |

### 10.5 Aturan auth yang berlaku

- Satu tabel `users`; pembeda `role` (`user`/`admin`). Akun admin tidak bisa masuk lewat `/masuk` dan akun pelanggan tidak bisa masuk lewat `/admin/masuk`.
- Registrasi: nama, email unik (huruf kecil), nomor HP (`08...`, `62...`, atau `+62...`, disimpan sebagai `08...`), password minimal 8 karakter berisi huruf dan angka. Akun baru otomatis `role=user`, `status=Aktif`.
- Session diregenerasi saat login; logout menginvalidasi session dan token CSRF.
- Pesan error validasi dan login berbahasa Indonesia.
- Akun pelanggan uji (id 2) dibuat lewat `/daftar`. Admin tetap `admin@gamebox.test` (password awal `Admin123!`, wajib diganti sebelum dipakai di luar komputer lokal).

### 10.6 Catatan kejadian saat L3 (agar tidak terulang)

- Error `View [layouts.admin] not found`: berkas ada tetapi **namanya salah** (ekstensi bukan `.blade.php`). Windows menyembunyikan ekstensi; aktifkan *File name extensions* di Explorer, dan cek dengan `Get-ChildItem resources\views\layouts`. Nama yang benar: `app.blade.php`, `auth.blade.php`, `admin.blade.php`.
- Tes keadaan akun (status `Nonaktif`) harus dibiarkan `Nonaktif` selama dicoba; mengembalikan ke `Aktif` di perintah berikutnya membuat tes tidak terlihat.

### 10.7 Status L4 (selesai): beranda dan katalog unit

**Keputusan yang dipakai:** foto unit pakai placeholder (upload foto menyusul di L10), katalog menampilkan semua unit, teks hero dan footer dibaca dari tabel `contents`.

**Berkas L4:**

| Berkas | Lokasi | Catatan |
|---|---|---|
| `Console.php`, `Package.php` | `app/Models/` | Diisi. `Console`: scope `urut()` (PS5 dulu), `fotoUrl()`, `daftarGame()`; `booking_aktif_id` tidak di fillable. `Package`: scope `aktif()`, `hargaRupiah()` |
| `HomeController.php` | `app/Http/Controllers/` | Controller satu aksi (`__invoke`). Membaca `contents`, `consoles`, `packages`; harga "mulai dari" = paket aktif termurah per jenis |
| `web.php` | `routes/` | `/` ke `HomeController` (nama `home`) |
| `navbar.blade.php` | `resources/views/partials/` | Ditambah tautan Katalog, Harga, Cara Sewa (anchor `#katalog`, `#harga`, `#cara`) |
| `home.blade.php` | `resources/views/` | Hero, katalog + filter PS5/PS4 (JavaScript ringan), harga paket, cara menyewa |

**Format teks di `contents`:** pasangan `Judul::Isi` dipisah `|` (kunci `hero_poin`, `cara_menyewa`, `operasional`); `kelengkapan_standar` dipisah koma. Kunci `catatan_buffer`, `catatan_pengiriman`, `catatan_identitas`, `syarat_ketentuan` berisi placeholder `{{buffer_jam}}` dan `{{denda}}` yang baru dipakai di L5/L6.

**Hasil tes (lolos):**
- Hero memuat judul, badge, dua poin dari database; "Unit tersedia 5 / 5".
- 5 kartu unit cocok dengan tabel `consoles` (PS5-01, PS5-02, PS5-03, PS4-01, PS4-02); harga mulai dari PS4 Rp 50.000 dan PS5 Rp 80.000 benar.
- Status unit dari database tampil benar (Disewa jadi badge kuning + tombol "Tidak Tersedia"); teks hero mengikuti perubahan di `contents`.
- Tamu melihat "Masuk untuk Menyewa"; pelanggan login melihat "Booking Segera Dibuka"; login admin dan `/dashboard` tetap benar.

**Catatan:** kesalahan umum saat tes: menjalankan beberapa perintah `mysql` ditempel sekaligus dan memakai `KODE` apa adanya (harus diganti kode unit asli, mis. `PS5-03`). Jalankan satu perintah, muat ulang halaman, baru perintah berikutnya.

### 10.8 Status L5 (selesai): booking

**Keputusan yang dipakai:** stik tambahan ikut, setelah booking muncul halaman ringkasan dengan hitung mundur batas bayar, zona waktu bisnis `Asia/Jakarta`.

**Berkas L5:**

| Berkas | Lokasi | Catatan |
|---|---|---|
| `Booking.php` | `app/Models/` | Baru (dibuat manual, bukan `make:model`). Tanpa `$fillable` (diisi lewat `forceFill()`); route key `kode_booking`; helper `kedaluwarsa()` dan `rupiah()` |
| `User.php` | `app/Models/` | Diubah: relasi `bookings()` |
| `BookingException.php` | `app/Exceptions/` | Baru. Membawa nama field untuk pesan validasi |
| `BookingService.php` | `app/Services/` | Baru. Seluruh aturan bisnis booking (di bawah) |
| `StoreBookingRequest.php` | `app/Http/Requests/` | Baru. Validasi input form |
| `BookingController.php` | `app/Http/Controllers/` | Baru: `create`, `slot` (JSON), `store`, `show` |
| `DashboardController.php` | `app/Http/Controllers/` | Baru. Mengirim 5 pesanan terbaru ke dasbor |
| `web.php` | `routes/` | Diubah (tabel route di bawah) |
| `create.blade.php`, `show.blade.php` | `resources/views/booking/` | Baru: form booking dan ringkasan pesanan |
| `dashboard.blade.php` | `resources/views/` | Diubah: daftar Pesanan Terbaru |
| `home.blade.php` | `resources/views/` | Diubah: tombol kartu menjadi "Sewa Sekarang" (`booking.create?unit=ID`) |

**Route L5 (semua `auth` + `role:user`):**

| URL | Nama | Fungsi |
|---|---|---|
| `GET /booking` | `booking.create` | Form booking (`?unit=ID` memilih unit) |
| `GET /booking/slot` | `booking.slot` | JSON ketersediaan jam untuk unit, tanggal, durasi |
| `POST /booking` | `booking.store` | Membuat booking (throttle 10/menit) |
| `GET /pesanan/{kode_booking}` | `booking.show` | Ringkasan pesanan (hanya milik sendiri; milik orang lain 404) |

**Aturan di `BookingService` (semua dicek di server, di dalam satu transaksi):**
- Transaksi dimulai dengan `lockForUpdate()` pada **semua baris `consoles`**, lalu semua pengecekan dibaca ulang setelah kunci. Dua permintaan bersamaan tidak bisa sama-sama lolos ke slot atau stok stik yang sama.
- Unit harus `Tersedia`; jam mulai harus termasuk `jam_mulai_slot`; jadwal harus di masa depan dan maksimal `maks_hari_ke_depan` (7) hari; tanggal di `blocked_dates` ditolak.
- Durasi dan harga dari tabel `packages` (aktif, sesuai jenis unit); harga **dihitung ulang di server**: `total_harga = harga_paket + jumlah_stik x harga_stik + biaya_pengiriman`. Kolom `harga_stik` di `bookings` berisi harga **per stik** (salinan dari `settings`).
- Bentrok: `mulaiBaru < selesaiLama + buffer` dan `selesaiBaru + buffer > mulaiLama`. Booking yang memegang jadwal: `Dibayar`, `Sedang Disewa`, dan `Menunggu Pembayaran` yang `batas_bayar`-nya belum lewat (booking kedaluwarsa otomatis melepas jadwal tanpa perlu scheduler).
- Stik tambahan dibatasi `stok_stik` dikurangi stik yang terpakai pada jadwal yang tumpang tindih.
- Maksimal `maks_booking_menunggu` (2) booking `Menunggu Pembayaran` yang belum kedaluwarsa per akun.
- `Diantar`: hanya bila `pengiriman_aktif`; alamat wajib (min. 10 karakter); biaya dari `biaya_pengiriman`.
- Kode booking `PS5-XXXXXX` (panjang dari `panjang_kode`, alfabet tanpa I/O/0/1), unik; `batas_bayar` = sekarang + `batas_bayar_menit` (15).
- Salinan `buffer_jam` dan `denda_per_keterlambatan` disimpan di setiap booking.

**Hasil tes (10 skenario, semua lolos):** route terdaftar; tamu diarahkan ke login lalu kembali ke form dengan unit terpilih; ringkasan harga benar (paket, stik, pengiriman); booking tersimpan dengan status `Menunggu Pembayaran` dan hitung mundur; jam bentrok bertanda (Terisi) dan buffer bekerja; batas 2 booking menunggu; dasbor menampilkan pesanan; booking kedaluwarsa melepas jadwal; admin yang membuka `/booking` dialihkan ke `/admin`.

**Catatan dan kejadian saat L5:**
- `route:list` bukan perintah PowerShell; harus `php artisan route:list --except-vendor`.
- Error `Call to undefined function Booking_rupiah()` di `booking/create.blade.php`: fungsi itu tidak ada. Yang benar `\App\Models\Booking::rupiah(...)` (dua tempat: harga stik dan biaya pengiriman). Setelah mengganti: `php artisan view:clear`.
- `blocked_dates` masih kosong dan nama kolomnya belum dikonfirmasi. `BookingService::tanggalDiblokir()` mencari kolom `tanggal`, `tanggal_blokir`, atau `tgl`, dan melewati pengecekan bila tidak ada. **Kirim `DESCRIBE blocked_dates;` agar dikunci ke nama kolom yang benar** (bisa di awal L6).
- Data uji booking dihapus dengan `DELETE FROM bookings;` sebelum lanjut.

### 10.9 Rancangan L6: pembayaran Midtrans sandbox (untuk dikonfirmasi di awal L6)

**Tujuan:** booking `Menunggu Pembayaran` bisa dibayar lewat Midtrans Snap (sandbox); setelah pembayaran sukses (`settlement`) booking menjadi `Dibayar`, kode konfirmasi terbit, dan pelanggan melihat riwayat serta detail pesanan.

**Yang dibutuhkan di awal L6 (kirim outputnya):**

```powershell
mysql -u root gamebox_rent -e 'DESCRIBE payments;'
mysql -u root gamebox_rent -e 'SELECT * FROM payment_methods;'
mysql -u root gamebox_rent -e 'DESCRIBE blocked_dates;'
```

**Yang perlu disiapkan pengguna:** akun **Midtrans sandbox** (dashboard sandbox.midtrans.com) dan **Client Key + Server Key** dari menu Settings, Access Keys. Kunci hanya masuk `.env`, jangan dikirim ke percakapan, jangan di-commit.

**Usulan isi L6:**
- Paket `midtrans/midtrans-php` (Composer); `config/midtrans.php` membaca `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION=false` dari `.env`.
- Model `Payment`; `PaymentService`: membuat transaksi Snap dengan `order_id` unik per percobaan, `gross_amount` = `total_harga` dari server (bukan dari browser), menyimpan `snap_token` di tabel `payments`.
- Route: `POST /pesanan/{booking}/bayar` (menghasilkan snap token), halaman pesanan memuat `snap.js` sandbox dan membuka popup pembayaran; riwayat `GET /pesanan`.
- **Webhook** `POST /midtrans/notifikasi`: tanpa CSRF; wajib verifikasi `signature_key` (SHA512 dari `order_id + status_code + gross_amount + ServerKey`); idempoten (notifikasi ganda tidak membuat efek ganda); `settlement` mengubah booking menjadi `Dibayar` dan menerbitkan `kode_konfirmasi` unik; `expire`/`cancel`/`deny` mencatat status pembayaran.
- Karena webhook dari internet tidak bisa menjangkau komputer lokal, disediakan **tombol "Cek status pembayaran"** yang menanyakan status transaksi langsung ke API Midtrans (bekerja tanpa tunnel), dan opsional memakai ngrok atau Cloudflare Tunnel untuk menguji webhook asli.
- Detail pesanan menampilkan kode konfirmasi setelah `Dibayar`; tombol "Bayar" hanya aktif selama belum kedaluwarsa.

**Pertanyaan yang dikonfirmasi di awal L6:**
1. Akun Midtrans sandbox dan kuncinya sudah siap (sudah dimasukkan ke `.env` sendiri)?
2. Webhook: uji dengan tunnel (ngrok/Cloudflare, usulan setelah alur dasar jalan) atau cukup tombol "Cek status" dulu?
3. Metode pembayaran: biarkan Snap menampilkan semua metode sandbox (usulan) atau dibatasi sesuai tabel `payment_methods`?

**Hasil yang benar di akhir L6:** pembayaran sandbox sukses mengubah booking menjadi `Dibayar` dan menerbitkan kode konfirmasi; notifikasi ganda atau dengan signature salah tidak mengubah apa pun; booking kedaluwarsa tidak bisa dibayar; commit `L6: pembayaran midtrans`.

---

## 11. Rancangan database (ringkasan dari L2)

**Konvensi:**
- Nama tabel bahasa Inggris jamak: `users`, `consoles`, `packages`, `bookings`, `payments`, `invoices`, `deliveries`, `delivery_events`, `activity_logs`, `console_movements`, `email_logs`, `settings`, `contents`, `email_templates`, `payment_methods`, `blocked_dates`. Tabel bawaan Laravel (`sessions`, `cache`, `jobs`) dipertahankan.
- Kunci utama `id`. Kunci asing: `user_id`, `console_id`, `booking_id`. Nomor yang dilihat manusia (mis. `BK-20261101-001`, `INV20261101-001`) disimpan di kolom tersendiri (`kode_booking`, `no_invoice`) dan unik.
- Kolom domain bahasa Indonesia (`nama`, `waktu_mulai`, `durasi_jam`, `total_harga`, `status_booking`, ...).
- Uang = **integer rupiah**. Waktu = kolom `datetime` (zona Asia/Jakarta).
- Pengaturan di tabel `settings` (key, value) + cache.
- Penguncian jadwal: indeks `(console_id, waktu_mulai, waktu_selesai)` pada `bookings`; `kode_konfirmasi` unik.
- Tabel `payments` disiapkan untuk Midtrans: `order_id` (unik), `snap_token`, `transaction_id`, `payment_type`, `transaction_status`, `fraud_status`, `payload` (JSON), `waktu_bayar`.
- Hapus fisik dibatasi (`restrictOnDelete`) agar riwayat transaksi utuh. Satu booking boleh punya beberapa percobaan bayar.

**Nilai status (tetap sama dengan proyek lama):**
- Booking: `Menunggu Pembayaran`, `Dibayar`, `Sedang Disewa`, `Selesai`, `Dibatalkan`
- Unit: `Tersedia`, `Disewa`, `Perawatan`, `Nonaktif`
- Pengiriman: `Menunggu Dikirim` → `Kurir Ditugaskan` → `Dalam Perjalanan` → `Telah Diterima`; `Gagal Dikirim`
- Cara penerimaan: `Ambil Sendiri`, `Diantar`
- Pembayaran Midtrans: mengikuti `transaction_status` (pending, settlement, deny, cancel, expire, failure); detail dipastikan di L6.
- Pengguna: `role` = `user`/`admin`; `status` = `Aktif` (nilai lain ditentukan di L10)

**Data awal (seeder):** `settings` (harga_stik 10000, denda 20000, buffer 1 jam, batas bayar 15 menit, toleransi ambil 30 menit, stok stik 6, jam slot 0,2,...,22, maks hari 7, maks booking menunggu 2, biaya pengiriman 20000, pengiriman aktif, identitas situs, tema), `packages` (PS4 dan PS5, 6 sampai 24 jam), `consoles` (PS4-01, PS4-02, PS5-01, PS5-02, PS5-03), `payment_methods`, `contents`, `email_templates`, satu akun admin.

**Aturan bisnis yang dibawa apa adanya:** bentrok `mulaiBaru < selesaiLama + buffer` dan `selesaiBaru + buffer > mulaiLama`; slot kelipatan 2 jam; durasi dari tabel paket; harga dihitung ulang di server dan disalin ke booking (harga stik, buffer, denda, biaya kirim); kode `PS5-XXXXXX` tanpa I/O/0/1; maksimal 2 booking menunggu per akun; biaya pengiriman Rp20.000. Mencegah dua booking di jadwal yang sama dilakukan di aplikasi (L5): `DB::transaction()` + `lockForUpdate()` pada baris unit.

**Peta pemindahan:**

| Apps Script | Laravel |
|---|---|
| Google Sheets | Tabel MySQL (migration) |
| `LockService` | `DB::transaction()` + `lockForUpdate()` |
| `CacheService` | `Cache` bawaan |
| `requireUser_` / `requireAdmin_` | Middleware `auth` + `role:admin` |
| Hash SHA-256 + token di sheet | Bawaan Laravel (bcrypt + session) |
| `google.script.run` | Route + controller |
| Time-driven trigger | Laravel Scheduler |
| `MailApp` | Laravel Mail |
| Invoice dari Google Docs | DomPDF |
| Pembayaran simulasi | Midtrans Snap + webhook |
| `testBooking`, `testPembayaran`, dll. | Tes PHPUnit/Pest dengan kasus uji yang sama |

---

## 12. Persiapan nanti (belum perlu sekarang)

- **L6:** buat akun **Midtrans sandbox**; Client Key dan Server Key disimpan di `.env`. Pengujian webhook dari komputer lokal butuh alamat publik sementara (ngrok atau Cloudflare Tunnel).
- **L7:** pengaturan email (SMTP uji seperti Mailtrap/Mailpit atau Gmail App Password); saat ini `MAIL_MAILER=log` (email hanya ditulis ke file log).
- **L13:** hosting yang mendukung PHP 8.3+, MySQL, dan cron (untuk Scheduler) serta kemampuan menerima webhook publik.
- **Sebelum dipakai sungguhan:** ganti password admin awal (`Admin123!`).

---

## 13. Checklist

### L1
- [x] `php -v` 8.3.36, `composer -V` 2.10.3, `node -v` v22.20.0, `git --version`, `mysql --version` 8.4.3
- [x] Proyek Laravel 13.40.0 dibuat; `npm install` dijalankan
- [x] `php artisan about`: Asia/Jakarta, locale id, database mysql
- [x] `DB_DATABASE=gamebox_rent`; `php artisan db:show` menampilkan `gamebox_rent`
- [x] `composer run dev` berjalan di `http://127.0.0.1:8000`
- [x] Folder sisa Laragon tidak ada lagi
- [x] Commit pertama dibuat
- [ ] Halaman `/tes` (dilewati, dihapus di L3)
- [x] Repository GitHub dibuat dan di-push (`muftiabunaseh0924/gamebox-rent`, branch `main`)

### L2
- [x] Zona waktu `Asia/Jakarta`
- [x] 6 migrasi + 8 seeder ditempatkan di folder yang benar
- [x] `php artisan migrate:fresh --seed` tanpa error; `db:show` menampilkan 24 tabel
- [x] Hitungan data sesuai (20 / 5 / 26 / 4 / 11 / 7 / 1); admin role `admin`
- [x] Commit `L2: migrasi dan seeder` (`dc40521`)

### L3
- [x] Keputusan: autentikasi manual, tanpa verifikasi email, login admin di `/admin/masuk`
- [x] Layout (`app`, `auth`, `admin`), partial, halaman daftar/masuk/keluar pelanggan, login admin, middleware `role`
- [x] Route dan view `/tes` dihapus
- [x] 11 tes lolos (pelanggan tidak bisa membuka admin; admin dan pelanggan tidak bisa masuk lewat login yang salah; throttle; akun nonaktif)
- [x] Commit `L3: layout, autentikasi, role` (`38890b1`)

### L4
- [x] Output `DESCRIBE consoles`, `DESCRIBE packages`, `SELECT * FROM packages LIMIT 6`, `SELECT * FROM contents` dikirim
- [x] Keputusan: foto placeholder, semua unit, teks hero dari `contents`
- [x] Model `Console` dan `Package` diisi; `HomeController`, route `/`, navbar, `home.blade.php`
- [x] Beranda menampilkan 5 unit dari database, status dan harga mulai benar; filter PS4/PS5 jalan
- [x] Commit `L4: beranda dan katalog`

### L5
- [x] Keputusan: stik tambahan ikut, halaman ringkasan + hitung mundur, zona waktu Asia/Jakarta
- [x] Model `Booking`, `BookingService`, `BookingException`, `BookingController`, `DashboardController`, `StoreBookingRequest`, route, form dan ringkasan pesanan
- [x] Bentrok + buffer ditolak; batas 2 booking menunggu; harga dihitung server; transaksi + `lockForUpdate`
- [x] 10 tes lolos (jam Terisi, buffer, batas 2 booking, kedaluwarsa, admin dialihkan)
- [x] Commit `L5: booking`
- [ ] (kecil) `DESCRIBE blocked_dates` dikirim untuk mengunci pengecekan tanggal libur

### L6 (berikutnya)
- [ ] Akun Midtrans sandbox dibuat; Client Key + Server Key dimasukkan ke `.env` sendiri
- [ ] Output `DESCRIBE payments`, `SELECT * FROM payment_methods`, `DESCRIBE blocked_dates` dikirim
- [ ] Keputusan: webhook (tunnel atau tombol cek status), metode pembayaran
- [ ] `config/midtrans.php`, model `Payment`, `PaymentService`, route bayar + webhook + riwayat
- [ ] `settlement` mengubah booking menjadi `Dibayar` dan menerbitkan kode konfirmasi; signature salah dan notifikasi ganda aman
- [ ] Commit `L6: pembayaran midtrans`

---

## 14. Cara melanjutkan

1. Selesaikan checklist langkah yang sedang berjalan.
2. Kirim bukti: output perintah cek atau screenshot halaman.
3. Tulis **"lanjut L6"** (atau langkah berikutnya).

Jika memulai percakapan baru, lampirkan README ini + `PRD_GameBox_Rent.pdf` + README dan zip proyek Apps Script lama (acuan aturan bisnis dan tes) + `stitch_prd_visual_interface_prototype.zip`, lalu tulis:

> Saya memindahkan proyek GameBox Rent dari Google Apps Script ke Laravel 13.40 (PHP 8.3, Blade + Tailwind + MySQL via Laragon di Windows, pembayaran Midtrans sandbox). L1, L2 (migrasi database + seeder), L3 (layout, login pelanggan dan admin terpisah, role + middleware), L4 (beranda dan katalog unit), dan L5 (booking: jadwal, bentrok + buffer, harga dihitung server) sudah selesai, lolos tes, dan di-commit. Baca README_Laravel.md. Lanjutkan dari **L6: pembayaran Midtrans sandbox (desain Stitch `pembayaran_simulasi` dan `detail_pesanan_kode_serah_terima`)**, step by step, beri kode dan README per langkah (jangan zip), jalankan satu perintah per baris, dan sebutkan file mana yang dibuat baru atau diubah.
