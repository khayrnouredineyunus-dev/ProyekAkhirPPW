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
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Anton&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>

:root {
  --black:   #060608; --dark:    #0c0d10; --card:    #111318; --card2:   #161820;
  --border:  rgba(255,255,255,0.06); --border2: rgba(255,255,255,0.1);
  --green:   #00ff88; --green2:  --green; --glow:    rgba(0,255,136,0.18); --glow-sm: rgba(0,255,136,0.07);
  --gray:    #6b7080; --gray2:   #9aa0b0; --white:   #eceef2; --red:     #ff3b5c;
}
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{background:var(--black);color:var(--white);font-family:'Plus Jakarta Sans',sans-serif;overflow-x:hidden;min-height:100vh;}

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
  font-family: 'Plus Jakarta Sans', sans-serif;
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

.nav-cta {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--green);
  background: rgba(0, 255, 136, 0.06);
  border: 1px solid rgba(0, 255, 136, 0.25);
  padding: 10px 22px;
  text-decoration: none;
  border-radius: 30px;
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
  nav { 
    padding: 0 20px; 
    width: calc(100% - 32px);
    top: 16px;
    height: 64px;
    border-radius: 16px;
  }
  .nav-links { display: none; }
  .nav-cta { padding: 10px 20px; font-size: 0.7rem; }
  
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
.sec-label{font-family:'Plus Jakarta Sans',sans-serif;font-size:.68rem;font-weight:700;letter-spacing:5px;text-transform:uppercase;color:var(--green);margin-bottom:10px;}
.sec-title{font-family:'Anton',sans-serif;font-size:clamp(2.2rem,4.5vw,3.8rem);font-weight:400;line-height:0.95;color:var(--white);text-transform:uppercase;letter-spacing:0.5px;}
.sec-sub{font-family:'Plus Jakarta Sans',sans-serif;font-size:.95rem;color:var(--gray2);margin-top:12px;line-height:1.75;}

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

.btn-p{font-family:'Plus Jakarta Sans',sans-serif;font-size:.88rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--black);background:var(--green);border:none;padding:17px 44px;text-decoration:none;clip-path:polygon(10px 0%,100% 0%,calc(100% - 10px) 100%,0 100%);transition:all .3s;position:relative;overflow:hidden;}
.btn-p::after{content:'';position:absolute;inset:0;background:rgba(255,255,255,.18);transform:translateX(-100%);transition:.3s;}
.btn-p:hover::after{transform:translateX(0);}
.btn-s{font-family:'Plus Jakarta Sans',sans-serif;font-size:.88rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--green);background:transparent;border:1px solid rgba(0,255,136,.35);padding:17px 44px;text-decoration:none;transition:all .3s;}
.btn-s:hover{border-color:var(--green);background:var(--glow-sm);}

/* Soften edge button for main bottom CTA */
.btn-soft {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .88rem;
  font-weight: 700;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  color: var(--black);
  background: var(--green);
  border: none;
  padding: 17px 44px;
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

.fields-sec {
  padding: 120px 0;
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
    ),
    var(--black);
  position: relative;
  overflow: hidden;
}
.fields-head{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:56px;}
.fields-title-wrap {
  display: inline-flex;
  align-items: baseline;
  flex-wrap: wrap;
  gap: 12px;
  margin: 0;
}
.fields-bg-text {
  font-family: 'Anton', sans-serif;
  font-size: clamp(2.2rem, 4.5vw, 3.8rem);
  font-weight: 400;
  color: transparent;
  -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.22);
  text-transform: uppercase;
  letter-spacing: 2px;
  line-height: 1;
  transition: color 0.3s ease, -webkit-text-stroke 0.3s ease;
  cursor: pointer;
}
.fields-bg-text:hover {
  color: var(--white);
  -webkit-text-stroke: 1.5px transparent;
}
.facilities-bg-text {
  font-family: 'Anton', sans-serif;
  font-size: clamp(2.2rem, 4.5vw, 3.8rem);
  font-weight: 400;
  color: transparent;
  -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.22);
  text-transform: uppercase;
  letter-spacing: 12px;
  line-height: 1;
  transition: color 0.3s ease, -webkit-text-stroke 0.3s ease;
  cursor: pointer;
  display: inline-flex;
  align-items: baseline;
}
.facilities-bg-text:hover {
  color: var(--white);
  -webkit-text-stroke: 1.5px transparent;
}
.fields-fg-text {
  font-family: 'Anton', sans-serif;
  font-size: clamp(2.2rem, 4.5vw, 3.8rem);
  font-weight: 400;
  color: var(--white);
  -webkit-text-stroke: 1.5px var(--white);
  text-transform: uppercase;
  letter-spacing: 1px;
  line-height: 1;
  display: inline-flex;
  align-items: baseline;
}
.fields-green-dot {
  display: inline-block;
  width: clamp(10px, 1.8vw, 14px);
  height: clamp(10px, 1.8vw, 14px);
  background-color: var(--green);
  margin-left: 6px;
  vertical-align: baseline;
  box-shadow: 0 0 12px var(--glow);
}

.title-style-swapped .fields-bg-text {
  color: var(--white);
  -webkit-text-stroke: 1.5px var(--white);
}
.title-style-swapped .fields-fg-text {
  color: transparent;
  -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.22);
}
.title-style-italic .fields-bg-text,
.title-style-italic .fields-fg-text {
  font-style: italic;
}
.title-style-mixed-swapped .fields-bg-text {
  color: var(--white);
  -webkit-text-stroke: 1.5px var(--white);
}
.title-style-mixed-swapped .fields-fg-text {
  color: transparent;
  -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.22);
  font-style: italic;
}
.title-style-mixed-bg-italic .fields-bg-text {
  font-style: italic;
}
.title-style-all-outline .fields-bg-text,
.title-style-all-outline .fields-fg-text {
  color: transparent;
  -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.22);
}
@media(max-width:768px){.fields-head{flex-direction:column;align-items:flex-start;gap:20px;}}
.fields-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:3px;}
.f-card{background:var(--card);border:1px solid var(--border);position:relative;overflow:hidden;transition:transform .4s,border-color .3s;transform-style:preserve-3d;}
.f-card:hover{border-color:rgba(0,255,136,.35);}
.f-card-img{height:270px;overflow:hidden;position:relative;}
.f-card-img img{width:100%;height:100%;object-fit:cover;filter:brightness(.65) saturate(.7);transition:transform .65s ease,filter .65s ease;}
.f-card:hover .f-card-img img{transform:scale(1.08);filter:brightness(.55) saturate(1.1);}
.f-card-img-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(6,6,8,.95) 0%,transparent 55%);}
.f-card-badge-wrap{position:absolute;bottom:18px;left:20px;z-index:1;}
.f-badge{display:inline-block;font-family:'Plus Jakarta Sans',sans-serif;font-size:.6rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--green);border:1px solid rgba(0,255,136,.4);padding:3px 10px;background:rgba(0,255,136,.06);margin-bottom:6px;}
.f-card-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.4rem;font-weight:700;color:var(--white);letter-spacing:0.5px;line-height:1;}
.f-card-body{padding:22px;}
.f-tags{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px;}
.f-card-body 
.f-tag{font-family:'Plus Jakarta Sans',sans-serif;font-size:.65rem;letter-spacing:1px;color:var(--gray2);background:rgba(255,255,255,.04);border:1px solid var(--border);padding:3px 9px;}
.f-price-row{display:flex;justify-content:space-between;align-items:center;}
.f-price-val{font-family:'Orbitron',sans-serif;font-size:1.2rem;font-weight:700;color:var(--green);letter-spacing:1px;}
.f-price-unit{font-family:'Plus Jakarta Sans',sans-serif;font-size:.72rem;color:var(--gray);margin-left:4px;}
.f-book-btn{font-family:'Plus Jakarta Sans',sans-serif;font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--green);background:transparent;border:1px solid rgba(0,255,136,.35);padding:8px 18px;text-decoration:none;transition:all .3s;}
.f-book-btn:hover{background:var(--green);color:var(--black);}

/* GALLERY */
.gallery-sec{padding:100px 0;background:var(--dark);overflow:hidden;}
.gallery-head{text-align:center;margin-bottom:52px;}
.gallery-title-wrap {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}
.gallery-bg-text {
  font-family: 'Anton', sans-serif;
  font-size: clamp(4.5rem, 9vw, 7rem);
  font-weight: 400;
  color: transparent;
  -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.18);
  text-transform: uppercase;
  letter-spacing: 4px;
  line-height: 0.9;
  z-index: 1;
  pointer-events: none;
}
.gallery-fg-text {
  font-family: 'Anton', sans-serif;
  font-size: clamp(3rem, 6vw, 4.8rem);
  font-weight: 400;
  color: var(--white);
  -webkit-text-stroke: 1.5px var(--white);
  text-transform: uppercase;
  letter-spacing: 2px;
  line-height: 0.9;
  z-index: 2;
  margin: 0;
  margin-top: -8px;
  display: inline-flex;
  align-items: baseline;
}
.gallery-green-dot {
  display: inline-block;
  width: clamp(12px, 2vw, 18px);
  height: clamp(12px, 2vw, 18px);
  background-color: var(--green);
  margin-left: 8px;
  vertical-align: baseline;
  box-shadow: 0 0 15px var(--glow);
}

.pricing-title-wrap {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}
.pricing-fg-text {
  font-family: 'Anton', sans-serif;
  font-size: clamp(3rem, 6vw, 4.8rem);
  font-weight: 400;
  color: var(--white);
  -webkit-text-stroke: 1.5px var(--white);
  text-transform: uppercase;
  letter-spacing: 2px;
  line-height: 0.9;
  z-index: 2;
  margin: 0;
}
.pricing-bg-text {
  font-family: 'Anton', sans-serif;
  font-size: clamp(4.5rem, 9vw, 7rem);
  font-weight: 400;
  color: transparent;
  -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.18);
  text-transform: uppercase;
  letter-spacing: 4px;
  line-height: 0.9;
  z-index: 1;
  pointer-events: none;
  margin-top: -8px;
  display: inline-flex;
  align-items: baseline;
}
.pricing-green-dot {
  display: inline-block;
  width: clamp(12px, 2vw, 18px);
  height: clamp(12px, 2vw, 18px);
  background-color: var(--green);
  margin-left: 8px;
  vertical-align: baseline;
  box-shadow: 0 0 15px var(--glow);
}

.gallery-mosaic{display:grid;grid-template-columns:repeat(12,1fr);grid-template-rows:220px 220px;gap:3px;max-width:1200px;margin:0 auto;padding:0 64px;}
.g-item{position:relative;overflow:hidden;}
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
.g-label{position:absolute;bottom:14px;left:14px;font-family:'Plus Jakarta Sans',sans-serif;font-size:.65rem;letter-spacing:2.5px;text-transform:uppercase;color:var(--green);opacity:0;transition:opacity .3s;}
.g-item:hover .g-label{opacity:1;}

#lb{position:fixed;inset:0;background:rgba(0,0,0,.96);z-index:9999;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s;}
#lb.open{opacity:1;pointer-events:all;}
#lb img{max-width:82vw;max-height:82vh;object-fit:contain;border:1px solid rgba(0,255,136,.2);}
#lb-close{position:absolute;top:28px;right:32px;font-size:1.4rem;color:var(--green);background:none;border:none;font-family:'Orbitron',monospace; transition:all 0.3s;}
#lb-close:hover { color: var(--white); text-shadow: 0 0 10px var(--green); }

.showcase-sec{padding:120px 0;background:var(--dark);overflow:hidden;}
.showcase-head{text-align:center;margin-bottom:56px;}
.showcase-grid-layout {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 16px;
}
@media(max-width: 1024px) {
  .showcase-grid-layout {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media(max-width: 600px) {
  .showcase-grid-layout {
    grid-template-columns: 1fr;
  }
}

.sc-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 16px;
  overflow: hidden;
  position: relative;
  transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1), border-color 0.3s ease, box-shadow 0.4s ease;
  transform-style: preserve-3d;
  display: flex;
  flex-direction: column;
}
.sc-card:hover {
  border-color: rgba(0, 255, 136, 0.35);
  box-shadow: 0 15px 35px rgba(0, 255, 136, 0.08);
}
.sc-card-img-wrap {
  height: 200px;
  overflow: hidden;
  position: relative;
}
.sc-card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: brightness(0.65) saturate(0.8);
  transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1), filter 0.6s ease;
}
.sc-card:hover .sc-card-img {
  transform: scale(1.08);
  filter: brightness(0.8) saturate(1.1);
}
.sc-card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(6, 6, 8, 0.95) 0%, transparent 60%);
}
.sc-card-body {
  padding: 24px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  z-index: 1;
}
.sc-card-title {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--white);
  margin-bottom: 10px;
  letter-spacing: 0.5px;
  line-height: 1.3;
  transition: color 0.3s ease;
}
.sc-card:hover .sc-card-title {
  color: var(--green);
}
.sc-card-desc {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.85rem;
  color: var(--gray2);
  line-height: 1.6;
  margin: 0;
}
.sc-card-glow {
  display: none;
}

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
  opacity: 0.5;
}
.featured-shine {
  animation: spin-shine 3s linear infinite;
  opacity: 0.6;
}

@keyframes spin-shine {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.p-card{
  padding:40px;
  background:var(--card);
  border:none;
  position:relative;
  transition:background 0.3s;
  margin: 2px;
  border-radius: 14px;
  height: calc(100% - 4px);
}
.p-card-wrapper:hover .p-card {
  background: rgba(17, 19, 24, 0.95);
}
.p-card.featured{background: #0d1612;}
.p-card.featured:hover {background: #111e18;}
.p-card.featured::before{content:'TERPOPULER';position:absolute;top:0;left:50%;transform:translate(-50%,-50%);font-family:'Plus Jakarta Sans',sans-serif;font-size:.58rem;font-weight:700;letter-spacing:3px;color:var(--black);background:var(--green);padding:4px 16px;white-space:nowrap;z-index:10;}

.p-tag{font-family:'Plus Jakarta Sans',sans-serif;font-size:.68rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:var(--green);margin-bottom:6px;}
.p-name{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.4rem;font-weight:800;color:var(--white);margin-bottom:20px;text-transform:none;letter-spacing:0.5px;line-height:1.1;}
.p-price{display:flex;align-items:baseline;gap:3px;margin-bottom:6px;}
.p-cur{font-family:'Plus Jakarta Sans',sans-serif;font-size:.9rem;color:var(--gray);}
.p-amount{display:inline-block;font-family:'Orbitron',sans-serif;font-size:2rem;font-weight:700;color:var(--green);transition:transform 0.3s ease, text-shadow 0.3s ease;letter-spacing:1px;}
.p-per{font-family:'Plus Jakarta Sans',sans-serif;font-size:.78rem;color:var(--gray);}
.p-div{height:1px;background:var(--border);margin:24px 0;}
.p-feats{list-style:none;display:flex;flex-direction:column;gap:11px;margin-bottom:28px;}
.p-feats li{font-family:'Plus Jakarta Sans',sans-serif;font-size:.84rem;color:var(--gray2);display:flex;align-items:center;gap:10px;}
.p-feats li::before{content:'✦';color:var(--green);font-size:.58rem;flex-shrink:0;}
.p-btn{font-family:'Plus Jakarta Sans',sans-serif;font-size:.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;display:block;text-align:center;text-decoration:none;padding:13px;border:1px solid rgba(0,255,136,.4);color:var(--green);transition:all .3s;}
.p-card:hover .p-amount{display:inline-block;font-family:'Orbitron',sans-serif;font-size:2rem;font-weight:700;color:var(--green);transition:transform 0.3s ease, text-shadow 0.3s ease;letter-spacing:1px;}
.p-card:hover .p-btn { background: var(--green); color: var(--black); box-shadow: 0 0 15px rgba(0, 255, 136, 0.4); border-color: var(--green); }
.p-card:hover .p-btn.solid { background: #00e676; box-shadow: 0 0 20px rgba(0,255,136,0.6); }
.p-btn.solid{background:var(--green);color:var(--black);border-color:var(--green);}

.values-sec{padding:130px 0;position:relative;overflow:hidden;}
.values-sec::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(0,255,136,.3),transparent);}
.values-sec::after{content:'';position:absolute;bottom:-150px;left:-150px;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(0,255,136,.04) 0%,transparent 70%);pointer-events:none;}
.values-hex-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:3px;margin-top:56px;}
@media(max-width:768px){.values-hex-grid{grid-template-columns:1fr;}}
.val-card{padding:48px 36px;background:var(--card);border:1px solid var(--border);position:relative;overflow:hidden;transition:all .35s;}
.val-card:hover{border-color:rgba(0,255,136,.25);transform:translateY(-4px);}
.val-card::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at var(--mx,50%) var(--my,50%),rgba(0,255,136,.06) 0%,transparent 65%);opacity:0;transition:opacity .35s;}
.val-card:hover::before{opacity:1;}
.val-num{font-family:'Anton',sans-serif;font-size:4rem;font-weight:400;color:transparent;-webkit-text-stroke:1px rgba(0,255,136,.12);position:absolute;top:16px;right:20px;line-height:1;}
.val-name{font-family:'Plus Jakarta Sans',sans-serif;font-size:1rem;font-weight:700;color:var(--white);margin-bottom:14px;letter-spacing:1px;}
.val-desc{font-family:'Plus Jakarta Sans',sans-serif;font-size:.88rem;color:var(--gray2);line-height:1.75;}

.awards-sec{padding:80px 0;background:var(--dark);overflow:hidden;position:relative;}
.awards-sec::before,.awards-sec::after{content:'';position:absolute;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(0,255,136,.2),transparent);}
.awards-sec::before{top:0;}.awards-sec::after{bottom:0;}
.awards-track{display:flex;gap:0;white-space:nowrap;animation:marqueeMove 30s linear infinite; will-change: transform;}
.awards-sec:hover .awards-track {animation-play-state: paused;}
.awards-track-rev{animation-direction:reverse;animation-duration:35s;margin-top:3px;}
@keyframes marqueeMove{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.award-item{display:inline-flex;align-items:center;gap:16px;padding:20px 36px;background:var(--card);border:1px solid var(--border);margin-right:3px;flex-shrink:0;transition:border-color .3s;}
.award-item:hover{border-color:rgba(0,255,136,.25);}
.award-name{font-family:'Plus Jakarta Sans',sans-serif;font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gray2);}
.award-year{font-family:'Orbitron',monospace;font-size:.65rem;color:var(--green);}

.cta-sec{padding:130px 0;background:var(--black);text-align:center;position:relative;overflow:hidden;}
.cta-sec .fields-bg-text, .cta-sec .fields-fg-text { font-family: 'Plus Jakarta Sans', sans-serif !important; font-weight: 800; }
.cta-title{font-family:'Anton',sans-serif;font-size:clamp(2.4rem,6vw,4.8rem);font-weight:400;color:var(--white);line-height:0.95;margin-bottom:20px;text-transform:uppercase;letter-spacing:1px;}
.cta-title span{color:var(--green);}
.cta-sub{font-family:'Plus Jakarta Sans',sans-serif;font-size:.95rem;color:var(--gray2);margin-bottom:40px;}

footer{padding:64px 0 28px;border-top:1px solid var(--border);position:relative;z-index:6;background:var(--black);}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:60px;margin-bottom:56px;}
@media(max-width:900px){.footer-grid{grid-template-columns:1fr 1fr;}}
.footer-desc{font-family:'Plus Jakarta Sans',sans-serif;font-size:.84rem;color:var(--gray);line-height:1.75;max-width:270px;margin-top:14px;}
.f-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:.65rem;letter-spacing:3px;text-transform:uppercase;color:var(--gray2);margin-bottom:20px;font-weight:700;}
.f-links{list-style:none;display:flex;flex-direction:column;gap:11px;}
.f-links li, .f-links a{font-family:'Plus Jakarta Sans',sans-serif;font-size:.85rem;color:var(--gray);text-decoration:none;transition:color .25s;line-height:1.5;}
.f-links a:hover{color:var(--green);}
.f-links li strong {color:var(--white); font-weight:500;}
.footer-bottom{display:flex;justify-content:space-between;align-items:center;padding-top:32px;border-top:1px solid var(--border);}
.footer-copy{font-family:'Plus Jakarta Sans',sans-serif;font-size:.7rem;letter-spacing:1px;color:var(--gray);}
::-webkit-scrollbar{width:4px;height:4px;}
::-webkit-scrollbar-thumb{background:rgba(0,255,136,.15) }

.fields-sec, .gallery-sec, .showcase-sec, .pricing-sec, .cta-sec { position: relative; }

.gallery-sec::before, .showcase-sec::before, .pricing-sec::before, .cta-sec::before {
  content:''; position:absolute; top:0; left:0; right:0; height:1px;
  background:linear-gradient(90deg, transparent, rgba(0,255,136,.3), transparent);
}
.pricing-sec::after {
  content:''; position:absolute; bottom:0; left:0; right:0; height:1px;
  background:linear-gradient(90deg, transparent, rgba(0,255,136,.3), transparent);
}

.cta-sec::after {
  content:''; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
  width:700px; height:350px; border-radius:50%;
  background:radial-gradient(ellipse, rgba(0,255,136,.07) 0%, transparent 70%); pointer-events:none;
}
.showcase-sec::after {
  content:''; position:absolute; pointer-events:none;
}

@media (max-width: 1024px) { 
  .gallery-mosaic { grid-template-columns: 1fr; grid-template-rows: auto; }
  .g-item:nth-child(n) { grid-column: 1 / -1 !important; grid-row: auto !important; height: 250px; }
}

.premium-showcase-container {
  background: var(--black);
  padding: 0;
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 5;
  height: 100vh;
  overflow: hidden;
  
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.premium-scroll-sec {
  position: absolute !important;
  inset: 0;
  height: 100vh;
  width: 100%;
  background: transparent !important;
  box-sizing: border-box;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
}

.premium-scroll-sec * {
  pointer-events: auto;
}

#scroll-sec-hero {
  background: #060608 !important;
  z-index: 10;
}
#scroll-sec-1 { z-index: 15; }
#scroll-sec-2 { z-index: 20; }
#scroll-sec-3 { z-index: 25; }

/* Container utama untuk kerangka outline overlay (Bingkai Hijau) */
#premium-border-frame {
  position: fixed;
  top: 16px; left: 16px; right: 16px; bottom: 16px;
  border-radius: 40px;
  pointer-events: none;
  opacity: 0;
  z-index: 998;
  box-shadow: 0 0 0 16px var(--green), 0 0 0 100vmax var(--green); 
}

#premium-pitch-bg {
  position: fixed;
  top: 16px; left: 16px; right: 16px; bottom: 16px;
  border-radius: 40px;
  pointer-events: none;
  opacity: 0;
  z-index: 6;
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
    height: 60vh;
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

.ball-section-text { 
  position: absolute; 
  z-index: 10; 
  top: 50%; 
  transform: translateY(-50%); 
  max-width: 380px; 
  opacity: 0; 
  
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
#bst-left { left: 8%; }
#bst-right { right: 8%; display: flex; flex-direction: column; align-items: flex-end; }
#bst-right .bst-title {
  font-size: clamp(3.6rem, 6.5vw, 5.6rem);
  line-height: 0.9;
  letter-spacing: -1px;
}
.bst-label { font-family: 'Plus Jakarta Sans', sans-serif; font-size: .85rem; font-weight: 600; letter-spacing: 5px; text-transform: uppercase; color: var(--green); margin-bottom: 10px; }
.bst-label.green-dot {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--green);
  text-transform: uppercase;
  letter-spacing: 2px;
  margin-bottom: 14px;
}
.bst-label.green-dot .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--green);
  display: inline-block;
  box-shadow: 0 0 10px var(--glow);
}
.bst-label-wrap {
  margin-bottom: 18px;
}
.bst-label-pill {
  display: inline-block;
  padding: 5px 16px;
  border: 1.5px solid rgba(255,255,255,0.4);
  border-radius: 20px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.68rem;
  font-weight: 700;
  color: var(--white);
  text-transform: uppercase;
  letter-spacing: 2.5px;
}
.bst-title { 
  font-family: 'Anton', sans-serif; 
  font-size: clamp(2.8rem, 5.2vw, 4.4rem); 
  font-weight: 400; 
  line-height: 0.95; 
  color: var(--white); 
  text-transform: uppercase; 
  letter-spacing: -0.5px; 
}
.bst-title span {
  transition: color 0.3s ease;
  color: inherit;
}
.bst-title span:hover {
  color: var(--green);
}
.bst-stats-wrap {
  margin-top: 40px;
  display: flex;
  flex-direction: column;
  gap: 32px;
}
.bst-stat-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding-left: 20px;
  border-left: 1.5px solid rgba(255, 255, 255, 0.25);
}
.bst-stat-val {
  font-family: 'Anton', sans-serif;
  font-size: 2.4rem;
  color: var(--white);
  line-height: 1;
}
.bst-stat-val .val-unit {
  font-size: 1.2rem;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-weight: 400;
  vertical-align: baseline;
  margin-left: 2px;
}
.bst-stat-sub {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.68rem;
  font-weight: 700;
  color: var(--white);
  letter-spacing: 2px;
  text-transform: uppercase;
}
.bst-stat-desc {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.78rem;
  font-weight: 300;
  color: var(--gray2);
  line-height: 1.5;
  margin-top: 4px;
  max-width: 320px;
}
.bst-stats-right-wrap {
  margin-top: 40px;
  display: flex;
  flex-direction: column;
  gap: 32px;
  align-items: flex-end;
}
.bst-stat-right-item {
  display: flex;
  align-items: flex-start;
  gap: 20px;
}
.bst-stat-right-text {
  text-align: right;
}
.bst-stat-right-val {
  font-family: 'Anton', sans-serif;
  font-size: 2.0rem;
  color: var(--white);
  line-height: 1;
  transition: color 0.3s ease;
}
.bst-stat-right-val:hover {
  color: var(--green);
}
.bst-stat-right-sub {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.68rem;
  font-weight: 700;
  color: var(--gray2);
  letter-spacing: 1.5px;
  margin-top: 4px;
}
.bst-stat-right-dot {
  width: 34px;
  height: 34px;
  border: 1.5px solid rgba(255,255,255,0.25);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 0px;
}
.bst-stat-right-dot .inner-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--white);
}
.bst-bottom-desc {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.92rem;
  font-weight: 300;
  color: var(--gray2);
  line-height: 1.7;
  margin-top: 48px;
  text-align: right;
  max-width: 350px;
}
.bst-grid-lines {
  position: absolute;
  inset: 0;
  display: flex;
  justify-content: space-between;
  pointer-events: none;
  z-index: 1;
  padding: 0 10%;
}
.bst-grid-lines .grid-line {
  width: 1px;
  height: 100%;
  background: rgba(255,255,255,0.04);
}
.bst-curved-bg {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.6;
}
.bst-curved-bg svg {
  width: 100%;
  height: 100%;
  transform: scale(1.4);
}

@media (max-width: 1024px) {
  .ball-section-text {
    max-width: 180px !important;
    width: auto;
    top: 50% !important;
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
    align-items: flex-end;
  }
  .bst-title {
    font-size: clamp(1.4rem, 5.5vw, 1.8rem);
    line-height: 1.1;
  }
  .bst-stats-wrap {
    margin-top: 16px;
    gap: 16px;
    padding-left: 12px;
  }
  .bst-stat-val, .bst-stat-right-val {
    font-size: 1.6rem;
  }
  .bst-stat-desc, .bst-bottom-desc {
    display: none !important;
  }
  .bst-stats-right-wrap {
    margin-top: 16px;
    gap: 16px;
  }
  .bst-stat-right-dot {
    width: 24px;
    height: 24px;
  }
  .bst-stat-right-item {
    gap: 10px;
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
  font-family: 'Plus Jakarta Sans', sans-serif;
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

.fanwall-sec {
  padding: 110px 0;
  background: var(--black);
  position: relative;
  overflow: hidden;
}

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
body, a, button, .f-card, .showcase-item, .g-item, .nav-cta, .btn-p, .btn-s, .btn-soft, .f-book-btn, #lb-close, .val-card, .award-item, .p-btn, .fw-card {
  cursor: auto !important;
}
a, button, [role="button"], input[type="submit"], select, textarea, .btn-p, .btn-s, .btn-soft, .f-book-btn, #lb-close, .nav-cta, .p-btn {
  cursor: pointer !important;
}

.gallery-sec, .pricing-sec, .values-sec {
  position: relative;
  overflow: hidden;
}
.gallery-sec .container, 
.gallery-mosaic,
.pricing-sec .container,
.values-sec .container {
  position: relative;
  z-index: 2;
}

/* 1. Monochrome Radial Spotlight (Galeri) */
.bg-deco-spotlight {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 0;
  overflow: hidden;
}
.spotlight-circle {
  position: absolute;
  top: -250px;
  left: -250px;
  width: 800px;
  height: 800px;
  border-radius: 50%;
  background: radial-gradient(circle, #181818 0%, transparent 70%);
  opacity: 0.8;
  animation: spotlightFloat 20s infinite ease-in-out alternate;
}
@keyframes spotlightFloat {
  0% { transform: translate(10%, 20%); }
  50% { transform: translate(60%, 40%); }
  100% { transform: translate(30%, 80%); }
}

/* 4. Tactile Film Grain / Grunge Noise Overlay (Values) */
.bg-deco-noise {
  position: absolute;
  inset: 0;
  opacity: 0.035;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
  pointer-events: none;
  z-index: 1;
}

/* Green glows scattered in values section representing lights refracting through glass cards */
.values-glows {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
  z-index: 1;
}
.v-glow {
  position: absolute;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0, 255, 136, 0.05) 0%, transparent 70%);
  filter: blur(50px);
  pointer-events: none;
}
.glow-1 {
  top: -10%;
  right: -5%;
  width: 450px;
  height: 450px;
}
.glow-2 {
  bottom: 10%;
  right: 10%;
  width: 350px;
  height: 350px;
}

/* Green Pricing Background Decorations (Grid + Blurs + Radial Glow) */
.pricing-sec {
  position: relative;
  overflow: hidden;
}
.bg-pricing-grid {
  position: absolute;
  inset: 0;
  background-image: 
    linear-gradient(to right, rgba(0, 255, 136, 0.04) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(0, 255, 136, 0.02) 1px, transparent 1px);
  background-size: 70px 80px;
  pointer-events: none;
  z-index: 1;
  mask-image: radial-gradient(ellipse 60% 50% at 50% 50%, black, transparent);
  -webkit-mask-image: radial-gradient(ellipse 60% 50% at 50% 50%, black, transparent);
}
.bg-pricing-blurs {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
  z-index: 0;
}
.pricing-blur-ellipse {
  position: absolute;
  top: -15%;
  left: 50%;
  transform: translateX(-50%);
  width: 900px;
  height: 900px;
  border-radius: 50%;
  border: 180px solid rgba(0, 255, 136, 0.05);
  filter: blur(80px);
  -webkit-filter: blur(80px);
}
.pricing-radial-glow {
  position: absolute;
  top: 10%;
  left: 50%;
  transform: translateX(-50%);
  width: 80%;
  height: 80%;
  background: radial-gradient(circle at center, rgba(0, 255, 136, 0.06) 0%, transparent 70%);
  pointer-events: none;
}
</style>
</head>
<body>
<div id="noise"></div>
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
      <a href="profile.php" title="Profil Saya" style="flex-shrink:0;">
        <img src="admin/uploads/<?= htmlspecialchars($user_foto, ENT_QUOTES, 'UTF-8') ?>" 
            style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid var(--green); box-shadow: 0 0 10px var(--glow); cursor: pointer; transition: box-shadow .3s, transform .3s; flex-shrink: 0;" 
            alt="Foto Profil"
            onmouseover="this.style.boxShadow='0 0 18px var(--green)';this.style.transform='scale(1.08)'"
            onmouseout="this.style.boxShadow='0 0 10px var(--glow)';this.style.transform='scale(1)'">
      </a>
    <?php else: ?>
      <a href="profile.php" title="Profil Saya" style="flex-shrink:0;text-decoration:none;">
        <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(0, 255, 136, 0.1); border: 2px solid var(--green); display: flex; align-items: center; justify-content: center; font-family: 'Orbitron', monospace; font-size: 0.85rem; font-weight: 700; color: var(--green); box-shadow: 0 0 10px var(--glow); cursor: pointer; transition: box-shadow .3s, transform .3s; flex-shrink: 0;"
            onmouseover="this.style.boxShadow='0 0 18px var(--green)';this.style.transform='scale(1.08)'"
            onmouseout="this.style.boxShadow='0 0 10px var(--glow)';this.style.transform='scale(1)'">
          <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
        </div>
      </a>
    <?php endif; ?>

    <a href="auth/logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari MiniFut Arena?')" class="nav-cta nav-cta-logout" style="background:rgba(255,59,92,.06); border-color:rgba(255,59,92,0.25); color:#ff7096; padding: 6px 14px; border-radius: 30px; font-size: 0.68rem;">
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
    
    <!-- CANVAS INTERACTIVE SOCCER STADIUM 3D (FOREGROUND LAYER) -->
    <canvas id="three-canvas"></canvas>
    
    <div class="hero-overlay"></div>
    <div class="hero-grid"></div>
    <div class="scroll-ind center">
      <span>Scroll Down</span>
      <div class="scroll-bar"></div>
    </div>
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
    <div class="bst-grid-lines">
      <div class="grid-line"></div>
      <div class="grid-line"></div>
      <div class="grid-line"></div>
    </div>
    <div class="ball-section-text" id="bst-left">
      <div class="bst-label green-dot">
        <span class="dot"></span>
        FIFA GRADE
      </div>
      <h2 class="bst-title">PREMIUM<br>FIELD</h2>
      <div class="bst-stats-wrap">
        <div class="bst-stat-item">
          <div class="bst-stat-sub">PENCEGAHAN CEDERA</div>
          <p class="bst-stat-desc">Menyerap benturan secara optimal untuk mencegah risiko cedera sendi dan otot.</p>
        </div>
        <div class="bst-stat-item">
          <div class="bst-stat-sub">BEBAS GENANGAN AIR</div>
          <p class="bst-stat-desc">Sistem drainase superior menjaga lapangan tetap kering and aman setelah hujan deras.</p>
        </div>
        <div class="bst-stat-item">
          <div class="bst-stat-sub">PERFORMA KONSISTEN</div>
          <p class="bst-stat-desc">Pantulan akurat dan pergerakan mulus memberikan sensasi main layaknya atlet profesional.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- SECTION 4: BOLA PREMIUM -->
  <section class="premium-scroll-sec" id="scroll-sec-3">
    <div class="bst-curved-bg">
      <svg viewBox="0 0 1000 600" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M-100,200 C300,220 700,220 1100,200" stroke="rgba(255,255,255,0.05)" stroke-width="1.5" stroke-dasharray="6,6" />
        <path d="M-100,300 C300,330 700,330 1100,300" stroke="rgba(255,255,255,0.05)" stroke-width="1.5" stroke-dasharray="6,6" />
        <path d="M-100,400 C300,440 700,440 1100,400" stroke="rgba(255,255,255,0.05)" stroke-width="1.5" stroke-dasharray="6,6" />
      </svg>
    </div>
    <div class="ball-section-text right" id="bst-right">
      <div class="bst-label-wrap">
        <div class="bst-label-pill">FIFA QUALITY</div>
      </div>
      <h2 class="bst-title">FIFA<br><span>BALL</span></h2>
      <div class="bst-stats-right-wrap">
        <div class="bst-stat-right-item">
          <div class="bst-stat-right-text">
            <div class="bst-stat-right-val">01</div>
            <div class="bst-stat-right-sub">AKURASI PRESISI</div>
          </div>
          <div class="bst-stat-right-dot">
            <span class="inner-dot"></span>
          </div>
        </div>
        <div class="bst-stat-right-item">
          <div class="bst-stat-right-text">
            <div class="bst-stat-right-val">02</div>
            <div class="bst-stat-right-sub">DAYA TAHAN SUPERIOR</div>
          </div>
          <div class="bst-stat-right-dot">
            <span class="inner-dot"></span>
          </div>
        </div>
      </div>
      <p class="bst-bottom-desc">Bola berstandar resmi FIFA menjamin akurasi lintasan presisi, daya tahan superior, dan minim penyerapan air.</p>
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
        <h2 class="fields-title-wrap">
          <span class="fields-bg-text">3 LAPANGAN</span>
          <span class="fields-fg-text">OUTDOOR<span class="fields-green-dot"></span></span>
        </h2>
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
            <div class="f-card-title">Lapangan 1</div>
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
            <div class="f-card-title">Lapangan 2</div>
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
            <div class="f-card-title">Lapangan 3</div>
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
  <div class="bg-deco-spotlight"><div class="spotlight-circle"></div></div>
  <div class="container">
    <div class="gallery-head reveal">
      <div class="gallery-title-wrap">
        <div class="gallery-bg-text">GALERI</div>
        <h2 class="gallery-fg-text">ARENA<span class="gallery-green-dot"></span></h2>
      </div>
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
      <h2 class="fields-title-wrap">
        <span class="fields-fg-text">ELITE</span>
        <span class="facilities-bg-text">FACILITIES<span class="fields-green-dot"></span></span>
      </h2>
    </div>
    <div class="showcase-grid-layout">
      <!-- Card 1 -->
      <div class="sc-card reveal">
        <div class="sc-card-img-wrap">
          <img src="assets/parkir.png" alt="Parkir" class="sc-card-img" loading="lazy">
          <div class="sc-card-overlay"></div>
        </div>
        <div class="sc-card-body">
          <h3 class="sc-card-title">Large Parking Area</h3>
          <p class="sc-card-desc">Area parkir kami mampu menampung puluhan kendaraan baik motor maupun mobil. Akses keluar masuk yang mudah dan diawasi CCTV.</p>
        </div>
        <div class="sc-card-glow"></div>
      </div>
      <!-- Card 2 -->
      <div class="sc-card reveal">
        <div class="sc-card-img-wrap">
          <img src="assets/cafe.png" alt="Cafe" class="sc-card-img" loading="lazy">
          <div class="sc-card-overlay"></div>
        </div>
        <div class="sc-card-body">
          <h3 class="sc-card-title">Restaurant & Café</h3>
          <p class="sc-card-desc">Haus setelah bertanding? Pesan minuman atau makanan di area restaurant kami sambil bersantai setelah pertandingan.</p>
        </div>
        <div class="sc-card-glow"></div>
      </div>
      <!-- Card 3 -->
      <div class="sc-card reveal">
        <div class="sc-card-img-wrap">
          <img src="https://i.pinimg.com/736x/5a/09/75/5a0975a1f56266edb107157f7158a6b3.jpg" alt="Ruang Ganti" class="sc-card-img" loading="lazy">
          <div class="sc-card-overlay"></div>
        </div>
        <div class="sc-card-body">
          <h3 class="sc-card-title">Changing Room</h3>
          <p class="sc-card-desc">Fasilitas kamar mandi dan ruang ganti yang selalu dijaga kebersihannya, lengkap, dan nyaman digunakan.</p>
        </div>
        <div class="sc-card-glow"></div>
      </div>
      <!-- Card 4 -->
      <div class="sc-card reveal">
        <div class="sc-card-img-wrap">
          <img src="https://i.pinimg.com/736x/d3/77/6e/d3776ef7e9e415c7d21822c9ebc1b51f.jpg" alt="Pencahayaan LED" class="sc-card-img" loading="lazy">
          <div class="sc-card-overlay"></div>
        </div>
        <div class="sc-card-body">
          <h3 class="sc-card-title">Full LED Lighting</h3>
          <p class="sc-card-desc">Bermain malam hari bukan masalah. Lapangan kami dilengkapi dengan lampu sorot LED standar internasional untuk visibilitas maksimal.</p>
        </div>
        <div class="sc-card-glow"></div>
      </div>
    </div>
  </div>
</section>

<section class="pricing-sec" id="harga">
  <div class="bg-pricing-blurs">
    <div class="pricing-blur-ellipse"></div>
    <div class="pricing-radial-glow"></div>
  </div>
  <div class="bg-pricing-grid"></div>
  <div class="container">
    <div class="showcase-head reveal" style="margin-bottom:56px;">
      <div class="sec-label">⬡ Harga Sewa</div>
      <div class="pricing-title-wrap">
        <h2 class="pricing-fg-text">AFFORDABLE</h2>
        <div class="pricing-bg-text">PRICE<span class="pricing-green-dot"></span></div>
      </div>
      <p class="sec-sub">Tidak ada biaya tersembunyi. Harga sama untuk siang maupun malam.</p>
    </div>
    <div class="pricing-grid">
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
  <div class="bg-deco-noise"></div>
  <div class="values-glows">
    <div class="v-glow glow-1"></div>
    <div class="v-glow glow-2"></div>
    <div class="v-glow glow-3"></div>
  </div>
  <div class="container">
    <div style="text-align:center;" class="reveal">
      <div class="sec-label" style="letter-spacing: 5px; font-size: 0.7rem; margin-bottom: 6px;">DNA KAMI</div>
      <h2 class="sec-title" style="margin-bottom: 48px; font-family: 'Anton', sans-serif; font-size: clamp(2.4rem, 5.5vw, 4.5rem); font-weight: 400; color: var(--white); line-height: 1.1; text-transform: uppercase; letter-spacing: 6px; word-spacing: 12px;">THE CHAMPION</h2>
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

<!-- ── GREEN-THEMED AWARDS MARQUEE DENGAN KONTEN INFORMATIF TENTANG ARENA ── -->
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
      <h2 class="sec-title" style="margin-bottom: 24px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: clamp(2.2rem, 5vw, 3.8rem); font-weight: 800; letter-spacing: 1px; text-align: center; color: var(--white); line-height: 1.2; text-transform: none !important;">
        Book Lapangan<br>Sekarang
      </h2>
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
const ballVertS = `
  varying vec3 vNormal;varying vec3 vPos;varying vec3 vWorldPos;
  void main(){
    vNormal=normalize(normalMatrix*normal);
    vPos=position;
    vWorldPos=(modelMatrix*vec4(position,1.)).xyz;
    gl_Position=projectionMatrix*modelViewMatrix*vec4(position,1.);
  }`;
    
const ballFragS = `
  uniform float uTime;
  varying vec3 vNormal;
  varying vec3 vPos;
  varying vec3 vWorldPos;

  // 3D hash for noise
  float hash3(vec3 p) {
    p = fract(p * vec3(127.1, 311.7, 74.7));
    return fract(sin(dot(p, vec3(12.9898, 78.233, 37.719))) * 43758.5453);
  }

  // 3D value noise
  float noise3(vec3 p) {
    vec3 i = floor(p);
    vec3 f = fract(p);
    vec3 u = f * f * (3.0 - 2.0 * f);
    return mix(
      mix(
        mix(hash3(i + vec3(0.0,0.0,0.0)), hash3(i + vec3(1.0,0.0,0.0)), u.x),
        mix(hash3(i + vec3(0.0,1.0,0.0)), hash3(i + vec3(1.0,1.0,0.0)), u.x),
        u.y
      ),
      mix(
        mix(hash3(i + vec3(0.0,0.0,1.0)), hash3(i + vec3(1.0,0.0,1.0)), u.x),
        mix(hash3(i + vec3(0.0,1.0,1.0)), hash3(i + vec3(1.0,1.0,1.0)), u.x),
        u.y
      ),
      u.z
    );
  }

  // Generates curved panel lines/grooves (FIFA star/curved geometry style)
  float getSoccerSeams(vec3 p) {
    float val1 = abs(sin(p.x * 3.14 + sin(p.y * 2.0)) * cos(p.z * 3.14));
    float val2 = abs(cos(p.y * 3.14 + sin(p.z * 2.0)) * sin(p.x * 3.14));
    float val3 = abs(sin(p.z * 3.14 + sin(p.x * 2.0)) * cos(p.y * 3.14));
    
    float combined = val1 * val2 + val2 * val3 + val3 * val1;
    float seam = smoothstep(0.09, 0.04, combined);
    
    float micro = smoothstep(0.03, 0.0, abs(sin(p.x * 8.0) * sin(p.y * 8.0) * sin(p.z * 8.0)) - 0.02);
    return clamp(seam + micro * 0.4, 0.0, 1.0);
  }

  // Modern FIFA sweeping graphics (neon green, dark teal, and black)
  vec3 getGraphics(vec3 p) {
    float angle = atan(p.y, p.x) + p.z * 1.5;
    float sweep1 = smoothstep(0.12, 0.0, abs(sin(angle * 2.0) - 0.35));
    float sweep2 = smoothstep(0.07, 0.0, abs(sin(angle * 2.0 + 1.5) - 0.65));
    
    vec3 neonGreen = vec3(0.0, 1.0, 0.53);
    vec3 darkTeal = vec3(0.0, 0.35, 0.45);
    
    return mix(vec3(0.0), neonGreen * sweep1 + darkTeal * sweep2, clamp(sweep1 + sweep2, 0.0, 1.0));
  }

  void main() {
    vec3 N = normalize(vNormal);
    vec3 pos = normalize(vPos);

    // 1. High-Frequency Pebbled Leather surface (noise-based bump)
    float pebbleCoord = 800.0;
    float pebble = noise3(pos * pebbleCoord);
    
    pebble += 0.5 * noise3(pos * (pebbleCoord * 2.0));
    pebble += 0.25 * noise3(pos * (pebbleCoord * 4.0));
    pebble = pebble / 1.75;
    
    vec3 perturbedN = normalize(N + (pebble - 0.5) * 0.07 * N);

    // 2. Seams mask (grooves)
    float seams = getSoccerSeams(pos);

    // 3. Base Leather color (Pearl White)
    vec3 baseLeather = vec3(0.96, 0.95, 0.92);

    // 4. FIFA sweeping graphic stripes
    vec3 graphics = getGraphics(pos);
    
    vec3 color = mix(baseLeather, graphics, clamp(length(graphics) * 0.85, 0.0, 1.0));
    color = mix(color, vec3(0.15, 0.15, 0.15), seams);

    // 5. Lighting calculations
    vec3 viewDir = normalize(-vWorldPos + vec3(0.0, 0.0, 10.0));
    vec3 lightDir = normalize(vec3(3.0, 5.0, 6.0));
    vec3 halfDir = normalize(lightDir + viewDir);

    float diff = max(dot(perturbedN, lightDir), 0.0);
    float spec = pow(max(dot(perturbedN, halfDir), 0.0), 55.0) * 0.6;
    float rim = pow(1.0 - max(dot(N, viewDir), 0.0), 3.0) * 0.4;

    vec3 finalColor = color * 0.22                           // Ambient
                    + color * vec3(1.0, 0.98, 0.95) * diff * 0.95 // Diffuse
                    + vec3(1.0) * spec                           // Specular
                    + vec3(0.0, 1.0, 0.55) * rim;                // Branding green rim glow

    finalColor = pow(finalColor, vec3(0.9));
    gl_FragColor = vec4(finalColor, 1.0);
  }
`;

// --- SINKRONISASI PROGRESS SCROLL GSAP KE THREEJS ---
let heroScrollProgress = 0;
let targetHeroScrollProgress = 0;

gsap.registerPlugin(ScrollTrigger);

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

document.querySelectorAll('.sc-card').forEach(card => {
  card.addEventListener('mousemove', e => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    card.style.setProperty('--mx', `${(x / rect.width) * 100}%`);
    card.style.setProperty('--my', `${(y / rect.height) * 100}%`);
    
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const rotateX = ((y - centerY) / centerY) * -8;
    const rotateY = ((x - centerX) / centerX) * 8;
    
    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
    card.style.transition = 'none';
  });
  
  card.addEventListener('mouseleave', () => {
    card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
    card.style.transition = 'transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), border-color 0.3s ease, box-shadow 0.4s ease';
  });
});

/* GALLERY LIGHTBOX LOGIC */
const lb=document.getElementById('lb'),lbImg=document.getElementById('lb-img');
document.querySelectorAll('.g-item').forEach(item=>{
  item.addEventListener('click',()=>{lbImg.src=item.dataset.full;lb.classList.add('open');});
});
document.getElementById('lb-close').onclick=()=>lb.classList.remove('open');
lb.addEventListener('click',e=>{if(e.target===lb)lb.classList.remove('open');});

const co=new IntersectionObserver(es=>es.forEach(e=>{
  if(!e.isIntersecting)return;
  const el=e.target,target=+el.dataset.target,suffix=el.dataset.suffix||'';
  const startVal=el.dataset.start ? +el.dataset.start : 0;
  let v=startVal;
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
      
      vec3 col1 = vec3(0.0, w1 * 0.5, w1 * 0.15);
      vec3 col2 = vec3(w2 * 0.15, w2 * 0.95, w2 * 0.3);
      vec3 col3 = vec3(0.0, w3 * 0.7, w3 * 0.5);
      
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

  const rendererS = new THREE.WebGLRenderer({ canvas: canvasScroll, antialias: true, opacity: true, alpha: true });
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

  const ballSGeo = new THREE.SphereGeometry(1.6, 64, 64);
  const ballSMat = new THREE.ShaderMaterial({ vertexShader: ballVertS, fragmentShader: ballFragS, uniforms: { uTime: { value: 0 } } });
  const scrollBall = new THREE.Mesh(ballSGeo, ballSMat);
  
  scrollBall.position.set(0, 0, 0);
  scrollBall.scale.setScalar(1.0);
  sceneS.add(scrollBall);

  sceneS.add(new THREE.AmbientLight(0x001a0d, 2.5));
  const dlS = new THREE.DirectionalLight(0x00ff88, 1.8);
  dlS.position.set(3, 5, 6);
  sceneS.add(dlS);

  // ── INTEGRASI INTERAKSI DRAG BOLA SECARA INTERAKTIF ──
  let isDragging = false;
  let previousMousePosition = { x: 0, y: 0 };
  let targetRotation = { x: 0, y: 0 };

  let currentRotation = { x: 0, y: 0, z: 0 };
  let lastRx = 0;
  let lastRy = 0;
  let lastRz = 0;
  let targetRotX = 0;
  let targetRotY = 0;
  let targetRotZ = 0;

  function lerpAngle(current, target, speed) {
    let diff = target - current;
    diff = Math.atan2(Math.sin(diff), Math.cos(diff));
    return current + diff * speed;
  }

  const containerShowcase = document.querySelector('.premium-showcase-container');

  containerShowcase.addEventListener('mousedown', e => {
    e.preventDefault();
    isDragging = true;
    previousMousePosition = { x: e.clientX, y: e.clientY };
    targetRotation.x = scrollBall.rotation.x;
    targetRotation.y = scrollBall.rotation.y;
  });

  window.addEventListener('mousemove', e => {
    if (!isDragging) return;
    const deltaMove = {
      x: e.clientX - previousMousePosition.x,
      y: e.clientY - previousMousePosition.y
    };
    
    targetRotation.y += deltaMove.x * 0.005;
    targetRotation.x += deltaMove.y * 0.005;
    previousMousePosition = { x: e.clientX, y: e.clientY };
  });

  window.addEventListener('mouseup', () => { isDragging = false; });

  containerShowcase.addEventListener('touchstart', e => {
    if (e.touches.length > 0) {
      e.preventDefault();
      isDragging = true;
      previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
      targetRotation.x = scrollBall.rotation.x;
      targetRotation.y = scrollBall.rotation.y;
    }
  }, { passive: false });

  window.addEventListener('touchmove', e => {
    if (!isDragging || e.touches.length === 0) return;
    const deltaMove = {
      x: e.touches[0].clientX - previousMousePosition.x,
      y: e.touches[0].clientY - previousMousePosition.y
    };
    
    targetRotation.y += deltaMove.x * 0.005;
    targetRotation.x += deltaMove.y * 0.005;
    previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
  }, { passive: true });

  window.addEventListener('touchend', () => { isDragging = false; });

  // Status variabel pergerakan fisik bola
  const ballState = {
    x: 0,
    y: 0.8,
    scale: window.innerWidth <= 768 ? 0.08 : 0.15,
    opacity: 1,
    rx: 0,
    ry: 0,
    rz: 0
  };

  let isMobile = window.innerWidth <= 768;
  let responsiveScale = {
    hero: isMobile ? 0.08 : 0.15,
    sec2: isMobile ? 0.6 : 0.95,
    sec3: isMobile ? 1.2 : 3.2,
    sec4: isMobile ? 1.2 : 3.2
  };

  let targetX = 3.5;
  function updateTargetX() {
    isMobile = window.innerWidth <= 768;
    responsiveScale.hero = isMobile ? 0.08 : 0.15;
    responsiveScale.sec2 = isMobile ? 0.6 : 0.95;
    responsiveScale.sec3 = isMobile ? 1.2 : 3.2;
    responsiveScale.sec4 = isMobile ? 1.2 : 3.2;

    const aspect = window.innerWidth / window.innerHeight;
    const visibleWidth = 2.0 * Math.tan((50 * Math.PI) / 360) * 10 * aspect;
    
    if (isMobile) {
      targetX = (visibleWidth / 2) - 0.5;
    } else {
      targetX = (visibleWidth / 2) - 1.2;
    }
  }
  
  window.addEventListener('resize', () => {
    updateTargetX();
    ScrollTrigger.refresh();
  });
  updateTargetX();

  gsap.set(["#premium-border-frame", "#premium-pitch-bg"], { opacity: 0 });
  gsap.set(".bst-curved-bg", { clipPath: "inset(0% 0% 0% 100%)" });

  // Mengatur seksi absolut dalam tumpukan vertikal reel kontinu (Reel)
  gsap.set(".premium-scroll-sec", { position: "absolute", inset: 0, opacity: 1, autoAlpha: 1 });
  gsap.set("#scroll-sec-hero", { yPercent: 0 });
  gsap.set("#scroll-sec-1", { yPercent: 100 });
  gsap.set("#scroll-sec-2", { yPercent: 200 });
  gsap.set("#scroll-sec-3", { yPercent: 300 });

  // Inisialisasi awal koordinat teks specs
  gsap.set("#bst-left", { opacity: 0, yPercent: -50, y: 35 });
  gsap.set("#bst-right", { opacity: 0, yPercent: -50, y: 35 });

  // Timeline GSAP Pinning dengan transisi sliding vertikal bertumpuk
  const ballScrollTl = gsap.timeline({
    scrollTrigger: {
      trigger: ".premium-showcase-container",
      start: "top top",
      end: "+=800%",
      scrub: 3.0,
      pin: true,
      anticipatePin: 1,
      onUpdate: (self) => {
        // Hubungkan scroll progress awal (0% s/d 25%) langsung ke kemiringan / fade stadium Hero Section
        targetHeroScrollProgress = Math.min(1.0, self.progress * 4.0);
      }
    }
  });

  ballScrollTl
    // ── JEDA DIAM AWAL: Hero tampil, bola diam melayang ──
    .to({}, { duration: 1.0 })

    // Sembunyikan scroll indicator smooth saat mulai scroll
    .to(".scroll-ind.center", { opacity: 0, y: 12, duration: 0.4, ease: "power2.in" }, "<0.2")

    // ── [1] HERO → SECTION 2 (MINI FUT) ──
    // Geser seluruh tumpukan seksi ke atas secara sinkron (Reel Effect)
    .to("#scroll-sec-hero", { yPercent: -100, duration: 2.0, ease: "power2.inOut" })
    .to("#scroll-sec-1", { yPercent: 0, duration: 2.0, ease: "power2.inOut" }, "<")
    .to("#scroll-sec-2", { yPercent: 100, duration: 2.0, ease: "power2.inOut" }, "<")
    .to("#scroll-sec-3", { yPercent: 200, duration: 2.0, ease: "power2.inOut" }, "<")
    // Border frame & pitch lines muncul organik
    .to(["#premium-border-frame", "#premium-pitch-bg"], { opacity: 1, duration: 1.8, ease: "sine.out" }, "<0.2")
    // Bola menggelinding masuk ke tengah, membesar alami (tepat 1 putaran maju)
    .to(ballState, {
      x: 0,
      y: 0,
      scale: () => responsiveScale.sec2,
      rx: 2.0 * Math.PI,
      duration: 2.0,
      ease: "power2.out"
    }, "<0.1")

    // JEDA DIAM DI SECTION 2
    .to({}, { duration: 1.8 })

    // ── [2] SECTION 2 → SECTION 3 (RUMPUT PREMIUM) ──
    // Geser reel seksi berikutnya ke atas
    .to("#scroll-sec-hero", { yPercent: -200, duration: 2.0, ease: "power2.inOut" })
    .to("#scroll-sec-1", { yPercent: -100, duration: 2.0, ease: "power2.inOut" }, "<")
    .to("#scroll-sec-2", { yPercent: 0, duration: 2.0, ease: "power2.inOut" }, "<")
    .to("#scroll-sec-3", { yPercent: 100, duration: 2.0, ease: "power2.inOut" }, "<")
    // Bola menggelinding secara horizontal ke sisi kanan (tepat 1 putaran ke kanan)
    .to(ballState, {
      x: () => targetX,
      y: 0,
      scale: () => responsiveScale.sec3,
      rx: 2.5 * Math.PI,
      rz: -2.0 * Math.PI,
      duration: 2.0,
      ease: "power2.inOut"
    }, "<")
    // Tampilkan teks spesifikasi kiri
    .to("#bst-left", { opacity: 1, y: 0, yPercent: -50, duration: 1.0, ease: "power2.out" }, "-=0.4")

    // JEDA DIAM DI SECTION 3
    .to({}, { duration: 2.0 })

    // ── [3] SECTION 3 → SECTION 4 (BOLA PREMIUM) ──
    // Sembunyikan teks kiri secara halus
    .to("#bst-left", { opacity: 0, y: -20, yPercent: -50, duration: 0.8, ease: "power2.in" })
    // Geser reel seksi terakhir ke atas
    .to("#scroll-sec-hero", { yPercent: -300, duration: 2.0, ease: "power2.inOut" }, "<0.2")
    .to("#scroll-sec-1", { yPercent: -200, duration: 2.0, ease: "power2.inOut" }, "<")
    .to("#scroll-sec-2", { yPercent: -100, duration: 2.0, ease: "power2.inOut" }, "<")
    .to("#scroll-sec-3", { yPercent: 0, duration: 2.0, ease: "power2.inOut" }, "<")
    // Bola menggelinding melintasi layar horizontal dari kanan ke kiri (tepat 1 putaran balik ke kiri)
    .to(ballState, {
      x: () => -targetX,
      y: 0,
      scale: () => responsiveScale.sec4,
      rx: 3.0 * Math.PI,
      rz: 0,
      duration: 2.0,
      ease: "power2.inOut"
    }, "<")
    // Animasi garis putus-putus muncul dari kanan ke kiri mengikuti transisi bola
    .to(".bst-curved-bg", {
      clipPath: "inset(0% 0% 0% 0%)",
      duration: 2.0,
      ease: "power2.inOut"
    }, "<")
    // Tampilkan teks spesifikasi kanan
    .to("#bst-right", { opacity: 1, y: 0, yPercent: -50, duration: 1.0, ease: "power2.out" }, "-=0.5")

    // JEDA DIAM DI SECTION 4
    .to({}, { duration: 2.2 })

    // ── [4] EXIT OUTRO ──
    .to("#bst-right", { opacity: 0, y: -30, yPercent: -50, duration: 1.0, ease: "power2.in" })
    .to("#scroll-sec-3", { yPercent: -100, duration: 1.5, ease: "power2.in" }, "<0.2")
    .to(["#premium-border-frame", "#premium-pitch-bg"], { opacity: 0, duration: 1.5, ease: "power2.in" }, "<")
    // Bola menggelinding keluar ke arah bawah panggung halaman secara perlahan
    .to(ballState, {
      opacity: 0,
      scale: () => isMobile ? 1.8 : 2.2,
      y: () => isMobile ? -4.5 : -5.5,
      rx: 5.0 * Math.PI,
      rz: -1.0 * Math.PI,
      duration: 1.4,
      ease: "power2.in"
    }, "<");

  // Loop Render Animasi
  let timeS = 0;
  let rotVelX = 0, rotVelY = 0, rotVelZ = 0;

  function renderScrollBall() {
    timeS += 0.01;

    let bounceY = 0;
    if (targetHeroScrollProgress < 0.85) {
      let bounceAmp = Math.max(0, 1.0 - targetHeroScrollProgress * 1.8);
      bounceY = (Math.sin(timeS * 2.2) * 0.14 + Math.sin(timeS * 3.7) * 0.04) * bounceAmp;
    }

    let deltaRx = ballState.rx - lastRx;
    let deltaRy = ballState.ry - lastRy;
    let deltaRz = ballState.rz - lastRz;

    lastRx = ballState.rx;
    lastRy = ballState.ry;
    lastRz = ballState.rz;

    rotVelX = rotVelX * 0.82 + deltaRx * 0.18;
    rotVelZ = rotVelZ * 0.82 + deltaRz * 0.18;

    if (!isDragging) {
      targetRotX += rotVelX;
      targetRotY += deltaRy;
      targetRotZ += rotVelZ;

      let isIdle = Math.abs(deltaRx) < 0.0001 && Math.abs(deltaRz) < 0.0001;
      if (isIdle) {
        targetRotX += 0.007 + Math.sin(timeS * 0.3) * 0.002;
        targetRotY += 0.005 + Math.cos(timeS * 0.2) * 0.002;
      }

      let lerpSpeed = isIdle ? 0.10 : 0.16;
      currentRotation.x = lerpAngle(currentRotation.x, targetRotX, lerpSpeed);
      currentRotation.y = lerpAngle(currentRotation.y, targetRotY, lerpSpeed);
      currentRotation.z = lerpAngle(currentRotation.z, targetRotZ, lerpSpeed);

      scrollBall.rotation.x = currentRotation.x;
      scrollBall.rotation.y = currentRotation.y;
      scrollBall.rotation.z = currentRotation.z;
    } else {
      scrollBall.rotation.x += (targetRotation.x - scrollBall.rotation.x) * 0.055;
      scrollBall.rotation.y += (targetRotation.y - scrollBall.rotation.y) * 0.055;

      currentRotation.x = scrollBall.rotation.x;
      currentRotation.y = scrollBall.rotation.y;
      currentRotation.z = scrollBall.rotation.z;
      targetRotX = scrollBall.rotation.x;
      targetRotY = scrollBall.rotation.y;
      targetRotZ = scrollBall.rotation.z;
    }

    let smoothX = scrollBall.position.x + (ballState.x - scrollBall.position.x) * 0.12;
    let targetPosY = ballState.y + bounceY;
    let smoothY = scrollBall.position.y + (targetPosY - scrollBall.position.y) * 0.12;

    scrollBall.position.x = smoothX;
    scrollBall.position.y = smoothY;
    scrollBall.scale.setScalar(ballState.scale);
    canvasScroll.style.opacity = ballState.opacity;

    ballSMat.uniforms.uTime.value = timeS;
    rendererS.render(sceneS, cameraS);
    requestAnimationFrame(renderScrollBall);
  }
  renderScrollBall();
})();
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
      <feTurbulence
        type="fractalNoise"
        baseFrequency="0.05 0.05"
        numOctaves="1"
        seed="1"
        result="turbulence"
      />
      <feGaussianBlur in="turbulence" stdDeviation="2" result="blurredNoise" />
      <feDisplacementMap
        in="SourceGraphic"
        in2="blurredNoise"
        scale="70"
        xChannelSelector="R"
        yChannelSelector="B"
        result="displaced"
      />
      <feGaussianBlur in="displaced" stdDeviation="4" result="finalBlur" />
      <feComposite in="finalBlur" in2="finalBlur" operator="over" />
    </filter>
  </defs>
</svg>
</body>
</html>