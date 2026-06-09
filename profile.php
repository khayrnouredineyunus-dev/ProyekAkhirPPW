<?php
// profile.php — Halaman Profil Pelanggan MiniFut
require_once __DIR__ . '/config.php';
startSecureSession();

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$pdo    = getDB();
$userId = (int)$_SESSION['user_id'];
$alert  = '';
$activeTab = $_GET['tab'] ?? 'info';

// Pastikan kolom FOTO_PROFIL ada
try { $pdo->query("SELECT FOTO_PROFIL FROM Pelanggan LIMIT 1"); }
catch (PDOException $e) { $pdo->exec("ALTER TABLE Pelanggan ADD COLUMN FOTO_PROFIL VARCHAR(255) NULL"); }

// Pastikan kolom sosial media ada
$socialCols = ['SOSMED_INSTAGRAM','SOSMED_TWITTER','SOSMED_TIKTOK','SOSMED_FACEBOOK','SOSMED_YOUTUBE'];
foreach ($socialCols as $col) {
    try { $pdo->query("SELECT $col FROM Pelanggan LIMIT 1"); }
    catch (PDOException $e) { $pdo->exec("ALTER TABLE Pelanggan ADD COLUMN $col VARCHAR(100) NULL"); }
}

// ── HANDLE POST ACTIONS ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. UPDATE INFORMASI PROFIL
    if ($action === 'update_profile') {
        $nama   = trim($_POST['nama']   ?? '');
        $email  = trim($_POST['email']  ?? '');
        $notelp = trim($_POST['notelp'] ?? '');
        $ig     = trim($_POST['instagram'] ?? '');
        $tw     = trim($_POST['twitter']   ?? '');
        $tt     = trim($_POST['tiktok']    ?? '');
        $fb     = trim($_POST['facebook']  ?? '');
        $yt     = trim($_POST['youtube']   ?? '');
        $errors = [];

        if (mb_strlen($nama) < 3)  $errors[] = 'Nama minimal 3 karakter.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
        if ($notelp && !preg_match('/^[0-9+\-\s]{8,20}$/', $notelp)) $errors[] = 'Nomor telepon tidak valid (8–20 digit).';

        if (empty($errors)) {
            $cek = $pdo->prepare("SELECT ID_PELANGGAN FROM Pelanggan WHERE U_EMAIL=? AND ID_PELANGGAN!=?");
            $cek->execute([$email, $userId]);
            if ($cek->fetch()) {
                $alert = 'error|Email sudah digunakan akun lain.';
            } else {
                $pdo->prepare(
                    "UPDATE Pelanggan
                     SET U_NAMA=?, U_EMAIL=?, U_NOTELP=?,
                         SOSMED_INSTAGRAM=?, SOSMED_TWITTER=?, SOSMED_TIKTOK=?,
                         SOSMED_FACEBOOK=?, SOSMED_YOUTUBE=?
                     WHERE ID_PELANGGAN=?"
                )->execute([$nama, $email, $notelp, $ig, $tw, $tt, $fb, $yt, $userId]);
                $_SESSION['user_name']  = $nama;
                $_SESSION['user_email'] = $email;
                $alert = 'success|Profil berhasil diperbarui.';
            }
        } else {
            $alert = 'error|' . implode(' ', $errors);
        }
        $activeTab = 'info';
    }

    if ($action === 'update_photo') {
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $up = uploadFoto($_FILES['foto'], 'profil');
            if ($up) {
                $stmt = $pdo->prepare("SELECT FOTO_PROFIL FROM Pelanggan WHERE ID_PELANGGAN=?");
                $stmt->execute([$userId]);
                $oldFoto = $stmt->fetchColumn();
                if ($oldFoto && file_exists(UPLOAD_DIR . $oldFoto)) {
                    @unlink(UPLOAD_DIR . $oldFoto);
                }
                $pdo->prepare("UPDATE Pelanggan SET FOTO_PROFIL=? WHERE ID_PELANGGAN=?")->execute([$up, $userId]);
                $alert = 'success|Foto profil berhasil diperbarui.';
            } else {
                $alert = 'error|Gagal mengupload foto. Format JPG/PNG/WebP, maks 5 MB.';
            }
        } else {
            $alert = 'error|Pilih file foto terlebih dahulu.';
        }
        $activeTab = 'avatar';
    }

    // 3. HAPUS FOTO PROFIL
    if ($action === 'delete_photo') {
        $stmt = $pdo->prepare("SELECT FOTO_PROFIL FROM Pelanggan WHERE ID_PELANGGAN=?");
        $stmt->execute([$userId]);
        $oldFoto = $stmt->fetchColumn();
        if ($oldFoto && file_exists(UPLOAD_DIR . $oldFoto)) {
            @unlink(UPLOAD_DIR . $oldFoto);
        }
        $pdo->prepare("UPDATE Pelanggan SET FOTO_PROFIL=NULL WHERE ID_PELANGGAN=?")->execute([$userId]);
        $alert = 'success|Foto profil berhasil dihapus.';
        $activeTab = 'avatar';
    }

    // 4. UBAH PASSWORD
    if ($action === 'change_password') {
        $curPw  = $_POST['current_password'] ?? '';
        $newPw  = $_POST['new_password']     ?? '';
        $confPw = $_POST['confirm_password'] ?? '';
        $stmt   = $pdo->prepare("SELECT U_PASSWORD FROM Pelanggan WHERE ID_PELANGGAN=?");
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($curPw, $hash)) {
            $alert = 'error|Password saat ini tidak benar.';
        } elseif (mb_strlen($newPw) < 8) {
            $alert = 'error|Password baru minimal 8 karakter.';
        } elseif (!preg_match('/[A-Z]/', $newPw)) {
            $alert = 'error|Password baru harus mengandung minimal 1 huruf kapital.';
        } elseif (!preg_match('/[0-9]/', $newPw)) {
            $alert = 'error|Password baru harus mengandung minimal 1 angka.';
        } elseif ($newPw !== $confPw) {
            $alert = 'error|Konfirmasi password tidak cocok.';
        } else {
            $pdo->prepare("UPDATE Pelanggan SET U_PASSWORD=? WHERE ID_PELANGGAN=?")
                ->execute([password_hash($newPw, PASSWORD_DEFAULT), $userId]);
            $alert = 'success|Password berhasil diubah.';
        }
        $activeTab = 'security';
    }
}

// Ambil data terbaru dari database
$stmt = $pdo->prepare("SELECT * FROM Pelanggan WHERE ID_PELANGGAN=?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Ambil riwayat booking
$histStmt = $pdo->prepare(
    "SELECT b.ID_BOOKING, b.TANGGAL_BOOKING, b.STATUS_BOOKING,
            l.NAMA_LAPANGAN, l.HARGA_PER_JAM,
            j.TANGGAL as TGL_MAIN, j.JAM_MULAI,
            pm.STATUS_PEMBAYARAN, pm.METODE_PEMBAYARAN
     FROM Booking b
     JOIN Jadwal j        ON b.ID_JADWAL   = j.ID_JADWAL
     JOIN Lapangan l      ON j.ID_LAPANGAN = l.ID_LAPANGAN
     LEFT JOIN Pembayaran pm ON b.ID_BOOKING = pm.ID_BOOKING
     WHERE b.ID_PELANGGAN = ?
     ORDER BY b.TANGGAL_BOOKING DESC"
);
$histStmt->execute([$userId]);
$bookingHistory = $histStmt->fetchAll();

$totalBookings   = count($bookingHistory);
$lunasBookings   = count(array_filter($bookingHistory, fn($b) => $b['STATUS_BOOKING'] === 'LUNAS'));
$pendingBookings = count(array_filter($bookingHistory, fn($b) => in_array($b['STATUS_BOOKING'], ['PENDING','DP'])));

[$alertType, $alertMsg] = $alert ? explode('|', $alert, 2) : ['', ''];

$userName  = $user['U_NAMA']             ?? $_SESSION['user_name']  ?? '';
$userEmail = $user['U_EMAIL']            ?? $_SESSION['user_email'] ?? '';
$userPhone = $user['U_NOTELP']           ?? '';
$userFoto  = $user['FOTO_PROFIL']        ?? null;
$userIg    = $user['SOSMED_INSTAGRAM']   ?? '';
$userTw    = $user['SOSMED_TWITTER']     ?? '';
$userTt    = $user['SOSMED_TIKTOK']      ?? '';
$userFb    = $user['SOSMED_FACEBOOK']    ?? '';
$userYt    = $user['SOSMED_YOUTUBE']     ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MiniFut — Profil Saya</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Anton&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ── RESET & GLOBAL ──────────────────────────────────────── */
:root {
  --black: #060608; --dark: #0c0d10; --card: #111318; --card2: #161820;
  --border: rgba(255,255,255,.06); --border2: rgba(255,255,255,.10);
  --green: #00ff88; --glow: rgba(0,255,136,.18); --glow-sm: rgba(0,255,136,.07);
  --gray: #6b7080; --gray2: #9aa0b0; --white: #eceef2;
  --red: #ff3b5c; --amber: #ffb600; --blue: #3b82f6;
  --font-head: 'Orbitron', monospace;
  --font-ui:   'Plus Jakarta Sans', sans-serif;
}
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }
body {
  background: var(--black);
  color: var(--white);
  font-family: var(--font-ui);
  min-height: 100vh;
  overflow-x: hidden;
}
input, button, select, textarea {
  font-family: var(--font-ui);
}

/* Scrollbar */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-thumb { background: rgba(0,255,136,.15); border-radius: 2px; }

/* ── BACKGROUND GRID ────────────────────────────────────── */
#bg-grid {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
  background-image:
    linear-gradient(rgba(0,255,136,.022) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0,255,136,.022) 1px, transparent 1px);
  background-size: 52px 52px;
}

/* ── FLOATING LINES CANVAS (WebGL) ─────────────────────── */
#fl-canvas {
  position: fixed; inset: 0; z-index: 1;
  pointer-events: none;
  mix-blend-mode: screen;
  opacity: 1;
}

/* ── NOISE OVERLAY ──────────────────────────────────────── */
#noise {
  position: fixed; inset: 0; z-index: 2; pointer-events: none; opacity: .016;
  background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* ── NAVBAR ─────────────────────────────────────────────── */
nav {
  position: fixed;
  top: 24px; left: 50%; transform: translateX(-50%);
  width: calc(100% - 128px); max-width: 1400px;
  height: 70px;
  background: rgba(6,6,8,.45);
  backdrop-filter: blur(22px);
  -webkit-backdrop-filter: blur(22px);
  border: 1px solid rgba(255,255,255,.05);
  border-radius: 16px;
  padding: 0 40px;
  display: flex; align-items: center; justify-content: space-between;
  z-index: 1001;
}
.logo { font-family: var(--font-head); font-size: 1.5rem; font-weight: 900; color: var(--green); letter-spacing: 5px; text-decoration: none; transition: all .3s; }
.logo em { color: var(--white); font-style: normal; }
.logo:hover { text-shadow: 0 0 12px var(--glow); }

/* CTA BACK — Liquid Glass dengan GSAP magnetic */
.nav-cta {
  font-family: var(--font-ui);
  font-size: .78rem; font-weight: 700;
  letter-spacing: 2px; text-transform: uppercase;
  color: var(--green);
  background: rgba(0,255,136,.06);
  border: 1px solid rgba(0,255,136,.25);
  padding: 11px 26px; border-radius: 30px;
  text-decoration: none;
  position: relative; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  transition: color .3s, background .3s, box-shadow .3s;
  overflow: visible;
  will-change: transform;
}
.nav-cta::before {
  content: '';
  position: absolute; top:0; left:0; width:100%; height:100%;
  border-radius: 30px; pointer-events: none; z-index: 1;
  box-shadow:
    0 0 6px rgba(0,0,0,.03),
    0 2px 6px rgba(0,0,0,.08),
    inset 3px 3px .5px -3px rgba(0,0,0,.9),
    inset -3px -3px .5px -3px rgba(0,0,0,.85),
    inset 1px 1px 1px -.5px rgba(0,0,0,.6),
    inset -1px -1px 1px -.5px rgba(0,0,0,.6),
    inset 0 0 6px 6px rgba(0,0,0,.12),
    inset 0 0 2px 2px rgba(0,0,0,.06),
    0 0 12px rgba(255,255,255,.15);
  transition: all .3s ease;
}
.nav-cta-backdrop {
  position: absolute; inset:0; border-radius:30px; overflow:hidden; z-index:0;
  backdrop-filter: url("#container-glass") blur(4px);
  background: rgba(0,255,136,.05);
  transition: all .3s ease;
}
.nav-cta:hover {
  color: var(--black); background: var(--green);
  box-shadow: 0 0 22px rgba(0,255,136,.45);
}
.nav-cta:hover::before { box-shadow: none; }
.nav-cta:hover .nav-cta-backdrop { opacity: 0; }
.nav-cta span.cta-text { position:relative; z-index:2; }

@media (max-width: 768px) {
  nav { width: calc(100% - 32px); top:16px; padding:0 20px; height:64px; border-radius:16px; }
  .logo { font-size: 1.15rem; letter-spacing: 3px; }
  .nav-cta { padding: 9px 18px; font-size: .7rem; }
}

/* ── PAGE LAYOUT ─────────────────────────────────────────── */
.page-wrap {
  position: relative; z-index: 10;
  max-width: 1180px; margin: 0 auto;
  padding: 130px 48px 80px;
  display: grid;
  grid-template-columns: 270px 1fr;
  gap: 24px;
  align-items: start;
}
@media (max-width: 900px) {
  .page-wrap { grid-template-columns: 1fr; padding: 108px 20px 60px; }
}

/* ── SIDEBAR ─────────────────────────────────────────────── */
.sidebar { display: flex; flex-direction: column; gap: 8px; position: sticky; top: 110px; }

.profile-card {
  background: var(--card);
  border: 1px solid var(--border2);
  border-radius: 16px; overflow: hidden;
}

/* avatar top */
.profile-card-top {
  padding: 32px 24px 20px;
  background: linear-gradient(135deg, rgba(0,255,136,.05) 0%, transparent 70%);
  border-bottom: 1px solid var(--border);
  text-align: center; position: relative;
}
.profile-card-top::before {
  content: ''; position: absolute; top:0; left:12%; right:12%; height:2px;
  background: linear-gradient(90deg, transparent, var(--green), transparent);
}

.avatar-wrap {
  position: relative; width: 96px; height: 96px;
  margin: 0 auto 16px; cursor: pointer;
}
.avatar-img {
  width: 96px; height: 96px; border-radius: 50%;
  object-fit: cover;
  border: 3px solid rgba(0,255,136,.35);
  box-shadow: 0 0 22px rgba(0,255,136,.16);
  transition: all .3s;
}
.avatar-initial {
  width: 96px; height: 96px; border-radius: 50%;
  background: linear-gradient(135deg, rgba(0,255,136,.12), rgba(0,255,136,.04));
  border: 3px solid rgba(0,255,136,.35);
  display: flex; align-items: center; justify-content: center;
  font-family: var(--font-head); font-size: 2rem; font-weight: 900; color: var(--green);
  box-shadow: 0 0 22px rgba(0,255,136,.16);
  transition: all .3s;
}
.avatar-overlay {
  position: absolute; inset:0; border-radius:50%;
  background: rgba(0,0,0,.55);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px;
  opacity: 0; transition: opacity .3s;
}
.avatar-wrap:hover .avatar-img,
.avatar-wrap:hover .avatar-initial { transform: scale(1.04); }
.avatar-wrap:hover .avatar-overlay { opacity: 1; }
.avatar-overlay i { font-size: 1.15rem; color: var(--green); }
.avatar-overlay span { font-family: var(--font-ui); font-size: .65rem; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: var(--white); }
.avatar-dot {
  position: absolute; bottom:4px; right:4px;
  width: 16px; height: 16px; border-radius: 50%;
  background: var(--green); border: 3px solid var(--card);
  box-shadow: 0 0 6px var(--green);
}

.profile-name  { font-family: var(--font-head); font-size: .95rem; font-weight: 700; color: var(--white); margin-bottom: 4px; }
.profile-email { font-family: var(--font-ui); font-size: .82rem; font-weight: 500; color: var(--gray2); margin-bottom: 16px; }
.member-badge {
  display: inline-flex; align-items: center; gap: 5px;
  font-family: var(--font-ui); font-size: .68rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
  color: var(--green); background: rgba(0,255,136,.08);
  border: 1px solid rgba(0,255,136,.2);
  padding: 4px 14px; border-radius: 100px;
}

/* stats */
.stats-row { display: flex; justify-content: center; }
.stat-item {
  flex: 1; padding: 14px 8px; text-align: center;
  border-right: 1px solid var(--border);
}
.stat-item:last-child { border-right: none; }
.stat-val { font-family: var(--font-head); font-size: 1.3rem; font-weight: 700; color: var(--green); }
.stat-key { font-family: var(--font-ui); font-size: .72rem; font-weight: 600; letter-spacing: 1px; color: var(--gray); margin-top: 2px; text-transform: uppercase; }

/* sidebar nav */
.sidebar-nav { padding: 8px; }
.snav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 11px 16px; border-radius: 10px;
  font-family: var(--font-ui); font-size: .92rem; font-weight: 600;
  color: var(--gray2); cursor: pointer;
  transition: all .22s;
  border: 1px solid transparent; margin-bottom: 2px;
}
.snav-item i { font-size: 1rem; flex-shrink: 0; width: 18px; text-align: center; }
.snav-item:hover { background: rgba(255,255,255,.03); color: var(--white); }
.snav-item.active {
  background: rgba(0,255,136,.07); color: var(--green);
  border-color: rgba(0,255,136,.18);
}
.snav-count {
  margin-left: auto;
  font-family: var(--font-head); font-size: .62rem; color: var(--green);
  background: rgba(0,255,136,.1); border: 1px solid rgba(0,255,136,.2);
  padding: 2px 8px; border-radius: 100px;
}

/* ── MAIN CONTENT ─────────────────────────────────────────── */
.main-content { display: flex; flex-direction: column; gap: 8px; }

/* alert */
.alert-box {
  padding: 13px 18px; border-radius: 10px;
  font-family: var(--font-ui); font-size: .9rem; font-weight: 600;
  display: flex; align-items: center; gap: 10px; margin-bottom: 4px;
}
.alert-success { background: rgba(0,255,136,.07); border: 1px solid rgba(0,255,136,.22); color: #4dffa0; }
.alert-error   { background: rgba(255,59,92,.07);  border: 1px solid rgba(255,59,92,.22);  color: #ff8099; }

/* section card */
.section-card {
  background: var(--card);
  border: 1px solid var(--border2);
  border-radius: 16px; overflow: hidden;
  display: none;
  animation: fadeIn .25s ease both;
}
.section-card.visible { display: block; }
@keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

.section-header {
  padding: 20px 26px 16px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; gap: 14px;
}
.section-icon-wrap {
  width: 38px; height: 38px; border-radius: 8px;
  background: rgba(0,255,136,.07); border: 1px solid rgba(0,255,136,.18);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.section-icon-wrap i { font-size: 1rem; color: var(--green); }
.section-title { font-family: var(--font-head); font-size: .88rem; font-weight: 700; color: var(--white); }
.section-sub   { font-family: var(--font-ui); font-size: .78rem; font-weight: 500; color: var(--gray); margin-top: 3px; }
.section-body  { padding: 26px; }

/* ── PHOTO UPLOAD SECTION ─────────────────────────────────── */
.photo-wrap {
  display: grid; grid-template-columns: auto 1fr; gap: 24px;
  align-items: center; padding: 20px;
  background: rgba(0,255,136,.025); border: 1px solid rgba(0,255,136,.1);
  border-radius: 12px; margin-bottom: 24px;
}
@media (max-width: 580px) { .photo-wrap { grid-template-columns: 1fr; text-align: center; } }

.photo-thumb {
  width: 76px; height: 76px; border-radius: 12px;
  object-fit: cover; border: 2px solid rgba(0,255,136,.28);
}
.photo-thumb-initial {
  width: 76px; height: 76px; border-radius: 12px;
  background: rgba(0,255,136,.08); border: 2px solid rgba(0,255,136,.28);
  display: flex; align-items: center; justify-content: center;
  font-family: var(--font-head); font-size: 1.6rem; font-weight: 900; color: var(--green);
}

.photo-label { font-family: var(--font-ui); font-size: .82rem; font-weight: 600; color: var(--gray2); margin-bottom: 10px; display: block; }
.upload-zone {
  position: relative; border: 1px dashed rgba(0,255,136,.28);
  border-radius: 8px; padding: 14px 18px; cursor: pointer;
  background: rgba(0,255,136,.018); transition: border-color .25s, background .25s;
  display: flex; align-items: center; gap: 12px;
}
.upload-zone:hover { border-color: rgba(0,255,136,.55); background: rgba(0,255,136,.045); }
.upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.upload-zone i { font-size: 1.4rem; color: var(--green); flex-shrink:0; }
.upload-zone-text { font-family: var(--font-ui); font-size: .88rem; font-weight: 600; color: var(--gray2); }
.upload-zone-text strong { color: var(--white); }
.upload-zone-hint { font-family: var(--font-ui); font-size: .74rem; font-weight: 500; color: var(--gray); margin-top: 2px; }
.upload-actions { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }

/* ── FORM ELEMENTS ──────────────────────────────────────── */
.divider { height: 1px; background: var(--border); margin: 22px 0; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-grid.cols1 { grid-template-columns: 1fr; }
@media (max-width: 580px) { .form-grid { grid-template-columns: 1fr; } }
.fg-full { grid-column: 1 / -1; }

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label {
  font-family: var(--font-ui); font-size: .8rem; font-weight: 700;
  letter-spacing: 1px; text-transform: uppercase;
  color: var(--gray2);
}
.form-input, .form-select {
  background: rgba(255,255,255,.03);
  border: 1px solid var(--border2);
  border-radius: 8px; padding: 11px 14px;
  font-family: var(--font-ui); font-size: .95rem; font-weight: 500; color: var(--white);
  outline: none; transition: border-color .22s, box-shadow .22s; width: 100%;
}
.form-input:focus, .form-select:focus {
  border-color: rgba(0,255,136,.42);
  box-shadow: 0 0 0 3px rgba(0,255,136,.06);
}
.form-input::placeholder { color: var(--gray); }
.form-hint { font-family: var(--font-ui); font-size: .76rem; font-weight: 500; color: var(--gray); }

/* social media prefix input */
.input-prefix-wrap { display: flex; align-items: center; gap: 0; border: 1px solid var(--border2); border-radius: 8px; overflow: hidden; background: rgba(255,255,255,.03); transition: border-color .22s, box-shadow .22s; }
.input-prefix-wrap:focus-within { border-color: rgba(0,255,136,.42); box-shadow: 0 0 0 3px rgba(0,255,136,.06); }
.input-prefix { font-family: var(--font-ui); font-size: .82rem; font-weight: 600; color: var(--gray); background: rgba(255,255,255,.04); border-right: 1px solid var(--border2); padding: 11px 12px; white-space: nowrap; }
.input-prefix-wrap input { border: none; border-radius: 0; background: transparent; flex: 1; outline: none; padding: 11px 14px; font-family: var(--font-ui); font-size: .95rem; font-weight: 500; color: var(--white); }

/* section sub-title */
.subsection-title { font-family: var(--font-ui); font-size: .84rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--gray2); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }

/* ── BUTTONS ─────────────────────────────────────────────── */
.btn {
  font-family: var(--font-ui); font-size: .88rem; font-weight: 700;
  letter-spacing: 1px; text-transform: uppercase;
  border-radius: 8px; padding: 10px 20px; cursor: pointer; border: none;
  display: inline-flex; align-items: center; gap: 7px; transition: all .22s;
  text-decoration: none;
}
.btn-green   { color: var(--black); background: var(--green); box-shadow: 0 2px 14px rgba(0,255,136,.22); }
.btn-green:hover  { box-shadow: 0 4px 20px rgba(0,255,136,.38); transform: translateY(-1px); }
.btn-outline { color: var(--green); background: transparent; border: 1px solid rgba(0,255,136,.28); }
.btn-outline:hover { background: rgba(0,255,136,.07); }
.btn-danger  { color: #ff8099; background: rgba(255,59,92,.07); border: 1px solid rgba(255,59,92,.22); }
.btn-danger:hover  { background: rgba(255,59,92,.16); }
.btn-sm { padding: 7px 14px; font-size: .8rem; }

/* ── PASSWORD STRENGTH ──────────────────────────────────── */
.pw-strength { margin-top: 6px; }
.pw-strength-bar { height: 3px; border-radius: 2px; background: var(--border); overflow: hidden; margin-bottom: 4px; }
.pw-strength-fill { height: 100%; border-radius: 2px; transition: all .3s; width: 0; }
.pw-strength-text { font-family: var(--font-ui); font-size: .74rem; font-weight: 600; color: var(--gray); }

/* ── PASSWORD EYE TOGGLE ───────────────────────────────── */
.pw-wrap { position: relative; }
.pw-wrap input { padding-right: 44px; }
.pw-eye {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; color: var(--gray);
  padding: 4px; display: flex; align-items: center; justify-content: center;
  transition: color .2s; font-size: 1rem;
}
.pw-eye:hover { color: var(--gray2); }

/* ── SECURITY INFO BOX ──────────────────────────────────── */
.info-box {
  background: rgba(255,182,0,.04); border: 1px solid rgba(255,182,0,.16);
  border-radius: 10px; padding: 14px 16px; margin-bottom: 20px;
  font-family: var(--font-ui); font-size: .84rem; font-weight: 500; color: var(--gray2); line-height: 1.65;
  display: flex; gap: 10px; align-items: flex-start;
}
.info-box i { color: var(--amber); font-size: 1rem; margin-top: 1px; flex-shrink: 0; }
.info-box strong { color: var(--white); }

/* ── BOOKING HISTORY ─────────────────────────────────────── */
.booking-list { display: flex; flex-direction: column; gap: 10px; }
.booking-row {
  background: var(--card2); border: 1px solid var(--border);
  border-radius: 12px; padding: 16px 20px;
  display: grid; grid-template-columns: 1fr auto;
  gap: 12px; align-items: center;
  transition: border-color .22s, transform .22s;
}
.booking-row:hover { border-color: rgba(0,255,136,.2); transform: translateY(-1px); }
.booking-code  { font-family: var(--font-head); font-size: .68rem; font-weight: 700; color: var(--green); margin-bottom: 4px; }
.booking-field { font-family: var(--font-head); font-size: .84rem; font-weight: 700; color: var(--white); margin-bottom: 6px; }
.booking-meta  { font-family: var(--font-ui); font-size: .8rem; font-weight: 500; color: var(--gray2); display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
.booking-meta i { font-size: .8rem; color: var(--gray); }
.booking-right { display: flex; flex-direction: column; align-items: flex-end; gap: 7px; }
.booking-price { font-family: var(--font-head); font-size: .85rem; font-weight: 700; color: var(--green); }
.booking-badges { display: flex; gap: 5px; flex-wrap: wrap; justify-content: flex-end; }
.booking-date  { font-family: var(--font-ui); font-size: .72rem; font-weight: 500; color: var(--gray); }

/* badges */
.badge {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 10px; border-radius: 100px;
  font-family: var(--font-ui); font-size: .72rem; font-weight: 700; letter-spacing: .5px; text-transform: uppercase;
}
.badge-green  { background: rgba(0,255,136,.1);   color: var(--green); border: 1px solid rgba(0,255,136,.2); }
.badge-amber  { background: rgba(255,182,0,.1);   color: var(--amber); border: 1px solid rgba(255,182,0,.2); }
.badge-red    { background: rgba(255,59,92,.1);   color: #ff8099;      border: 1px solid rgba(255,59,92,.2); }
.badge-blue   { background: rgba(59,130,246,.1);  color: #60a5fa;      border: 1px solid rgba(59,130,246,.2); }
.badge-gray   { background: rgba(107,112,128,.1); color: var(--gray2); border: 1px solid rgba(107,112,128,.2); }

/* empty state */
.empty-state { padding: 56px 24px; text-align: center; }
.empty-icon  { font-size: 2.8rem; margin-bottom: 14px; opacity: .28; color: var(--green); }
.empty-title { font-family: var(--font-head); font-size: .82rem; font-weight: 700; color: var(--gray2); margin-bottom: 6px; }
.empty-sub   { font-family: var(--font-ui); font-size: .84rem; font-weight: 500; color: var(--gray); margin-bottom: 20px; }
body, a, button, .snav-item, .booking-row, .avatar-wrap, .upload-zone {
  cursor: auto !important;
}
a, button, .nav-cta, .snav-item, .booking-row, .avatar-wrap, .upload-zone {
  cursor: pointer !important;
}
</style>
</head>
<body>
<div id="bg-grid"></div>
<canvas id="fl-canvas"></canvas>
<div id="noise"></div>

<!-- SVG Glass filter for liquid glass button -->
<svg style="position:absolute;width:0;height:0;" width="0" height="0">
  <defs>
    <filter id="container-glass" x="0%" y="0%" width="100%" height="100%" color-interpolation-filters="sRGB">
      <feTurbulence type="fractalNoise" baseFrequency="0.05 0.05" numOctaves="1" seed="1" result="turbulence"/>
      <feGaussianBlur in="turbulence" stdDeviation="2" result="blurredNoise"/>
      <feDisplacementMap in="SourceGraphic" in2="blurredNoise" scale="70" xChannelSelector="R" yChannelSelector="B" result="displaced"/>
      <feGaussianBlur in="displaced" stdDeviation="4" result="finalBlur"/>
      <feComposite in="finalBlur" in2="finalBlur" operator="over"/>
    </filter>
  </defs>
</svg>

<!-- ── NAVBAR ─────────────────────────────────────────────── -->
<nav id="nav">
  <a href="index.php" class="logo">MINI<em>FUT</em></a>
  <a href="index.php" class="nav-cta" id="navCtaBack">
    <span class="nav-cta-backdrop"></span>
    <i class="bi bi-arrow-left cta-text" style="position:relative;z-index:2;"></i>
    <span class="cta-text">Kembali</span>
  </a>
</nav>

<!-- ── PAGE WRAPPER ───────────────────────────────────────── -->
<div class="page-wrap">

  <!-- ══════════ SIDEBAR ══════════ -->
  <aside class="sidebar">
    <div class="profile-card">
      <div class="profile-card-top">
        <!-- Avatar — click goes to avatar tab -->
        <a href="profile.php?tab=avatar" id="avatarLink" style="text-decoration:none;display:inline-block;">
          <div class="avatar-wrap">
            <?php if (!empty($userFoto)): ?>
              <img src="<?= UPLOAD_URL . e($userFoto) ?>" alt="Foto Profil" class="avatar-img" id="avatarPreview">
            <?php else: ?>
              <div class="avatar-initial" id="avatarPreview"><?= strtoupper(substr($userName,0,1)) ?></div>
            <?php endif; ?>
            <div class="avatar-overlay">
              <i class="bi bi-camera-fill"></i>
              <span>Ganti</span>
            </div>
            <div class="avatar-dot"></div>
          </div>
        </a>

        <div class="profile-name"><?= e($userName) ?></div>
        <div class="profile-email"><?= e($userEmail) ?></div>
        <div class="member-badge"><i class="bi bi-patch-check-fill"></i> Active Member</div>
      </div>

      <div class="stats-row">
        <div class="stat-item">
          <div class="stat-val"><?= $totalBookings ?></div>
          <div class="stat-key">Total</div>
        </div>
        <div class="stat-item">
          <div class="stat-val" style="color:var(--green);"><?= $lunasBookings ?></div>
          <div class="stat-key">Lunas</div>
        </div>
        <div class="stat-item">
          <div class="stat-val" style="color:var(--amber);"><?= $pendingBookings ?></div>
          <div class="stat-key">Proses</div>
        </div>
      </div>

      <div class="sidebar-nav">
        <div class="snav-item <?= $activeTab === 'info'     ? 'active' : '' ?>" onclick="switchTab('info')">
          <i class="bi bi-person-fill"></i> Profile Info
        </div>
        <div class="snav-item <?= $activeTab === 'avatar'   ? 'active' : '' ?>" onclick="switchTab('avatar')">
          <i class="bi bi-camera-fill"></i> Photo
        </div>
        <div class="snav-item <?= $activeTab === 'social'   ? 'active' : '' ?>" onclick="switchTab('social')">
          <i class="bi bi-share-fill"></i> Social Media
        </div>
        <div class="snav-item <?= $activeTab === 'security' ? 'active' : '' ?>" onclick="switchTab('security')">
          <i class="bi bi-shield-lock-fill"></i> Password
        </div>
        <div class="snav-item <?= $activeTab === 'history'  ? 'active' : '' ?>" onclick="switchTab('history')">
          <i class="bi bi-journal-bookmark-fill"></i> Booking History
          <?php if ($totalBookings > 0): ?>
          <span class="snav-count"><?= $totalBookings ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <a href="booking.php" class="btn btn-green" style="width:100%;justify-content:center;padding:13px;border-radius:12px;">
      <i class="bi bi-calendar-plus-fill"></i> Book Lapangan
    </a>
  </aside>

  <!-- ══════════ MAIN CONTENT ══════════ -->
  <div class="main-content">

    <?php if ($alertMsg): ?>
    <div class="alert-box alert-<?= $alertType === 'success' ? 'success' : 'error' ?>" id="alertBox">
      <i class="bi bi-<?= $alertType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
      <?= e($alertMsg) ?>
    </div>
    <?php endif; ?>

    <!-- ── TAB: Profile Info ──────────────────────────── -->
    <div class="section-card <?= $activeTab === 'info' ? 'visible' : '' ?>" id="tab-info">
      <div class="section-header">
        <div class="section-icon-wrap">
          <i class="bi bi-person-fill"></i>
        </div>
        <div>
          <div class="section-title">Profile Info</div>
          <div class="section-sub">Perbarui nama, email, dan nomor telepon kamu</div>
        </div>
      </div>
      <div class="section-body">
        <form method="POST" id="formProfile">
          <input type="hidden" name="action" value="update_profile">
          <div class="form-grid">
            <div class="form-group fg-full">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="nama" class="form-input" value="<?= e($userName) ?>" required placeholder="Nama lengkap kamu">
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-input" value="<?= e($userEmail) ?>" required placeholder="kamu@email.com">
            </div>
            <div class="form-group">
              <label class="form-label">Telepon / WhatsApp</label>
              <input type="tel" name="notelp" class="form-input" value="<?= e($userPhone) ?>" placeholder="08xx-xxxx-xxxx">
              <span class="form-hint">Digunakan untuk konfirmasi booking</span>
            </div>
          </div>
          <!-- hidden social fields forwarded so they are not erased on profile save -->
          <input type="hidden" name="instagram" value="<?= e($userIg) ?>">
          <input type="hidden" name="twitter"   value="<?= e($userTw) ?>">
          <input type="hidden" name="tiktok"    value="<?= e($userTt) ?>">
          <input type="hidden" name="facebook"  value="<?= e($userFb) ?>">
          <input type="hidden" name="youtube"   value="<?= e($userYt) ?>">
          <div style="margin-top:20px;">
            <button type="submit" class="btn btn-green"><i class="bi bi-check-lg"></i> Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>

    <div class="section-card <?= $activeTab === 'avatar' ? 'visible' : '' ?>" id="tab-avatar">
      <div class="section-header">
        <div class="section-icon-wrap">
          <i class="bi bi-camera-fill"></i>
        </div>
        <div>
          <div class="section-title">Profile Photo</div>
          <div class="section-sub">Format JPG, PNG, atau WebP maks 5 MB. Foto lama otomatis dihapus dari server.</div>
        </div>
      </div>
      <div class="section-body">
        <div class="photo-wrap">
          <div>
            <?php if (!empty($userFoto)): ?>
              <img src="<?= UPLOAD_URL . e($userFoto) ?>" alt="" class="photo-thumb" id="photoThumb">
            <?php else: ?>
              <div class="photo-thumb-initial" id="photoThumb"><?= strtoupper(substr($userName,0,1)) ?></div>
            <?php endif; ?>
          </div>
          <div>
            <span class="photo-label">Foto saat ini</span>
            <form method="POST" enctype="multipart/form-data" id="formPhoto">
              <input type="hidden" name="action" value="update_photo">
              <div class="upload-zone" id="uploadZone">
                <input type="file" name="foto" id="fotoInput" accept="image/jpeg,image/png,image/webp"
                      onchange="handlePhotoChange(this)">
                <i class="bi bi-cloud-arrow-up-fill"></i>
                <div>
                  <div class="upload-zone-text"><strong>Klik untuk memilih file</strong> atau seret & lepas</div>
                  <div class="upload-zone-hint">Foto lama dihapus otomatis dari server saat foto baru disimpan</div>
                </div>
              </div>
              <div class="upload-actions">
                <button type="submit" class="btn btn-green btn-sm" id="btnUpload" style="display:none;"><i class="bi bi-cloud-check-fill"></i> Simpan Foto</button>
                <?php if (!empty($userFoto)): ?>
                <form method="POST" style="display:contents;" onsubmit="return confirm('Hapus foto profil?')">
                  <input type="hidden" name="action" value="delete_photo">
                  <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash3-fill"></i> Hapus Foto</button>
                </form>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- ── TAB: Social Media ──────────────────────────── -->
    <div class="section-card <?= $activeTab === 'social' ? 'visible' : '' ?>" id="tab-social">
      <div class="section-header">
        <div class="section-icon-wrap">
          <i class="bi bi-share-fill"></i>
        </div>
        <div>
          <div class="section-title">Social Media</div>
          <div class="section-sub">Username media sosial kamu yang bisa dilihat publik</div>
        </div>
      </div>
      <div class="section-body">
        <form method="POST" id="formSocial">
          <input type="hidden" name="action" value="update_profile">
          <input type="hidden" name="nama"   value="<?= e($userName) ?>">
          <input type="hidden" name="email"  value="<?= e($userEmail) ?>">
          <input type="hidden" name="notelp" value="<?= e($userPhone) ?>">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label"><i class="bi bi-instagram" style="color:#e1306c;"></i> Instagram</label>
              <div class="input-prefix-wrap">
                <span class="input-prefix">@</span>
                <input type="text" name="instagram" placeholder="username" value="<?= e($userIg) ?>">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label"><i class="bi bi-twitter-x"></i> Twitter / X</label>
              <div class="input-prefix-wrap">
                <span class="input-prefix">@</span>
                <input type="text" name="twitter" placeholder="username" value="<?= e($userTw) ?>">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label"><i class="bi bi-tiktok" style="color:#69c9d0;"></i> TikTok</label>
              <div class="input-prefix-wrap">
                <span class="input-prefix">@</span>
                <input type="text" name="tiktok" placeholder="username" value="<?= e($userTt) ?>">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label"><i class="bi bi-facebook" style="color:#1877f2;"></i> Facebook</label>
              <div class="input-prefix-wrap">
                <span class="input-prefix">fb.com/</span>
                <input type="text" name="facebook" placeholder="halaman-kamu" value="<?= e($userFb) ?>">
              </div>
            </div>
            <div class="form-group fg-full">
              <label class="form-label"><i class="bi bi-youtube" style="color:#ff0000;"></i> YouTube</label>
              <div class="input-prefix-wrap">
                <span class="input-prefix">youtube.com/</span>
                <input type="text" name="youtube" placeholder="@channel" value="<?= e($userYt) ?>">
              </div>
            </div>
          </div>
          <div style="margin-top:20px;">
            <button type="submit" class="btn btn-green"><i class="bi bi-check-lg"></i> Simpan Media Sosial</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── TAB: Password ──────────────────────────────── -->
    <div class="section-card <?= $activeTab === 'security' ? 'visible' : '' ?>" id="tab-security">
      <div class="section-header">
        <div class="section-icon-wrap">
          <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div>
          <div class="section-title">Change Password</div>
          <div class="section-sub">Min. 8 karakter, 1 huruf kapital, 1 angka</div>
        </div>
      </div>
      <div class="section-body">
        <div class="info-box">
          <i class="bi bi-info-circle-fill"></i>
          <span>Password disimpan menggunakan enkripsi <strong>bcrypt</strong>. Kami tidak pernah menyimpan password dalam bentuk teks biasa. Ganti password secara berkala untuk menjaga keamanan akun kamu.</span>
        </div>
        <form method="POST">
          <input type="hidden" name="action" value="change_password">
          <div class="form-grid cols1">
            <div class="form-group">
              <label class="form-label">Password Saat Ini</label>
              <div class="pw-wrap">
                <input type="password" name="current_password" class="form-input" id="curPw" placeholder="Masukkan password saat ini" required>
                <button type="button" class="pw-eye" onclick="togglePw('curPw',this)" aria-label="Tampilkan">
                  <i class="bi bi-eye-fill" id="eye-cur"></i>
                </button>
              </div>
            </div>
            <div class="divider"></div>
            <div class="form-group">
              <label class="form-label">Password Baru</label>
              <div class="pw-wrap">
                <input type="password" name="new_password" class="form-input" id="newPw" placeholder="Min. 8 karakter, 1 kapital, 1 angka" required oninput="checkStrength(this.value)">
                <button type="button" class="pw-eye" onclick="togglePw('newPw',this)" aria-label="Tampilkan">
                  <i class="bi bi-eye-fill"></i>
                </button>
              </div>
              <div class="pw-strength" id="pwStrength" style="display:none;">
                <div class="pw-strength-bar"><div class="pw-strength-fill" id="pwBar"></div></div>
                <div class="pw-strength-text" id="pwText"></div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Konfirmasi Password Baru</label>
              <div class="pw-wrap">
                <input type="password" name="confirm_password" class="form-input" id="confPw" placeholder="Ulangi password baru" required oninput="checkConfirm()">
                <button type="button" class="pw-eye" onclick="togglePw('confPw',this)" aria-label="Tampilkan">
                  <i class="bi bi-eye-fill"></i>
                </button>
              </div>
              <span class="form-hint" id="matchHint"></span>
            </div>
          </div>
          <div style="margin-top:22px;">
            <button type="submit" class="btn btn-green"><i class="bi bi-shield-check-fill"></i> Perbarui Password</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── TAB: Booking History ───────────────────────── -->
    <div class="section-card <?= $activeTab === 'history' ? 'visible' : '' ?>" id="tab-history">
      <div class="section-header">
        <div class="section-icon-wrap">
          <i class="bi bi-journal-bookmark-fill"></i>
        </div>
        <div>
          <div class="section-title">Booking History</div>
          <div class="section-sub"><?= $totalBookings ?> booking tercatat</div>
        </div>
        <div style="margin-left:auto;">
          <a href="booking.php" class="btn btn-outline btn-sm"><i class="bi bi-plus-lg"></i> Booking Baru</a>
        </div>
      </div>
      <div class="section-body">
        <?php if (empty($bookingHistory)): ?>
        <div class="empty-state">
          <div class="empty-icon"><i class="bi bi-calendar-x"></i></div>
          <div class="empty-title">Belum Ada Booking</div>
          <div class="empty-sub">Riwayat booking kamu akan muncul di sini.</div>
          <a href="booking.php" class="btn btn-green"><i class="bi bi-calendar-plus-fill"></i> Pesan Lapangan</a>
        </div>
        <?php else: ?>
        <div class="booking-list">
          <?php foreach ($bookingHistory as $b):
            $slots   = array_map('trim', explode(',', $b['JAM_MULAI']));
            $slotCnt = count($slots);
            $total   = $slotCnt * (int)$b['HARGA_PER_JAM'];
            $timeStr = implode(', ', array_map(fn($s) => sprintf('%02d:00', (int)$s), $slots));
            $stClass = match($b['STATUS_BOOKING']) {
              'LUNAS' => 'badge-green', 'DP' => 'badge-amber',
              'BATAL' => 'badge-red',  default => 'badge-gray'
            };
            $pmClass = match($b['STATUS_PEMBAYARAN'] ?? '') {
              'LUNAS' => 'badge-green', 'PENDING' => 'badge-amber',
              'GAGAL' => 'badge-red',  'REFUND'  => 'badge-blue',
              default => 'badge-gray'
            };
          ?>
          <div class="booking-row">
            <div>
              <div class="booking-code"><?= e($b['ID_BOOKING']) ?></div>
              <div class="booking-field"><?= e($b['NAMA_LAPANGAN']) ?></div>
              <div class="booking-meta">
                <span><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($b['TGL_MAIN'])) ?></span>
                <span><i class="bi bi-clock"></i> <?= e($timeStr) ?></span>
                <span><i class="bi bi-hourglass-split"></i> <?= $slotCnt ?> jam</span>
                <?php if (!empty($b['METODE_PEMBAYARAN'])): ?>
                <span><i class="bi bi-credit-card-fill"></i> <?= e($b['METODE_PEMBAYARAN']) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <div class="booking-right">
              <div class="booking-price"><?= formatRupiah($total) ?></div>
              <div class="booking-badges">
                <span class="badge <?= $stClass ?>"><?= e($b['STATUS_BOOKING']) ?></span>
                <?php if (!empty($b['STATUS_PEMBAYARAN'])): ?>
                <span class="badge <?= $pmClass ?>"><?= e($b['STATUS_PEMBAYARAN']) ?></span>
                <?php endif; ?>
              </div>
              <div class="booking-date"><i class="bi bi-clock-history"></i> Dipesan <?= date('d M Y', strtotime($b['TANGGAL_BOOKING'])) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /main-content -->
</div><!-- /page-wrap -->

<!-- ── THREE.JS + GSAP ── -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>

/* ================================================================
   NAV CTA — GSAP MAGNETIC (sama persis seperti index.php)
================================================================ */
const navCta = document.getElementById('navCtaBack');
if (navCta && window.matchMedia('(pointer:fine)').matches) {
  navCta.addEventListener('mousemove', e => {
    const rect  = navCta.getBoundingClientRect();
    const relX  = e.clientX - (rect.left + rect.width  / 2);
    const relY  = e.clientY - (rect.top  + rect.height / 2);
    gsap.to(navCta, { x: relX * 0.28, y: relY * 0.28, duration: 0.35, ease: 'power2.out' });
  });
  navCta.addEventListener('mouseleave', () => {
    gsap.to(navCta, { x: 0, y: 0, duration: 0.7, ease: 'elastic.out(1, 0.3)' });
  });
}

/* ================================================================
   TAB SWITCHING
================================================================ */
const TABS = ['info','avatar','social','security','history'];
function switchTab(tab) {
  TABS.forEach(t => {
    const card = document.getElementById('tab-' + t);
    const nav  = document.querySelector('.snav-item[onclick="switchTab(\'' + t + '\')"]');
    if (card) card.classList.toggle('visible', t === tab);
    if (nav)  nav.classList.toggle('active',  t === tab);
  });
  history.replaceState(null, '', '?tab=' + tab);
}

/* ================================================================
   PHOTO PREVIEW
================================================================ */
function handlePhotoChange(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const reader = new FileReader();
  reader.onload = function(e) {
    // sidebar avatar
    const ap = document.getElementById('avatarPreview');
    if (ap.tagName === 'IMG') {
      ap.src = e.target.result;
    } else {
      const img = document.createElement('img');
      img.src = e.target.result; img.className = 'avatar-img'; img.id = 'avatarPreview';
      ap.replaceWith(img);
    }
    // photo tab thumb
    const pt = document.getElementById('photoThumb');
    if (pt) {
      if (pt.tagName === 'IMG') {
        pt.src = e.target.result;
      } else {
        const img2 = document.createElement('img');
        img2.src = e.target.result; img2.className = 'photo-thumb'; img2.id = 'photoThumb';
        pt.replaceWith(img2);
      }
    }
    // zone text
    const zt = document.querySelector('.upload-zone-text');
    if (zt) zt.innerHTML = '<strong>' + file.name + '</strong>';
    const btn = document.getElementById('btnUpload');
    if (btn) btn.style.display = 'inline-flex';
  };
  reader.readAsDataURL(file);
}

// Drag & drop
const uploadZone = document.getElementById('uploadZone');
if (uploadZone) {
  uploadZone.addEventListener('dragover',  e => { e.preventDefault(); uploadZone.style.borderColor='var(--green)'; uploadZone.style.background='rgba(0,255,136,.05)'; });
  uploadZone.addEventListener('dragleave', () => { uploadZone.style.borderColor=''; uploadZone.style.background=''; });
  uploadZone.addEventListener('drop', e => {
    e.preventDefault(); uploadZone.style.borderColor=''; uploadZone.style.background='';
    const fi = document.getElementById('fotoInput');
    if (fi && e.dataTransfer.files.length) { fi.files = e.dataTransfer.files; handlePhotoChange(fi); }
  });
}

/* ================================================================
   PASSWORD HELPERS
================================================================ */
function togglePw(inputId, btn) {
  const inp = document.getElementById(inputId);
  if (!inp) return;
  const show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  const ic = btn.querySelector('i');
  if (ic) {
    ic.className = show ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
  }
}

function checkStrength(val) {
  const wrap = document.getElementById('pwStrength');
  const bar  = document.getElementById('pwBar');
  const txt  = document.getElementById('pwText');
  if (!wrap) return;
  if (!val) { wrap.style.display='none'; return; }
  wrap.style.display = 'block';
  let s = 0;
  if (val.length >= 8)           s++;
  if (val.length >= 12)          s++;
  if (/[A-Z]/.test(val))        s++;
  if (/[0-9]/.test(val))        s++;
  if (/[^A-Za-z0-9]/.test(val)) s++;
  const levels = [
    { w:'20%', c:'#ff3b5c', t:'Sangat Lemah' },
    { w:'40%', c:'#ff7033', t:'Lemah' },
    { w:'60%', c:'#ffb600', t:'Cukup' },
    { w:'80%', c:'#00e676', t:'Kuat' },
    { w:'100%',c:'#00ff88', t:'Sangat Kuat' },
  ];
  const lv = levels[Math.min(s,4)];
  bar.style.width = lv.w; bar.style.background = lv.c;
  txt.textContent = lv.t; txt.style.color = lv.c;
}

function checkConfirm() {
  const nv = document.getElementById('newPw')?.value;
  const cv = document.getElementById('confPw')?.value;
  const hint = document.getElementById('matchHint');
  if (!hint || !cv) return;
  hint.textContent = cv === nv ? '✓ Password cocok' : '✗ Password tidak cocok';
  hint.style.color  = cv === nv ? 'var(--green)' : '#ff8099';
}

/* ================================================================
   AUTO-HIDE ALERT
================================================================ */
const alertBox = document.getElementById('alertBox');
if (alertBox) {
  setTimeout(() => {
    alertBox.style.transition = 'opacity .5s';
    alertBox.style.opacity = '0';
    setTimeout(() => alertBox.remove(), 500);
  }, 4500);
}

/* ================================================================
   FLOATING LINES (WebGL via Three.js)
================================================================ */
(function initFloatingLines() {
  const canvas = document.getElementById('fl-canvas');
  if (!canvas || typeof THREE === 'undefined') return;

  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: false });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

  const scene  = new THREE.Scene();
  const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);
  camera.position.z = 1;

  const vertexShader = `
    precision highp float;
    void main() { gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0); }
  `;

  const fragmentShader = `
    precision highp float;
    uniform float iTime;
    uniform vec3  iResolution;
    uniform float animationSpeed;
    uniform bool  enableMiddle;
    uniform int   middleLineCount;
    uniform float middleLineDistance;
    uniform vec3  middleWavePosition;
    uniform vec2  iMouse;
    uniform bool  interactive;
    uniform float bendRadius;
    uniform float bendStrength;
    uniform float bendInfluence;
    uniform bool  parallax;
    uniform vec2  parallaxOffset;
    uniform vec3  lineGradient[8];
    uniform int   lineGradientCount;

    mat2 rotate(float r){ return mat2(cos(r),sin(r),-sin(r),cos(r)); }

    vec3 getColor(float t) {
      if (lineGradientCount <= 0) return vec3(0.0, 0.7, 0.4);
      if (lineGradientCount == 1) return lineGradient[0];
      float s = clamp(t, 0.0, 0.9999) * float(lineGradientCount - 1);
      int   i = int(floor(s));
      float f = fract(s);
      int   j = min(i + 1, lineGradientCount - 1);
      return mix(lineGradient[i], lineGradient[j], f) * 0.55;
    }

    float wave(vec2 uv, float offset, vec2 screenUv, vec2 mouseUv) {
      float time = iTime * animationSpeed;
      float amp  = sin(offset + time * 0.2) * 0.28;
      float y    = sin(uv.x + offset + time * 0.1) * amp;
      if (interactive) {
        vec2  d = screenUv - mouseUv;
        float infl = exp(-dot(d,d) * bendRadius);
        y += (mouseUv.y - screenUv.y) * infl * bendStrength * bendInfluence;
      }
      float m = uv.y - y;
      return 0.018 / max(abs(m) + 0.01, 1e-3) + 0.01;
    }

    void mainImage(out vec4 col, in vec2 fc) {
      vec2 uv = (2.0 * fc - iResolution.xy) / iResolution.y;
      uv.y *= -1.0;
      if (parallax) uv += parallaxOffset;
      vec2 mouseUv = vec2(0.0);
      if (interactive) {
        mouseUv = (2.0 * iMouse - iResolution.xy) / iResolution.y;
        mouseUv.y *= -1.0;
      }
      vec3 c = vec3(0.0);
      if (enableMiddle) {
        for (int i = 0; i < middleLineCount; i++) {
          float fi = float(i);
          float t  = fi / max(float(middleLineCount - 1), 1.0);
          vec3  lc = getColor(t);
          float angle = middleWavePosition.z * log(length(uv) + 1.0);
          vec2  ruv = uv * rotate(angle);
          c += lc * wave(ruv + vec2(middleLineDistance * fi + middleWavePosition.x, middleWavePosition.y), 2.0 + 0.15 * fi, uv, mouseUv);
        }
      }
      col = vec4(c, 1.0);
    }

    void main() { vec4 c = vec4(0.0); mainImage(c, gl_FragCoord.xy); gl_FragColor = c; }
  `;

  const uniforms = {
    iTime:              { value: 0 },
    iResolution:        { value: new THREE.Vector3(1,1,1) },
    animationSpeed:     { value: 0.9 },
    enableMiddle:       { value: true },
    middleLineCount:    { value: 14 },
    middleLineDistance: { value: 0.06 },
    middleWavePosition: { value: new THREE.Vector3(5.0, 0.0, 0.2) },
    iMouse:             { value: new THREE.Vector2(-1000,-1000) },
    interactive:        { value: true },
    bendRadius:         { value: 5.0 },
    bendStrength:       { value: -0.45 },
    bendInfluence:      { value: 0 },
    parallax:           { value: true },
    parallaxOffset:     { value: new THREE.Vector2(0,0) },
    lineGradient:       { value: Array.from({ length:8 }, () => new THREE.Vector3(1,1,1)) },
    lineGradientCount:  { value: 3 },
  };

  const palette = ['#00ff88','#00cc66','#004422'];
  palette.forEach((hex, i) => {
    const v = parseInt(hex.slice(1), 16);
    uniforms.lineGradient.value[i].set(((v>>16)&255)/255, ((v>>8)&255)/255, (v&255)/255);
  });

  const mat  = new THREE.ShaderMaterial({ uniforms, vertexShader, fragmentShader });
  const mesh = new THREE.Mesh(new THREE.PlaneGeometry(2,2), mat);
  scene.add(mesh);

  const clock = new THREE.Clock();

  function resize() {
    const w = window.innerWidth, h = window.innerHeight;
    renderer.setSize(w, h, false);
    uniforms.iResolution.value.set(renderer.domElement.width, renderer.domElement.height, 1);
  }
  resize();
  window.addEventListener('resize', resize);

  const targetMouse     = new THREE.Vector2(-1000,-1000);
  const currentMouse    = new THREE.Vector2(-1000,-1000);
  const targetParallax  = new THREE.Vector2(0,0);
  const currentParallax = new THREE.Vector2(0,0);
  let   targetInfl = 0, currentInfl = 0;
  const damp = 0.055;

  document.addEventListener('mousemove', e => {
    const dpr = renderer.getPixelRatio();
    targetMouse.set(e.clientX * dpr, (window.innerHeight - e.clientY) * dpr);
    targetInfl = 1.0;
    const ox = (e.clientX/window.innerWidth  - 0.5) * 0.18;
    const oy = -(e.clientY/window.innerHeight - 0.5) * 0.18;
    targetParallax.set(ox, oy);
  });
  document.addEventListener('mouseleave', () => { targetInfl = 0; });

  (function animate() {
    requestAnimationFrame(animate);
    uniforms.iTime.value = clock.getElapsedTime();
    currentMouse.lerp(targetMouse, damp);
    uniforms.iMouse.value.copy(currentMouse);
    currentInfl += (targetInfl - currentInfl) * damp;
    uniforms.bendInfluence.value = currentInfl;
    currentParallax.lerp(targetParallax, damp);
    uniforms.parallaxOffset.value.copy(currentParallax);
    renderer.render(scene, camera);
  })();
})();

/* ================================================================
   AVATAR LINK — click → go to avatar tab and open file picker
================================================================ */
document.getElementById('avatarLink').addEventListener('click', function(e) {
  if (window.location.search.includes('tab=avatar')) {
    e.preventDefault();
    document.getElementById('fotoInput')?.click();
    return;
  }
});
</script>
</body>
</html>