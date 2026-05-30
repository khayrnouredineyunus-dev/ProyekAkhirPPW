# MiniFut — Panduan Setup & Penggunaan

## Struktur Folder

```
ProyekAkhirPPW/
├── index.php              ← Landing page (dilindungi auth login pelanggan)
├── booking.php            ← Halaman booking (dilindungi auth login pelanggan)
├── config.php             ← Konfigurasi DB & helper functions
├── migration.sql          ← SQL tambahan untuk kolom baru (jalankan 1x)
├── .htaccess              ← Keamanan akses file
│
├── auth/
│   ├── login.php          ← Login pelanggan
│   ├── register.php       ← Registrasi pelanggan baru
│   └── logout.php         ← Logout pelanggan
│
└── admin/
    ├── login.php          ← Login admin
    ├── logout.php         ← Logout admin
    ├── dashboard.php      ← Dashboard statistik
    ├── lapangan.php       ← CRUD lapangan + upload foto lapangan
    ├── pelanggan.php      ← CRUD pelanggan + upload foto profil
    ├── booking.php        ← CRUD booking + search + pagination
    ├── jadwal.php         ← CRUD jadwal + search + pagination
    ├── pembayaran.php     ← CRUD & konfirmasi pembayaran
    ├── _header.php        ← Shared layout header + sidebar
    ├── _footer.php        ← Shared layout footer
    └── uploads/           ← Folder upload foto (auto-created)
        └── .htaccess      ← Blokir eksekusi PHP di folder upload
```

---

## Langkah Setup

### 1. Tempatkan Folder
Letakkan folder `ProyekAkhirPPW/` di dalam `htdocs/` (XAMPP) atau `www/` (WAMP).

```
C:\xampp\htdocs\ProyekAkhirPPW\
```

### 2. Buat Database
Buka **phpMyAdmin** → buat database `minifut_db` → jalankan DDL utama dari tugas:

```sql
CREATE DATABASE minifut_db;
USE minifut_db;
-- (paste DDL tabel Lapangan, Pelanggan, Jadwal, Booking, Pembayaran + INSERT data lapangan)
```

### 3. Jalankan Migration SQL
Setelah database dibuat, jalankan `migration.sql` di phpMyAdmin untuk menambahkan kolom baru:

```sql
-- Buka tab SQL di phpMyAdmin, paste isi migration.sql dan klik Go
```

### 4. Konfigurasi Database (jika perlu)
Edit `config.php` sesuai konfigurasi MySQL Anda:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'minifut_db');
define('DB_USER', 'root');
define('DB_PASS', '');         // ← Isi password MySQL jika ada
```

### 5. Buat Folder Upload
Folder `admin/uploads/` akan dibuat otomatis saat pertama kali upload foto.  
Pastikan XAMPP/PHP memiliki izin tulis ke folder tersebut.

---

## Akses Website

| Halaman | URL |
|---|---|
| Landing Page | `http://localhost/ProyekAkhirPPW/` |
| Halaman Booking | `http://localhost/ProyekAkhirPPW/booking.php` |
| Login Pelanggan | `http://localhost/ProyekAkhirPPW/auth/login.php` |
| Daftar Akun Baru | `http://localhost/ProyekAkhirPPW/auth/register.php` |
| Login Admin | `http://localhost/ProyekAkhirPPW/admin/login.php` |
| Dashboard Admin | `http://localhost/ProyekAkhirPPW/admin/dashboard.php` |

---

## Kredensial Default

### Admin
| Field | Value |
|---|---|
| Username | `admin` |
| Password | `Admin@Minifut2025` |

> ⚠️ **Ganti password admin** di file `admin/login.php` baris `$ADMIN_PASS`.

### Pelanggan
Daftar akun baru melalui halaman **Register** (`auth/register.php`).

---

## Fitur yang Ditambahkan

### ✅ 1. Autentikasi (Login/Logout)
- **Pelanggan**: harus login sebelum bisa akses `index.php` dan `booking.php`
- **Admin**: panel terpisah dengan login khusus di `/admin/login.php`
- Session aman dengan `session_regenerate_id()` dan cookie `httponly`

### ✅ 2. Upload Foto
- **Foto Lapangan**: upload di halaman Admin → Lapangan (format JPG/PNG/WebP, maks 5MB)
- **Foto Profil Pelanggan**: upload di halaman Admin → Pelanggan, atau bisa diatur sendiri
- File disimpan di `admin/uploads/` dengan nama unik (anti-collision)

### ✅ 3. Pencarian Data (Search)
- **Lapangan**: cari berdasarkan nama, jenis, status
- **Pelanggan**: cari berdasarkan nama, email, nomor telepon
- **Booking**: cari berdasarkan kode booking, nama, email, nama lapangan; filter by status
- **Jadwal**: cari berdasarkan nama lapangan, tanggal, status
- **Pembayaran**: cari berdasarkan kode booking, nama, metode; filter by status

### ✅ 4. Pagination
- Semua tabel admin: 10 data per halaman
- Navigasi Prev/Next + nomor halaman
- Info jumlah data ditampilkan

### ✅ 5. Validasi Data Lengkap
- **Register**: nama min 3 karakter, email valid, telepon 8-20 digit, password min 8 karakter + kapital + angka, konfirmasi password
- **Admin CRUD**: validasi wajib isi, format email, cek duplikat email, cek relasi foreign key sebelum hapus
- **Upload**: validasi tipe file (image only), ukuran max 5MB, ekstensi aman
- **Booking**: cek race condition slot, validasi lapangan & tanggal wajib diisi

---

## Keamanan

- ✅ Password di-hash dengan `password_hash()` (bcrypt)
- ✅ Semua query pakai **Prepared Statements** (anti SQL Injection)
- ✅ Output di-escape dengan `htmlspecialchars()` (anti XSS)
- ✅ Session cookie: `httponly`, `samesite=Strict`
- ✅ Folder `uploads/` diproteksi `.htaccess` (PHP tidak bisa dieksekusi)
- ✅ File sensitif (`config.php`) diblokir akses langsung via `.htaccess`
- ✅ Delay 1 detik pada login admin gagal (anti brute-force)

---

## Troubleshooting

**Tidak bisa upload foto?**  
→ Pastikan folder `admin/uploads/` ada dan PHP punya izin write.  
→ Di Windows XAMPP biasanya tidak perlu setting ekstra.

**Login gagal padahal password benar?**  
→ Pastikan database sudah diisi data pelanggan dengan password yang di-hash `password_hash()`.  
→ Data lama dari booking.php menggunakan password `password123` (hash otomatis).

**Error "Kolom FOTO tidak ditemukan"?**  
→ Jalankan `migration.sql` terlebih dahulu.

**Session tidak tersimpan?**  
→ Pastikan `session.save_path` di `php.ini` sudah dikonfigurasi, atau coba restart XAMPP.