# PT Andalan Beton — Sistem Monitoring Web

Sistem monitoring persediaan, distribusi material, dan pemesanan online berbasis web.

## Teknologi
- PHP Native (tanpa framework)
- PostgreSQL via Supabase
- PDO PostgreSQL
- HTML5 + CSS3 + JavaScript ES6
- Chart.js
- Font Awesome 6

---

## Instalasi Cepat

### 1. Konfigurasi Database Supabase

Buka file `config/database.php` dan ubah:

```php
define('DB_HOST', 'db.xxxxxxxxxxxxxxxxxxxx.supabase.co');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres');
define('DB_PASS', 'password_supabase_anda');
```

### 2. Import Schema Database

Masuk ke Supabase Dashboard → SQL Editor → paste isi file `database/schema.sql` → Run.

### 3. Buat Folder Uploads

```bash
mkdir -p uploads/products uploads/company
chmod -R 755 uploads/
```

### 4. Jalankan Server

```bash
cd andalan-beton
php -S localhost:8000
```

### 5. Akses Website

| URL | Keterangan |
|-----|-----------|
| http://localhost:8000 | Website Publik |
| http://localhost:8000/admin/login.php | Login Admin |

---

## Akun Default Admin

| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `admin123` |

> **Ganti password** setelah pertama login via menu Pengaturan!

---

## Struktur Proyek

```
andalan-beton/
├── index.php              # Halaman utama (publik)
├── profil.php             # Profil perusahaan
├── produk.php             # Katalog produk
├── galeri.php             # Galeri foto
├── kontak.php             # Halaman kontak
├── pesan.php              # Form pemesanan online
│
├── admin/
│   ├── login.php          # Login admin
│   ├── logout.php         # Logout
│   ├── dashboard.php      # Dashboard utama
│   ├── partials/          # Header, sidebar, footer admin
│   └── modules/
│       ├── material/      # CRUD material
│       ├── stock_in/      # CRUD barang masuk
│       ├── stock_out/     # CRUD barang keluar
│       ├── orders/        # Manajemen pesanan
│       ├── reports/       # Laporan & export PDF
│       └── settings/      # Pengaturan sistem
│
├── assets/
│   ├── css/main.css       # Design system lengkap
│   └── js/main.js         # JavaScript utama
│
├── config/
│   ├── database.php       # Koneksi PDO PostgreSQL
│   └── app.php            # Konfigurasi & helper functions
│
├── database/
│   └── schema.sql         # Schema + seed data
│
├── includes/
│   ├── public_head.php    # Header publik
│   └── public_footer.php  # Footer publik
│
└── uploads/               # File upload (foto produk, logo)
```

---

## Fitur Utama

### Website Publik
- Halaman beranda dengan hero section dan animasi
- Profil perusahaan dengan visi & misi
- Katalog produk dengan filter kategori & pencarian
- Galeri foto dengan lightbox
- Halaman kontak dengan Google Maps
- Form pemesanan online multi-item

### Dashboard Admin
- Dashboard analitik dengan Chart.js (grafik mingguan, bulanan)
- Statistik real-time (stok, pesanan, pendapatan)
- Notifikasi stok menipis/habis
- Log aktivitas terbaru

### Manajemen Inventori
- CRUD material dengan upload foto
- Pencatatan barang masuk (dengan supplier & invoice)
- Pencatatan barang keluar (dengan driver & kendaraan)
- Kalkulasi stok otomatis via PostgreSQL VIEW

### Manajemen Pesanan
- Pesanan masuk dari website publik
- Update status: Menunggu → Diproses → Selesai / Ditolak
- Detail pesanan dengan item breakdown

### Laporan
- Rekap barang masuk & keluar
- Rekap pesanan
- Ringkasan stok semua material
- Export PDF (print-friendly)

### Pengaturan
- Profil perusahaan lengkap (nama, alamat, kontak, sosmed)
- Upload logo
- SEO meta title & description
- Ganti password admin

---

## Koneksi Supabase

1. Buat akun di [supabase.com](https://supabase.com)
2. Buat project baru
3. Settings → Database → Connection string
4. Salin host, password ke `config/database.php`
5. SQL Editor → paste `database/schema.sql` → Run

---

## Deployment Hosting PHP (cPanel/Niagahoster)

1. Upload semua file ke `public_html/`
2. Update `APP_URL` di `config/app.php`
3. Pastikan PHP 8.0+ dengan ekstensi `pdo_pgsql`
4. Set permission folder `uploads/` ke 755
5. Import schema ke database

---

## Keamanan

- Prepared Statement PDO (SQL Injection protection)
- CSRF Token pada semua form
- Session security & regenerate ID
- Validasi & sanitasi semua input
- Upload file validation (mime type + ekstensi)
- Password hashing dengan `password_hash()`

---

## Reset Password Admin

Jalankan di Supabase SQL Editor:

```sql
UPDATE users 
SET password = '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TiJVVGDiSCU.lKUuQqwPJyuEpOoy'
WHERE username = 'admin';
-- Password: admin123
```

---

**PT Andalan Beton** © <?= date('Y') ?> | Sistem dibuat untuk keperluan skripsi/seminar
