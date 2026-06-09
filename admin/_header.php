<?php
// admin/_header.php — Shared Admin Layout
// Require auth before including this file
if (!defined('ADMIN_PAGE')) {
    define('ADMIN_PAGE', true);
}
require_once __DIR__ . '/../config.php';
startSecureSession();
requireAdmin();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$adminName   = $_SESSION['user_name'] ?? 'Administrator';

function navItem(string $href, string $icon, string $label, string $current): string {
    $page    = basename($href, '.php');
    $active  = ($current === $page) ? 'active' : '';
    return "<a href=\"$href\" class=\"nav-item $active\">
              <span class=\"nav-icon\">$icon</span>
              <span class=\"nav-label\">$label</span>
            </a>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MiniFut Admin — <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Anton&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
:root{
  --black:#060608;--dark:#0c0d10;--card:#111318;--card2:#161820;
  --border:rgba(255,255,255,.06);--border2:rgba(255,255,255,.1);
  --green:#00ff88;--glow:rgba(0,255,136,.18);--glow-sm:rgba(0,255,136,.07);
  --gray:#6b7080;--gray2:#9aa0b0;--white:#eceef2;
  --red:#ff3b5c;--amber:#ffb600;--blue:#3b82f6;
  --sidebar-w:240px;
}
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{background:var(--black);color:var(--white);font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;display:flex;}

/* ── SIDEBAR ─────────────────────────────────────────── */
.sidebar{
  width:var(--sidebar-w);min-height:100vh;
  background:var(--dark);border-right:1px solid var(--border);
  display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100;
}
.sidebar-logo{
  padding:28px 24px 20px;border-bottom:1px solid var(--border);
}
.sidebar-logo .logo{
  font-family:'Orbitron',monospace;font-size:1.15rem;font-weight:900;
  color:var(--green);letter-spacing:4px;text-decoration:none;
}
.sidebar-logo .logo em{color:var(--white);font-style:normal;}
.sidebar-logo .badge{
  font-family:'Plus Jakarta Sans',sans-serif;font-size:.58rem;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;color:var(--gray2);
  margin-top:4px;display:block;
}

.sidebar-nav{flex:1;padding:16px 12px;overflow-y:auto;}
.nav-section{
  font-family:'Plus Jakarta Sans',sans-serif;font-size:.58rem;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;color:var(--gray);
  padding:12px 12px 6px;
}
.nav-item{
  display:flex;align-items:center;gap:12px;padding:10px 12px;
  border-radius:8px;text-decoration:none;color:var(--gray2);
  font-family:'Plus Jakarta Sans',sans-serif;font-size:.84rem;
  transition:all .2s;margin-bottom:2px;
}
.nav-item:hover{background:rgba(255,255,255,.04);color:var(--white);}
.nav-item.active{background:rgba(0,255,136,.08);color:var(--green);border-left:2px solid var(--green);}
.nav-icon{font-size:1rem;flex-shrink:0;width:20px;text-align:center;}
.nav-label{font-size:.84rem;}

.sidebar-footer{
  padding:16px 12px;border-top:1px solid var(--border);
}
.user-info{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;background:rgba(255,255,255,.03);}
.user-avatar{
  width:32px;height:32px;border-radius:50%;
  background:rgba(0,255,136,.15);border:1px solid rgba(0,255,136,.25);
  display:flex;align-items:center;justify-content:center;
  font-family:'Orbitron',monospace;font-size:.7rem;font-weight:700;color:var(--green);
  flex-shrink:0;
}
.user-name{font-family:'Plus Jakarta Sans',sans-serif;font-size:.78rem;font-weight:600;color:var(--white);}
.user-role{font-size:.65rem;color:var(--gray);}
.btn-logout{
  display:block;width:100%;margin-top:8px;
  font-family:'Plus Jakarta Sans',sans-serif;font-size:.68rem;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;text-align:center;
  color:var(--gray2);background:transparent;
  border:1px solid var(--border2);border-radius:6px;padding:8px;
  text-decoration:none;transition:all .2s;
}
.btn-logout:hover{color:var(--red);border-color:rgba(255,59,92,.3);}

/* ── MAIN CONTENT ──────────────────────────────────────── */
.main{margin-left:var(--sidebar-w);flex:1;min-height:100vh;display:flex;flex-direction:column;}

.topbar{
  background:var(--dark);border-bottom:1px solid var(--border);
  padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;
  position:sticky;top:0;z-index:50;
}
.page-title{font-family:'Orbitron',monospace;font-size:.95rem;font-weight:700;color:var(--white);}
.breadcrumb{font-size:.78rem;color:var(--gray2);}
.breadcrumb a{color:var(--green);text-decoration:none;}
.topbar-right{display:flex;align-items:center;gap:16px;}
.view-site-btn{
  font-family:'Plus Jakarta Sans',sans-serif;font-size:.65rem;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  color:var(--green);border:1px solid rgba(0,255,136,.25);
  background:rgba(0,255,136,.05);padding:7px 14px;border-radius:100px;
  text-decoration:none;transition:all .2s;
}
.view-site-btn:hover{background:rgba(0,255,136,.12);}

.content{padding:32px;flex:1;}

/* ── CARDS & STATS ──────────────────────────────────────── */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:32px;}
.stat-card{
  background:var(--card);border:1px solid var(--border);border-radius:12px;
  padding:24px;position:relative;overflow:hidden;
}
.stat-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,transparent,var(--green),transparent);
}
.stat-label{font-family:'Plus Jakarta Sans',sans-serif;font-size:.65rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gray2);margin-bottom:12px;}
.stat-value{font-family:'Orbitron',monospace;font-size:1.8rem;font-weight:700;color:var(--green);}
.stat-sub{font-size:.78rem;color:var(--gray);margin-top:4px;}

/* ── TABLES ──────────────────────────────────────────────── */
.table-card{background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;}
.table-header{
  padding:20px 24px;display:flex;align-items:center;justify-content:space-between;
  border-bottom:1px solid var(--border);flex-wrap:wrap;gap:12px;
}
.table-title{font-family:'Orbitron',monospace;font-size:.88rem;font-weight:700;color:var(--white);}
.table-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}

/* Search */
.search-wrap{position:relative;}
.search-wrap input{
  background:rgba(255,255,255,.04);border:1px solid var(--border2);
  border-radius:8px;padding:8px 12px 8px 36px;
  font-family:'Plus Jakarta Sans',sans-serif;font-size:.82rem;color:var(--white);
  outline:none;width:220px;transition:border-color .25s;
}
.search-wrap input:focus{border-color:rgba(0,255,136,.35);}
.search-wrap input::placeholder{color:var(--gray);}
.search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--gray);font-size:.9rem;}

/* Buttons */
.btn{
  font-family:'Plus Jakarta Sans',sans-serif;font-size:.7rem;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;border-radius:6px;
  padding:8px 16px;cursor:pointer;text-decoration:none;
  display:inline-flex;align-items:center;gap:6px;transition:all .2s;border:none;
}
.btn-green{color:var(--black);background:var(--green);box-shadow:0 2px 8px rgba(0,255,136,.2);}
.btn-green:hover{box-shadow:0 4px 16px rgba(0,255,136,.35);transform:translateY(-1px);}
.btn-outline{color:var(--green);background:transparent;border:1px solid rgba(0,255,136,.3);}
.btn-outline:hover{background:rgba(0,255,136,.08);}
.btn-red{color:var(--white);background:rgba(255,59,92,.15);border:1px solid rgba(255,59,92,.3);}
.btn-red:hover{background:rgba(255,59,92,.25);}
.btn-blue{color:var(--white);background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.3);}
.btn-blue:hover{background:rgba(59,130,246,.25);}
.btn-amber{color:var(--white);background:rgba(255,182,0,.15);border:1px solid rgba(255,182,0,.3);}
.btn-amber:hover{background:rgba(255,182,0,.25);}
.btn-sm{padding:5px 10px;font-size:.62rem;}

table{width:100%;border-collapse:collapse;}
thead tr{border-bottom:1px solid var(--border2);}
th{padding:12px 16px;font-family:'Plus Jakarta Sans',sans-serif;font-size:.62rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gray2);text-align:left;}
td{padding:12px 16px;font-size:.84rem;color:var(--gray2);border-bottom:1px solid var(--border);vertical-align:middle;}
tbody tr:last-child td{border-bottom:none;}
tbody tr:hover td{background:rgba(255,255,255,.02);}
.td-green{color:var(--green);font-family:'Orbitron',monospace;font-size:.78rem;}
.td-white{color:var(--white);}

/* Badges */
.badge{display:inline-block;padding:3px 10px;border-radius:100px;font-family:'Plus Jakarta Sans',sans-serif;font-size:.6rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;}
.badge-green{background:rgba(0,255,136,.1);color:var(--green);border:1px solid rgba(0,255,136,.2);}
.badge-red{background:rgba(255,59,92,.1);color:#ff7096;border:1px solid rgba(255,59,92,.2);}
.badge-amber{background:rgba(255,182,0,.1);color:var(--amber);border:1px solid rgba(255,182,0,.2);}
.badge-gray{background:rgba(107,112,128,.1);color:var(--gray2);border:1px solid rgba(107,112,128,.2);}
.badge-blue{background:rgba(59,130,246,.1);color:#60a5fa;border:1px solid rgba(59,130,246,.2);}

/* Pagination */
.pagination{display:flex;align-items:center;gap:6px;padding:16px 24px;border-top:1px solid var(--border);}
.page-btn{
  font-family:'Plus Jakarta Sans',sans-serif;font-size:.7rem;font-weight:600;
  padding:6px 12px;border-radius:6px;text-decoration:none;
  color:var(--gray2);border:1px solid var(--border);transition:all .2s;
}
.page-btn:hover{border-color:rgba(0,255,136,.3);color:var(--green);}
.page-btn.active{background:rgba(0,255,136,.1);border-color:rgba(0,255,136,.3);color:var(--green);}
.page-btn.disabled{opacity:.3;pointer-events:none;}
.page-info{font-size:.75rem;color:var(--gray);margin-left:auto;}

/* Modal */
.modal-overlay{
  display:none;position:fixed;inset:0;z-index:1000;
  background:rgba(0,0,0,.75);backdrop-filter:blur(4px);
  align-items:center;justify-content:center;padding:24px;
}
.modal-overlay.open{display:flex;}
.modal{
  background:var(--card);border:1px solid var(--border2);
  border-radius:16px;width:100%;max-width:520px;
  max-height:90vh;overflow-y:auto;
  animation:modalIn .3s cubic-bezier(.22,1,.36,1) both;
}
@keyframes modalIn{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}
.modal-head{
  padding:24px 28px 20px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  position:sticky;top:0;background:var(--card);
}
.modal-title{font-family:'Orbitron',monospace;font-size:.9rem;font-weight:700;color:var(--white);}
.modal-close{background:none;border:none;color:var(--gray2);font-size:1.3rem;cursor:pointer;padding:4px;transition:color .2s;}
.modal-close:hover{color:var(--white);}
.modal-body{padding:24px 28px 28px;}

/* Form */
.form-group{margin-bottom:18px;}
.form-label{display:block;font-family:'Plus Jakarta Sans',sans-serif;font-size:.65rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gray2);margin-bottom:7px;}
.form-input,.form-select,.form-textarea{
  width:100%;background:rgba(255,255,255,.03);border:1px solid var(--border2);
  border-radius:8px;padding:10px 12px;
  font-family:'Plus Jakarta Sans',sans-serif;font-size:.88rem;color:var(--white);
  outline:none;transition:border-color .25s;
}
.form-select option{background:var(--dark);}
.form-textarea{min-height:80px;resize:vertical;}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:rgba(0,255,136,.45);}
.form-input::placeholder,.form-textarea::placeholder{color:var(--gray);}

/* Photo preview */
.photo-preview{
  width:80px;height:80px;border-radius:50%;object-fit:cover;
  border:2px solid rgba(0,255,136,.25);background:rgba(255,255,255,.04);
  display:flex;align-items:center;justify-content:center;font-size:2rem;
}
.photo-preview img{width:100%;height:100%;border-radius:50%;object-fit:cover;}
.upload-area{
  border:1px dashed var(--border2);border-radius:8px;padding:16px;
  text-align:center;cursor:pointer;transition:border-color .25s;
  position:relative;overflow:hidden;
}
.upload-area:hover{border-color:rgba(0,255,136,.35);}
.upload-area input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.upload-text{font-size:.78rem;color:var(--gray);}

/* Alert */
.alert{padding:12px 16px;border-radius:8px;font-size:.84rem;margin-bottom:20px;}
.alert-success{background:rgba(0,255,136,.08);border:1px solid rgba(0,255,136,.2);color:#4dffa0;}
.alert-error{background:rgba(255,59,92,.08);border:1px solid rgba(255,59,92,.2);color:#ff7096;}

/* Foto lapangan */
.field-photo{width:56px;height:40px;border-radius:4px;object-fit:cover;background:rgba(255,255,255,.04);}
.avatar-sm{width:36px;height:36px;border-radius:50%;object-fit:cover;background:rgba(255,255,255,.04);border:1px solid var(--border);}

/* Empty state */
.empty-state{padding:48px;text-align:center;color:var(--gray);}
.empty-icon{font-size:2.5rem;margin-bottom:12px;opacity:.4;}
.empty-text{font-size:.84rem;}

/* Responsive */
@media(max-width:900px){
  .sidebar{transform:translateX(-100%);transition:transform .3s;}
  .sidebar.open{transform:translateX(0);}
  .main{margin-left:0;}
  .content{padding:20px;}
  .topbar{padding:0 20px;}
  .hamburger{display:block!important;}
}
.hamburger{display:none;background:none;border:none;color:var(--white);font-size:1.3rem;cursor:pointer;}
</style>
</head>
<body>
<!-- ── SIDEBAR ──────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <a href="dashboard.php" class="logo">MINI<em>FUT</em></a>
    <span class="badge"><i class="bi bi-gear-fill"></i> Admin Panel</span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section">Utama</div>
    <?= navItem('dashboard.php',   '<i class="bi bi-grid"></i>', 'Dashboard',    $currentPage) ?>

    <div class="nav-section">Manajemen</div>
    <?= navItem('lapangan.php',    '<i class="bi bi-hexagon"></i>', 'Lapangan',     $currentPage) ?>
    <?= navItem('jadwal.php',      '<i class="bi bi-calendar3"></i>', 'Jadwal',       $currentPage) ?>
    <?= navItem('booking.php',     '<i class="bi bi-journal-bookmark-fill"></i>', 'Booking',      $currentPage) ?>
    <?= navItem('pembayaran.php',  '<i class="bi bi-credit-card-fill"></i>', 'Pembayaran',   $currentPage) ?>
    <?= navItem('pelanggan.php',   '<i class="bi bi-people-fill"></i>', 'Pelanggan',    $currentPage) ?>
  </nav>

  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?= strtoupper(substr($adminName,0,1)) ?></div>
      <div>
        <div class="user-name"><?= e($adminName) ?></div>
        <div class="user-role">Administrator</div>
      </div>
    </div>
    <a href="logout.php" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>
</aside>

<!-- ── MAIN ─────────────────────────────────────────────── -->
<main class="main">
  <header class="topbar">
    <div style="display:flex;align-items:center;gap:16px;">
      <button class="hamburger" id="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
      <div>
        <div class="page-title"><?= e($pageTitle ?? 'Dashboard') ?></div>
        <div class="breadcrumb">
          <a href="dashboard.php">Admin</a> / <?= e($pageTitle ?? 'Dashboard') ?>
        </div>
      </div>
    </div>
    <div class="topbar-right">
      <a href="../index.php" class="view-site-btn" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Lihat Website</a>
    </div>
  </header>

  <div class="content">