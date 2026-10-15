# Sistem Data WNA

Aplikasi web internal keimigrasian untuk pengelolaan data, administrasi, dan pemantauan masa berlaku dokumen Warga Negara Asing (WNA), Pendaftaran ABG, Affidavit, Permohonan, Laporan Izin Tinggal, Penerbitan, Realisasi PNBP, serta Manajemen Pengguna.

## Tech Stack

- **Backend:** Laravel 13, PHP 8.3+, Laravel Fortify (Autentikasi Sesi & Rate Limiting)
- **Frontend:** Inertia.js v3, Vue 3 (Composition API), TypeScript, Tailwind CSS, Lucide Icons, Radix Vue / shadcn-vue
- **Database:** MySQL 8 / MariaDB (InnoDB, utf8mb4)
- **Code Quality & Testing:** Pest PHP, PHPStan, Laravel Pint, Vue-TSC, Vite

---

## Modul & Matriks Hak Akses

| Modul | Administrator | Petugas (User) |
| :--- | :--- | :--- |
| **Dashboard** | Statistik Lengkap & Metrik Finansial | Statistik Operasional WNA/ABG/Affidavit |
| **Data WNA** | CRUD Penuh, Ekspor CSV, Cetak PDF Daftar, Cetak Kartu | Lihat Data & Cetak Kartu Individu |
| **Pendaftaran ABG** | CRUD Penuh, Ekspor CSV, Cetak PDF Daftar | Lihat Data |
| **Pendaftaran Affidavit** | CRUD Penuh, Ekspor CSV, Cetak PDF Daftar | Lihat Data |
| **Early Warning** | Pemantauan & Filter Kedaluwarsa 3 Bulan Kalender | Pemantauan & Filter Kedaluwarsa 3 Bulan Kalender |
| **Permohonan** | CRUD Penuh & Cetak PDF | Dibatasi (403 Forbidden) |
| **Laporan Izin Tinggal** | CRUD Penuh & Cetak PDF Rekapitulasi Gender L/P | Dibatasi (403 Forbidden) |
| **Penerbitan** | CRUD Penuh & Cetak PDF | Dibatasi (403 Forbidden) |
| **Realisasi PNBP** | CRUD Penuh, Validasi Tanggal Awal Bulan, Cetak PDF | Dibatasi (403 Forbidden) |
| **Data User** | Pengelolaan Akun Pengguna & Proteksi Anti-Lockout | Dibatasi (403 Forbidden) |
| **Pengaturan Aplikasi**| Identitas Sistem, Nama Satuan Kerja & Unggah Logo Resmi | Dibatasi (403 Forbidden) |
| **Edit Profile** | Kelola Profil Mandiri, Avatar, dan Ganti Kata Sandi | Kelola Profil Mandiri, Avatar, dan Ganti Kata Sandi |

---

## Persyaratan Sistem

- PHP >= 8.3 (dengan ekstensi `pdo_mysql`, `mbstring`, `fileinfo`, `gd`/`imagick`, `curl`)
- Composer >= 2.7
- Node.js >= 20.x dan npm
- MySQL >= 8.0 atau MariaDB >= 10.4 (misalnya via MAMP)

---

## Panduan Instalasi & Konfigurasi

### 1. Kloning & Dependensi
```bash
git clone <repository-url>
cd Laravel13Inertia-SistemDataWNA

# Instal dependensi PHP dan JavaScript
composer install
npm install
```

### 2. Konfigurasi Lingkungan (`.env`)
Salin file konfigurasi lingkungan dan buat kunci aplikasi:
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan parameter koneksi database pada `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889         # Sesuaikan dengan port MySQL (misal 8889 pada MAMP)
DB_DATABASE=db_data_wna
DB_USERNAME=root
DB_PASSWORD=root
```

### 3. Migrasi Database & Seeder
Jalankan migrasi skema tabel dan seeder akun awal:
```bash
php artisan migrate --seed
```

### 4. Storage Link
Hubungkan direktori publik untuk penyimpanan aset publik (ikon aplikasi):
```bash
php artisan storage:link
```
> **Catatan Keamanan Berkas:** Berkas foto pribadi (WNA, ABG, Affidavit, foto profil akun) disimpan di disk lokal privat (`storage/app/private/` atau `storage/app/`) dan disajikan secara aman via controller streaming terotorisasi dengan pencegahan *path traversal*.

---

## Kredensial Pengguna Awal (Development Only)

Seeder `DevelopmentUserSeeder` menyediakan akun pengujian lokal:

- **Administrator:**
  - Username: `admin`
  - Password: `password` (dikonfigurasi via `DEV_ADMIN_PASSWORD` pada `.env`)
- **Petugas:**
  - Username: `petugas`
  - Password: `password` (dikonfigurasi via `DEV_PETUGAS_PASSWORD` pada `.env`)

> **PENTING:** Kredensial ini hanya untuk lingkungan pengembangan lokal dan dicegah berjalan di environment `production`.

---

## Menjalankan Aplikasi

### Development Mode
Jalankan server aplikasi lokal dan Vite bundler secara paralel:
```bash
# Terminal 1 - Vite development server
npm run dev

# Terminal 2 - Laravel server (jika tidak menggunakan virtual host MAMP)
php artisan serve
```
Akses aplikasi melalui peramban: `http://localhost:8000` (atau `http://localhost:8888/Laravel13Inertia-SistemDataWNA/public` pada MAMP).

---

## Quality Gates & Pengujian Otomatis

Seluruh modul dilengkapi dengan rangkaian pengujian komprehensif. Perintah verifikasi mutu kode:

```bash
# 1. Jalankan seluruh test suite fitur & unit (Pest)
php artisan test --compact

# 2. Analisis statis tipe & arsitektur PHP
./vendor/bin/phpstan analyse

# 3. Pemeriksaan & perapian gaya kode PHP PSR-12
./vendor/bin/pint --dirty --format agent

# 4. Pengecekan tipe statis TypeScript & Vue SFC
npm run types:check

# 5. Build bundle aset produksi
npm run build

# 6. Pemeriksaan whitespace & conflict marker git
git diff --check
```

---

## Fitur Keamanan Unggulan

1. **Role-Based Access Control (RBAC):** Seluruh rute dilindungi di level server melalui Laravel Policies dan middleware `EnsureUserRole`. Upaya manipulasi ID atau URL menghasilkan respon `403 Forbidden`.
2. **Pencegahan Anti-Lockout:** Admin dicegah menghapus atau mendemosikan akunnya sendiri, serta dicegah mendemosi atau menghapus akun Administrator aktif terakhir.
3. **Penyimpanan Berkas Privat & Streaming Aman:** Validasi MIME, ekstensi, batas ukuran 2MB, directory jail, serta pencegahan traversal `..` dan null-byte injection.
4. **Perlindungan Injeksi Formula CSV:** Seluruh ekspor CSV disanitasi via `CsvSanitizer` untuk mencegah eksekusi formula jahat (`=`, `+`, `-`, `@`, `|`, `%`).
5. **Sanitasi Data Sensitif:** Password hash, token sesi, dan secret autentikasi secara ketat dikecualikan dari serialisasi model (`#[Hidden]`) dan tidak pernah dikirim ke props Inertia.
