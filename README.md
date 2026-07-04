<div align="center">

# Cashflow System

Aplikasi pencatatan cashflow ringan berbasis CodeIgniter 4, SQLite, Vue, Tailwind, dan PWA.

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4-EF4223?style=flat-square&logo=codeigniter&logoColor=white)](https://codeigniter.com/)
[![SQLite](https://img.shields.io/badge/SQLite-3-003B57?style=flat-square&logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![License](https://img.shields.io/badge/License-MIT%20with%20Attribution-blue?style=flat-square)](LICENSE)

Repository: [github.com/ZidanNaaf/cashflow-system](https://github.com/ZidanNaaf/cashflow-system)

</div>

## Ringkasan

Cashflow System adalah aplikasi web untuk mencatat pemasukan dan pengeluaran, memantau saldo, mengelola kategori, mengatur user berdasarkan role, dan mengekspor laporan. Aplikasi ini dibuat agar mudah dipasang, cocok untuk kebutuhan personal, internal bisnis kecil, atau starter project open source berbasis CodeIgniter 4.

Frontend menggunakan CDN agar sederhana: Vue 3, Tailwind CSS, SweetAlert2, Chart.js, SheetJS, dan jsPDF. Database default memakai SQLite supaya setup awal tidak perlu membuat database server.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Role dan Akses](#role-dan-akses)
- [Tech Stack](#tech-stack)
- [Requirement](#requirement)
- [Instalasi Cepat](#instalasi-cepat)
- [Akun Default](#akun-default)
- [Konfigurasi Production](#konfigurasi-production)
- [Export dan Grafik](#export-dan-grafik)
- [PWA](#pwa)
- [Keamanan](#keamanan)
- [Struktur Proyek](#struktur-proyek)
- [Command Berguna](#command-berguna)
- [Kontribusi](#kontribusi)
- [Lisensi](#lisensi)

## Fitur Utama

| Area | Fitur |
| --- | --- |
| Dashboard | Saldo saat ini, pemasukan bulan ini, pengeluaran bulan ini |
| Grafik | Grafik pemasukan dan pengeluaran 6 bulan terakhir dengan Chart.js |
| Transaksi | Tambah, edit, hapus, dan filter transaksi |
| Kategori | Kategori pemasukan dan pengeluaran terpisah |
| Export | Export data terfilter ke Excel `.xlsx` dan PDF |
| User | Multi-user dengan role admin dan superadmin |
| Setting | Setting sistem, logo custom, kategori, dan user via popup/modal |
| Auth | Login, logout, remember me, show/hide password |
| PWA | Manifest, service worker, offline fallback, tombol install |
| Security | CSRF, secure headers, role filter, auth filter, login throttling |

## Role dan Akses

| Role | Akses |
| --- | --- |
| `admin` | Input, edit, hapus, filter, export transaksi |
| `superadmin` | Semua akses admin, plus setting sistem, logo, kategori, dan user |

## Tech Stack

- PHP 8.2+
- CodeIgniter 4
- SQLite3
- Vue 3 via CDN
- Tailwind CSS via CDN
- SweetAlert2 via CDN
- Chart.js via CDN
- SheetJS via CDN
- jsPDF + AutoTable via CDN
- PWA Web Manifest + Service Worker

## Requirement

Extension PHP yang diperlukan:

- `intl`
- `mbstring`
- `sqlite3`
- `fileinfo`

Opsional:

- `curl`
- `gd` atau `imagick` untuk pengembangan fitur image processing di masa depan

## Instalasi Cepat

Clone repository:

```bash
git clone https://github.com/ZidanNaaf/cashflow-system.git
cd cashflow-system
```

Install dependency jika menggunakan Composer:

```bash
composer install
```

Siapkan file `.env` di root project:

```bash
cp .env.example .env
```

Isi minimalnya tersedia di `.env.example`:

```env
CI_ENVIRONMENT = production
app.baseURL = 'http://localhost:8080/'
app.forceGlobalSecureRequests = false
cookie.secure = false
encryption.key = ganti_dengan_key_random_yang_kuat
```

Generate encryption key:

```bash
php spark key:generate
```

Jika `.env` sudah memiliki key dan ingin menggantinya:

```bash
php spark key:generate --force
```

Jalankan migration dan seeder:

```bash
php spark migrate --all
php spark db:seed InitialSeeder
```

Jalankan server lokal:

```bash
php -S 127.0.0.1:8080 -t public system/rewrite.php
```

Buka aplikasi:

```text
http://127.0.0.1:8080/login
```

## Akun Default

Seeder hanya membuat satu akun awal:

```text
Email    : superadmin@cashflow.local
Password : superadmin123
Role     : superadmin
```

Ganti password superadmin segera setelah login pertama.

## Konfigurasi Production

Untuk production, ubah `.env`:

```env
CI_ENVIRONMENT = production
app.baseURL = 'https://domain-kamu.com/'
app.forceGlobalSecureRequests = true
cookie.secure = true
```

Checklist sebelum deploy:

- Web server mengarah ke folder `public`, bukan root project.
- HTTPS aktif.
- `encryption.key` unik dan kuat.
- Password default superadmin sudah diganti.
- File SQLite di `writable/database/cashflow.db` tidak bisa diakses publik.
- Folder `writable` bisa ditulis oleh web server.
- Backup SQLite sudah dijadwalkan.
- `.env` production tidak ikut dipublikasi.

## SQLite

Database default tersimpan di:

```text
writable/database/cashflow.db
```

SQLite cocok untuk deployment kecil, single server, internal tool, atau penggunaan personal. Untuk traffic tinggi dan banyak user aktif bersamaan, pertimbangkan migrasi ke MySQL atau PostgreSQL.

## Export dan Grafik

Menu `Data` menyediakan export berdasarkan filter aktif:

- `Export Excel` menghasilkan file `.xlsx` memakai SheetJS.
- `Export PDF` menghasilkan file `.pdf` memakai jsPDF dan AutoTable.

Dashboard menampilkan grafik pemasukan dan pengeluaran 6 bulan terakhir. Data grafik diambil dari endpoint:

```text
GET /api/reports/monthly
```

Karena fitur export dan chart memakai CDN, browser client perlu akses internet ke CDN tersebut. Jika ingin full self-hosted/offline, pindahkan library terkait ke folder `public`.

## PWA

File PWA berada di:

```text
public/manifest.webmanifest
public/sw.js
public/offline.html
public/icons/
```

Android Chrome/Edge dapat menampilkan prompt install. iOS Safari tidak menyediakan prompt otomatis, sehingga pengguna perlu membuka menu Share lalu memilih Add to Home Screen.

PWA install di perangkat nyata umumnya membutuhkan HTTPS, kecuali saat berjalan di localhost.

## Keamanan

Yang sudah diterapkan:

- Password menggunakan `password_hash`.
- Remember me memakai token acak yang disimpan dalam bentuk hash.
- CSRF global aktif.
- Secure headers aktif.
- Role filter untuk endpoint superadmin.
- Auth filter untuk halaman aplikasi dan API.
- Login throttling sederhana.
- Upload logo dibatasi MIME, ukuran, dan validasi image.
- Folder upload memblok eksekusi script melalui `.htaccess`.

Hal yang perlu tetap dijaga:

- Ganti credential default setelah install.
- Gunakan HTTPS di production.
- Jangan commit `.env` production.
- Backup SQLite secara rutin.
- Review CSP jika ingin mengaktifkan `app.CSPEnabled`, karena aplikasi memakai beberapa CDN.

## Struktur Proyek

```text
app/Controllers/Auth.php              Login, logout, remember me
app/Controllers/App.php               Halaman dashboard utama
app/Controllers/Api/                  Endpoint JSON aplikasi
app/Filters/AuthFilter.php            Proteksi login
app/Filters/RoleFilter.php            Proteksi role
app/Libraries/RememberMe.php          Remember-me token
app/Models/                           Model data
app/Database/Migrations/              Struktur database
app/Database/Seeds/InitialSeeder.php  Seeder akun awal dan setting
app/Views/auth/login.php              Halaman login
app/Views/app/index.php               UI dashboard Vue
public/                               Document root web server
public/manifest.webmanifest           Manifest PWA
public/sw.js                          Service worker
writable/database/cashflow.db         Database SQLite
```

## Command Berguna

```bash
# Jalankan migration
php spark migrate --all

# Lihat status migration
php spark migrate:status

# Seed data awal
php spark db:seed InitialSeeder

# Lihat route
php spark routes

# Jalankan server lokal
php -S 127.0.0.1:8080 -t public system/rewrite.php

# Lint file PHP
php -l app/Controllers/Auth.php
```

## Kontribusi

Kontribusi terbuka untuk siapa pun. Alur yang disarankan:

1. Fork repository.
2. Buat branch fitur atau perbaikan.
3. Lakukan perubahan dengan scope yang jelas.
4. Jalankan pengecekan manual atau otomatis yang relevan.
5. Buat pull request dengan deskripsi perubahan.

Sebelum membuka pull request, pastikan:

- Tidak ada credential production di commit.
- Migration baru aman untuk database yang sudah berjalan.
- Akses role `admin` dan `superadmin` tetap sesuai.
- UI tetap nyaman di desktop dan mobile.

## Roadmap Ide

- Import transaksi.
- Backup dan restore database dari UI superadmin.
- Audit log perubahan transaksi.
- Multi-currency.
- Mode database MySQL/PostgreSQL.
- Test suite untuk controller/API.
- Self-host semua asset CDN untuk mode offline penuh.

## Kredit

Cashflow System dibuat pertama kali oleh [ZidanNaaf](https://github.com/ZidanNaaf).

Framework utama menggunakan [CodeIgniter 4](https://codeigniter.com/), yang dirilis dengan MIT License oleh British Columbia Institute of Technology dan CodeIgniter Foundation.

## Lisensi

Proyek ini menggunakan MIT License dengan atribusi pembuat pertama. Artinya proyek boleh dipakai, dimodifikasi, dan didistribusikan, tetapi notice copyright dan atribusi pembuat pertama harus tetap dicantumkan.

Lihat detail lengkap di file [LICENSE](LICENSE).
