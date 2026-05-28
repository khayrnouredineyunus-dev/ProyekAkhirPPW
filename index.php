<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MiniFut — Premium Mini Soccer Arena</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&family=Barlow:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>

:root {
  --black:   #060608; --dark:    #0c0d10; --card:    #111318; --card2:   #161820;
  --border:  rgba(255,255,255,0.06); --border2: rgba(255,255,255,0.1);
  --green:   #00ff88; --green2:  #00e676; --glow:    rgba(0,255,136,0.18); --glow-sm: rgba(0,255,136,0.07);
  --gray:    #6b7080; --gray2:   #9aa0b0; --white:   #eceef2; --red:     #ff3b5c;
}
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{background:var(--black);color:var(--white);font-family:'Barlow',sans-serif;cursor:none;overflow-x:hidden;min-height:100vh;}

/* Kursor Buatan */
#cur{position:fixed;width:10px;height:10px;background:var(--green);border-radius:50%;pointer-events:none;z-index:99999;}
#cur-r{position:fixed;width:34px;height:34px;border:1px solid var(--green);border-radius:50%;pointer-events:none;z-index:99998;opacity:.45;}
body:hover #cur{opacity:1;}

/* MATIKAN KURSOR KUSTOM DI HP / LAYAR SENTUH */
@media (hover: none) and (pointer: coarse) {
  body, a, button, .f-card, .showcase-item, .g-item, .nav-cta, .btn-p, .btn-s, #lb-close, .val-card, .award-item { cursor: auto !important; }
  #cur, #cur-r { display: none !important; }
}

#noise{position:fixed;inset:0;opacity:.018;pointer-events:none;z-index:8000;
  background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");}
nav{position:fixed;top:0;left:0;right:0;z-index:1000;padding:22px 64px;display:flex;align-items:center;justify-content:space-between;background:transparent;transition:all .4s;}
nav.stuck{background:rgba(6,6,8,.92);backdrop-filter:blur(24px);padding:14px 64px;border-bottom:1px solid rgba(0,255,136,.08);}
.logo{font-family:'Orbitron',monospace;font-size:1.65rem;font-weight:900;color:var(--green);letter-spacing:5px;text-decoration:none;}
.logo em{color:var(--white);font-style:normal;}
.logo:hover{text-shadow:0 0 10px var(--glow);}
.nav-links{display:flex;gap:44px;list-style:none;}
.nav-links a{font-family:'Rajdhani',sans-serif;font-size:.78rem;font-weight:600;letter-spacing:2.5px;text-transform:uppercase;color:var(--gray2);text-decoration:none;transition:color .25s;}
.nav-links a:hover{color:var(--green);}
.nav-cta{font-family:'Rajdhani',sans-serif;font-size:.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--black);background:var(--green);border:none;padding:12px 30px;cursor:none;text-decoration:none;clip-path:polygon(9px 0%,100% 0%,calc(100% - 9px) 100%,0 100%);transition:all .3s;position:relative;overflow:hidden;}
.nav-cta::after{content:'';position:absolute;inset:0;background:rgba(255,255,255,.18);transform:translateX(-100%);transition:transform .3s;}
.nav-cta:hover::after{transform:translateX(0);}

@media (max-width: 768px) {
  nav { padding: 15px 24px; }
  .nav-links { display: none; }
  .nav-cta { padding: 10px 20px; font-size: 0.7rem; }
}

.container{max-width:1200px;margin:0 auto;padding:0 64px;}
@media (max-width: 768px) { .container{padding:0 24px;} }
.sec-label{font-family:'Rajdhani',sans-serif;font-size:.68rem;font-weight:700;letter-spacing:5px;text-transform:uppercase;color:var(--green);margin-bottom:10px;}
.sec-title{font-family:'Orbitron',monospace;font-size:clamp(1.8rem,3.8vw,3rem);font-weight:700;line-height:1.08;color:var(--white);}
.sec-sub{font-family:'Barlow',sans-serif;font-size:.95rem;color:var(--gray2);margin-top:12px;line-height:1.75;}

/* REVEAL DIAMBIL ALIH OLEH GSAP, SISA OPACITY 0 */
.reveal{opacity:0;}

/* CSS UNTUK 3D CANVAS BACKGROUND */
.hero{position:relative;height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;}
#three-canvas{position:absolute;inset:0;width:100%!important;height:100%!important;z-index:0;opacity:.55;}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(6,6,8,.97) 0%,rgba(6,6,8,.65) 60%,rgba(6,6,8,.85) 100%);z-index:1;}
.hero-grid{position:absolute;inset:0;z-index:1;background-image:linear-gradient(rgba(0,255,136,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(0,255,136,.03) 1px,transparent 1px);background-size:56px 56px;animation:gridDrift 25s linear infinite;}
@keyframes gridDrift{0%{transform:translateY(0)}100%{transform:translateY(56px)}}
.scan{position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--green),transparent);opacity:.25;animation:scanDown 5s linear infinite;z-index:2;}
@keyframes scanDown{0%{top:0}100%{top:100%}}
.hero-content{position:relative;z-index:3;text-align:center;max-width:950px;padding:0 40px;}

.hero-eyebrow{font-family:'Rajdhani',sans-serif;font-size:.7rem;font-weight:600;letter-spacing:6px;text-transform:uppercase;color:var(--green);margin-bottom:22px;display:flex;align-items:center;justify-content:center;gap:12px;opacity:0;}
.eyebrow-line{width:40px;height:1px;background:var(--green);}

/* LOGO ANIMASI GRADIENT TEXT (INI TETAP CSS KARENA GRADIENT POSITION) */
.hero-title{font-family:'Orbitron',monospace;font-size:clamp(3.8rem,9vw,7.5rem);font-weight:900;line-height:.9;letter-spacing:-3px;margin-bottom:44px;opacity:0;}
.logo-mini{background:linear-gradient(90deg, var(--green) 0%, #ffffff 50%, var(--green) 100%);background-size:200% auto;-webkit-background-clip:text;-webkit-text-fill-color:transparent;animation:shine 3s linear infinite;}
.logo-fut{background:linear-gradient(90deg, var(--white) 0%, #6b7080 50%, var(--white) 100%);background-size:200% auto;-webkit-background-clip:text;-webkit-text-fill-color:transparent;animation:shine 3s linear infinite;}
@keyframes shine{to{background-position:200% center;}}

/* TOMBOL HERO */
.hero-btns { display:flex; gap:0; justify-content:center; margin-top:40px; opacity:0;}
.hero-btns .btn-p { clip-path:polygon(0 0, 100% 0, calc(100% - 18px) 100%, 0 100%); padding-right:52px; }
.hero-btns .btn-s { 
  clip-path:polygon(18px 0, 100% 0, 100% 100%, 0 100%); 
  padding-left:52px; 
  margin-left:-4px; 
  border-left:none; 
  position:relative; 
}
.hero-btns .btn-s::before { 
  content:''; position:absolute; top:-1px; left:0; bottom:-1px; width:20px; 
  background:rgba(0,255,136,.35); 
  clip-path:polygon(18px 0, 19.5px 0, 1.5px 100%, 0 100%); 
  transition:background .3s; z-index:1; 
}
.hero-btns .btn-s:hover::before { background:var(--green); }

.btn-p{font-family:'Rajdhani',sans-serif;font-size:.88rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--black);background:var(--green);border:none;padding:17px 44px;cursor:none;text-decoration:none;clip-path:polygon(10px 0%,100% 0%,calc(100% - 10px) 100%,0 100%);transition:all .3s;position:relative;overflow:hidden;}
.btn-p::after{content:'';position:absolute;inset:0;background:rgba(255,255,255,.18);transform:translateX(-100%);transition:.3s;}
.btn-p:hover::after{transform:translateX(0);}
.btn-s{font-family:'Rajdhani',sans-serif;font-size:.88rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--green);background:transparent;border:1px solid rgba(0,255,136,.35);padding:17px 44px;cursor:none;text-decoration:none;transition:all .3s;}
.btn-s:hover{border-color:var(--green);background:var(--glow-sm);}

/* REVISI UKURAN TOMBOL DI MOBILE PHONE */
@media (max-width: 768px) {
  .hero-btns { margin-top: 24px; }
  .btn-p, .btn-s { padding: 12px 24px; font-size: 0.75rem; }
  .hero-btns .btn-p { padding-right: 36px; clip-path:polygon(0 0, 100% 0, calc(100% - 14px) 100%, 0 100%); }
  .hero-btns .btn-s { padding-left: 36px; clip-path:polygon(14px 0, 100% 0, 100% 100%, 0 100%); margin-left: -4px; }
  .hero-btns .btn-s::before { width: 16px; clip-path:polygon(14px 0, 15.5px 0, 1.5px 100%, 0 100%); }
}

.ticker-wrap{background:var(--green);padding:11px 0;overflow:hidden;}

/* DIKEMBALIKAN KE CSS ANIMATION AGAR TICKER TIDAK GLITCH */
.ticker{display:flex;gap:0;white-space:nowrap;animation:tickerMove 22s linear infinite; will-change: transform;}
.ticker-wrap:hover .ticker {animation-play-state: paused;}
@keyframes tickerMove{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}

.ticker-item{font-family:'Rajdhani',sans-serif;font-size:.72rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--black);padding:0 30px;display:flex;align-items:center;gap:14px;}
.ticker-item::after{content:'⬡';}

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
.f-tag{font-family:'Rajdhani',sans-serif;font-size:.65rem;letter-spacing:1px;color:var(--gray2);background:rgba(255,255,255,.04);border:1px solid var(--border);padding:3px 9px;}
.f-price-row{display:flex;justify-content:space-between;align-items:center;}
.f-price-val{font-family:'Orbitron',monospace;font-size:1.2rem;font-weight:700;color:var(--green);}
.f-price-unit{font-family:'Rajdhani',sans-serif;font-size:.72rem;color:var(--gray);margin-left:4px;}
.f-book-btn{font-family:'Rajdhani',sans-serif;font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--green);background:transparent;border:1px solid rgba(0,255,136,.35);padding:8px 18px;cursor:none;text-decoration:none;transition:all .3s;}
.f-book-btn:hover{background:var(--green);color:var(--black);}

/* ── GALLERY ── */
.gallery-sec{padding:100px 0;background:var(--dark);}
.gallery-head{text-align:center;margin-bottom:52px;}
.gallery-mosaic{display:grid;grid-template-columns:repeat(12,1fr);grid-template-rows:220px 220px;gap:3px;max-width:1200px;margin:0 auto;padding:0 64px;}
.g-item{position:relative;overflow:hidden;cursor:none;}
.g-item:nth-child(1){grid-column:1/6;grid-row:1/2;}
.g-item:nth-child(2){grid-column:6/9;grid-row:1/2;}
.g-item:nth-child(3){grid-column:9/13;grid-row:1/3;}
.g-item:nth-child(4){grid-column:1/4;grid-row:2/3;}
.g-item:nth-child(5){grid-column:4/9;grid-row:2/3;}

/* EFEK GELAP GALERI */
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
.pricing-sec{padding:120px 0;position:relative;}
.pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:3px;}
.p-card{padding:40px;background:var(--card);border:1px solid var(--border);position:relative;cursor:none;transition:transform .35s,border-color .3s;}
.p-card:hover{transform:translateY(-5px);border-color:rgba(0,255,136,.3);}
.p-card.featured{background:rgba(0,255,136,.045);border-color:rgba(0,255,136,.25);}
.p-card.featured::before{content:'TERPOPULER';position:absolute;top:0;left:50%;transform:translate(-50%,-50%);font-family:'Rajdhani',sans-serif;font-size:.58rem;font-weight:700;letter-spacing:3px;color:var(--black);background:var(--green);padding:4px 16px;white-space:nowrap;}
.p-tag{font-family:'Rajdhani',sans-serif;font-size:.68rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:var(--green);margin-bottom:6px;}
.p-name{font-family:'Orbitron',monospace;font-size:1.1rem;font-weight:700;color:var(--white);margin-bottom:28px;}
.p-price{display:flex;align-items:baseline;gap:3px;margin-bottom:6px;}
.p-cur{font-family:'Rajdhani',sans-serif;font-size:.9rem;color:var(--gray);}
.p-amount{font-family:'Orbitron',monospace;font-size:2.3rem;font-weight:700;color:var(--green);}
.p-per{font-family:'Rajdhani',sans-serif;font-size:.78rem;color:var(--gray);}
.p-div{height:1px;background:var(--border);margin:24px 0;}
.p-feats{list-style:none;display:flex;flex-direction:column;gap:11px;margin-bottom:28px;}
.p-feats li{font-family:'Barlow',sans-serif;font-size:.84rem;color:var(--gray2);display:flex;align-items:center;gap:10px;}
.p-feats li::before{content:'✦';color:var(--green);font-size:.58rem;flex-shrink:0;}
.p-btn{font-family:'Rajdhani',sans-serif;font-size:.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;display:block;text-align:center;text-decoration:none;padding:13px;border:1px solid rgba(0,255,136,.4);color:var(--green);cursor:none;transition:all .3s;}
.p-btn:hover,.p-btn.solid:hover{opacity:.88;}
.p-btn.solid{background:var(--green);color:var(--black);border-color:var(--green);}

/* ── VALUES SECTION (DNA KAMI) ── */
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

/* ── AWARDS MARQUEE (DIKEMBALIKAN KE CSS AGAR TIDAK GLITCH) ── */
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

footer{padding:64px 0 28px;border-top:1px solid var(--border);}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:60px;margin-bottom:56px;}
@media(max-width:900px){.footer-grid{grid-template-columns:1fr 1fr;}}
.footer-desc{font-family:'Barlow',sans-serif;font-size:.84rem;color:var(--gray);line-height:1.75;max-width:270px;margin-top:14px;}
.f-title{font-family:'Rajdhani',sans-serif;font-size:.65rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--green);margin-bottom:18px;}
.f-links{list-style:none;display:flex;flex-direction:column;gap:11px;}
.f-links li, .f-links a{font-family:'Barlow',sans-serif;font-size:.83rem;color:var(--gray);text-decoration:none;transition:color .25s;}
.f-links a:hover{color:var(--green);}
.f-links li strong {color:var(--white); font-weight:500;}
.footer-bottom{display:flex;justify-content:space-between;align-items:center;padding-top:28px;border-top:1px solid var(--border);}
.footer-copy{font-family:'Rajdhani',sans-serif;font-size:.7rem;letter-spacing:1px;color:var(--gray);}
::-webkit-scrollbar{width:4px;height:4px;}
::-webkit-scrollbar-thumb{background:rgba(0,255,136,.15);}

/* ── NEW CSS FOR STATS, SCROLL & GLOWS ── */
.hero-stats{position:absolute;bottom:44px;right:64px;z-index:3;display:flex;flex-direction:column;gap:22px;opacity:0;}
.stat-i{text-align:right;}
.stat-n{font-family:'Orbitron',monospace;font-size:1.6rem;font-weight:700;color:var(--green);}
.stat-l{font-family:'Rajdhani',sans-serif;font-size:.65rem;letter-spacing:2.5px;color:var(--gray);text-transform:uppercase;}

.scroll-ind{position:absolute;bottom:15px;left:50%;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;gap:8px;z-index:3;opacity:0;}
.scroll-ind span{font-family:'Rajdhani',sans-serif;font-size:.65rem;letter-spacing:3px;color:var(--gray);text-transform:uppercase;}
.scroll-bar{width:1px;height:40px;background:linear-gradient(to bottom,var(--green),transparent);animation:scrollPulse 2.2s ease-in-out infinite;}

@keyframes scrollPulse{0%,100%{opacity:.25}50%{opacity:1}}

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

@media (max-width: 768px) { 
  .hero-stats { display: none !important; } 
  .gallery-mosaic { grid-template-columns: 1fr; grid-template-rows: auto; }
  .g-item:nth-child(n) { grid-column: 1 / -1 !important; grid-row: auto !important; height: 250px; }
}

/* --- EFEK 3D TILT & GLARE --- */
.f-glare {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.2) 0%, transparent 60%);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.4s ease;
  z-index: 10;
}
</style>
</head>
<body>
<div id="noise"></div>
<div id="cur"></div>
<div id="cur-r"></div>
<div id="lb"><button id="lb-close">✕</button><img id="lb-img" src="" alt=""></div>

<nav id="nav">
  <a href="#" class="logo">MINI<em>FUT</em></a>
  
  <ul class="nav-links" id="nav-links">
    <li><a href="#lapangan">Lapangan</a></li>
    <li><a href="#galeri">Galeri</a></li>
    <li><a href="#fasilitas">Fasilitas</a></li>
    <li><a href="#harga">Harga</a></li>
    <li><a href="booking.php">Booking</a></li>
  </ul>
  <a href="booking.php" class="nav-cta">Book Sekarang</a>
</nav>

<section class="hero">
  <canvas id="three-canvas"></canvas>
  <div class="hero-overlay"></div>
  <div class="hero-grid"></div>
  <div class="scan"></div>

  <div class="hero-content">
    <div class="hero-eyebrow">
      <span class="eyebrow-line"></span>
      Yogyakarta's Premium Mini Soccer
      <span class="eyebrow-line"></span>
    </div>
    
    <h1 class="hero-title">
      <span class="logo-mini">MINI</span><span class="logo-fut">FUT</span>
    </h1>
    
    <div class="hero-btns">
      <a href="booking.php" class="btn-p">Book Lapangan</a>
      <a href="#lapangan" class="btn-s">Lihat Lapangan ↓</a>
    </div>
  </div>

  <div class="hero-stats">
    <div class="stat-i">
      <div class="stat-n"><span class="counter" data-target="3" data-suffix="">0</span></div>
      <div class="stat-l">Lapangan</div>
    </div>
    <div class="stat-i">
      <div class="stat-n"><span class="counter" data-target="500" data-suffix="+" data-bounce="1000">0</span></div>
      <div class="stat-l">Customer Puas</div>
    </div>
    <div class="stat-i">
      <div class="stat-n">24/7</div>
      <div class="stat-l">Operasional</div>
    </div>
  </div>

  <div class="scroll-ind">
    <span>Scroll</span>
    <div class="scroll-bar"></div>
  </div>
</section>

<div class="ticker-wrap">
  <div class="ticker">
    <span class="ticker-item">Rumput Sintetis Premium</span>
    <span class="ticker-item">Kapasitas 8v8 - 10v10</span>
    <span class="ticker-item">Restaurant & Café</span>
    <span class="ticker-item">Play With Pride</span>
    <span class="ticker-item">Elite Facilities</span>
    <span class="ticker-item">LED Lighting</span>
    <span class="ticker-item">Yogyakarta's Finest</span>
    <span class="ticker-item">Rumput Sintetis Premium</span>
    <span class="ticker-item">Kapasitas 8v8 - 10v10</span>
    <span class="ticker-item">Restaurant & Café</span>
    <span class="ticker-item">Play With Pride</span>
    <span class="ticker-item">Elite Facilities</span>
    <span class="ticker-item">LED Lighting</span>
    <span class="ticker-item">Yogyakarta's Finest</span>
  </div>
</div>

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
          <!-- LAZY LOAD DITAMBAHKAN -->
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
          <!-- LAZY LOAD DITAMBAHKAN -->
          <img src="https://images.unsplash.com/photo-1518604666860-9ed391f76460?w=800&q=80" alt="Lapangan 3" loading="lazy">
          <div class="f-card-img-overlay"></div>
          <div class="f-card-badge-wrap">
            <div class="f-badge">Rumput Sintetis Elite</div>
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
      <h2 class="sec-title">LIHAT SENDIRI<br>ARENANNYA</h2>
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
    <div class="g-item" data-full="tribun.png?w=1400&q=90">
      <img src="tribun.png?w=900&q=80" alt="" loading="lazy">
      <div class="g-overlay"><div class="g-zoom">+</div></div>
      <div class="g-label">Area Tribun</div>
    </div>
    <div class="g-item" data-full="fasilitas.png?w=1400&q=90">
      <img src="fasilitas.png?w=900&q=80" alt="" loading="lazy">
      <div class="g-overlay"><div class="g-zoom">+</div></div>
      <div class="g-label">Fasilitas</div>
    </div>
  </div>
</section>

<section class="showcase-sec" id="fasilitas">
  <div class="container">
    <div class="showcase-head reveal">
      <div class="sec-label">⬡ Fasilitas Pendukung</div>
      <h2 class="sec-title">KENYAMANAN<br>TIM ANDA</h2>
    </div>
    
    <div class="showcase-layout reveal">
      <div class="showcase-list" id="sc-list">
        <div class="showcase-item active" data-img="sc-img-1">
          <div class="sc-title">Parkir Luas & Aman</div>
          <div class="sc-desc">Area parkir kami mampu menampung puluhan kendaraan baik motor maupun mobil. Akses keluar masuk yang mudah dan diawasi.</div>
          <div class="sc-progress-bar"></div>
        </div>
        <div class="showcase-item" data-img="sc-img-2">
          <div class="sc-title">Restaurant & Café</div>
          <div class="sc-desc">Haus setelah bertanding? Pesan minuman dingin atau makanan ringan di area restaurant kami sambil bersantai setelah pertandingan.</div>
          <div class="sc-progress-bar"></div>
        </div>
        <div class="showcase-item" data-img="sc-img-3">
          <div class="sc-title">Ruang Ganti & Kamar Mandi</div>
          <div class="sc-desc">Fasilitas kamar mandi dan ruang ganti yang selalu dijaga kebersihannya, lengkap dan nyaman digunakan.</div>
          <div class="sc-progress-bar"></div>
        </div>
        <div class="showcase-item" data-img="sc-img-4">
          <div class="sc-title">Pencahayaan LED Penuh</div>
          <div class="sc-desc">Bermain malam hari bukan masalah. Lapangan kami dilengkapi dengan lampu sorot LED standar pertandingan untuk visibilitas maksimal.</div>
          <div class="sc-progress-bar"></div>
        </div>
      </div>

      <div class="showcase-visual">
        <img src="parkir.png" alt="Parkir" class="sc-img active" id="sc-img-1" loading="lazy">
        <img src="cafe.png" alt="Cafe" class="sc-img" id="sc-img-2" loading="lazy">
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
      <h2 class="sec-title">TRANSPARAN &<br>TERJANGKAU</h2>
      <p class="sec-sub">Tidak ada biaya tersembunyi. Harga sama untuk siang maupun malam.</p>
    </div>
    <div class="pricing-grid">
      <div class="p-card reveal">
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
      <div class="p-card featured reveal">
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
          <li>Ruang Ganti & Toilet Bersih</li>
          <li>Akses Restaurant & Parkir</li>
        </ul>
        <a href="booking.php" class="p-btn solid">Pilih Lapangan 2</a>
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
        <div class="val-desc">Kemudahan booking, pelayanan yang responsif, dan lingkungan yang nyaman menjadi bagian dari komitmen kami kepada setiap pelanggan.</div>
      </div>
      <div class="val-card reveal">
        <div class="val-num">04</div>
        <div class="val-name">Professional Service</div>
        <div class="val-desc">Kami mengutamakan pelayanan yang profesional, jadwal yang jelas, dan pengalaman booking yang dapat diandalkan.</div>
      </div>
      <div class="val-card reveal">
        <div class="val-num">05</div>
        <div class="val-name">Community & Sportsmanship</div>
        <div class="val-desc">MiniFut menjadi tempat bagi para pemain untuk berkumpul, bertanding, membangun kebersamaan, dan menjunjung sportivitas.</div>
      </div>
      <div class="val-card reveal">
        <div class="val-num">06</div>
        <div class="val-name">Integrity & Transparency</div>
        <div class="val-desc">Harga yang jelas, komunikasi yang terbuka, dan pelayanan yang jujur adalah prinsip yang selalu kami pegang.</div>
      </div>
    </div>
  </div>
</section>

<!-- AWARDS MARQUEE -->
<section class="awards-sec">
  <div class="awards-track">
    <div class="award-item"><div><div class="award-name">Best Sports Facility</div><div class="award-year">Sport Indonesia Awards 2023</div></div></div>
    <div class="award-item"><div><div class="award-name">Top Rated Venue</div><div class="award-year">Google 4.9/5 — 2,400 ulasan</div></div></div>
    <div class="award-item"><div><div class="award-name">Best UX App</div><div class="award-year">Jogja Tech Awards 2024</div></div></div>
    <div class="award-item"><div><div class="award-name">Community Choice</div><div class="award-year">Futsal Indonesia Community 2023</div></div></div>
    <div class="award-item"><div><div class="award-name">Green Facility</div><div class="award-year">Eco Sports Award 2024</div></div></div>
    <div class="award-item"><div><div class="award-name">Premium Venue Partner</div><div class="award-year">FIFA Indonesia 2023</div></div></div>
    <div class="award-item"><div><div class="award-name">Best Sports Facility</div><div class="award-year">Sport Indonesia Awards 2023</div></div></div>
    <div class="award-item"><div><div class="award-name">Top Rated Venue</div><div class="award-year">Google 4.9/5 — 2,400 ulasan</div></div></div>
    <div class="award-item"><div><div class="award-name">Best UX App</div><div class="award-year">Jogja Tech Awards 2024</div></div></div>
    <div class="award-item"><div><div class="award-name">Community Choice</div><div class="award-year">Futsal Indonesia Community 2023</div></div></div>
    <div class="award-item"><div><div class="award-name">Green Facility</div><div class="award-year">Eco Sports Award 2024</div></div></div>
    <div class="award-item"><div><div class="award-name">Premium Venue Partner</div><div class="award-year">FIFA Indonesia 2023</div></div></div>
  </div>
  <div class="awards-track awards-track-rev">
    <div class="award-item"><div><div class="award-name">Official Partner</div><div class="award-year">Persatuan Futsal Indonesia</div></div></div>
    <div class="award-item"><div><div class="award-name">Best Sports App</div><div class="award-year">App Store Indonesia 2024</div></div></div>
    <div class="award-item"><div><div class="award-name">Venue of the Year</div><div class="award-year">Jogja Event Awards 2024</div></div></div>
    <div class="award-item"><div><div class="award-name">Trending Venue</div><div class="award-year">TripAdvisor Indonesia 2023</div></div></div>
    <div class="award-item"><div><div class="award-name">Excellence in Service</div><div class="award-year">Customer Care Award 2024</div></div></div>
    <div class="award-item"><div><div class="award-name">Digital Innovation</div><div class="award-year">Startup Jogja Award 2023</div></div></div>
    <div class="award-item"><div><div class="award-name">Official Partner</div><div class="award-year">Persatuan Futsal Indonesia</div></div></div>
    <div class="award-item"><div><div class="award-name">Best Sports App</div><div class="award-year">App Store Indonesia 2024</div></div></div>
    <div class="award-item"><div><div class="award-name">Venue of the Year</div><div class="award-year">Jogja Event Awards 2024</div></div></div>
    <div class="award-item"><div><div class="award-name">Trending Venue</div><div class="award-year">TripAdvisor Indonesia 2023</div></div></div>
    <div class="award-item"><div><div class="award-name">Excellence in Service</div><div class="award-year">Customer Care Award 2024</div></div></div>
    <div class="award-item"><div><div class="award-name">Digital Innovation</div><div class="award-year">Startup Jogja Award 2023</div></div></div>
  </div>
</section>

<!-- SECTION CTA DENGAN PARTIKEL -->
<section class="cta-sec">
  <div id="particles-js" style="position:absolute; width:100%; height:100%; top:0; left:0; z-index:0;"></div>
  <div class="container" style="position:relative;z-index:1">
    <div class="reveal">
      <div class="sec-label" style="display:flex;justify-content:center">⬡ Siap Bermain?</div>
      <h2 class="cta-title">BOOK LAPANGAN<br><span>SEKARANG</span></h2>
      <p class="cta-sub">Jangan tunda lagi. Atur jadwal pertandinganmu dan nikmati pengalaman<br>mini soccer terbaik di Yogyakarta.</p>
      <a href="booking.php" class="btn-p" style="display:inline-block">Mulai Booking →</a>
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
        <a href="#">Instagram</a>
        <a href="#">WhatsApp</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

<!-- TAMBAHAN LIBRARY GSAP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

<script>
gsap.registerPlugin(ScrollTrigger);

/* --- GSAP FEATURE 4: QUICKTO CUSTOM CURSOR & MAGNETIC EFFECT --- */
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

  document.querySelectorAll('a, button, .f-card, .showcase-item, .g-item, #lb-close, .val-card, .award-item').forEach(el=>{
    el.addEventListener('mouseenter',()=>{gsap.to(cur, {width: 15, height: 15, duration: 0.2}); gsap.to(curR, {width: 50, height: 50, opacity: 0.65, duration: 0.2});});
    el.addEventListener('mouseleave',()=>{gsap.to(cur, {width: 10, height: 10, duration: 0.2}); gsap.to(curR, {width: 34, height: 34, opacity: 0.45, duration: 0.2});});
  });

  // Magnetic Button Effect
  document.querySelectorAll('.btn-p, .btn-s, .nav-cta').forEach(btn => {
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

window.addEventListener('scroll', () => { 
  document.getElementById('nav').classList.toggle('stuck', window.scrollY > 60); 
}, {passive: true});

/* --- GSAP FEATURE 1: HERO ENTRANCE TIMELINE --- */
const heroTl = gsap.timeline();
heroTl.fromTo(".hero-eyebrow", {y: 30, opacity: 0}, {y: 0, opacity: 1, duration: 1, ease: "power3.out"}, 0.3)
      .fromTo(".hero-title", {y: 40, opacity: 0}, {y: 0, opacity: 1, duration: 1, ease: "power3.out"}, 0.5)
      .fromTo(".hero-btns", {y: 30, opacity: 0}, {y: 0, opacity: 1, duration: 1, ease: "power3.out"}, 0.7)
      .fromTo(".hero-stats", {opacity: 0}, {opacity: 1, duration: 1, ease: "power2.out"}, 1)
      .fromTo(".scroll-ind", {y: 20, opacity: 0, xPercent: -50}, {y: 0, opacity: 1, xPercent: -50, duration: 1, ease: "power2.out"}, 1.2);

/* --- GSAP FEATURE 2: SMOOTH SCROLL REVEAL (STAGGER BATCH) --- */
gsap.set(".reveal", { y: 40, opacity: 0 }); // Set titik awal
ScrollTrigger.batch(".reveal", {
  onEnter: batch => {
    gsap.to(batch, { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: "power3.out", overwrite: true });
  },
  once: true,
  start: "top 85%"
});

/* GALLERY, SHOWCASE, STATS COUNTER, & 3D GLARE */

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

/* COUNTERS LOGIC */
const co=new IntersectionObserver(es=>es.forEach(e=>{
  if(!e.isIntersecting)return;
  const el=e.target,target=+el.dataset.target,suffix=el.dataset.suffix||'';
  const bounceMax=el.dataset.bounce ? +el.dataset.bounce : null;
  let v=0;const step=target/(1400/16);
  const t=setInterval(()=>{
    v+=step;
    if(v>=target){
      v=target;
      clearInterval(t);
      if(bounceMax){
        let cur=target;
        setInterval(()=>{
          cur+=2;
          if(cur>=bounceMax){cur=target;}
          el.textContent=Math.floor(cur)+suffix;
        }, 45);
      }
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

  const ballGeo = new THREE.SphereGeometry(0.3, 16, 16);
  const ballMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.4, metalness: 0.1 });
  const ball = new THREE.Mesh(ballGeo, ballMat);
  ball.position.set(0, 1.5, 0);
  scene.add(ball);

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
  document.addEventListener('touchmove', e => { if (e.touches.length > 0) { mouseX = (e.touches[0].clientX / window.innerWidth - 0.5) * 2; mouseY = (e.touches[0].clientY / window.innerHeight - 0.5) * 2; } }, { passive: true });
  document.addEventListener('touchstart', e => { if (e.touches.length > 0) { mouseX = (e.touches[0].clientX / window.innerWidth - 0.5) * 2; mouseY = (e.touches[0].clientY / window.innerHeight - 0.5) * 2; } }, { passive: true });

  let t = 0;
  function animate() {
    t += 0.01;
    ball.position.y = 1.5 + Math.sin(t * 1.4) * 0.6;
    ball.rotation.x += 0.02; ball.rotation.z += 0.015;
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
</script>
</body>
</html>