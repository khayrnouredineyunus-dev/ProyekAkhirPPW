-- ============================================================
--  migration.sql — Migrasi Database MiniFut
--  Jalankan file ini SEKALI di phpMyAdmin atau MySQL CLI
--  setelah database minifut_db sudah dibuat dari DDL utama
-- ============================================================

-- 1. Tambah kolom FOTO pada tabel Lapangan (untuk upload foto lapangan)
ALTER TABLE Lapangan
  ADD COLUMN IF NOT EXISTS FOTO VARCHAR(255) NULL COMMENT 'Nama file foto lapangan (disimpan di admin/uploads/)';

-- 2. Tambah kolom FOTO_PROFIL pada tabel Pelanggan (untuk foto profil pelanggan)
ALTER TABLE Pelanggan
  ADD COLUMN IF NOT EXISTS FOTO_PROFIL VARCHAR(255) NULL COMMENT 'Nama file foto profil (disimpan di admin/uploads/)';

-- 3. Tambah kolom TEAM_NAME dan NOTES pada tabel Booking (sesuai frontend booking.php)
ALTER TABLE Booking
  ADD COLUMN IF NOT EXISTS TEAM_NAME VARCHAR(100) NULL COMMENT 'Nama tim yang booking',
  ADD COLUMN IF NOT EXISTS NOTES TEXT NULL COMMENT 'Catatan tambahan dari pelanggan';

-- 4. Update STATUS_JADWAL agar konsisten (YA = tersedia, TIDAK = terisi)
--    (Sudah sesuai dengan booking.php yang menggunakan STATUS_JADWAL = 'TIDAK' untuk slot terisi)

-- 5. Pastikan STATUS_PEMBAYARAN memiliki nilai default PENDING
ALTER TABLE Pembayaran
  MODIFY COLUMN STATUS_PEMBAYARAN VARCHAR(20) NOT NULL DEFAULT 'PENDING';

-- ── Verifikasi: tampilkan struktur tabel setelah migrasi ──
SHOW COLUMNS FROM Lapangan;
SHOW COLUMNS FROM Pelanggan;
SHOW COLUMNS FROM Booking;
SHOW COLUMNS FROM Pembayaran;