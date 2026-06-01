# MiniFut — Panduan Setup & Penggunaan

> **MiniFut** adalah sistem manajemen arena mini soccer (futsal) premium berbasis web.  
> Dibangun dengan PHP native + MySQL + desain UI/UX dark-mode premium bertema hijau neon.

---

## Struktur Folder

```
ProyekAkhirPPW/
├── index.php              ← Landing page (dilindungi auth login pelanggan)
├── index.html             ← Landing page versi statis (HTML murni, tanpa auth)
├── booking.php            ← Halaman booking + API back-end (5 tabel, validasi lengkap)
├── booking.html           ← Halaman booking versi statis (HTML murni, demo frontend)
├── profile.php            ← Halaman profil pelanggan (edit info, foto, password, riwayat)
├── config.php             ← Konfigurasi DB, session, auth, upload & helper functions
├── migration.sql          ← SQL migrasi kolom baru (jalankan 1x setelah DDL utama)
├── .htaccess              ← Keamanan akses file sensitif
│
├── assets/                ← Aset gambar statis (foto fasilitas, dll)
│   ├── bruno.jpeg
│   ├── cafe.png
│   ├── fasilitas.png
│   ├── parkir.png
│   ├── ronaldo.jpeg
│   └── tribun.png
│
├── auth/
│   ├── login.php          ← Login pelanggan (email + password, shader background)
│   ├── register.php       ← Registrasi pelanggan baru (validasi lengkap)
│   └── logout.php         ← Logout pelanggan
│
└── admin/
    ├── login.php          ← Login admin (shader background, gold accent)
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

Kolom yang ditambahkan oleh migrasi:
- `Lapangan.FOTO` — nama file foto lapangan
- `Pelanggan.FOTO_PROFIL` — nama file foto profil
- `Booking.TEAM_NAME` — nama tim yang booking
- `Booking.NOTES` — catatan tambahan pelanggan
- `Pembayaran.STATUS_PEMBAYARAN` — default `PENDING`

> ℹ️ Kolom sosial media (`SOSMED_INSTAGRAM`, `SOSMED_TWITTER`, dll.) pada tabel Pelanggan dibuat otomatis saat pertama kali membuka halaman profil.

### 4. Konfigurasi Database (jika perlu)
Edit `config.php` sesuai konfigurasi MySQL Anda:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'minifut_db');
define('DB_USER', 'root');
define('DB_PASS', '');         
```

### 5. Buat Folder Upload
Folder `admin/uploads/` akan dibuat otomatis saat pertama kali upload foto.  
Pastikan XAMPP/PHP memiliki izin tulis ke folder tersebut.

---

## Akses Website

| Halaman | URL |
|---|---|
| Landing Page (PHP) | `http://localhost/ProyekAkhirPPW/index.php` |
| Landing Page (HTML) | `http://localhost/ProyekAkhirPPW/index.html` |
| Halaman Booking (PHP) | `http://localhost/ProyekAkhirPPW/booking.php` |
| Halaman Booking (HTML) | `http://localhost/ProyekAkhirPPW/booking.html` |
| Profil Pelanggan | `http://localhost/ProyekAkhirPPW/profile.php` |
| Login Pelanggan | `http://localhost/ProyekAkhirPPW/auth/login.php` |
| Daftar Akun Baru | `http://localhost/ProyekAkhirPPW/auth/register.php` |
| Login Admin | `http://localhost/ProyekAkhirPPW/admin/login.php` |
| Dashboard Admin | `http://localhost/ProyekAkhirPPW/admin/dashboard.php` |

> 💡 Versi `.html` adalah frontend statis tanpa koneksi database (untuk demo/presentasi). Versi `.php` adalah versi fungsional penuh.

---

## Kredensial Default

### Admin
| Field | Value |
|---|---|
| Username | `Khayr` |
| Password | `minifut107` |

> ⚠️ **Ganti password admin** di file `admin/login.php` baris `$ADMIN_PASS`.

### Pelanggan
Daftar akun baru melalui halaman **Register** (`auth/register.php`).

---

## Fitur Lengkap

### ✅ 1. Autentikasi (Login/Register/Logout)
- **Pelanggan**: login dengan email + password, harus login untuk akses `index.php`, `booking.php`, `profile.php`
- **Admin**: panel terpisah dengan login khusus di `/admin/login.php`
- Session aman dengan `session_regenerate_id()` dan cookie `httponly`, `samesite=Strict`
- Halaman login/register dilengkapi **WebGL shader background** animasi

### ✅ 2. Landing Page Premium (`index.html` / `index.php`)
- Hero section dengan **Three.js 3D soccer ball** interaktif
- Shrinking navbar saat scroll
- Showcase fasilitas dengan auto-slide + progress bar
- Grid gallery dengan lightbox
- Pricing cards dengan **rotating shine border** (conic-gradient)
- Fan Wall / awards marquee scroll
- SEO lengkap (meta title, description, Open Graph, Twitter Card)
- Custom cursor hijau neon (hidden di mobile/touch)
- Scroll-driven animations (GSAP ScrollTrigger)

### ✅ 3. Halaman Booking 4-Step (`booking.php`)
- **Step 1**: Pilih lapangan (3D tilt card + glare effect)
- **Step 2**: Pilih tanggal (custom calendar, disable tanggal lampau)
- **Step 3**: Pilih jam sesi (multi-select, **real-time dari database**, slot yang sudah dipesan ditandai)
- **Step 4**: Data diri (nama, email, telepon, tim, catatan, tipe pembayaran DP/Lunas)
- Sidebar ringkasan harga real-time
- Success overlay dengan kode booking, instruksi pembayaran, & countdown timer 5 menit

### ✅ 4. Halaman Profil Pelanggan (`profile.php`)
- **Tab Profil**: edit nama, email, telepon
- **Tab Foto**: upload/hapus foto profil (file lama otomatis dihapus dari server)
- **Tab Social Media**: isi link Instagram, Twitter, TikTok, Facebook, YouTube
- **Tab Password**: ubah password (validasi password lama, min 8 karakter + kapital + angka)
- **Tab Riwayat Booking**: daftar semua booking dengan status, harga, dan badge warna
- Sidebar statistik (total booking, lunas, proses)
- UI premium dark-mode dengan animasi, custom cursor, dan WebGL floating lines

### ✅ 5. Upload Foto
- **Foto Lapangan**: upload di halaman Admin → Lapangan (format JPG/PNG/WebP/GIF, maks 5MB)
- **Foto Profil Pelanggan**: upload di Admin → Pelanggan, atau self-service di `profile.php`
- File disimpan di `admin/uploads/` dengan nama unik `uniqid()` (anti-collision)
- File lama otomatis dihapus saat foto baru di-upload (pembersihan storage)

### ✅ 6. Pencarian Data (Search)
- **Lapangan**: cari berdasarkan nama, jenis, status
- **Pelanggan**: cari berdasarkan nama, email, nomor telepon
- **Booking**: cari berdasarkan kode booking, nama, email, nama lapangan; filter by status
- **Jadwal**: cari berdasarkan nama lapangan, tanggal, status
- **Pembayaran**: cari berdasarkan kode booking, nama, metode; filter by status

### ✅ 7. Pagination
- Semua tabel admin: 10 data per halaman
- Navigasi Prev/Next + nomor halaman
- Info jumlah data ditampilkan

### ✅ 8. Validasi Data Lengkap (Front-End & Back-End)

#### Validasi Back-End (`booking.php` API):
- **Lapangan**: cek ID valid, cek lapangan ada di database
- **Tanggal**: format YYYY-MM-DD, tanggal valid, tidak di masa lalu
- **Slot jam**: array tidak kosong, setiap slot antara 08–23, maks 16 slot, unik
- **Nama**: wajib, min 2 karakter, maks 100, hanya huruf/spasi/titik/tanda hubung
- **Email**: wajib, format valid (FILTER_VALIDATE_EMAIL), maks 150 karakter
- **Telepon**: wajib, format Indonesia (+62/62/08), 8–13 digit setelah prefix
- **Tipe pembayaran**: harus `dp` atau `lunas`
- **Race condition**: cek ulang slot sebelum insert (dalam transaksi)
- Semua error dikembalikan dalam `errors` object (field-specific)

#### Validasi Front-End (`booking.php` form):
- Real-time validation per field dengan visual error/success state
- Pesan error inline per input (icon ⚠ + animasi)
- Checkbox terms wajib dicentang
- Tombol submit disabled sampai semua validasi pass

#### Validasi Register (`auth/register.php`):
- Nama min 3 karakter
- Email valid + cek duplikat
- Telepon 8–20 digit
- Password min 8 karakter + huruf kapital + angka + konfirmasi cocok

#### Validasi Admin CRUD:
- Wajib isi semua field required
- Format email valid + cek duplikat
- Cek relasi foreign key sebelum hapus data

#### Validasi Upload:
- Tipe file: image only (JPEG, PNG, WebP, GIF)
- Ukuran max 5MB
- Ekstensi aman

### ✅ 9. Harga dari Database
- Harga per jam **diambil langsung dari tabel Lapangan** (`HARGA_PER_JAM`), bukan hardcoded
- Kalkulasi total di back-end: `HARGA_PER_JAM × jumlah slot`
- Harga aktual dikembalikan ke front-end via response JSON (`actual_price`)

### ✅ 10. Transaksi Database Aman
- Seluruh proses booking (5 tabel) dibungkus dalam `PDO::beginTransaction()` + `commit()`
- Jika gagal di tengah jalan → `rollBack()` otomatis, tidak ada data parsial
- Urutan insert: Pelanggan → Jadwal → Booking → Pembayaran

---

## Keamanan

- ✅ Password di-hash dengan `password_hash()` (bcrypt)
- ✅ Semua query pakai **Prepared Statements** (anti SQL Injection)
- ✅ Output di-escape dengan `htmlspecialchars()` via helper `e()` (anti XSS)
- ✅ Session cookie: `httponly`, `samesite=Strict`, lifetime 2 jam
- ✅ Folder `uploads/` diproteksi `.htaccess` (PHP tidak bisa dieksekusi)
- ✅ File sensitif (`config.php`) diblokir akses langsung via `.htaccess`
- ✅ Delay 1 detik pada login admin gagal (anti brute-force)
- ✅ `session_regenerate_id(true)` setelah login berhasil (anti session fixation)
- ✅ Auth helpers tersentralisasi di `config.php` (`isLoggedIn()`, `isAdmin()`, `isPelanggan()`, `requireLogin()`, `requireAdmin()`)

---

## Tech Stack & Libraries

| Komponen | Teknologi |
|---|---|
| Back-End | PHP 8+ (native, tanpa framework) |
| Database | MySQL / MariaDB via PDO |
| Front-End | HTML5, CSS3 (vanilla), JavaScript (vanilla) |
| 3D / WebGL | Three.js r128 (bola 3D, shader backgrounds) |
| Animasi | GSAP + ScrollTrigger |
| Font | Google Fonts (Orbitron, Rajdhani, Barlow) |
| Icon | Bootstrap Icons |
| Server | XAMPP / WAMP |

---

## Desain & UI/UX

- **Dark mode** tema hitam pekat dengan aksen **hijau neon** (`#00ff88`)
- **Custom cursor** hijau neon (otomatis hidden di perangkat touch/mobile)
- **Noise overlay** SVG untuk tekstur premium
- **WebGL shader background** pada halaman login (pelanggan & admin)
- **Three.js 3D soccer ball** interaktif di hero section
- **Glassmorphism** pada navbar dan tombol CTA
- **3D tilt** + **glare effect** pada kartu booking
- **Micro-animations** di seluruh halaman (fade-in, slide-up, hover glow)
- **Responsive** — layout menyesuaikan untuk desktop, tablet, dan mobile

---

## Troubleshooting

**Tidak bisa upload foto?**  
→ Pastikan folder `admin/uploads/` ada dan PHP punya izin write.  
→ Di Windows XAMPP biasanya tidak perlu setting ekstra.

**Login gagal padahal password benar?**  
→ Pastikan database sudah diisi data pelanggan dengan password yang di-hash `password_hash()`.  
→ Data lama dari booking.php menggunakan password `password123` (hash otomatis saat booking pertama).

**Error "Kolom FOTO tidak ditemukan"?**  
→ Jalankan `migration.sql` terlebih dahulu.

**Session tidak tersimpan?**  
→ Pastikan `session.save_path` di `php.ini` sudah dikonfigurasi, atau coba restart XAMPP.

**Kolom sosial media error?**  
→ Kolom sosial media dibuat otomatis saat pertama kali membuka halaman `profile.php`. Buka halaman profil minimal sekali setelah migrasi.

**Halaman profil tidak bisa diakses?**  
→ Pastikan sudah login sebagai pelanggan terlebih dahulu. Halaman profil dilindungi oleh auth.