<?php
// ── AUTENTIKASI: Hanya pelanggan yang sudah login yang bisa akses halaman ini ──
require_once __DIR__ . '/config.php';
startSecureSession();
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

// Ambil data foto profil terbaru dari database berdasarkan ID pelanggan di session
$user_foto = null;
if (isset($_SESSION['user_id'])) {
    try {
        $db = getDB();
        $stmt_user = $db->prepare("SELECT FOTO_PROFIL FROM Pelanggan WHERE ID_PELANGGAN = ?");
        $stmt_user->execute([$_SESSION['user_id']]);
        $user_foto = $stmt_user->fetchColumn();
    } catch (PDOException $e) {
        // Gagal mengambil data, fallback otomatis ke inisial huruf nanti
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="index, follow">
<title>MiniFut — Premium Mini Soccer Arena Yogyakarta</title>
<meta name="description" content="MiniFut is adalah arena mini soccer outdoor premium di Yogyakarta. Rumput sintetis berkualitas tinggi, pencahayaan LED penuh, and facilities terlengkap. Buka setiap hari 08.00–24.00 WIB.">

<!-- Open Graph / Social Media Preview -->
<meta property="og:type" content="website">
<meta property="og:url" content="https://minifut.id/">
<meta property="og:title" content="MiniFut — Premium Mini Soccer Arena Yogyakarta">
<meta property="og:description" content="Arena mini soccer outdoor premium di Yogyakarta. Rumput sintetis berkualitas, LED penuh, fasilitas lengkap. Booking sekarang!">
<meta property="og:image" content="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=1200&q=80">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:title" content="MiniFut — Premium Mini Soccer Arena Yogyakarta">
<meta property="twitter:description" content="Arena mini soccer outdoor premium di Yogyakarta. Booking sekarang!">
<meta property="twitter:image" content="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=1200&q=80">

<!-- Favicon -->
<link class="favicon" rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='16' fill='%23060608'/><text y='.9em' font-size='80' x='10'</text></svg>">

<!-- Google Fonts Preconnect -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&family=Barlow:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>

:root {
  --black:   #060608; --dark:    #0c0d10; --card:    #111318; --card2:   #161820;
  --border:  rgba(255,255,255,0.06); --border2: rgba(255,255,255,0.1);
  --green:   #00ff88; --green2:  --green; --glow:    rgba(0,255,136,0.18); --glow-sm: rgba(0,255,136,0.07);
  --gray:    #6b7080; --gray2:   #9aa0b0; --white:   #eceef2; --red:     #ff3b5c;
}
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{background:var(--black);color:var(--white);font-family:'Barlow',sans-serif;cursor:none;overflow-x:hidden;min-height:100vh;}

/* Custom Cursor */
#cur{position:fixed;width:10px;height:10px;background:var(--green);border-radius:50%;pointer-events:none;z-index:99999;}
#cur-r{position:fixed;width:34px;height:34px;border:1px solid var(--green);border-radius:50%;pointer-events:none;z-index:99998;opacity:.45;}
body:hover #cur{opacity:1;}

/* MATIKAN KURSOR KUSTOM DI HP */
@media (hover: none) and (pointer: coarse) {
  body, a, button, .f-card, .showcase-item, .g-item, .nav-cta, .btn-p, .btn-s, .btn-soft, #lb-close, .val-card, .award-item { cursor: auto !important; }
  #cur, #cur-r { display: none !important; }
}

#noise{position:fixed;inset:0;opacity:.018;pointer-events:none;z-index:8000;
  background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");}


nav {
  position: fixed;
  top: 24px;
  left: 50%;
  transform: translateX(-50%);
  width: calc(100% - 128px);
  max-width: 1400px;
  height: 70px;
  background: rgba(6, 6, 8, 0.4);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 0 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 1001;
  transition: all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
}
nav.stuck {
  background: rgba(6, 6, 8, 0.85);
  backdrop-filter: blur(28px) saturate(180%);
}

nav.shrunk {
  top: 36px;
  width: calc(100% - 160px);
  max-width: 780px;
  height: 54px;
  border-radius: 100px;
  background: rgba(12, 13, 16, 0.75);
  border: 1px solid rgba(0, 255, 136, 0.2);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 20px rgba(0, 255, 136, 0.05);
  padding: 0 24px;
}

.logo {
  font-family: 'Orbitron', monospace;
  font-size: 1.5rem;
  font-weight: 900;
  color: var(--green);
  letter-spacing: 5px;
  text-decoration: none;
  transition: all 0.3s;
}
.logo em { color: var(--white); font-style: normal; }
.logo:hover { text-shadow: 0 0 10px var(--glow); }

nav.shrunk .logo {
  font-size: 1.15rem;
  letter-spacing: 3px;
}

.nav-links {
  display: flex;
  gap: 36px;
  list-style: none;
  transition: all 0.3s;
}
nav.shrunk .nav-links { gap: 20px; }
.nav-links a {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--gray2);
  text-decoration: none;
  transition: color 0.25s;
}
.nav-links a:hover { color: var(--green); }
nav.shrunk .nav-links a { font-size: 0.7rem; }

/* REVISI: Mengubah nav-cta menjadi Premium Green Liquid Glass Button */
.nav-cta {
  font-family: 'Rajdhani', sans-serif;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--green);
  background: rgba(0, 255, 136, 0.06);
  border: 1px solid rgba(0, 255, 136, 0.25);
  padding: 10px 22px;
  cursor: none;
  text-decoration: none;
  border-radius: 30px; /* Fully rounded liquid pill shape */
  transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  overflow: visible;
}
.nav-cta::before {
  content: '';
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  border-radius: 30px;
  pointer-events: none;
  z-index: 1;
  box-shadow: 
    0 0 6px rgba(0,0,0,0.03),
    0 2px 6px rgba(0,0,0,0.08),
    inset 3px 3px 0.5px -3px rgba(0,0,0,0.9),
    inset -3px -3px 0.5px -3px rgba(0,0,0,0.85),
    inset 1px 1px 1px -0.5px rgba(0,0,0,0.6),
    inset -1px -1px 1px -0.5px rgba(0,0,0,0.6),
    inset 0 0 6px 6px rgba(0,0,0,0.12),
    inset 0 0 2px 2px rgba(0,0,0,0.06),
    0 0 12px rgba(255,255,255,0.15);
  transition: all 0.3s ease;
}
.nav-cta-backdrop {
  position: absolute;
  inset: 0;
  border-radius: 30px;
  overflow: hidden;
  z-index: 0;
  backdrop-filter: url("#container-glass") blur(4px);
  -webkit-backdrop-filter: url("#container-glass") blur(4px);
  background: rgba(0, 255, 136, 0.05);
  transition: all 0.3s ease;
}
.nav-cta:hover {
  color: var(--black);
  background: var(--green);
  box-shadow: 0 0 20px rgba(0, 255, 136, 0.4);
  transform: scale(1.05);
}
.nav-cta-logout:hover {
  color: var(--white) !important;
  background: var(--red) !important;
  box-shadow: 0 0 20px rgba(255, 59, 92, 0.5) !important;
}
.nav-cta:hover::before {
  box-shadow: none;
}
.nav-cta:hover .nav-cta-backdrop {
  opacity: 0;
}
nav.shrunk .nav-cta { padding: 8px 16px; font-size: 0.68rem; border-radius: 30px; }

@media (max-width: 1024px) {
  /* REVISI RESPONSIVE: Menyesuaikan panjang awal navbar di mobile pada Hero Section */
  nav { 
    padding: 0 20px; 
    width: calc(100% - 32px); /* Menentukan lebar proporsional pada mobile agar dapat bertransisi memendek */
    top: 16px;
    height: 64px;
    border-radius: 16px;
  }
  .nav-links { display: none; }
  .nav-cta { padding: 10px 20px; font-size: 0.7rem; }
  
  /* REVISI RESPONSIVE: Navbar memendek sedikit saja di mobile, adaptif dan premium */
  nav.shrunk {
    top: 14px;
    width: calc(100% - 64px);
    max-width: none;
    height: 54px;
    border-radius: 12px;
    padding: 0 16px;
    background: rgba(12, 13, 16, 0.85);
    border: 1px solid rgba(0, 255, 136, 0.25);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 15px rgba(0, 255, 136, 0.05);
  }
  nav.shrunk .logo {
    font-size: 1.05rem;
    letter-spacing: 2px;
  }
  nav.shrunk .nav-cta {
    padding: 8px 16px;
    font-size: 0.68rem;
    border-radius: 30px;
  }
}

.container{max-width:1200px;margin:0 auto;padding:0 64px;}
@media (max-width: 1024px) { .container{padding:0 24px;} }
.sec-label{font-family:'Rajdhani',sans-serif;font-size:.68rem;font-weight:700;letter-spacing:5px;text-transform:uppercase;color:var(--green);margin-bottom:10px;}
.sec-title{font-family:'Orbitron',monospace;font-size:clamp(1.8rem,3.8vw,3rem);font-weight:700;line-height:1.08;color:var(--white);}
.sec-sub{font-family:'Barlow',sans-serif;font-size:.95rem;color:var(--gray2);margin-top:12px;line-height:1.75;}

.reveal{opacity:0;}

/* Hero Section */
.hero{position:relative;height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;}

/* Z-INDEX LAYER UNTUK HERO CANVAS */
#shader-canvas{position:absolute;inset:0;width:100%!important;height:100%!important;z-index:0;}
#three-canvas{position:absolute;inset:0;width:100%!important;height:100%!important;z-index:4;opacity:1;}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(6,6,8,.85) 0%,rgba(6,6,8,.3) 60%,rgba(6,6,8,.7) 100%);z-index:2;pointer-events:none;}
.hero-grid{position:absolute;inset:0;z-index:3;background-image:linear-gradient(rgba(0,255,136,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(0,255,136,.03) 1px,transparent 1px);background-size:56px 56px;animation:gridDrift 25s linear infinite;pointer-events:none;}
@keyframes gridDrift{0%{transform:translateY(0)}100%{transform:translateY(56px)}}

.btn-p { clip-path:polygon(0 0, 100% 0, calc(100% - 18px) 100%, 0 100%); padding-right:52px;}
.btn-s { 
  clip-path:polygon(18px 0, 100% 0, 100% 100%, 0 100%); 
  padding-left:52px; 
  margin-left:-4px; 
  border-left:none; 
  position:relative; 
}
.btn-s::before { 
  content:''; position:absolute; top:-1px; left:0; bottom:-1px; width:20px; 
  background:rgba(0,255,136,.35); 
  clip-path:polygon(18px 0, 19.5px 0, 1.5px 100%, 0 100%); 
  transition:background .3s; z-index:1; 
}
.btn-s:hover::before { background:var(--green); }

.btn-p{font-family:'Rajdhani',sans-serif;font-size:.88rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--black);background:var(--green);border:none;padding:17px 44px;cursor:none;text-decoration:none;clip-path:polygon(10px 0%,100% 0%,calc(100% - 10px) 100%,0 100%);transition:all .3s;position:relative;overflow:hidden;}
.btn-p::after{content:'';position:absolute;inset:0;background:rgba(255,255,255,.18);transform:translateX(-100%);transition:.3s;}
.btn-p:hover::after{transform:translateX(0);}
.btn-s{font-family:'Rajdhani',sans-serif;font-size:.88rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--green);background:transparent;border:1px solid rgba(0,255,136,.35);padding:17px 44px;cursor:none;text-decoration:none;transition:all .3s;}
.btn-s:hover{border-color:var(--green);background:var(--glow-sm);}

/* REVISI: SOFTEN EDGE BUTTON UNTUK CTA PALING BAWAH */
.btn-soft {
  font-family: 'Rajdhani', sans-serif;
  font-size: .88rem;
  font-weight: 700;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  color: var(--black);
  background: var(--green);
  border: none;
  padding: 17px 44px;
  cursor: none;
  text-decoration: none;
  border-radius: 12px;
  transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
}
.btn-soft::after {
  content: ''; position: absolute; inset: 0; background: rgba(255, 255, 255, 0.22); transform: translateX(-100%); transition: transform 0.3s ease;
}
.btn-soft:hover::after {
  transform: translateX(0);
}
.btn-soft:hover {
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.5);
  transform: translateY(-3px);
}

@media (max-width: 1024px) {
  .btn-p, .btn-s, .btn-soft { padding: 12px 24px; font-size: 0.75rem; }
  .btn-p { padding-right: 36px; clip-path:polygon(0 0, 100% 0, calc(100% - 14px) 100%, 0 100%); }
  .btn-s { padding-left: 36px; clip-path:polygon(14px 0, 100% 0, 100% 100%, 0 100%); margin-left: -4px; }
  .btn-s::before { width: 16px; clip-path:polygon(14px 0, 15.5px 0, 1.5px 100%, 0 100%); }
}

.fields-sec{padding:120px 0;}
.fields-head{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:56px;}
@media(max-width:768px){.fields-head{flex-direction:column;align-items:flex-start;gap:20px;}}
.fields-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:3px;}
.f-card{background:var(--card);border:1px solid var(--border);position:relative;overflow:hidden;cursor:none;transition:transform .4s,border-color .3s;transform-style:preserve-3d;}
.f-card:hover{border-color:rgba(0,255,136,.35);}
.f-card-img{height:270px;overflow:hidden;position:relative;}
.f-card-img img{width:100%;height:100%;object-fit:cover;filter:brightness(.65) saturate(.7);transition:transform .65s ease,filter .65s ease;}
.f-card:hover .f-card-img img{transform:scale(1.08);filter:brightness(.55) saturate(1.1);}
.f-card-img-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(6,6,8,.95) 0%,transparent 55%);}
.f-card-badge-wrap{position:absolute;bottom:18px;left:20px;z-index:1;}
.f-badge{display:inline-block;font-family:'Rajdhani',sans-serif;font-size:.6rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--green);border:1px solid rgba(0,255,136,.4);padding:3px 10px;background:rgba(0,255,136,.06);margin-bottom:6px;}
.f-card-title{font-family:'Orbitron',monospace;font-size:1.1rem;font-weight:700;color:var(--white);}
.f-card-body{padding:22px;}
.f-tags{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px;}
.f-card-body 
.f-tag{font-family:'Rajdhani',sans-serif;font-size:.65rem;letter-spacing:1px;color:var(--gray2);background:rgba(255,255,255,.04);border:1px solid var(--border);padding:3px 9px;}
.f-price-row{display:flex;justify-content:space-between;align-items:center;}
.f-price-val{font-family:'Orbitron',monospace;font-size:1.2rem;font-weight:700;color:var(--green);}
.f-price-unit{font-family:'Rajdhani',sans-serif;font-size:.72rem;color:var(--gray);margin-left:4px;}
.f-book-btn{font-family:'Rajdhani',sans-serif;font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--green);background:transparent;border:1px solid rgba(0,255,136,.35);padding:8px 18px;cursor:none;text-decoration:none;transition:all .3s;}
.f-book-btn:hover{background:var(--green);color:var(--black);}

/* GALLERY */
.gallery-sec{padding:100px 0;background:var(--dark);overflow:hidden;}
.gallery-head{text-align:center;margin-bottom:52px;}
.gallery-mosaic{display:grid;grid-template-columns:repeat(12,1fr);grid-template-rows:220px 220px;gap:3px;max-width:1200px;margin:0 auto;padding:0 64px;}
.g-item{position:relative;overflow:hidden;cursor:none;}
.g-item:nth-child(1){grid-column:1/6;grid-row:1/2;}
.g-item:nth-child(2){grid-column:6/9;grid-row:1/2;}
.g-item:nth-child(3){grid-column:9/13;grid-row:1/3;}
.g-item:nth-child(4){grid-column:1/5;grid-row:2/3;}
.g-item:nth-child(5){grid-column:5/9;grid-row:2/3;}
.g-item img{width:100%;height:100%;object-fit:cover;filter:brightness(0.3) saturate(0.5);transition:transform .65s ease,filter .65s ease;}
.g-item:hover img{transform:scale(1.07);filter:brightness(1) saturate(1.1);}
.g-overlay{position:absolute;inset:0;background:rgba(0,255,136,.06);opacity:0;transition:opacity .3s;display:flex;align-items:center;justify-content:center;}
.g-item:hover .g-overlay{opacity:1;}
.g-zoom{width:44px;height:44px;border:1px solid var(--green);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--green);font-size:1.1rem;font-weight:300;}
.g-label{position:absolute;bottom:14px;left:14px;font-family:'Rajdhani',sans-serif;font-size:.65rem;letter-spacing:2.5px;text-transform:uppercase;color:var(--green);opacity:0;transition:opacity .3s;}
.g-item:hover .g-label{opacity:1;}

/* ── LIGHTBOX ── */
#lb{position:fixed;inset:0;background:rgba(0,0,0,.96);z-index:9999;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s;}
#lb.open{opacity:1;pointer-events:all;}
#lb img{max-width:82vw;max-height:82vh;object-fit:contain;border:1px solid rgba(0,255,136,.2);}
#lb-close{position:absolute;top:28px;right:32px;font-size:1.4rem;color:var(--green);background:none;border:none;cursor:none;font-family:'Orbitron',monospace; transition:all 0.3s;}
#lb-close:hover { color: var(--white); text-shadow: 0 0 10px var(--green); }

.showcase-sec{padding:120px 0;background:var(--dark);overflow:hidden;}
.showcase-head{text-align:center;margin-bottom:56px;}
.showcase-layout{display:grid;grid-template-columns:1fr 1.5fr;gap:40px;align-items:center;}
@media(max-width:900px){.showcase-layout{grid-template-columns:1fr;}}
.showcase-list{display:flex;flex-direction:column;gap:12px;}
.showcase-item{padding:24px 30px;background:var(--card);border:1px solid var(--border);cursor:none;transition:all .3s;position:relative;}
.showcase-item.active{border-color:var(--green);background:rgba(0,255,136,.03);}
.showcase-item.active::before{content:'';position:absolute;left:-1px;top:-1px;bottom:-1px;width:3px;background:var(--green);}
.sc-title{font-family:'Orbitron',monospace;font-size:1.1rem;font-weight:700;color:var(--white);margin-bottom:8px;transition:color .3s;}
.showcase-item.active .sc-title{color:var(--green);}
.sc-desc{font-family:'Barlow',sans-serif;font-size:.85rem;color:var(--gray2);line-height:1.6;max-height:0;overflow:hidden;transition:max-height .4s ease, opacity .4s ease;opacity:0;}
.showcase-item.active .sc-desc{max-height:100px;opacity:1;margin-top:12px;}
.showcase-visual{height:500px;position:relative;border:1px solid var(--border);overflow:hidden;}
.sc-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity .5s ease, transform 4s ease;transform:scale(1.05);}
.sc-img.active{opacity:1;transform:scale(1);}
.sc-overlay{position:absolute;inset:0;background:linear-gradient(0deg, rgba(6,6,8,0.8) 0%, transparent 40%);}
.sc-progress-bar{position:absolute;bottom:0;left:0;height:3px;background:var(--green);width:0;transition:width 0.1s linear;}
.showcase-item.active .sc-progress-bar{width:100%; transition:width 4s linear;}

/* REVISI: SEKSI SEWA HARGA LEBIH INTERAKTIF & PREMIUM DENGAN HOVER EFFECT & SHINE BORDER */
.pricing-sec{padding:120px 0;position:relative;}
.pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;}

.p-card-wrapper {
  position: relative;
  border-radius: 16px;
  transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
}
.p-card-wrapper:hover {
  transform: translateY(-12px) scale(1.02);
  box-shadow: 0 20px 45px rgba(0, 255, 136, 0.18);
}

/* Shine Border wrapper backgrounds */
.shine-border-bg {
  position: absolute;
  inset: 0;
  border-radius: 16px;
  overflow: hidden;
  pointer-events: none;
  z-index: 0;
}
.p-card-shine {
  position: absolute;
  inset: -150%;
  background: conic-gradient(
    from 0deg,
    transparent 20%,
    var(--green) 40%,
    var(--green) 60%,
    transparent 80%
  );
  animation: spin-shine 4s linear infinite;
  opacity: 0.5; /* Meredam pendaran berlebih agar lebih elegan */
}
.featured-shine {
  animation: spin-shine 3s linear infinite;
  opacity: 0.6; /* Meredam pendaran berlebih pada kartu populer agar tetap seimbang */
}

@keyframes spin-shine {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.p-card{
  padding:40px;
  background:var(--card);
  border:none; /* remove default border to let shine border show */
  position:relative;
  cursor:none;
  transition:background 0.3s;
  margin: 2px; /* leaves 2px of space for the shine border */
  border-radius: 14px;
  height: calc(100% - 4px); /* offsets margin */
}
.p-card-wrapper:hover .p-card {
  background: rgba(17, 19, 24, 0.95);
}
.p-card.featured{background: #0d1612;} /* Menggunakan solid background agar pendaran tidak tembus menerobos teks */
.p-card.featured:hover {background: #111e18;}
.p-card.featured::before{content:'TERPOPULER';position:absolute;top:0;left:50%;transform:translate(-50%,-50%);font-family:'Rajdhani',sans-serif;font-size:.58rem;font-weight:700;letter-spacing:3px;color:var(--black);background:var(--green);padding:4px 16px;white-space:nowrap;z-index:10;}

.p-tag{font-family:'Rajdhani',sans-serif;font-size:.68rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:var(--green);margin-bottom:6px;}
.p-name{font-family:'Orbitron',monospace;font-size:1.1rem;font-weight:700;color:var(--white);margin-bottom:28px;}
.p-price{display:flex;align-items:baseline;gap:3px;margin-bottom:6px;}
.p-cur{font-family:'Rajdhani',sans-serif;font-size:.9rem;color:var(--gray);}
.p-amount{display:inline-block;font-family:'Orbitron',monospace;font-size:2rem;font-weight:700;color:var(--green);transition:transform 0.3s ease, text-shadow 0.3s ease;}
.p-per{font-family:'Rajdhani',sans-serif;font-size:.78rem;color:var(--gray);}
.p-div{height:1px;background:var(--border);margin:24px 0;}
.p-feats{list-style:none;display:flex;flex-direction:column;gap:11px;margin-bottom:28px;}
.p-feats li{font-family:'Barlow',sans-serif;font-size:.84rem;color:var(--gray2);display:flex;align-items:center;gap:10px;}
.p-feats li::before{content:'✦';color:var(--green);font-size:.58rem;flex-shrink:0;}
.p-btn{font-family:'Rajdhani',sans-serif;font-size:.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;display:block;text-align:center;text-decoration:none;padding:13px;border:1px solid rgba(0,255,136,.4);color:var(--green);cursor:none;transition:all .3s;}
.p-card:hover .p-amount { text-shadow: 0 0 15px var(--glow); transform: scale(1.05); }
.p-card:hover .p-btn { background: var(--green); color: var(--black); box-shadow: 0 0 15px rgba(0, 255, 136, 0.4); border-color: var(--green); }
.p-card:hover .p-btn.solid { background: #00e676; box-shadow: 0 0 20px rgba(0,255,136,0.6); }
.p-btn.solid{background:var(--green);color:var(--black);border-color:var(--green);}

/* VALUES SECTION */
.values-sec{padding:130px 0;position:relative;overflow:hidden;}
.values-sec::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(0,255,136,.3),transparent);}
.values-sec::after{content:'';position:absolute;bottom:-150px;left:-150px;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(0,255,136,.04) 0%,transparent 70%);pointer-events:none;}
.values-hex-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:3px;margin-top:56px;}
@media(max-width:768px){.values-hex-grid{grid-template-columns:1fr;}}
.val-card{padding:48px 36px;background:var(--card);border:1px solid var(--border);position:relative;overflow:hidden;transition:all .35s;cursor:none;}
.val-card:hover{border-color:rgba(0,255,136,.25);transform:translateY(-4px);}
.val-card::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at var(--mx,50%) var(--my,50%),rgba(0,255,136,.06) 0%,transparent 65%);opacity:0;transition:opacity .35s;}
.val-card:hover::before{opacity:1;}
.val-num{font-family:'Orbitron',monospace;font-size:4rem;font-weight:900;color:transparent;-webkit-text-stroke:1px rgba(0,255,136,.12);position:absolute;top:16px;right:20px;line-height:1;}
.val-name{font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;color:var(--white);margin-bottom:14px;}
.val-desc{font-family:'Barlow',sans-serif;font-size:.88rem;color:var(--gray2);line-height:1.75;}

/* ── AWARDS MARQUEE ── */
.awards-sec{padding:80px 0;background:var(--dark);overflow:hidden;position:relative;}
.awards-sec::before,.awards-sec::after{content:'';position:absolute;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(0,255,136,.2),transparent);}
.awards-sec::before{top:0;}.awards-sec::after{bottom:0;}
.awards-track{display:flex;gap:0;white-space:nowrap;animation:marqueeMove 30s linear infinite; will-change: transform;}
.awards-sec:hover .awards-track {animation-play-state: paused;}
.awards-track-rev{animation-direction:reverse;animation-duration:35s;margin-top:3px;}
@keyframes marqueeMove{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.award-item{display:inline-flex;align-items:center;gap:16px;padding:20px 36px;background:var(--card);border:1px solid var(--border);margin-right:3px;flex-shrink:0;transition:border-color .3s;cursor:none;}
.award-item:hover{border-color:rgba(0,255,136,.25);}
.award-name{font-family:'Rajdhani',sans-serif;font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gray2);}
.award-year{font-family:'Orbitron',monospace;font-size:.65rem;color:var(--green);}

/* ── CTA SECTION DIPERBARUI BACKGROUND HITAM PEKAT ── */
.cta-sec{padding:130px 0;background:var(--black);text-align:center;position:relative;overflow:hidden;}
.cta-title{font-family:'Orbitron',monospace;font-size:clamp(2rem,5.5vw,4.2rem);font-weight:900;color:var(--white);line-height:1.08;margin-bottom:18px;}
.cta-title span{color:var(--green);}
.cta-sub{font-family:'Barlow',sans-serif;font-size:.95rem;color:var(--gray2);margin-bottom:40px;}

footer{padding:64px 0 28px;border-top:1px solid var(--border);position:relative;z-index:6;background:var(--black);}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:60px;margin-bottom:56px;}
@media(max-width:900px){.footer-grid{grid-template-columns:1fr 1fr;}}
.footer-desc{font-family:'Barlow',sans-serif;font-size:.84rem;color:var(--gray);line-height:1.75;max-width:270px;margin-top:14px;}
.f-title{font-family:'Rajdhani',sans-serif;font-size:.65rem;letter-spacing:3px;text-transform:uppercase;color:var(--gray2);margin-bottom:20px;font-weight:700;}
.f-links{list-style:none;display:flex;flex-direction:column;gap:11px;}
.f-links li, .f-links a{font-family:'Barlow',sans-serif;font-size:.85rem;color:var(--gray);text-decoration:none;transition:color .25s;line-height:1.5;}
.f-links a:hover{color:var(--green);}
.f-links li strong {color:var(--white); font-weight:500;}
.footer-bottom{display:flex;justify-content:space-between;align-items:center;padding-top:32px;border-top:1px solid var(--border);}
.footer-copy{font-family:'Rajdhani',sans-serif;font-size:.7rem;letter-spacing:1px;color:var(--gray);}
::-webkit-scrollbar{width:4px;height:4px;}
::-webkit-scrollbar-thumb{background:rgba(0,255,136,.15) }

.fields-sec, .gallery-sec, .showcase-sec, .pricing-sec, .cta-sec { position: relative; }

/* Separator Lines */
.gallery-sec::before, .showcase-sec::before, .pricing-sec::before, .cta-sec::before {
  content:''; position:absolute; top:0; left:0; right:0; height:1px;
  background:linear-gradient(90deg, transparent, rgba(0,255,136,.3), transparent);
}
.pricing-sec::after {
  content:''; position:absolute; bottom:0; left:0; right:0; height:1px;
  background:linear-gradient(90deg, transparent, rgba(0,255,136,.3), transparent);
}

/* Green Glows */
.cta-sec::after {
  content:''; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
  width:700px; height:350px; border-radius:50%;
  background:radial-gradient(ellipse, rgba(0,255,136,.07) 0%, transparent 70%); pointer-events:none;
}
.showcase-sec::after {
  content:''; position:absolute; top:-150px; right:-150px; width:500px; height:500px;
  border-radius:50%; background:radial-gradient(circle, rgba(0,255,136,.05) 0%, transparent 70%); pointer-events:none;
}
.fields-sec::after {
  content:''; position:absolute; bottom:-150px; left:-150px; width:500px; height:500px;
  border-radius:50%; background:radial-gradient(circle, rgba(0,255,136,.04) 0%, transparent 70%); pointer-events:none; z-index:-1;
}

@media (max-width: 1024px) { 
  .gallery-mosaic { grid-template-columns: 1fr; grid-template-rows: auto; }
  .g-item:nth-child(n) { grid-column: 1 / -1 !important; grid-row: auto !important; height: 250px; }
}

/* ═══════════════════════════════════════════════════════════════
   COMBINASI HIJAU TEPI LAYAR & OUT LINE LAPANGAN PUTIH + DASHED BACKGROUND
   ═══════════════════════════════════════════════════════════════ */
.premium-showcase-container {
  background: var(--black);
  padding: 0;
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 5;
  
  /* Mengunci teks agar tidak dapat terblokir / ter-highlight secara tidak sengaja saat click-and-drag */
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.premium-scroll-sec {
  position: relative;
  height: 100vh;
  width: 100%;
  background: #090a0d;
  box-sizing: border-box;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Container utama untuk kerangka outline overlay (Bingkai Hijau) */
#premium-border-frame {
  position: fixed;
  top: 16px; left: 16px; right: 16px; bottom: 16px;
  border-radius: 40px;
  pointer-events: none;
  opacity: 0;
  z-index: 998; /* Di depan bola untuk masking sisi luar */
  box-shadow: 0 0 0 16px var(--green), 0 0 0 100vmax var(--green); 
}

/* Garis Putih & Diagram Taktis Lapangan (Pindah Ke Belakang Bola) */
#premium-pitch-bg {
  position: fixed;
  top: 16px; left: 16px; right: 16px; bottom: 16px;
  border-radius: 40px;
  pointer-events: none;
  opacity: 0;
  z-index: 6; /* Di belakang canvas bola (z-index 8) agar tidak menghalangi bola */
}

.pitch-tactical-svg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  z-index: 0; 
  opacity: 0.15;
  pointer-events: none;
}

.pitch-white-lines {
  position: absolute;
  inset: 4px;
  border: 4px solid rgba(255, 255, 255, 0.85);
  border-radius: 32px;
  z-index: 1; 
}

/* 4 Busur Sudut Seperempat Lingkaran (Corner kick arcs) */
.pitch-corner-arc {
  position: absolute;
  width: 60px; height: 60px;
  border-color: rgba(255, 255, 255, 0.85);
  border-style: solid;
  border-width: 0;
  z-index: 1000;
}
.pitch-corner-arc.tl { top: 0; left: 0; border-right-width: 4px; border-bottom-width: 4px; border-bottom-right-radius: 100%; }
.pitch-corner-arc.tr { top: 0; right: 0; border-left-width: 4px; border-bottom-width: 4px; border-bottom-left-radius: 100%; }
.pitch-corner-arc.bl { bottom: 0; left: 0; border-right-width: 4px; border-top-width: 4px; border-top-right-radius: 100%; }
.pitch-corner-arc.br { bottom: 0; right: 0; border-left-width: 4px; border-top-width: 4px; border-top-left-radius: 100%; }

/* REVISI RESPONSIVE: Penyesuaian frame tepi & garis sudut lapangan taktis pada mobile */
@media (max-width: 1024px) {
  #premium-border-frame {
    top: 8px; left: 8px; right: 8px; bottom: 8px;
    border-radius: 20px;
    box-shadow: 0 0 0 8px var(--green), 0 0 0 100vmax var(--green);
  }
  #premium-pitch-bg {
    top: 8px; left: 8px; right: 8px; bottom: 8px;
    border-radius: 20px;
  }
  .pitch-white-lines {
    border-width: 2px;
    border-radius: 16px;
    inset: 2px;
  }
  .pitch-corner-arc {
    width: 30px; height: 30px;
  }
  .pitch-corner-arc.tl { border-right-width: 2px; border-bottom-width: 2px; }
  .pitch-corner-arc.tr { border-left-width: 2px; border-bottom-width: 2px; }
  .pitch-corner-arc.bl { border-right-width: 2px; border-top-width: 2px; }
  .pitch-corner-arc.br { border-left-width: 2px; border-top-width: 2px; }
}

/* REVISI DESKTOP: Mengembalikan posisi horizontal MINI FUT di desktop ke tengah layar terpisah */
.sc1-title-container {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 10%;
  pointer-events: none;
  z-index: 7;
  
  /* Proteksi agar judul tidak terblokir */
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.sc1-title-left, .sc1-title-right {
  font-family: 'Orbitron', monospace;
  font-size: clamp(4rem, 18vw, 15rem);
  font-weight: 900;
  color: #ffffff;
  letter-spacing: -4px;
  line-height: 1;
  text-transform: uppercase;
  transform: scaleY(1.3);
  display: inline-block;
  transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1), text-shadow 0.4s ease, color 0.4s ease;
  pointer-events: auto; 
  cursor: default;
}
.sc1-title-left:hover {
  transform: scaleY(1.3) scaleX(1.05) translateX(-20px);
  color: var(--green);
  text-shadow: 0 0 30px var(--glow), 0 0 60px rgba(0, 255, 136, 0.4);
}
.sc1-title-right:hover {
  transform: scaleY(1.3) scaleX(1.05) translateX(20px);
  color: var(--green);
  text-shadow: 0 0 30px var(--glow), 0 0 60px rgba(0, 255, 136, 0.4);
}

/* REVISI RESPONSIVE: Penataan vertical (atas dan bawah) teks "MINI" (sc1-up) & "FUT" (sc1-down) pada mobile agar terbagi bersih di atas dan bawah bola */
@media (max-width: 1024px) {
  .sc1-title-container {
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    top: 50% !important;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 0;
    width: 100%;
    height: 60vh; /* Memberikan batasan tinggi yang presisi agar di tengahnya pas menampung bola 3D */
    gap: 0;
  }
  .sc1-title-left, .sc1-title-right {
    font-size: clamp(3.2rem, 15vw, 6rem);
    transform: scaleY(1.1);
    letter-spacing: 2px;
    display: block;
    text-align: center;
  }
  .sc1-title-left:hover, .sc1-title-left:active {
    transform: scaleY(1.1) scaleX(1.05) translateY(-8px);
    color: var(--green);
    text-shadow: 0 0 25px var(--glow), 0 0 50px rgba(0, 255, 136, 0.4);
  }
  .sc1-title-right:hover, .sc1-title-right:active {
    transform: scaleY(1.1) scaleX(1.05) translateY(8px);
    color: var(--green);
    text-shadow: 0 0 25px var(--glow), 0 0 50px rgba(0, 255, 136, 0.4);
  }
}

/* REVISI DESKTOP: BST TEXT DI DESKTOP TETAP LEBAR DAN PARAGRAF LENGKAP */
.ball-section-text { 
  position: absolute; 
  z-index: 10; 
  top: 50%; 
  transform: translateY(-50%); 
  max-width: 380px; 
  opacity: 0; 
  
  /* Mengunci seleksi teks agar tidak mengganggu fokus gesture mouse drag */
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
#bst-left { left: 8%; }
#bst-right { right: 8%; text-align: right; }
.bst-label { font-family: 'Rajdhani', sans-serif; font-size: .85rem; letter-spacing: 5px; text-transform: uppercase; color: var(--green); margin-bottom: 10px; }
.bst-title { font-family: 'Orbitron', monospace; font-size: clamp(1.4rem, 2.8vw, 2.2rem); font-weight: 700; line-height: 1.15; color: var(--white); }
.bst-body { font-family: 'Barlow', sans-serif; font-size: 1.02rem; color: var(--gray2); margin-top: 14px; line-height: 1.7; display: block; }

/* REVISI RESPONSIVE: Tata letak teks BST di HP (Menghilangkan bst-body hanya di mobile & menyetel max-width ke 160px) */
@media (max-width: 1024px) {
  .ball-section-text {
    max-width: 160px !important; /* Batasi lebar teks agar tidak melebar ke arah bola */
    width: auto;
    top: 50% !important; /* Posisikan pas di tengah vertikal di HP */
    transform: translateY(-50%) !important;
  }
  #bst-left {
    left: 20px;
    right: auto;
    text-align: left;
  }
  #bst-right {
    right: 20px;
    left: auto;
    text-align: right;
  }
  .bst-title {
    font-size: clamp(1.1rem, 5.5vw, 1.4rem);
    line-height: 1.2;
  }
  .bst-body {
    display: none !important; /* Menghapus deskripsi kecil bst-body eksklusif di HP */
  }
}

.scroll-ind.center {
  position: absolute;
  bottom: 40px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  z-index: 5;
  opacity: 1;
}
.scroll-ind.center span {
  font-family: 'Rajdhani', sans-serif;
  font-size: .85rem;
  font-weight: 700;
  letter-spacing: 4px;
  color: var(--green);
  text-transform: uppercase;
}
.scroll-bar {
  width: 2px;
  height: 60px;
  background: linear-gradient(to bottom, var(--green), transparent);
  animation: scrollPulse 2.2s ease-in-out infinite;
}
@keyframes scrollPulse{0%,100%{opacity:.25; transform:translateY(0)} 50%{opacity:1; transform:translateY(8px)}}

/* ── FAN WALL SECTION ── */
.fanwall-sec {
  padding: 110px 0;
  background: var(--black);
  position: relative;
  overflow: hidden;
}

/* Neon green scanlines background */
.fanwall-sec::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    repeating-linear-gradient(
      0deg,
      transparent,
      transparent 38px,
      rgba(0,255,136,0.028) 38px,
      rgba(0,255,136,0.028) 39px
    ),
    repeating-linear-gradient(
      90deg,
      transparent,
      transparent 38px,
      rgba(0,255,136,0.018) 38px,
      rgba(0,255,136,0.018) 39px
    );
  pointer-events: none;
  z-index: 0;
}

/* Soft radial neon glow center */
.fanwall-sec::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 80vw;
  height: 60vh;
  border-radius: 50%;
  background: radial-gradient(ellipse, rgba(0,255,136,0.06) 0%, transparent 68%);
  pointer-events: none;
  z-index: 0;
}

.fanwall-neon-l,
.fanwall-neon-r {
  position: absolute;
  pointer-events: none;
  z-index: 0;
}
.fanwall-neon-l {
  top: -80px; left: -100px;
  width: 520px; height: 2px;
  background: linear-gradient(90deg, transparent, rgba(0,255,136,0.18), transparent);
  transform: rotate(32deg);
  box-shadow: 0 0 18px 3px rgba(0,255,136,0.12);
  animation: neonStreakL 6s ease-in-out infinite alternate;
}
.fanwall-neon-r {
  bottom: -80px; right: -100px;
  width: 520px; height: 2px;
  background: linear-gradient(90deg, transparent, rgba(0,255,136,0.14), transparent);
  transform: rotate(32deg);
  box-shadow: 0 0 18px 3px rgba(0,255,136,0.1);
  animation: neonStreakR 7s ease-in-out infinite alternate;
}
@keyframes neonStreakL {
  0%   { opacity: 0.5; transform: rotate(32deg) translateX(0); }
  100% { opacity: 1;   transform: rotate(32deg) translateX(40px); }
}
@keyframes neonStreakR {
  0%   { opacity: 0.4; transform: rotate(32deg) translateX(0); }
  100% { opacity: 0.9; transform: rotate(32deg) translateX(-40px); }
}

.fanwall-track-wrap {
  position: relative;
  z-index: 1;
  overflow: hidden;
  -webkit-mask-image: linear-gradient(90deg, transparent 0%, black 8%, black 92%, transparent 100%);
  mask-image: linear-gradient(90deg, transparent 0%, black 8%, black 92%, transparent 100%);
}

.fanwall-track {
  display: flex;
  gap: 16px;
  width: max-content;
  animation: fanwallScroll 38s linear infinite;
  will-change: transform;
}
.fanwall-track-rev {
  animation-direction: reverse;
  animation-duration: 44s;
  margin-top: 16px;
}

.fanwall-track-wrap:hover .fanwall-track,
.fanwall-track-wrap:hover .fanwall-track-rev {
  animation-play-state: paused;
}

@keyframes fanwallScroll {
  0%   { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

.fw-card {
  flex-shrink: 0;
  position: relative;
  overflow: hidden;
  border-radius: 12px;
  border: 1px solid rgba(0,255,136,0.08);
  cursor: none;
  transition: transform 0.4s cubic-bezier(0.25,1,0.5,1), border-color 0.3s, box-shadow 0.3s;
}
.fw-card:hover {
  transform: translateY(-8px) scale(1.03);
  border-color: rgba(0,255,136,0.35);
  box-shadow: 0 12px 40px rgba(0,255,136,0.14), 0 0 0 1px rgba(0,255,136,0.1);
}

.fw-card.sz-s  { width: 200px; height: 260px; }
.fw-card.sz-m  { width: 240px; height: 310px; }
.fw-card.sz-l  { width: 200px; height: 260px; }
.fw-card.sz-p  { width: 220px; height: 290px; }

.fanwall-track-rev .fw-card.sz-s  { height: 240px; }
.fanwall-track-rev .fw-card.sz-m  { height: 288px; }
.fanwall-track-rev .fw-card.sz-l  { height: 240px; }
.fanwall-track-rev .fw-card.sz-p  { height: 268px; }

.fw-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: brightness(0.72) saturate(0.75);
  transition: filter 0.55s ease, transform 0.55s ease;
  display: block;
}
.fw-card:hover img {
  filter: brightness(0.9) saturate(1.1);
  transform: scale(1.07);
}

.fw-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(0,255,136,0.1) 0%, transparent 45%);
  opacity: 0;
  transition: opacity 0.35s;
  z-index: 1;
  pointer-events: none;
}
.fw-card:hover::before { opacity: 1; }

.fw-card::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, var(--green), transparent);
  opacity: 0;
  transition: opacity 0.35s;
  z-index: 2;
}
.fw-card:hover::after { opacity: 1; }

@media (max-width: 1024px) {
  .fanwall-sec { padding: 72px 0; }
  .fw-card.sz-s  { width: 150px; height: 195px; }
  .fw-card.sz-m  { width: 180px; height: 230px; }
  .fw-card.sz-l  { width: 150px; height: 195px; }
  .fw-card.sz-p  { width: 164px; height: 215px; }
  .fanwall-track-rev .fw-card.sz-s  { height: 180px; }
  .fanwall-track-rev .fw-card.sz-m  { height: 212px; }
  .fanwall-track-rev .fw-card.sz-l  { height: 180px; }
  .fanwall-track-rev .fw-card.sz-p  { height: 198px; }
  .fanwall-track { gap: 10px; }
  .fanwall-track-rev { margin-top: 10px; }
}
</style>
</head>
<body>
<div id="noise"></div>
<div id="cur"></div>
<div id="cur-r"></div>
<div id="lb"><button id="lb-close">✕</button><img id="lb-img" src="" alt=""></div>

<!-- OUTLINE BINGKAI OVERLAY HIJAU (SCREEN BORDER MASK DI DEPAN BOLA) -->
<div id="premium-border-frame"></div>

<!-- DIAGRAM TAKTIS LAPANGAN & GARIS PUTIH (LATAR BACKGROUND DI BELAKANG BOLA) -->
<div id="premium-pitch-bg">
  <svg class="pitch-tactical-svg" viewBox="0 0 1000 600" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M50,300 C150,100 350,100 500,300 C650,500 850,500 950,300" stroke="rgba(255,255,255,0.4)" stroke-width="2" stroke-dasharray="8,8" />
    <path d="M100,50 C300,150 700,150 900,50" stroke="rgba(255,255,255,0.3)" stroke-width="2" stroke-dasharray="6,6" />
    <circle cx="500" cy="300" r="250" stroke="rgba(255,255,255,0.2)" stroke-width="2" stroke-dasharray="10,10" />
  </svg>

  <div class="pitch-white-lines">
    <div class="pitch-corner-arc tl"></div>
    <div class="pitch-corner-arc tr"></div>
    <div class="pitch-corner-arc bl"></div>
    <div class="pitch-corner-arc br"></div>
  </div>
</div>

<!-- CANVAS BOLA FIXED OVERLAY UNTUK ANIMASI SCROLL SHOWCASE (DI-SET VISIBLE OPACITY: 1) -->
<canvas id="ball-scroll-canvas" style="position: fixed; inset: 0; width: 100vw; height: 100vh; pointer-events: none; z-index: 8; opacity: 1; transition: opacity 0.4s;"></canvas>

<nav id="nav">
  <a href="#" class="logo">MINI<em>FUT</em></a>
  <ul class="nav-links" id="nav-links">
    <li><a href="#lapangan">Lapangan</a></li>
    <li><a href="#galeri">Galeri</a></li>
    <li><a href="#fasilitas">Fasilitas</a></li>
    <li><a href="#harga">Harga</a></li>
    <li><a href="booking.php">Booking</a></li>
  </ul>
  <!-- AUTH: Tampilkan profil user + logout jika sudah login, atau Book Sekarang jika belum -->
  <?php if (isPelanggan()): ?>
  <div style="display:flex;align-items:center;gap:12px;">
    
    <?php if (!empty($user_foto)): ?>
      <img src="admin/uploads/<?= htmlspecialchars($user_foto, ENT_QUOTES, 'UTF-8') ?>" 
          style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid var(--green); box-shadow: 0 0 10px var(--glow); flex-shrink: 0;" 
          alt="Foto Profil">
    <?php else: ?>
      <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(0, 255, 136, 0.1); border: 2px solid var(--green); display: flex; align-items: center; justify-content: center; font-family: 'Orbitron', monospace; font-size: 0.85rem; font-weight: 700; color: var(--green); box-shadow: 0 0 10px var(--glow); flex-shrink: 0;">
        <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
      </div>
    <?php endif; ?>

    <a href="auth/logout.php" class="nav-cta nav-cta-logout" style="background:rgba(255,59,92,.06); border-color:rgba(255,59,92,0.25); color:#ff7096; padding: 6px 14px; border-radius: 30px; font-size: 0.68rem;">
      <span style="position:relative;z-index:2;">Logout</span>
    </a>

  </div>
  <?php else: ?>
  <a href="booking.php" class="nav-cta">
    <span class="nav-cta-backdrop"></span>
    <span style="position: relative; z-index: 2;">Book Sekarang</span>
  </a>
  <?php endif; ?>
</nav>

<!-- PINNED SHOWCASE CONTAINER UTAMA (MENCAKUP HERO DAN SECTION 2-4) -->
<div class="premium-showcase-container">
  <!-- SECTION 1: HERO (Pindah ke dalam container agar pin terintegrasi) -->
  <section class="premium-scroll-sec hero" id="scroll-sec-hero">
    <!-- CANVAS ANIMATED GLOWING FLUID SHADER (BACKGROUND LAYER) -->
    <canvas id="shader-canvas"></canvas>
    
    <!-- CANVAS INTERACTIVE SOCCER STADIUM 3D (FOREGROUND LAYER - REVISI: Z-INDEX: 4 SUPAYA DI ATAS GRID & ANIMATION) -->
    <canvas id="three-canvas"></canvas>
    
    <div class="hero-overlay"></div>
    <div class="hero-grid"></div>
    <div class="scroll-ind center">
      <span>Scroll Down</span>
      <div class="scroll-bar"></div>
  </section>

  <!-- SECTION 2: MINI FUT TEXT -->
  <section class="premium-scroll-sec" id="scroll-sec-1">
    <div class="sc1-title-container">
      <span class="sc1-title-left">MINI</span>
      <span class="sc1-title-right">FUT</span>
    </div>
  </section>

  <!-- SECTION 3: RUMPUT PREMIUM -->
  <section class="premium-scroll-sec" id="scroll-sec-2">
    <div class="ball-section-text" id="bst-left">
      <div class="bst-label">Kualitas Terbaik</div>
      <h2 class="bst-title">RUMPUT<br>SINTETIS PRO</h2>
      <p class="bst-body">Material premium grade internasional. Setiap inci lapangan dirancang untuk performa dan kestabilan optimal pergerakan tim Anda.</p>
    </div>
  </section>

  <!-- SECTION 4: BOLA PREMIUM -->
  <section class="premium-scroll-sec" id="scroll-sec-3">
    <div class="ball-section-text right" id="bst-right">
      <div class="bst-label">Fasilitas Lengkap</div>
      <h2 class="bst-title">PENGALAMAN<br>TERBAIK</h2>
      <p class="bst-body">Dari bola standar kompetisi internasional hingga café tribun santai, setiap detail dirancang untuk kepuasan bermain Anda.</p>
    </div>
  </section>
</div>

<!-- ── FAN WALL: DOKUMENTASI CUSTOMER ── -->
<section class="fanwall-sec">
  <div class="fanwall-neon-l"></div>
  <div class="fanwall-neon-r"></div>

  <!-- ROW 1 — scroll kiri -->
  <div class="fanwall-track-wrap">
    <div class="fanwall-track">
      <div class="fw-card sz-m"><img src="https://images.unsplash.com/photo-1517927033932-b3d18e61fb3a?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-s"><img src="https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-l"><img src="https://images.unsplash.com/photo-1560272564-c83b66b1ad12?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-p"><img src="https://images.unsplash.com/photo-1526232761682-d26e03ac148e?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-m"><img src="https://images.unsplash.com/photo-1570498839593-e565b39455fc?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-s"><img src="https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-p"><img src="https://images.unsplash.com/photo-1553778263-73a83bab9b0c?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-l"><img src="https://images.unsplash.com/photo-1517466787929-bc90951d0974?w=600&q=80" alt="" loading="lazy"></div>
      <!-- duplikat untuk seamless loop -->
      <div class="fw-card sz-m"><img src="https://images.unsplash.com/photo-1517927033932-b3d18e61fb3a?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-s"><img src="https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-l"><img src="https://images.unsplash.com/photo-1560272564-c83b66b1ad12?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-p"><img src="https://images.unsplash.com/photo-1526232761682-d26e03ac148e?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-m"><img src="https://images.unsplash.com/photo-1570498839593-e565b39455fc?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-s"><img src="https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-p"><img src="https://images.unsplash.com/photo-1553778263-73a83bab9b0c?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-l"><img src="https://images.unsplash.com/photo-1517466787929-bc90951d0974?w=600&q=80" alt="" loading="lazy"></div>
    </div>
  </div>

  <!-- ROW 2 — scroll kanan (reverse) -->
  <div class="fanwall-track-wrap">
    <div class="fanwall-track fanwall-track-rev">
      <div class="fw-card sz-s"><img src="https://images.unsplash.com/photo-1517927033932-b3d18e61fb3a?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-p"><img src="https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-m"><img src="https://images.unsplash.com/photo-1560272564-c83b66b1ad12?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-l"><img src="https://images.unsplash.com/photo-1526232761682-d26e03ac148e?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-s"><img src="https://images.unsplash.com/photo-1570498839593-e565b39455fc?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-m"><img src="https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-l"><img src="https://images.unsplash.com/photo-1553778263-73a83bab9b0c?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-p"><img src="https://images.unsplash.com/photo-1517466787929-bc90951d0974?w=600&q=80" alt="" loading="lazy"></div>
      <!-- duplikat untuk seamless loop -->
      <div class="fw-card sz-s"><img src="https://images.unsplash.com/photo-1517927033932-b3d18e61fb3a?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-p"><img src="https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-m"><img src="https://images.unsplash.com/photo-1560272564-c83b66b1ad12?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-l"><img src="https://images.unsplash.com/photo-1526232761682-d26e03ac148e?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-s"><img src="https://images.unsplash.com/photo-1570498839593-e565b39455fc?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-m"><img src="https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-l"><img src="https://images.unsplash.com/photo-1553778263-73a83bab9b0c?w=600&q=80" alt="" loading="lazy"></div>
      <div class="fw-card sz-p"><img src="https://images.unsplash.com/photo-1517466787929-bc90951d0974?w=600&q=80" alt="" loading="lazy"></div>
    </div>
  </div>
</section>
<!-- ── END FAN WALL ── -->

<section class="fields-sec" id="lapangan">
  <div class="container">
    <div class="fields-head reveal">
      <div>
        <div class="sec-label">⬡ Pilihan Lapangan</div>
        <h2 class="sec-title">3 LAPANGAN<br>OUTDOOR</h2>
      </div>
      <p class="sec-sub" style="max-width:360px;text-align:right;">Sistem pencahayaan LED penuh untuk bermain siang maupun malam di atas rumput sintetis berkualitas.</p>
    </div>
    <div class="fields-grid">
      <div class="f-card reveal">
        <div class="f-card-img">
          <img src="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=800&q=80" alt="Lapangan 1" loading="lazy">
          <div class="f-card-img-overlay"></div>
          <div class="f-card-badge-wrap">
            <div class="f-badge">Rumput Sintetis Pro</div>
            <div class="f-card-title">LAPANGAN 1</div>
          </div>
        </div>
        <div class="f-card-body">
          <div class="f-tags">
            <span class="f-tag">16-20 Orang</span>
            <span class="f-tag">8v8 / 10v10</span>
            <span class="f-tag">Outdoor</span>
          </div>
          <div class="f-price-row">
            <div><span class="f-price-val">1.000K</span><span class="f-price-unit">/jam</span></div>
            <a href="booking.php" class="f-book-btn">Book →</a>
          </div>
        </div>
      </div>
      <div class="f-card reveal">
        <div class="f-card-img">
          <img src="https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=800&q=80" alt="Lapangan 2" loading="lazy">
          <div class="f-card-img-overlay"></div>
          <div class="f-card-badge-wrap">
            <div class="f-badge">Rumput Sintetis Premium</div>
            <div class="f-card-title">LAPANGAN 2</div>
          </div>
        </div>
        <div class="f-card-body">
          <div class="f-tags">
            <span class="f-tag">Area Tribun</span>
            <span class="f-tag">16-20 Orang</span>
            <span class="f-tag">Outdoor</span>
          </div>
          <div class="f-price-row">
            <div><span class="f-price-val">1.200K</span><span class="f-price-unit">/jam</span></div>
            <a href="booking.php" class="f-book-btn">Book →</a>
          </div>
        </div>
      </div>
      <div class="f-card reveal">
        <div class="f-card-img">
          <img src="https://images.unsplash.com/photo-1518604666860-9ed391f76460?w=800&q=80" alt="Lapangan 3" loading="lazy">
          <div class="f-card-img-overlay"></div>
          <div class="f-card-badge-wrap">
            <div class="f-badge">Rumput Sintetis Pro</div>
            <div class="f-card-title">LAPANGAN 3</div>
          </div>
        </div>
        <div class="f-card-body">
          <div class="f-tags">
            <span class="f-tag">16-20 Orang</span>
            <span class="f-tag">8v8 / 10v10</span>
            <span class="f-tag">Outdoor</span>
          </div>
          <div class="f-price-row">
            <div><span class="f-price-val">1.000K</span><span class="f-price-unit">/jam</span></div>
            <a href="booking.php" class="f-book-btn">Book →</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- GALLERY -->
<section class="gallery-sec" id="galeri">
  <div class="container">
    <div class="gallery-head reveal">
      <div class="sec-label">⬡ Galeri Arena</div>
      <h2 class="sec-title">Jelajahi Arena<br>Kami</h2>
    </div>
  </div>
  <div class="gallery-mosaic reveal">
    <div class="g-item" data-full="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=1400&q=90">
      <img src="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=900&q=80" alt="" loading="lazy">
      <div class="g-overlay"><div class="g-zoom">+</div></div>
      <div class="g-label">Lapangan A</div>
    </div>
    <div class="g-item" data-full="https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=1400&q=90">
      <img src="https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=900&q=80" alt="" loading="lazy">
      <div class="g-overlay"><div class="g-zoom">+</div></div>
      <div class="g-label">Lapangan B</div>
    </div>
    <div class="g-item" data-full="https://images.unsplash.com/photo-1518604666860-9ed391f76460?w=1400&q=90">
      <img src="https://images.unsplash.com/photo-1518604666860-9ed391f76460?w=900&q=80" alt="" loading="lazy">
      <div class="g-overlay"><div class="g-zoom">+</div></div>
      <div class="g-label">Lapangan C</div>
    </div>
    <div class="g-item" data-full="assets/tribun.png?w=1400&q=90">
      <img src="assets/tribun.png?w=900&q=80" alt="" loading="lazy">
      <div class="g-overlay"><div class="g-zoom">+</div></div>
      <div class="g-label">Area Tribun</div>
    </div>
    <div class="g-item" data-full="assets/fasilitas.png?w=1400&q=90">
      <img src="assets/fasilitas.png?w=900&q=80" alt="" loading="lazy">
      <div class="g-overlay"><div class="g-zoom">+</div></div>
      <div class="g-label">Fasilitas</div>
    </div>
  </div>
</section>

<section class="showcase-sec" id="fasilitas">
  <div class="container">
    <div class="showcase-head reveal">
      <div class="sec-label">⬡ Fasilitas Pendukung</div>
      <h2 class="sec-title">Nikmati Fasilitas<br>Lengkap</h2>
    </div>
    <div class="showcase-layout reveal">
      <div class="showcase-list" id="sc-list">
        <div class="showcase-item active" data-img="sc-img-1">
          <div class="sc-title">Parkir Luas & Aman</div>
          <div class="sc-desc">Area parkir kami mampu menampung puluhan kendaraan baik motor maupun mobil. Akses keluar masuk yang mudah dan diawasi CCTV.</div>
          <div class="sc-progress-bar"></div>
        </div>
        <div class="showcase-item" data-img="sc-img-2">
          <div class="sc-title">Restaurant & Café</div>
          <div class="sc-desc">Haus setelah bertanding? Pesan minuman atau makanan di area restaurant kami sambil bersantai setelah pertandingan.</div>
          <div class="sc-progress-bar"></div>
        </div>
        <div class="showcase-item" data-img="sc-img-3">
          <div class="sc-title">Ruang Ganti & Kamar Mandi</div>
          <div class="sc-desc">Fasilitas kamar mandi dan ruang ganti yang selalu dijaga kebersihannya, lengkap, dan nyaman digunakan.</div>
          <div class="sc-progress-bar"></div>
        </div>
        <div class="showcase-item" data-img="sc-img-4">
          <div class="sc-title">Pencahayaan LED Penuh</div>
          <div class="sc-desc">Bermain malam hari bukan masalah. Lapangan kami dilengkapi dengan lampu sorot LED standar internasional untuk visibilitas maksimal.</div>
          <div class="sc-progress-bar"></div>
        </div>
      </div>
      <div class="showcase-visual">
        <img src="assets/parkir.png" alt="Parkir" class="sc-img active" id="sc-img-1" loading="lazy">
        <img src="assets/cafe.png" alt="Cafe" class="sc-img" id="sc-img-2" loading="lazy">
        <img src="https://i.pinimg.com/736x/5a/09/75/5a0975a1f56266edb107157f7158a6b3.jpg" alt="Ruang Ganti" class="sc-img" id="sc-img-3" loading="lazy">
        <img src="https://i.pinimg.com/736x/d3/77/6e/d3776ef7e9e415c7d21822c9ebc1b51f.jpg" alt="Pencahayaan LED" class="sc-img" id="sc-img-4" loading="lazy">
        <div class="sc-overlay"></div>
      </div>
    </div>
  </div>
</section>

<section class="pricing-sec" id="harga">
  <div class="container">
    <div class="showcase-head reveal" style="margin-bottom:56px;">
      <div class="sec-label">⬡ Harga Sewa</div>
      <h2 class="sec-title">Temukan Paket<br>Terbaik</h2>
      <p class="sec-sub">Tidak ada biaya tersembunyi. Harga sama untuk siang maupun malam.</p>
    </div>
    <div class="pricing-grid">
      <!-- Card 1 wrapped with ShineBorder equivalent -->
      <div class="p-card-wrapper reveal">
        <div class="shine-border-bg">
          <div class="p-card-shine"></div>
        </div>
        <div class="p-card">
          <div class="p-tag">Lapangan 1 & 3</div>
          <div class="p-name">Rumput Sintetis Pro</div>
          <div class="p-price">
            <span class="p-cur">Rp</span>
            <span class="p-amount">1.000.000</span>
            <span class="p-per">/jam</span>
          </div>
          <div class="p-div"></div>
          <ul class="p-feats">
            <li>Rumput Sintetis Grade Pro</li>
            <li>Kapasitas 16-20 Orang</li>
            <li>Pencahayaan LED Penuh</li>
            <li>Ruang Ganti & Toilet Bersih</li>
            <li>Akses Restaurant</li>
          </ul>
          <a href="booking.php" class="p-btn">Pilih Lapangan</a>
        </div>
      </div>
      
      <!-- Card 2 wrapped with ShineBorder equivalent -->
      <div class="p-card-wrapper featured-wrapper reveal">
        <div class="shine-border-bg">
          <div class="p-card-shine featured-shine"></div>
        </div>
        <div class="p-card featured">
          <div class="p-tag">Lapangan 2</div>
          <div class="p-name">Premium + Tribun</div>
          <div class="p-price">
            <span class="p-cur">Rp</span>
            <span class="p-amount">1.200.000</span>
            <span class="p-per">/jam</span>
          </div>
          <div class="p-div"></div>
          <ul class="p-feats">
            <li>Rumput Sintetis Premium</li>
            <li>Area Tribun Penonton</li>
            <li>Kapasitas 16-20 Orang</li>
            <li>Ruang Ganti & Toilet Eksklusif</li>
            <li>Akses Restaurant & Cafe</li>
          </ul>
          <a href="booking.php" class="p-btn solid">Pilih Lapangan 2</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- VALUES SECTION -->
<section class="values-sec">
  <div class="container">
    <div style="text-align:center;" class="reveal">
      <div class="sec-label">DNA Kami</div>
      <h2 class="sec-title">Nilai-Nilai<br>Yang Kami Pegang</h2>
    </div>
    <div class="values-hex-grid">
      <div class="val-card reveal">
        <div class="val-num">01</div>
        <div class="val-name">Passion for the Game</div>
        <div class="val-desc">Setiap keputusan kami selalu dimulai dari satu pertanyaan: "Apakah ini akan membuat pengalaman bermain lebih baik?"</div>
        </div>
      <div class="val-card reveal">
        <div class="val-num">02</div>
        <div class="val-name">Quality Standards</div>
        <div class="val-desc">Dari kualitas lapangan hingga fasilitas pendukung, kami menjaga standar agar pengalaman bermain tetap konsisten dan menyenangkan.</div>
      </div>
      <div class="val-card reveal">
        <div class="val-num">03</div>
        <div class="val-name">Customer Experience</div>
        <div class="val-desc">Kemudahan booking, pelayanan yang responsif, and lingkungan yang nyaman menjadi bagian dari komitmen kami kepada pelanggan.</div>
      </div>
      <div class="val-card reveal">
        <div class="val-num">04</div>
        <div class="val-name">Professional Service</div>
        <div class="val-desc">Kami mengutamakan pelayanan yang profesional, jadwal yang jelas, and pengalaman booking yang dapat diandalkan.</div>
      </div>
      <div class="val-card reveal">
        <div class="val-num">05</div>
        <div class="val-name">Community & Sportsmanship</div>
        <div class="val-desc">MiniFut menjadi tempat bagi para pemain untuk berkumpul, bertanding, membangun kebersamaan, and menjunjung sportivitas.</div>
      </div>
      <div class="val-card reveal">
        <div class="val-num">06</div>
        <div class="val-name">Integrity & Transparency</div>
        <div class="val-desc">Harga yang jelas, komunikasi yang terbuka, and pelayanan yang jujur adalah prinsip yang selalu kami pegang.</div>
      </div>
    </div>
  </div>
</section>

<!-- REVISI: GREEN-THEMED AWARDS MARQUEE DENGAN KONTEN INFORMATIF TENTANG ARENA -->
<section class="awards-sec">
  <div class="awards-track">
    <div class="award-item"><div><div class="award-name">RUMPUT FIFA GRADE PRO</div><div class="award-year">Teknologi Sintetis Premium Teruji</div></div></div>
    <div class="award-item"><div><div class="award-name">1200 LUX LED SYSTEM</div><div class="award-year">Sistem Lampu Sorot Tanpa Bayangan</div></div></div>
    <div class="award-item"><div><div class="award-name">EXCLUSIVELY DESIGNED TRIBUN</div><div class="award-year">Area Penonton Nyaman & Luas</div></div></div>
    <div class="award-item"><div><div class="award-name">BOOKING INSTAN ONLINE</div><div class="award-year">Reservasi Cepat & Aman 24/7</div></div></div>
    <div class="award-item"><div><div class="award-name">TOILET BERSIH & HIGIENIS</div><div class="award-year">Fasilitas Higienis & Terawat</div></div></div>
    <div class="award-item"><div><div class="award-name">RESTO & CAFE STRATEGIS</div><div class="award-year">Area Santai & Makan Minum Tim</div></div></div>
    <div class="award-item"><div><div class="award-name">RUMPUT FIFA GRADE A</div><div class="award-year">Teknologi Sintetis Premium Teruji</div></div></div>
    <div class="award-item"><div><div class="award-name">1200 LUX LED SYSTEM</div><div class="award-year">Sistem Lampu Sorot Tanpa Bayangan</div></div></div>
    <div class="award-item"><div><div class="award-name">EXCLUSIVELY DESIGNED TRIBUN</div><div class="award-year">Area Penonton Nyaman & Luas</div></div></div>
    <div class="award-item"><div><div class="award-name">BOOKING INSTAN ONLINE</div><div class="award-year">Reservasi Cepat & Aman 24/7</div></div></div>
    <div class="award-item"><div><div class="award-name">TOILET BERSIH & HIGIENIS</div><div class="award-year">Fasilitas Higienis & Terawat</div></div></div>
    <div class="award-item"><div><div class="award-name">RESTO & CAFE STRATEGIS</div><div class="award-year">Area Santai & Makan Minum Tim</div></div></div>
  </div>
  <div class="awards-track awards-track-rev">
    <div class="award-item"><div><div class="award-name">PARKIR MOTOR & MOBIL AMAN</div><div class="award-year">Kapasitas Luas Terpantau CCTV</div></div></div>
    <div class="award-item"><div><div class="award-name">RUANG GANTI BER-AC</div><div class="award-year">Kesejukan Maksimal Sebelum Laga</div></div></div>
    <div class="award-item"><div><div class="award-name">FASILITAS BOLA KOMPETISI</div><div class="award-year">Peralatan Standar Internasional</div></div></div>
    <div class="award-item"><div><div class="award-name">YOGYAKARTA PREMIER VENUE</div><div class="award-year">Lokasi Strategis Mudah Dijangkau</div></div></div>
    <div class="award-item"><div><div class="award-name">BASECAMP KOMUNITAS BOLA</div><div class="award-year">Wadah Silahturahmi & Sportivitas</div></div></div>
    <div class="award-item"><div><div class="award-name">SHOWER HANGAT & DINGIN</div><div class="award-year">Menyediakan Fasilitas yang Lengkap</div></div></div>
    <div class="award-item"><div><div class="award-name">PARKIR MOTOR & MOBIL AMAN</div><div class="award-year">Kapasitas Luas Terpantau CCTV</div></div></div>
    <div class="award-item"><div><div class="award-name">RUANG GANTI BER-AC</div><div class="award-year">Kesejukan Maksimal Sebelum Laga</div></div></div>
    <div class="award-item"><div><div class="award-name">FASILITAS BOLA KOMPETISI</div><div class="award-year">Peralatan Standar Internasional</div></div></div>
    <div class="award-item"><div><div class="award-name">YOGYAKARTA PREMIER VENUE</div><div class="award-year">Lokasi Strategis Mudah Dijangkau</div></div></div>
    <div class="award-item"><div><div class="award-name">BASECAMP KOMUNITAS BOLA</div><div class="award-year">Wadah Silahturahmi & Sportivitas</div></div></div>
    <div class="award-item"><div><div class="award-name">SHOWER HANGAT & DINGIN</div><div class="award-year">Menyediakan Fasilitas yang Lengkap</div></div></div>
  </div>
</section>

<!-- SECTION CTA DENGAN BACKGROUND HITAM -->
<section class="cta-sec">
  <div id="particles-js" style="position:absolute; width:100%; height:100%; top:0; left:0; z-index:0;"></div>
  <div class="container" style="position:relative;z-index:1">
    <div class="reveal">
      <div class="sec-label" style="display:flex;justify-content:center">⬡ Siap Bermain?</div>
      <h2 class="cta-title">Book Lapangan<br><span>Sekarang</span></h2>
      <p class="cta-sub">Jangan tunda lagi. Atur jadwal pertandinganmu dan nikmati pengalaman<br>mini soccer terbaik di Yogyakarta.</p>
      <a href="booking.php" class="btn-soft" style="display:inline-block">Mulai Booking →</a>
    </div>
  </div>
</section>

<footer>
  <div class="container">
    <div class="footer-grid">
      <div>
        <a href="#" class="logo">MINI<em>FUT</em></a>
        <p class="footer-desc">Arena mini soccer outdoor premium di Yogyakarta. Rumput sintetis kualitas tinggi dengan fasilitas terlengkap untuk kenyamanan tim Anda.</p>
      </div>
      <div>
        <div class="f-title">Navigasi</div>
        <ul class="f-links">
          <li><a href="#lapangan">Lapangan</a></li>
          <li><a href="#fasilitas">Fasilitas</a></li>
          <li><a href="#harga">Harga</a></li>
          <li><a href="booking.php">Booking Lapangan</a></li>
        </ul>
      </div>
      <div>
        <div class="f-title">Informasi Operasional</div>
        <ul class="f-links">
          <li><strong>Buka Setiap Hari:</strong></li>
          <li>08.00 – 24.00 WIB</li>
          <li style="margin-top:8px"><strong>Lokasi:</strong></li>
          <li>Yogyakarta, DIY</li>
        </ul>
      </div>
      <div>
        <div class="f-title">Kontak Kami</div>
        <ul class="f-links">
          <li><strong>Telepon / WhatsApp:</strong></li>
          <li>+62 812-3456-7890</li>
          <li style="margin-top:8px"><strong>Email:</strong></li>
          <li>info@minifut.id</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="footer-copy">© 2026 MiniFut — All Rights Reserved</div>
      <div class="f-links" style="flex-direction:row; gap:20px;">
        <a href="https://instagram.com/minifut.id" target="_blank" rel="noopener noreferrer">Instagram</a>
        <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer">WhatsApp</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

<!-- LIBRARY GSAP & SCROLLTRIGGER -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

<script>
// --- SHADER SOCCER PANEL DEFINISI GLOBAL ---
const ballVertS = `
  varying vec3 vNormal;varying vec3 vPos;varying vec3 vWorldPos;
  void main(){
    vNormal=normalize(normalMatrix*normal);
    vPos=position;
    vWorldPos=(modelMatrix*vec4(position,1.)).xyz;
    gl_Position=projectionMatrix*modelViewMatrix*vec4(position,1.);
  }`;
    
const ballFragS = `
  uniform float uTime;varying vec3 vNormal;varying vec3 vPos;varying vec3 vWorldPos;
  
  float hash(vec2 p){return fract(sin(dot(p,vec2(127.1,311.7)))*43758.5453);}
  float noise(vec2 p){
    vec2 i=floor(p),f=fract(p);
    vec2 u=f*f*(3.-2.*f);
    return mix(mix(hash(i),hash(i+vec2(1,0)),u.x),mix(hash(i+vec2(0,1)),hash(i+vec2(1,1)),u.x),u.y);
  }
  float fbm(vec2 p){
    float v=0.;float a=.5;
    for(int i=0;i<4;i++){v+=a*noise(p);p*=2.2;a*=.5;}return v;
  }

  float soccerPanel(vec3 p){
    float t=atan(p.z,p.x)*3.;
    float ph=asin(clamp(p.y,-1.,1.))*3.;
    vec2 uv=vec2(t,ph);
    float h=smoothstep(.35,.32,abs(sin(uv.x*3.14159)*sin(uv.y*3.14159)));
    h+=smoothstep(.35,.32,abs(cos((uv.x+uv.y)*3.14159*.5)));
    return clamp(h,0.,1.);
  }

  void main(){
    vec3 N=normalize(vNormal);vec3 pos=normalize(vPos);
    
    float panel=soccerPanel(pos);
    vec3 bc=mix(vec3(.92,.9,.86), vec3(.05,.05,.04), panel);
    
    vec3 viewDir=normalize(-vWorldPos+vec3(0.,0.,10.));
    float rim=pow(1.-max(dot(N,viewDir),0.),3.);
    vec3 lightDir=normalize(vec3(2.,3.,4.));
    float diff=max(dot(N,lightDir),0.);
    
    vec3 finalColor=bc*vec3(0.,.06,.03)*2.+bc*vec3(.9,.9,.85)*diff*1.1+vec3(0.,.9,.55)*rim*.25;
    finalColor=pow(finalColor,vec3(.85));
    
    gl_FragColor=vec4(finalColor,1.0);
  }`;

// --- SINKRONISASI PROGRESS SCROLL GSAP KE THREEJS ---
let heroScrollProgress = 0;
let targetHeroScrollProgress = 0;

gsap.registerPlugin(ScrollTrigger);

const cur=document.getElementById('cur'), curR=document.getElementById('cur-r');
if (window.matchMedia("(pointer: fine)").matches) {
  gsap.set(cur, {xPercent: -50, yPercent: -50});
  gsap.set(curR, {xPercent: -50, yPercent: -50});
  
  const xTo = gsap.quickTo(cur, "x", {duration: 0.1, ease: "power3"});
  const yTo = gsap.quickTo(cur, "y", {duration: 0.1, ease: "power3"});
  const xToR = gsap.quickTo(curR, "x", {duration: 0.3, ease: "power3"});
  const yToR = gsap.quickTo(curR, "y", {duration: 0.3, ease: "power3"});

  document.addEventListener('mousemove', e => {
    xTo(e.clientX); yTo(e.clientY);
    xToR(e.clientX); yToR(e.clientY);
  });

  document.querySelectorAll('a, button, .f-card, .showcase-item, .g-item, #lb-close, .val-card, .award-item, .btn-soft').forEach(el=>{
    el.addEventListener('mouseenter',()=>{gsap.to(cur, {width: 15, height: 15, duration: 0.2}); gsap.to(curR, {width: 50, height: 50, opacity: 0.65, duration: 0.2});});
    el.addEventListener('mouseleave',()=>{gsap.to(cur, {width: 10, height: 10, duration: 0.2}); gsap.to(curR, {width: 34, height: 34, opacity: 0.45, duration: 0.2});});
  });

  document.querySelectorAll('.btn-p, .btn-s, .btn-soft, .nav-cta').forEach(btn => {
    btn.addEventListener('mousemove', e => {
      const rect = btn.getBoundingClientRect();
      const relX = e.clientX - (rect.left + rect.width / 2);
      const relY = e.clientY - (rect.top + rect.height / 2);
      gsap.to(btn, {x: relX * 0.25, y: relY * 0.25, duration: 0.3, ease: "power2.out"});
    });
    btn.addEventListener('mouseleave', () => {
      gsap.to(btn, {x: 0, y: 0, duration: 0.7, ease: "elastic.out(1, 0.3)"});
    });
  });
}

// ── REVISI NAV DYNAMIC TRANSITION (MEMENDEK SAAT DI LUAR HERO SECTION) ──
window.addEventListener('scroll', () => {
  const nav = document.getElementById('nav');
  const showcase = document.querySelector('.premium-showcase-container');
  const showcaseRect = showcase.getBoundingClientRect();
  
  if (window.scrollY > window.innerHeight - 100) {
    nav.classList.add('shrunk');
  } else {
    nav.classList.remove('shrunk');
  }

  if (showcaseRect.top <= 60 && showcaseRect.bottom >= 60) {
    nav.classList.add('showcase-active');
    nav.classList.remove('stuck');
  } else {
    nav.classList.remove('showcase-active');
    nav.classList.toggle('stuck', window.scrollY > 60); 
  }
}, {passive: true});

/* --- GSAP FEATURE 2: SMOOTH SCROLL REVEAL (STAGGER BATCH) --- */
gsap.set(".reveal", { y: 40, opacity: 0 });
ScrollTrigger.batch(".reveal", {
  onEnter: batch => {
    gsap.to(batch, { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: "power3.out", overwrite: true });
  },
  once: true,
  start: "top 85%"
});

const scItems = document.querySelectorAll('.showcase-item'), scImgs = document.querySelectorAll('.sc-img');
let scIndex = 0, scInterval = setInterval(nextSc, 4000);
function nextSc() {
  scItems[scIndex].classList.remove('active'); document.getElementById(scItems[scIndex].getAttribute('data-img')).classList.remove('active');
  scIndex = (scIndex + 1) % scItems.length;
  scItems[scIndex].classList.add('active'); document.getElementById(scItems[scIndex].getAttribute('data-img')).classList.add('active');
}
scItems.forEach((item, idx) => {
  item.addEventListener('mouseenter', () => {
    clearInterval(scInterval);
    scItems.forEach(i => i.classList.remove('active')); scImgs.forEach(img => img.classList.remove('active'));
    item.classList.add('active'); document.getElementById(item.getAttribute('data-img')).classList.add('active'); scIndex = idx;
  });
  item.addEventListener('mouseleave', () => { scInterval = setInterval(nextSc, 4000); });
});

/* GALLERY LIGHTBOX LOGIC */
const lb=document.getElementById('lb'),lbImg=document.getElementById('lb-img');
document.querySelectorAll('.g-item').forEach(item=>{
  item.addEventListener('click',()=>{lbImg.src=item.dataset.full;lb.classList.add('open');});
});
document.getElementById('lb-close').onclick=()=>lb.classList.remove('open');
lb.addEventListener('click',e=>{if(e.target===lb)lb.classList.remove('open');});

/* REVISI COUNTERS LOGIC: START DARI DATA-START (500) HINGGA DATA-TARGET (999) DENGAN KECEPATAN LEBIH LAMBAT */
const co=new IntersectionObserver(es=>es.forEach(e=>{
  if(!e.isIntersecting)return;
  const el=e.target,target=+el.dataset.target,suffix=el.dataset.suffix||'';
  const startVal=el.dataset.start ? +el.dataset.start : 0;
  let v=startVal;
  // Memperlambat laju konter (Spread over 2500ms / 2.5 seconds)
  const duration = 2500;
  const step=Math.max(1, (target-startVal)/(duration/16));
  const t=setInterval(()=>{
    v+=step;
    if(v>=target){
      v=target;
      clearInterval(t);
    }
    el.textContent=Math.floor(v)+suffix;
  },16);
  co.unobserve(el);
}),{threshold:1});
document.querySelectorAll('.counter').forEach(el=>co.observe(el));

/* PARTICLES CTA LOGIC */
if(typeof particlesJS !== 'undefined') {
  particlesJS("particles-js", {
    "particles": {
      "number": { "value": 100, "density": { "enable": true, "value_area": 800 } },
      "color": { "value": "#00ff88" },
      "shape": { "type": "circle" },
      "opacity": { "value": 0.8, "random": true, "anim": { "enable": true, "speed": 1, "opacity_min": 0.2, "sync": false } },
      "size": { "value": 4, "random": true, "anim": { "enable": true, "speed": 2, "size_min": 1, "sync": false } },
      "line_linked": { "enable": false },
      "move": { "enable": true, "speed": 1.5, "direction": "none", "random": true, "straight": false, "out_mode": "out", "bounce": false }
    },
    "interactivity": {
      "detect_on": "window",
      "events": { "onhover": { "enable": true, "mode": "repulse" }, "onclick": { "enable": false }, "resize": true },
      "modes": { "repulse": { "distance": 120, "duration": 0.4 } }
    },
    "retina_detect": true
  });
}

/* --- LOGIKA 3D TILT & GLARE --- */
document.querySelectorAll('.f-card').forEach(card => {
  const glare = document.createElement('div');
  glare.classList.add('f-glare');
  card.appendChild(glare);

  card.addEventListener('mousemove', (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left, y = e.clientY - rect.top;
    const centerX = rect.width / 2, centerY = rect.height / 2;
    const rotateX = ((y - centerY) / centerY) * -12; 
    const rotateY = ((x - centerX) / centerX) * 12;
    
    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
    card.style.transition = 'none';
    
    glare.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.15) 0%, transparent 70%)`;
    glare.style.opacity = '1';
  });

  card.addEventListener('mouseleave', () => {
    card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
    card.style.transition = 'transform 0.4s ease, border-color 0.3s';
    glare.style.opacity = '0';
  });
});

/* ─── VALUES CARDS REACTIVE GLOW ─── */
document.querySelectorAll('.val-card').forEach(card => {
  card.addEventListener('mousemove', e => {
    const r = card.getBoundingClientRect();
    card.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100) + '%');
    card.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100) + '%');
  });
});

// --- 3D HERO SHADER BACKGROUND LOGIC (INTEGRATED PORT) ---
(function initShaderBackground() {
  const canvas = document.getElementById('shader-canvas');
  if (!canvas) return;

  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(window.innerWidth, window.innerHeight);

  const scene = new THREE.Scene();
  const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);

  const uniforms = {
    resolution: { value: new THREE.Vector2(window.innerWidth, window.innerHeight) },
    time: { value: 0.0 },
    xScale: { value: 1.0 },
    yScale: { value: 0.5 },
    distortion: { value: 0.05 },
  };

  const vertexShader = `
    varying vec2 vUv;
    void main() {
      vUv = uv;
      gl_Position = vec4(position, 1.0);
    }
  `;

  // ADAPTASI HIJAU PREMIUM DARI FRAGMENT SHADER LIQUID WAVES
  const fragmentShader = `
    uniform vec2 resolution;
    uniform float time;
    uniform float xScale;
    uniform float yScale;
    uniform float distortion;

    void main() {
      vec2 p = (gl_FragCoord.xy * 2.0 - resolution) / min(resolution.x, resolution.y);
      
      float d = length(p) * distortion;
      
      float rx = p.x * (1.0 + d);
      float gx = p.x;
      float bx = p.x * (1.0 - d);

      float w1 = 0.05 / abs(p.y + sin((rx + time) * xScale) * yScale);
      float w2 = 0.05 / abs(p.y + sin((gx + time) * xScale) * yScale);
      float w3 = 0.05 / abs(p.y + sin((bx + time) * xScale) * yScale);
      
      // Mengonversi gelombang RGB asli menjadi paduan warna Hijau Emerald / Neon Premium
      vec3 col1 = vec3(0.0, w1 * 0.5, w1 * 0.15);     // Gelombang hijau tua/teal
      vec3 col2 = vec3(w2 * 0.15, w2 * 0.95, w2 * 0.3); // Gelombang utama neon lime-green
      vec3 col3 = vec3(0.0, w3 * 0.7, w3 * 0.5);       // Gelombang cyan-green elektrik
      
      vec3 finalColor = col1 + col2 + col3;
      
      gl_FragColor = vec4(finalColor, 1.0);
    }
  `;

  const geometry = new THREE.PlaneGeometry(2, 2);
  const material = new THREE.ShaderMaterial({
    vertexShader,
    fragmentShader,
    uniforms,
    depthWrite: false,
    depthTest: false
  });

  const mesh = new THREE.Mesh(geometry, material);
  scene.add(mesh);

  function handleResize() {
    const w = window.innerWidth;
    const h = window.innerHeight;
    renderer.setSize(w, h);
    uniforms.resolution.value.set(w, h);
  }
  window.addEventListener('resize', handleResize);

  function animate() {
    uniforms.time.value += 0.01;
    renderer.render(scene, camera);
    requestAnimationFrame(animate);
  }
  animate();
})();

// --- 3D HERO CANVAS LOGIC ---
(function initHeroThree() {
  const canvas = document.getElementById('three-canvas');
  if (!canvas) return;
  const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 100);
  let baseY = 8;

  function updateResponsive() {
    const w = canvas.parentElement.offsetWidth, h = canvas.parentElement.offsetHeight;
    renderer.setSize(w, h);
    camera.aspect = w / h;
    if (w < 768) { camera.position.z = 24; baseY = 12; } 
    else { camera.position.z = 14; baseY = 8; }
    camera.updateProjectionMatrix();
  }

  window.addEventListener('resize', updateResponsive);
  updateResponsive();

  camera.position.set(0, baseY, camera.position.z);
  camera.lookAt(0, 0, 0);

  const fieldGeo = new THREE.PlaneGeometry(14, 9, 32, 20);
  const fieldMat = new THREE.MeshStandardMaterial({ color: 0x002211, roughness: 0.8, metalness: 0.05 });
  const field = new THREE.Mesh(fieldGeo, fieldMat);
  field.rotation.x = -Math.PI / 2;
  scene.add(field);

  function addLine(points, color = 0x00ff88, opacity = 0.5) {
    const geo = new THREE.BufferGeometry().setFromPoints(points);
    const mat = new THREE.LineBasicMaterial({ color, transparent: true, opacity });
    scene.add(new THREE.Line(geo, mat));
  }

  const bL = 7, bW = 4.5;
  addLine([new THREE.Vector3(-bL, 0.02, -bW), new THREE.Vector3(bL, 0.02, -bW), new THREE.Vector3(bL, 0.02, bW), new THREE.Vector3(-bL, 0.02, bW), new THREE.Vector3(-bL, 0.02, -bW)]);
  addLine([new THREE.Vector3(0, 0.02, -bW), new THREE.Vector3(0, 0.02, bW)]);
  
  const circPts = [];
  for (let i = 0; i <= 64; i++) { const a = (i / 64) * Math.PI * 2; circPts.push(new THREE.Vector3(Math.cos(a) * 1.8, 0.02, Math.sin(a) * 1.8)); }
  addLine(circPts);
  
  const dotGeo = new THREE.CircleGeometry(0.12, 16);
  const dotMat = new THREE.MeshStandardMaterial({ color: 0x00ff88, emissive: 0x00ff88, emissiveIntensity: 0.8 });
  const dot = new THREE.Mesh(dotGeo, dotMat);
  dot.rotation.x = -Math.PI / 2;
  dot.position.y = 0.02;
  scene.add(dot);
  
  addLine([new THREE.Vector3(-bL, 0.02, -1.5), new THREE.Vector3(-bL + 2, 0.02, -1.5), new THREE.Vector3(-bL + 2, 0.02, 1.5), new THREE.Vector3(-bL, 0.02, 1.5)]);
  addLine([new THREE.Vector3(bL, 0.02, -1.5), new THREE.Vector3(bL - 2, 0.02, -1.5), new THREE.Vector3(bL - 2, 0.02, 1.5), new THREE.Vector3(bL, 0.02, 1.5)]);
  
  for (let side = -1; side <= 1; side += 2) {
    const arcPts = [];
    for (let i = 0; i <= 32; i++) {
      const a = (i / 32) * Math.PI - Math.PI / 2;
      arcPts.push(new THREE.Vector3(side * (bL - 2) + Math.cos(a) * -side * 1.5, 0.02, Math.sin(a) * 1.5));
    }
    addLine(arcPts, 0x00ff88, 0.3);
  }

  const postGeo = new THREE.CylinderGeometry(0.03, 0.03, 1.2, 8);
  const barGeo = new THREE.CylinderGeometry(0.03, 0.03, 2, 8);
  const postMat = new THREE.MeshStandardMaterial({ color: 0x00ff88, emissive: 0x00ff88, emissiveIntensity: 0.8 });

  function addGoal(xPos) {
    const goal = new THREE.Group();
    const post1 = new THREE.Mesh(postGeo, postMat); post1.position.set(0, 0.6, -1); goal.add(post1);
    const post2 = new THREE.Mesh(postGeo, postMat); post2.position.set(0, 0.6, 1); goal.add(post2);
    const crossbar = new THREE.Mesh(barGeo, postMat); crossbar.rotation.x = Math.PI / 2; crossbar.position.set(0, 1.2, 0); goal.add(crossbar);

    const supportGeo = new THREE.CylinderGeometry(0.02, 0.02, 1.4, 8);
    const supportMat = new THREE.MeshStandardMaterial({ color: 0x00ff88, emissive: 0x00ff88, emissiveIntensity: 0.3, transparent: true, opacity: 0.6 });
    const dir = xPos > 0 ? 1 : -1;
    
    const support1 = new THREE.Mesh(supportGeo, supportMat); support1.rotation.z = (Math.PI / 4) * dir; support1.position.set(0.5 * dir, 0.5, -1); goal.add(support1);
    const support2 = new THREE.Mesh(supportGeo, supportMat); support2.rotation.z = (Math.PI / 4) * dir; support2.position.set(0.5 * dir, 0.5, 1); goal.add(support2);
    const backBar = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, 2, 8), supportMat); backBar.rotation.x = Math.PI / 2; backBar.position.set(1 * dir, 0, 0); goal.add(backBar);
    const netBottom1 = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, 1, 8), supportMat); netBottom1.rotation.z = Math.PI / 2; netBottom1.position.set(0.5 * dir, 0, -1); goal.add(netBottom1);
    const netBottom2 = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, 1, 8), supportMat); netBottom2.rotation.z = Math.PI / 2; netBottom2.position.set(0.5 * dir, 0, 1); goal.add(netBottom2);

    goal.position.set(xPos, 0, 0);
    scene.add(goal);
  }

  addGoal(-bL); addGoal(bL);

  scene.add(new THREE.AmbientLight(0x001a08, 2));
  const dirLight = new THREE.DirectionalLight(0x00ff88, 1.2); dirLight.position.set(5, 10, 5); scene.add(dirLight);
  const pointLight1 = new THREE.PointLight(0x00ff88, 2, 12); pointLight1.position.set(-4, 5, 0); scene.add(pointLight1);
  const pointLight2 = new THREE.PointLight(0x00cc66, 1.5, 12); pointLight2.position.set(4, 5, 0); scene.add(pointLight2);
  
  [[-bL + 1, 4, -bW + 1], [bL - 1, 4, -bW + 1], [-bL + 1, 4, bW - 1], [bL - 1, 4, bW - 1]].forEach(([x, y, z]) => {
    const sl = new THREE.PointLight(0xffeedd, 0.6, 8);
    sl.position.set(x, y, z);
    scene.add(sl);
  });

  let mouseX = 0, mouseY = 0;
  document.addEventListener('mousemove', e => { mouseX = (e.clientX / window.innerWidth - 0.5) * 2; mouseY = (e.clientY / window.innerHeight - 0.5) * 2; });

  let t = 0;
  function animate() {
    t += 0.01;

    // Interpolasi halus scroll progress dengan efek buttery smooth damping
    heroScrollProgress += (targetHeroScrollProgress - heroScrollProgress) * 0.1;

    camera.position.x += (mouseX * 3 - camera.position.x) * 0.02;
    camera.position.y += (-mouseY * 1 + baseY - camera.position.y) * 0.02; 
    camera.lookAt(0, 0, 0);

    fieldMat.emissive = new THREE.Color(0x001a08);
    fieldMat.emissiveIntensity = 0.3 + Math.sin(t * 0.5) * 0.1;
    renderer.render(scene, camera);
    requestAnimationFrame(animate);
  }
  animate();
})();

// --- 3D SHOWCASE MESH OVERLAY SCROLLING CANVAS LOGIC ---
(function() {
  const canvasScroll = document.getElementById('ball-scroll-canvas');
  if (!canvasScroll) return;

  const rendererS = new THREE.WebGLRenderer({ canvas: canvasScroll, antialias: true, alpha: true });
  rendererS.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  rendererS.toneMapping = THREE.ACESFilmicToneMapping;
  rendererS.toneMappingExposure = 1.2;

  const sceneS = new THREE.Scene();
  const cameraS = new THREE.PerspectiveCamera(50, window.innerWidth / window.innerHeight, 0.1, 200);
  cameraS.position.set(0, 0, 10);

  function resizeS() {
    rendererS.setSize(window.innerWidth, window.innerHeight);
    cameraS.aspect = window.innerWidth / window.innerHeight;
    cameraS.updateProjectionMatrix();
  }
  window.addEventListener('resize', resizeS);
  resizeS();

  // Menggunakan shared shader ballVertS dan ballFragS global agar konsisten
  const ballSGeo = new THREE.SphereGeometry(1.6, 64, 64);
  const ballSMat = new THREE.ShaderMaterial({ vertexShader: ballVertS, fragmentShader: ballFragS, uniforms: { uTime: { value: 0 } } });
  const scrollBall = new THREE.Mesh(ballSGeo, ballSMat);
  
  // Posisi awal bola classic
  scrollBall.position.set(0, 0, 0);
  scrollBall.scale.setScalar(1.0);
  sceneS.add(scrollBall);

  sceneS.add(new THREE.AmbientLight(0x001a0d, 2.5));
  const dlS = new THREE.DirectionalLight(0x00ff88, 1.8);
  dlS.position.set(3, 5, 6);
  sceneS.add(dlS);

  // ── INTEGRASI INTERAKSI DRAG: KLIK & PUTAR BOLA SECARA INTERAKTIF ──
  let isDragging = false;
  let previousMousePosition = { x: 0, y: 0 };
  let targetRotation = { x: 0, y: 0 };

  const containerShowcase = document.querySelector('.premium-showcase-container');

  containerShowcase.addEventListener('mousedown', e => {
    // e.preventDefault() ditambahkan agar klik drag tidak memicu blokir teks bawaan browser
    e.preventDefault();
    isDragging = true;
    previousMousePosition = { x: e.clientX, y: e.clientY };
  });

  window.addEventListener('mousemove', e => {
    if (!isDragging) return;
    const deltaMove = {
      x: e.clientX - previousMousePosition.x,
      y: e.clientY - previousMousePosition.y
    };
    
    // Mengurangi sensitivitas geser (dari 0.008 menjadi 0.003) agar terasa sangat halus & smooth
    targetRotation.y += deltaMove.x * 0.003;
    targetRotation.x += deltaMove.y * 0.003;
    previousMousePosition = { x: e.clientX, y: e.clientY };
  });

  window.addEventListener('mouseup', () => { isDragging = false; });

  // Touch drag listener untuk device handphone
  containerShowcase.addEventListener('touchstart', e => {
    if (e.touches.length > 0) {
      // e.preventDefault() mencegah drag seleksi teks & viewport freeze di browser HP
      e.preventDefault();
      isDragging = true;
      previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
    }
  }, { passive: false }); // passive: false wajib agar preventDefault() disetujui browser mobile

  window.addEventListener('touchmove', e => {
    if (!isDragging || e.touches.length === 0) return;
    const deltaMove = {
      x: e.touches[0].clientX - previousMousePosition.x,
      y: e.touches[0].clientY - previousMousePosition.y
    };
    
    // Mengurangi sensitivitas sentuhan HP (dari 0.008 menjadi 0.003)
    targetRotation.y += deltaMove.x * 0.003;
    targetRotation.x += deltaMove.y * 0.003;
    previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
  }, { passive: true });

  window.addEventListener('touchend', () => { isDragging = false; });

  // ── REVISI GSAP SCROLL: BOLA MULAI BERGERAK DARI TENGAH LAPANGAN HERO SECARA SEAMLESS KE SECTION 2, 3, 4 ──
  
  // Stacking seluruh section agar bisa di-pin dan bertransisi fade silang di atas satu sama lain
  gsap.set(".premium-scroll-sec", { position: "absolute", inset: 0, opacity: 0, autoAlpha: 0 });
  gsap.set("#scroll-sec-hero", { opacity: 1, autoAlpha: 1 }); // Hero stadium tampil pertama di layar

  // Inisialisasi posisi teks spec (tersembunyi sebelum bola tiba)
  gsap.set("#bst-left", { opacity: 0, y: 35 });
  gsap.set("#bst-right", { opacity: 0, y: 35 });

  // Definisikan state bola awal (bola duduk pas di atas center circle stadium lapangan)
  const ballState = {
    x: 0,
    y: 0.8,      // Posisi y melayang tipis di tengah lingkaran lapangan
    scale: 0.15,  // REVISI: Ukuran mikro (0.15) agar pas dengan perspektif 3D stadion di belakangnya
    opacity: 1
  };

  // REVISI RESPONSIVE: Penyetelan skala & batas translasi horizontal bola secara adaptif di Mobile & Desktop
  let isMobile = window.innerWidth <= 768;
  let responsiveScale = {
    hero: isMobile ? 0.08 : 0.15,
    sec2: isMobile ? 0.6 : 0.9,
    sec3: isMobile ? 1.4 : 3.2,
    sec4: isMobile ? 1.4 : 3.2
  };

  let targetX = 3.5;
  function updateTargetX() {
    isMobile = window.innerWidth <= 768;
    responsiveScale.hero = isMobile ? 0.08 : 0.15;
    responsiveScale.sec2 = isMobile ? 0.6 : 0.9;
    responsiveScale.sec3 = isMobile ? 1.4 : 3.2;
    responsiveScale.sec4 = isMobile ? 1.4 : 3.2;

    const aspect = window.innerWidth / window.innerHeight;
    const visibleWidth = 2.0 * Math.tan((50 * Math.PI) / 360) * 10 * aspect;
    
    if (isMobile) {
      // Pada mobile/portrait, bola digeser ke pinggir kanan/kiri luar viewport tapi disisakan radiusnya
      // targetX diletakkan tepat di pinggir agar separuh bola bersembunyi (Slam Dunk Store Style)
      targetX = (visibleWidth / 2);
    } else {
      targetX = (visibleWidth / 2) - 0.25; // Potongan tepi presisi untuk layar desktop
    }
  }
  
  window.addEventListener('resize', () => {
    updateTargetX();
    ScrollTrigger.refresh();
  });
  updateTargetX();

  // Frame border & Pitch outline disembunyikan awalnya, muncul saat scroll masuk Slide 2
  gsap.set(["#premium-border-frame", "#premium-pitch-bg"], { opacity: 0 });

  // Scrubbing Timeline Utama dengan Pinning total diperpanjang ke 700% (memberikan efek scroll yang sangat lambat, megah, dan buttery smooth)
  const ballScrollTl = gsap.timeline({
    scrollTrigger: {
      trigger: ".premium-showcase-container",
      start: "top top",
      end: "+=700%",  
      scrub: 2.5,     
      pin: true,
      anticipatePin: 1,
      onUpdate: (self) => {
        // Hubungkan scroll progress awal (0% s/d 25%) langsung ke kemiringan / fade stadium Hero Section
        targetHeroScrollProgress = Math.min(1.0, self.progress * 4.0);
      }
    }
  });

  ballScrollTl
    // Jeda diam awal di Hero Section agar user bisa membaca / melihat pantulan bola sebelum menggelinding
    .to({}, { duration: 1.0 })
    // REVISI: Sembunyikan scroll indicator tengah bawah secara smooth begitu mulai di-scroll
    .to(".scroll-ind.center", { opacity: 0, y: 15, duration: 0.5, ease: "power1.in" }, "<")
    // 1. TRANSISI SEAMLESS HERO -> SECTION 2 (MINI FUT)
    // Lapangan stadium 3D & background shader air di belakang memudar halus ke hitam
    .to("#scroll-sec-hero", { opacity: 0, autoAlpha: 0, duration: 1.5, ease: "power2.inOut" })
    // Slide Section 2 (MINI FUT) memudar masuk
    .to("#scroll-sec-1", { opacity: 1, autoAlpha: 1, duration: 1.5, ease: "power2.inOut" }, "<")
    // Munculkan outline lapangan taktis putih & bingkai hijau overlay
    .to(["#premium-border-frame", "#premium-pitch-bg"], { opacity: 1, duration: 1.5, ease: "power2.out" }, "<")
    // Bola menggelinding maju (Y: 0.8 -> 0) dan membesar secara buttery smooth sesuai skala responsive
    .to(ballState, { 
      x: 0,
      y: 0, 
      scale: () => responsiveScale.sec2, 
      duration: 1.8, 
      ease: "power2.inOut" 
    }, "<")
    
    // Jeda diam di Section 2 (MINI FUT)
    .to({}, { duration: 1.5 })
    
    // 2. TRANSISI SECTION 2 -> SECTION 3 (RUMPUT PREMIUM)
    .to("#scroll-sec-1", { opacity: 0, autoAlpha: 0, duration: 1.5, ease: "power2.inOut" })
    .to("#scroll-sec-2", { opacity: 1, autoAlpha: 1, duration: 1.5, ease: "power2.inOut" }, "<")
    // Bola bergeser ke kanan & membesar ke mode macro close-up (di HP bergeser ke tepi menyisakan area legibilitas teks kiri)
    .to(ballState, { 
      x: () => targetX, 
      scale: () => responsiveScale.sec3, 
      duration: 2.2, 
      ease: "power3.inOut" 
    }, "<")
    // Teks deskripsi rumput meluncur masuk
    .to("#bst-left", { opacity: 1, y: 0, duration: 1.0, ease: "power2.out" })
    
    // Jeda diam di Section 3 (Premium Synthetic Turf)
    .to({}, { duration: 1.8 })
    
    // 3. TRANSISI SECTION 3 -> SECTION 4 (BOLA PREMIUM)
    .to("#scroll-sec-2", { opacity: 0, autoAlpha: 0, duration: 1.5, ease: "power2.inOut" })
    .to("#scroll-sec-3", { opacity: 1, autoAlpha: 1, duration: 1.5, ease: "power2.inOut" }, "<")
    // Bola bergeser meluncur dari kanan ke kiri secara seamless
    .to(ballState, { 
      x: () => -targetX, 
      scale: () => responsiveScale.sec4, 
      duration: 2.2, 
      ease: "power3.inOut" 
    }, "<")
    // Teks fasilitas masuk
    .to("#bst-right", { opacity: 1, y: 0, duration: 1.0, ease: "power2.out" })
    
    // Jeda diam di Section 4 (Perfect Flight)
    .to({}, { duration: 2.0 })
    
    // 4. TRANSISI KELUAR (EXIT OUTRO)
    // Memudarkan Section 4, border frame, dan pitch bg secara total ke hitam
    .to("#scroll-sec-3", { opacity: 0, autoAlpha: 0, duration: 1.5, ease: "power2.in" })
    .to(["#premium-border-frame", "#premium-pitch-bg"], { opacity: 0, duration: 1.5, ease: "power2.in" }, "<")
    // Bola menggelinding keluar ke arah bawah melewati batas bingkai secara natural
    .to(ballState, { 
      opacity: 0, 
      scale: 1.5, 
      y: -5, 
      duration: 1.5, 
      ease: "power2.in" 
    }, "<")
    .to("#bst-right", { opacity: 0, y: 35, duration: 1.5, ease: "power2.in" }, "<");

  // Loop Render Animasi
  let timeS = 0;
  function renderScrollBall() {
    timeS += 0.01;

    // Kalkulasi efek memantul (bounce) bola saat diam di Hero Section (Scroll progress paling atas)
    let bounceY = 0;
    if (targetHeroScrollProgress < 0.9) {
      // Pantulan meredup seiring ditariknya scroll ke bawah
      let bounceAmp = Math.max(0, 1.0 - targetHeroScrollProgress * 1.5);
      bounceY = Math.sin(timeS * 2.5) * 0.18 * bounceAmp; 
    }

    // Rotasi otomatis saat tidak di-drag pengguna
    if (!isDragging) {
      if (targetHeroScrollProgress < 0.1) {
        // Rotasi melayang santai (idle) di atas lapangan saat scroll di paling atas
        scrollBall.rotation.x = timeS * 0.2;
        scrollBall.rotation.y = timeS * 0.15;
        scrollBall.rotation.z = 0;
      } else {
        // Rotasi menggelinding dinamis mengikuti arah perpindahan koordinat horizontal bola
        scrollBall.rotation.z = -ballState.x * 0.6; 
        scrollBall.rotation.y = -ballState.x * 0.4; 
        // Tambahkan rotasi sumbu X ke depan saat menggelinding dari Hero ke Section 2
        scrollBall.rotation.x = timeS * 0.3 + (ballState.x * 0.3) + (targetHeroScrollProgress * 4.0);
      }
    } else {
      // Jika di-drag, redaman lerp diatur ke 0.05 untuk efek inersia drag yang sangat berbobot & premium
      scrollBall.rotation.x += (targetRotation.x - scrollBall.rotation.x) * 0.05;
      scrollBall.rotation.y += (targetRotation.y - scrollBall.rotation.y) * 0.05;
    }

    // Sinkronisasi posisi data state GSAP ke mesh 3D & CSS overlay
    scrollBall.position.x = ballState.x;
    scrollBall.position.y = ballState.y + bounceY; // Gabungkan posisi y animasi dengan bounceY lapangan
    scrollBall.scale.setScalar(ballState.scale);
    canvasScroll.style.opacity = ballState.opacity;
    
    ballSMat.uniforms.uTime.value = timeS;
    rendererS.render(sceneS, cameraS);
    requestAnimationFrame(renderScrollBall);
  }
  renderScrollBall();
})();

// --- CYBERPUNK SYNTH SOUNDS (Web Audio API) ---
// Menghasilkan suara UI Sci-Fi tanpa file audio eksternal secara asinkron
let audioCtx = null;

function playBeep(freq, type, duration, vol) {
  try {
    if (!audioCtx) {
      audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (audioCtx.state === 'suspended') {
      audioCtx.resume();
    }
    const osc = audioCtx.createOscillator();
    const gainNode = audioCtx.createGain();

    osc.type = type; // 'sine', 'square', 'sawtooth', 'triangle'
    osc.frequency.setValueAtTime(freq, audioCtx.currentTime);

    gainNode.gain.setValueAtTime(vol, audioCtx.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);

    osc.connect(gainNode);
    gainNode.connect(audioCtx.destination);

    osc.start();
    osc.stop(audioCtx.currentTime + duration);
  } catch (e) {
    // Mengabaikan error pemblokiran kebijakan autoplay browser sebelum interaksi awal
  }
}

// Hook suara hover pada seluruh elemen interaktif
const hoverElements = document.querySelectorAll('a, button, .f-card, .p-card-wrapper, .showcase-item, .g-item, #lb-close, .val-card, .award-item, .btn-soft, .fw-card');
hoverElements.forEach(el => {
  el.addEventListener('mouseenter', () => {
    playBeep(800, 'sine', 0.05, 0.02); 
  });
});

// Hook suara klik saat terjadi interaksi aktif pengguna
document.addEventListener('click', (e) => {
  const el = e.target.closest('a, button, .f-card, .p-card-wrapper, .showcase-item, .g-item, #lb-close, .val-card, .award-item, .btn-soft, .fw-card');
  if (el) {
    playBeep(1200, 'square', 0.08, 0.03);
  }
});
</script>

<!-- REVISI: SVG Glass Distortion Filter Definition untuk Liquid Glass Button -->
<svg class="hidden" style="position: absolute; width: 0; height: 0;" width="0" height="0">
  <defs>
    <filter
      id="container-glass"
      x="0%"
      y="0%"
      width="100%"
      height="100%"
      color-interpolation-filters="sRGB"
    >
      <!-- Generate turbulent noise for distortion -->
      <feTurbulence
        type="fractalNoise"
        baseFrequency="0.05 0.05"
        numOctaves="1"
        seed="1"
        result="turbulence"
      />
      <!-- Blur the turbulence pattern slightly -->
      <feGaussianBlur in="turbulence" stdDeviation="2" result="blurredNoise" />
      <!-- Displace the source graphic with the noise -->
      <feDisplacementMap
        in="SourceGraphic"
        in2="blurredNoise"
        scale="70"
        xChannelSelector="R"
        yChannelSelector="B"
        result="displaced"
      />
      <!-- Apply overall blur on the final result -->
      <feGaussianBlur in="displaced" stdDeviation="4" result="finalBlur" />
      <!-- Output the result -->
      <feComposite in="finalBlur" in2="finalBlur" operator="over" />
    </filter>
  </defs>
</svg>
</body>
</html>