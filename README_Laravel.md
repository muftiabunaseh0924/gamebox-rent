# GameBox Rent (Laravel): Panduan Proyek

Pemindahan proyek **GameBox Rent** dari Google Apps Script + Google Sheets ke **Laravel**, dengan pembayaran lewat **gateway sungguhan (mode sandbox)**. Dokumen ini diperbarui setiap selesai satu langkah.

Proyek lama (Apps Script, Langkah 1 sampai 6 selesai dan lolos tes) **tetap disimpan sebagai acuan** aturan bisnis, daftar tes, dan desain. Jangan dihapus.

**Posisi sekarang: L1 sampai L8 selesai dan lolos tes. Berikutnya L9.**

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
| L6 | **Pembayaran Midtrans (sandbox)**: Snap, webhook, kode konfirmasi setelah `settlement`, riwayat, detail pesanan | **Selesai, lolos semua tes** (bagian 10.9) |
| L7 | Invoice PDF + email (DomPDF + Laravel Mail) | **Selesai, lolos semua tes** (bagian 10.10) |
| L8 | Admin: konfirmasi kode, serah terima, tandai selesai, lokasi konsol | **Selesai, lolos semua tes** (bagian 10.11) |
| **L9** | Pelacakan kurir (berbasis status) | **Berikutnya** (bagian 10.12) |
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
| `865004b` | `L5: booking` |
| (L6) | `L6: pasang paket Midtrans dan konfigurasi sandbox`, lalu `L6: pembayaran midtrans` |
| `b29a3b7` | `L7: invoice dan email` |
| (L8) | `L8: admin serah terima` |

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
| `SQLSTATE[HY000] [2002] No connection could be made ... 3306` (log membengkak) | MySQL belum menyala | Laragon, **Start All**; bersihkan `Clear-Content storage\logs\laravel.log` |
| `Unknown column 'prefix_invoice'` pada `mysql -e` | Tanda kutip ganda di dalam argumen PowerShell dihapus | Kutip ganda di luar, kutip tunggal di dalam: `mysql -u root gamebox_rent -e "SELECT ... WHERE settings.key IN ('a','b');"` |
| `composer require` timeout (`curl error 28`) ke `repo.packagist.org` | WiFi memblokir Packagist | Pakai hotspot HP, cek `Test-NetConnection repo.packagist.org -Port 443` |

---

## 10. Status L3 sampai L8 (selesai) dan rancangan L9

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

### 10.9 Status L6 (selesai): pembayaran Midtrans sandbox

**Keputusan yang dipakai:** Midtrans **Sandbox** (tanpa uang sungguhan), tipe akun **Pedagang** (Merchant); status pembayaran dicek lewat tombol "Cek Status Pembayaran" (webhook tersedia tetapi baru bisa diuji dengan alamat publik); popup Snap menampilkan semua metode sandbox (tabel `payment_methods` belum dipakai).

**Setup (selesai sebelum kode):**
- Paket `midtrans/midtrans-php` 2.6.2 (`composer require`), `config/midtrans.php` (`server_key`, `client_key`, `is_production`, `snap_js_url` sandbox/production).
- `.env`: `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION=false`. `.env.example` memuat nama variabel dengan nilai kosong. `.env` tidak ikut Git (`git check-ignore -v .env` menunjukkan aturan di `.gitignore`).
- Kunci sandbox berawalan `SB-Mid-server-` dan `SB-Mid-client-`. Server Key dibuat di dasbor Sandbox (Settings, Access Keys, General Credentials, Generate server key): Snap Checkout Write, Core API Read, Expire dan Cancel transaction Write, tanpa kedaluwarsa. Abaikan "BI Snap Credentials".
- Uji kunci tanpa membocorkannya: `php artisan tinker --execute="echo substr(...)"` mencetak hanya 7 huruf pertama (`SB-Mid-|SB-Mid-`); uji token Snap berhasil.

**Berkas L6:**

| Berkas | Lokasi | Catatan |
|---|---|---|
| `Payment.php` | `app/Models/` | Baru (manual, tanpa `$fillable`; `payload` di-cast array) |
| `Booking.php` | `app/Models/` | Diubah: relasi `payments()`, `statusTampil()`, `kelasStatus()` |
| `PaymentService.php` | `app/Services/` | Baru. `buatTransaksi()`, `sinkronkan()`, `terapkan()`, `tandaTanganValid()` |
| `PaymentController.php` | `app/Http/Controllers/` | Baru: `bayar`, `cekStatus`, `webhook` |
| `BookingController.php` | `app/Http/Controllers/` | Diubah: ditambah `index` (riwayat), `show` memuat pembayaran |
| `web.php` | `routes/` | Diubah (tabel di bawah) |
| `show.blade.php`, `index.blade.php` | `resources/views/booking/` | `show`: popup Snap, hitung mundur, tombol cek status, kode konfirmasi, riwayat pembayaran. `index`: daftar semua pesanan (baru) |
| `dashboard.blade.php` | `resources/views/` | Diubah: tombol "Semua Pesanan" |
| `midtrans.php` | `config/` | Baru |

**Route baru:**

| URL | Nama | Akses |
|---|---|---|
| `GET /pesanan` | `booking.index` | `auth` + `role:user` |
| `POST /pesanan/{kode_booking}/bayar` | `booking.bayar` | `auth` + `role:user` (throttle 10/menit); mengembalikan token Snap (JSON) |
| `POST /pesanan/{kode_booking}/cek-status` | `booking.cek-status` | `auth` + `role:user` (throttle 20/menit) |
| `POST /midtrans/notifikasi` | `midtrans.notifikasi` | Publik, tanpa CSRF, dijaga tanda tangan |

**Aturan di `PaymentService`:**
- **Jumlah bayar diambil dari database** (`total_harga`), bukan dari browser; rincian item (paket, stik, pengiriman) dikirim ke Midtrans.
- `order_id` unik per percobaan (`KODE-BOOKING-waktu+2 huruf`); percobaan Pending yang masih punya token dipakai ulang.
- Masa berlaku transaksi Midtrans mengikuti `batas_bayar` booking.
- Status Midtrans dipetakan: `settlement` atau `capture`+`accept` menjadi `Berhasil`; `pending` menjadi `Pending`; `expire` menjadi `Kedaluwarsa`; `cancel` menjadi `Dibatalkan`; `deny`/`failure` menjadi `Gagal`.
- Sumber kebenaran adalah API status Midtrans (`Transaction::status`), baik dari tombol cek status maupun webhook; webhook memverifikasi `signature_key` (SHA512 dari `order_id + status_code + gross_amount + ServerKey`).
- Idempoten: pembayaran yang sudah `Berhasil` tidak diproses ulang. Nominal Midtrans yang berbeda dari database diabaikan.
- `Berhasil` mengubah booking menjadi `Dibayar` dan menerbitkan `kode_konfirmasi` (6 karakter, alfabet tanpa I/O/0/1, unik). Bila pembayaran masuk setelah batas bayar dan jadwal sudah dipakai orang lain, booking tidak diubah dan dicatat di log untuk ditangani admin.
- Transaksi database mengunci booking dulu, lalu payment (urutan konsisten).

**Hasil tes (12 skenario, semua lolos):** cek status sebelum bayar; popup Snap dengan total yang sama; pembayaran kartu uji berhasil; booking menjadi `Dibayar` dengan kode konfirmasi; data `payments` dan `bookings` benar di database; Virtual Account lewat simulator; jadwal yang sudah dibayar tampil Terisi di form booking; `/pesanan` menampilkan riwayat; admin yang membuka `/pesanan` dialihkan ke `/admin`.

**Tentang tes terakhir (admin membuka `/pesanan`):** bukan lewat menu. Menu "Pesanan" di sidebar admin memang belum aktif (bertanda Segera) sampai L10. Cara mengujinya: login di `/admin/masuk`, lalu **ketik langsung** `http://localhost:8000/pesanan` di address bar; hasil yang benar adalah dialihkan ke `/admin`. Penyebabnya middleware `role:user` pada route pelanggan (`EnsureRole`) yang mengalihkan admin ke dasbor admin.

**Kartu uji Sandbox:** `4811 1111 1111 1114`, CVV `123`, kedaluwarsa bulan dan tahun apa saja di masa depan, OTP `112233`. Virtual Account dan QRIS diselesaikan lewat simulator pembayaran di docs.midtrans.com (Testing Payment on Sandbox).

**Catatan dan kejadian saat L6 (agar tidak terulang):**
- Dasbor Midtrans membuka lingkungan **Production** secara bawaan. Selalu cek kotak **Environment** di kiri atas bertuliskan **Sandbox** sebelum membuat kunci; kunci Production tidak berawalan `SB-Mid-`.
- WiFi rumah memblokir `repo.packagist.org` (timeout `curl error 28`, `Test-NetConnection` gagal), sehingga `composer require` gagal. Solusi: **hotspot HP** saat memasang paket Composer. Paket yang sudah terpasang (folder `vendor`) tidak butuh internet; panggilan ke Midtrans butuh internet ke `app.sandbox.midtrans.com`. Paket baru berikutnya (DomPDF di L7, Excel di L11) juga butuh hotspot.
- Jangan mengetik penugasan variabel kunci di tinker (`Config::$serverKey = config(...)`) karena tinker mencetak nilainya. Pakai perintah tinker yang membungkus panggilan dalam fungsi atau hanya mencetak 7 huruf pertama. Kunci yang pernah terlihat (di layar atau percakapan) sebaiknya dibuat ulang di dasbor lalu yang lama dihapus.
- `git commit` tanpa `git add .` sebelumnya memberi `no changes added to commit`; urutan yang benar: `git add .`, `git commit`, `git pull --rebase origin main`, `git push`.
- Webhook asli tidak bisa menerima notifikasi selama aplikasi di `localhost`. Pengujiannya butuh tunnel (ngrok atau Cloudflare Tunnel) dan pengisian Payment Notification URL di dasbor Sandbox; untuk sekarang tombol "Cek Status Pembayaran" menggantikannya.

### 10.10 Status L7 (selesai): invoice PDF dan email

**Keputusan yang dipakai:** pengujian email lewat `MAIL_MAILER=log` (email dan lampiran tercatat di `storage/logs/laravel.log`, tanpa akun email); invoice dibuat saat diminta (tidak disimpan sebagai berkas, tabel `invoices` tidak dipakai); email dikirim langsung tanpa antrean.

**Setup:** paket `barryvdh/laravel-dompdf` (`composer require`, butuh hotspot HP bila WiFi memblokir Packagist). Migrasi baru menambah kolom `bookings.email_pembayaran_at` (penanda email sudah terkirim).

**Berkas L7:**

| Berkas | Lokasi | Catatan |
|---|---|---|
| `*_tambah_email_pembayaran_ke_bookings.php` | `database/migrations/` | Baru: kolom `email_pembayaran_at` |
| `EmailTemplateService.php` | `app/Services/` | Baru. Mengisi placeholder `{{...}}` dari tabel `email_templates` (kolom `jenis_email`, `subjek`, `isi`); ada teks cadangan bila baris hilang |
| `InvoiceService.php` | `app/Services/` | Baru. Nomor invoice `INV-YYYYMMDD-KODE` (awalan dari `settings.prefix_invoice`, tanggal dari waktu bayar), pembuatan PDF, aturan "invoice hanya untuk booking yang sudah dibayar" |
| `NotifikasiService.php` | `app/Services/` | Baru. `pembayaranBerhasil()` mengklaim `email_pembayaran_at` secara atomik, sehingga email terkirim **sekali** per booking; kegagalan email tidak membatalkan pembayaran dan klaim dilepas agar bisa dicoba lagi |
| `PembayaranBerhasilMail.php` | `app/Mail/` | Baru. Email HTML + lampiran invoice PDF |
| `InvoiceController.php` | `app/Http/Controllers/` | Baru. Unduh invoice (hanya milik sendiri, hanya bila sudah dibayar) |
| `KirimEmailPembayaran.php` | `app/Console/Commands/` | Baru: `php artisan email:kirim-pembayaran KODE [--paksa]` untuk booking yang dibayar sebelum L7 atau untuk uji ulang |
| `PaymentService.php` | `app/Services/` | Diubah: `tandaiDibayar()` mengembalikan `bool`; email dikirim lewat `DB::afterCommit()` hanya ketika status benar-benar berubah menjadi `Dibayar` |
| `pdf.blade.php` | `resources/views/invoice/` | Baru: tampilan invoice A4 (identitas situs, pelanggan, rincian, tanda LUNAS, kode konfirmasi) |
| `pembayaran-berhasil.blade.php` | `resources/views/emails/` | Baru |
| `show.blade.php` | `resources/views/booking/` | Diubah: tombol "Unduh Invoice (PDF)" |
| `web.php` | `routes/` | Diubah: `GET /pesanan/{kode_booking}/invoice` (nama `booking.invoice`, `auth` + `role:user`) |

**Placeholder email yang didukung:** `nama`, `kode`, `kode_booking`, `unit`, `jadwal`, `waktu_mulai`, `waktu_selesai`, `batas_bayar`, `total`, `denda`, `alasan`.

**Hasil tes (semua lolos):** tombol Unduh Invoice muncul hanya pada pesanan yang sudah dibayar; isi PDF sesuai; invoice booking yang belum dibayar menjadi 404; email muncul di log dengan subjek berisi kode konfirmasi dan lampiran `Invoice-INV-YYYYMMDD-KODE.pdf` (`application/pdf`); email hanya satu walau status dicek tiga kali; `email_pembayaran_at` terisi; kirim manual untuk booking lama berhasil; admin yang membuka alamat invoice dialihkan ke `/admin`.

**Catatan dan kejadian saat L7:**
- Booking yang dibayar **sebelum** kode L7 terpasang punya `email_pembayaran_at` kosong dan tidak memicu email otomatis; kirim manual dengan `php artisan email:kirim-pembayaran KODE`.
- `laravel.log` sempat membengkak (sekitar 10 MB) karena `SQLSTATE[HY000] [2002] No connection could be made... 3306` berulang saat Laragon/MySQL belum menyala. Nyalakan **Start All** di Laragon dulu; bersihkan dengan `Clear-Content storage\logs\laravel.log`.
- Perintah PowerShell yang memuat tanda kutip ganda di dalam `mysql -e '...'` gagal (`Unknown column`); gunakan kutip ganda di luar dan kutip tunggal di dalam, dan tulis `settings.key` untuk kolom `key`.
- Edit parsial pada berkas besar (mis. `PaymentService.php`, `show.blade.php`) sering terlewat; setelah mengedit, cek dengan `Select-String` atau tempel isi berkas untuk diperiksa.

### 10.11 Status L8 (selesai): admin, serah terima dan armada

**Keputusan yang dipakai:** denda per jam keterlambatan (dibulatkan ke atas) dikali tarif yang disalin ke booking (`denda_per_keterlambatan`, Rp20.000); serah terima dibuka 1 jam sebelum jadwal mulai sampai `toleransi_ambil_menit` (30 menit) sesudah mulai, dengan opsi admin "Abaikan jendela waktu" yang dicatat di log; email `terima_kasih` dikirim saat Tandai Selesai. Tidak ada migrasi baru (semua kolom dan tabel sudah ada dari L2).

**Berkas L8:**

| Berkas | Lokasi | Catatan |
|---|---|---|
| `Console.php` | `app/Models/` | Diubah: relasi `bookingAktif()` dan `bookings()` |
| `HandoverService.php` | `app/Services/` | Baru. `cariByKode`, `evaluasi` (aturan serah terima), `serahkan`, `selesaikan`, `hitungDenda`; mencatat ke `activity_logs` dan `console_movements` |
| `NotifikasiService.php` | `app/Services/` | Diubah: ditambah `sewaSelesai()` (email terima kasih, kegagalan email tidak mengganggu proses selesai) |
| `TemplateMail.php` | `app/Mail/` | Baru: email umum dari templat database |
| `HandoverController.php`, `FleetController.php` | `app/Http/Controllers/Admin/` | Baru |
| `web.php` | `routes/` | Diubah (tabel di bawah) |
| `admin.blade.php` | `resources/views/layouts/` | Diubah: menu Konfirmasi Kode dan Jadwal & Lokasi aktif (Pesanan dan Laporan masih "Segera") |
| `konfirmasi.blade.php`, `armada.blade.php` | `resources/views/admin/` | Baru |
| `sederhana.blade.php` | `resources/views/emails/` | Baru |

**Route L8 (semua `auth` + `role:admin`):**

| URL | Nama | Fungsi |
|---|---|---|
| `GET /admin/konfirmasi` | `admin.konfirmasi` | Form kode konfirmasi |
| `POST /admin/konfirmasi` | `admin.konfirmasi.cek` | Verifikasi kode, tampilkan detail (throttle 30/menit) |
| `POST /admin/konfirmasi/serahkan` | `admin.konfirmasi.serahkan` | Serah terima unit; dicocokkan dengan kode booking **dan** kode konfirmasi |
| `GET /admin/armada` | `admin.armada` | Jadwal & lokasi konsol, ringkasan armada |
| `POST /admin/armada/{kode_booking}/selesai` | `admin.armada.selesai` | Tandai selesai, hitung denda |

**Aturan di `HandoverService`:**
- Serah terima hanya untuk booking `Dibayar`, kode belum dipakai, unit `Tersedia`, dan dalam jendela waktu. Booking `Sedang Disewa`, `Selesai`, `Dibatalkan`, atau `Menunggu Pembayaran` ditolak dengan pesan yang jelas.
- Serah terima (dalam satu transaksi dengan penguncian booking lalu unit): booking `Sedang Disewa`, `kode_dipakai=1`, `waktu_serah_terima`, `admin_konfirmasi_id`; unit `Disewa`, `booking_aktif_id`, `lokasi_saat_ini` (`Dibawa pelanggan` atau `Di alamat pelanggan` bila Diantar); semua pengecekan diulang di dalam transaksi.
- Tandai Selesai: booking `Selesai`, `waktu_kembali`, `denda`, `catatan_kondisi`; unit `Tersedia`, `Di tempat sewa`, `booking_aktif_id` kosong; email terima kasih lewat `DB::afterCommit()`.
- Setiap aksi tercatat di `activity_logs` (aksi `serah_terima` dan `selesai_sewa`, dengan `admin_id`) dan perpindahan unit di `console_movements`.

**Hasil tes (semua lolos):** menu admin aktif; kode salah ditolak; kode di luar jendela waktu menampilkan opsi abaikan; kode valid menampilkan detail dan dapat diserahkan; status booking dan unit berubah konsisten; satu baris di `activity_logs` dan `console_movements`; kode yang sama tidak bisa dipakai dua kali; kartu armada menampilkan pemegang, batas kembali, dan sisa waktu; keterlambatan dihitung benar (90 menit menjadi 2 jam menjadi Rp40.000); Tandai Selesai mengembalikan unit ke `Tersedia`; email terima kasih tercatat di log; pelanggan yang membuka alamat admin mendapat 403.

**Catatan dan kejadian saat L8:**
- Untuk menguji jendela waktu dan keterlambatan, jadwal booking uji digeser lewat `php artisan tinker --execute="App\Models\Booking::where('kode_booking','KODE')->update([...]);"` (ubah `waktu_mulai` dan `waktu_selesai`). Hanya untuk data uji.
- Unit berstatus `Disewa` tidak bisa menerima serah terima booking lain; selesaikan penyewaan sebelumnya dulu.
- Menu admin lain (Pesanan, Laporan, dll.) baru aktif di L10 dan L11; menu Perangkat digabung ke "Jadwal & Lokasi" untuk status unit, sedangkan pengelolaan data unit ada di L10.

### 10.12 Rancangan L9: pelacakan pengantaran (untuk dikonfirmasi di awal L9)

**Tujuan:** pesanan dengan cara penerimaan **Diantar** punya status pengantaran yang bisa diubah admin dan dilihat pelanggan sebagai linimasa di halaman pesanan. Status: `Menunggu Dikirim` lalu `Kurir Ditugaskan` lalu `Dalam Perjalanan` lalu `Telah Diterima`; cabang `Gagal Dikirim`.

**Yang dibutuhkan di awal L9 (kirim outputnya):**

```powershell
mysql -u root gamebox_rent -e 'DESCRIBE deliveries;'
mysql -u root gamebox_rent -e 'DESCRIBE delivery_events;'
```

**Usulan isi L9:**
- Catatan pengantaran (`deliveries`) dibuat otomatis ketika booking Diantar menjadi `Dibayar`; setiap perubahan status menambah baris di `delivery_events` (linimasa dengan waktu dan pelaku).
- Halaman admin untuk daftar pengantaran dan mengubah status (menugaskan kurir, berangkat, tiba, gagal) beserta catatan; kurir dicatat sebagai **nama** (tanpa akun kurir).
- Linimasa status di halaman pesanan pelanggan (hanya untuk pesanan Diantar).
- Saat status mencapai `Telah Diterima`, admin memasukkan kode konfirmasi pelanggan sehingga serah terima unit (booking `Sedang Disewa`) berjalan lewat `HandoverService` yang sama.
- Aksi dicatat di `activity_logs`.

**Pertanyaan yang dikonfirmasi di awal L9:**
1. Kurir dicatat sebagai nama (usulan) atau perlu akun kurir sendiri?
2. `Telah Diterima` wajib memasukkan kode konfirmasi dan otomatis melakukan serah terima (usulan), atau terpisah dari menu Konfirmasi Kode?
3. Perlu email ke pelanggan saat status "Dalam Perjalanan" (usulan: tidak dulu)?

**Hasil yang benar di akhir L9:** pesanan Diantar mendapat catatan pengantaran otomatis setelah dibayar; perubahan status berurutan dan tercatat di linimasa; urutan status yang salah ditolak; pelanggan melihat linimasa; serah terima lewat pengantaran konsisten dengan L8; commit `L9: pelacakan pengantaran`.

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

### L6
- [x] Akun Midtrans Pedagang + Sandbox, Server Key dan Client Key di `.env` sendiri
- [x] Paket `midtrans/midtrans-php`, `config/midtrans.php`, `.env.example` (nilai kosong)
- [x] Output `DESCRIBE payments`, `payment_methods`, `blocked_dates` dikirim (`blocked_dates` memakai kolom `tanggal`, sesuai `BookingService`)
- [x] Model `Payment`, `PaymentService`, `PaymentController`, route bayar + cek status + webhook, popup Snap, riwayat `/pesanan`
- [x] Pembayaran sandbox sukses mengubah booking menjadi `Dibayar` dan menerbitkan kode konfirmasi
- [x] 12 tes lolos
- [x] Commit `L6: pasang paket Midtrans dan konfigurasi sandbox`, lalu `L6: pembayaran midtrans`
- [ ] (kecil, nanti) uji webhook asli dengan tunnel dan isi Payment Notification URL di dasbor Sandbox

### L7
- [x] Output `DESCRIBE email_templates` dan `SELECT * FROM email_templates` dikirim
- [x] Keputusan: email lewat `log`, invoice dibuat saat diminta, email langsung
- [x] Paket `barryvdh/laravel-dompdf` dipasang (hotspot HP)
- [x] Migrasi `email_pembayaran_at`; `EmailTemplateService`, `InvoiceService`, `NotifikasiService`, `PembayaranBerhasilMail`, `InvoiceController`, perintah `email:kirim-pembayaran`, tampilan invoice dan email
- [x] Invoice PDF terunduh dari halaman pesanan; email dengan lampiran tercatat di log, hanya sekali per pembayaran
- [x] Tes lolos (termasuk kirim manual untuk booking lama)
- [x] Commit `L7: invoice dan email`

### L8
- [x] Output `DESCRIBE activity_logs`, `DESCRIBE console_movements`, status unit di `consoles` dikirim
- [x] Keputusan: denda per jam, jendela pengambilan 1 jam sebelum sampai toleransi 30 menit, email terima kasih
- [x] Menu admin Konfirmasi Kode dan Jadwal & Lokasi aktif
- [x] Serah terima (`Sedang Disewa`) dan Tandai Selesai (`Selesai`, denda, unit `Tersedia`) dalam transaksi, tercatat di log
- [x] Tes lolos
- [x] Commit `L8: admin serah terima`

### L9 (berikutnya)
- [ ] Output `DESCRIBE deliveries` dan `DESCRIBE delivery_events` dikirim
- [ ] Keputusan: kurir sebagai nama, kode konfirmasi saat Telah Diterima, email Dalam Perjalanan
- [ ] Catatan pengantaran otomatis untuk pesanan Diantar yang sudah dibayar, halaman admin, linimasa pelanggan
- [ ] Commit `L9: pelacakan pengantaran`

---

## 14. Cara melanjutkan

1. Selesaikan checklist langkah yang sedang berjalan.
2. Kirim bukti: output perintah cek atau screenshot halaman.
3. Tulis **"lanjut L9"** (atau langkah berikutnya).

Jika memulai percakapan baru, lampirkan README ini + `PRD_GameBox_Rent.pdf` + README dan zip proyek Apps Script lama (acuan aturan bisnis dan tes) + `stitch_prd_visual_interface_prototype.zip`, lalu tulis:

> Saya memindahkan proyek GameBox Rent dari Google Apps Script ke Laravel 13.40 (PHP 8.3, Blade + Tailwind + MySQL via Laragon di Windows, pembayaran Midtrans sandbox). L1, L2 (migrasi database + seeder), L3 (layout, login pelanggan dan admin terpisah, role + middleware), L4 (beranda dan katalog unit), L5 (booking: jadwal, bentrok + buffer, harga dihitung server), dan L6 (pembayaran Midtrans sandbox: popup Snap, cek status, kode konfirmasi, riwayat pesanan) L7 (invoice PDF dan email konfirmasi pembayaran), dan L8 (admin konfirmasi kode, serah terima, tandai selesai, armada) sudah selesai, lolos tes, dan di-commit. Baca README_Laravel.md. Lanjutkan dari **L9: pelacakan pengantaran untuk pesanan Diantar (status, linimasa, halaman admin)**, step by step, beri kode dan README per langkah (jangan zip), jalankan satu perintah per baris, dan sebutkan file mana yang dibuat baru atau diubah.
