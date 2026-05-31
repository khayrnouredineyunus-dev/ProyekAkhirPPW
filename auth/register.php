<?php
// auth/register.php — Halaman Registrasi Pelanggan MiniFut
require_once __DIR__ . '/../config.php';
startSecureSession();

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$errors  = [];
$success = false;
$values  = ['nama'=>'','email'=>'','notelp'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $notelp   = trim($_POST['notelp']   ?? '');
    $password = $_POST['password']      ?? '';
    $konfirm  = $_POST['konfirm']       ?? '';
    $values   = compact('nama','email','notelp');

    // ── Validasi ──────────────────────────────────────────
    if (empty($nama))   $errors[] = 'Nama lengkap tidak boleh kosong.';
    elseif (mb_strlen($nama) < 3) $errors[] = 'Nama minimal 3 karakter.';

    if (empty($email))  $errors[] = 'Email tidak boleh kosong.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';

    if (empty($notelp)) $errors[] = 'Nomor telepon tidak boleh kosong.';
    elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $notelp)) $errors[] = 'Nomor telepon tidak valid (8-20 digit).';

    if (empty($password))       $errors[] = 'Password tidak boleh kosong.';
    elseif (mb_strlen($password) < 8) $errors[] = 'Password minimal 8 karakter.';
    elseif (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password harus mengandung minimal 1 huruf kapital.';
    elseif (!preg_match('/[0-9]/', $password)) $errors[] = 'Password harus mengandung minimal 1 angka.';

    if ($password !== $konfirm) $errors[] = 'Konfirmasi password tidak cocok.';

    if (empty($errors)) {
        $pdo  = getDB();
        // Cek email duplikat
        $stmt = $pdo->prepare("SELECT ID_PELANGGAN FROM Pelanggan WHERE U_EMAIL = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar. Silakan login atau gunakan email lain.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO Pelanggan (U_NAMA, U_EMAIL, U_PASSWORD, U_NOTELP) VALUES (?,?,?,?)");
            $stmt->execute([$nama, $email, $hash, $notelp]);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MiniFut — Daftar Akun</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@400;500;600;700&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
  --black:#060608; --dark:#0c0d10; --card:#111318;
  --border:rgba(255,255,255,0.06); --border2:rgba(255,255,255,0.1);
  --green:#00ff88; --glow:rgba(0,255,136,0.18);
  --gray:#6b7080; --gray2:#9aa0b0; --white:#eceef2; --red:#ff3b5c;
}
*{margin:0;padding:0;box-sizing:border-box;}
html,body{font-family:'Barlow',sans-serif;background:var(--black);color:var(--white);}
#shader-bg{position:fixed;inset:0;z-index:0;opacity:.45;}
#shader-bg canvas{display:block;width:100%;height:100%;}
.wrap {
  position:relative;z-index:2;
  display:flex;align-items:center;justify-content:center;
  min-height:100vh;padding:32px 24px;
}
.card {
  width:100%;max-width:460px;
  background:var(--card);border:1px solid var(--border2);
  border-radius:20px;padding:44px 40px 40px;
  position:relative;box-shadow:0 32px 80px rgba(0,0,0,.6);
  animation:slideUp .5s cubic-bezier(.22,1,.36,1) both;
}
@keyframes slideUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}
.card::before {
  content:'';position:absolute;top:0;left:12%;right:12%;height:1px;
  background:linear-gradient(90deg,transparent,var(--green),transparent);
}
.logo-wrap { text-align:center; margin-bottom:28px; }
.logo { font-family:'Orbitron',monospace;font-size:1.5rem;font-weight:900;color:var(--green);letter-spacing:6px;text-decoration:none; }
.logo em{color:var(--white);font-style:normal;}
.tagline { font-family:'Rajdhani',sans-serif;font-size:.65rem;letter-spacing:4px;text-transform:uppercase;color:var(--gray);margin-top:5px; }
h2 { font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;color:var(--white);margin-bottom:5px; }
.sub { font-family:'Barlow',sans-serif;font-size:.84rem;color:var(--gray2);margin-bottom:24px; }

/* Alerts */
.alert-err { background:rgba(255,59,92,.08);border:1px solid rgba(255,59,92,.25);border-radius:8px;padding:12px 14px;margin-bottom:18px; }
.alert-err ul { margin:0;padding-left:16px; }
.alert-err li { font-size:.82rem;color:#ff7096;margin-bottom:4px; }
.alert-ok { background:rgba(0,255,136,.08);border:1px solid rgba(0,255,136,.25);border-radius:8px;padding:16px;margin-bottom:18px;text-align:center; }
.alert-ok strong { font-family:'Orbitron',monospace;font-size:.9rem;color:var(--green);display:block;margin-bottom:6px; }
.alert-ok p { font-size:.82rem;color:var(--gray2); }

.field { margin-bottom:16px; }
label { display:block;font-family:'Rajdhani',sans-serif;font-size:.65rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gray2);margin-bottom:7px; }
input[type=text],input[type=email],input[type=password],input[type=tel] {
  width:100%;background:rgba(255,255,255,.03);border:1px solid var(--border2);
  border-radius:8px;padding:12px 14px;
  font-family:'Barlow',sans-serif;font-size:.88rem;color:var(--white);
  outline:none;transition:border-color .25s,box-shadow .25s;
}
input:focus { border-color:rgba(0,255,136,.45);box-shadow:0 0 0 3px rgba(0,255,136,.08); }
input::placeholder { color:var(--gray); }

/* Password strength */
.pw-hint { font-family:'Barlow',sans-serif;font-size:.72rem;color:var(--gray);margin-top:5px; }

.btn-submit {
  width:100%;margin-top:10px;
  font-family:'Rajdhani',sans-serif;font-size:.85rem;font-weight:700;
  letter-spacing:2.5px;text-transform:uppercase;
  color:var(--black);background:var(--green);border:none;border-radius:8px;padding:14px;
  cursor:pointer;transition:all .3s;box-shadow:0 4px 16px rgba(0,255,136,.25);
  position:relative;overflow:hidden;
}
.btn-submit::after{content:'';position:absolute;inset:0;background:rgba(255,255,255,.18);transform:translateX(-100%);transition:.3s;}
.btn-submit:hover::after{transform:translateX(0);}
.btn-submit:hover{box-shadow:0 8px 28px rgba(0,255,136,.45);transform:translateY(-2px);}

.links { text-align:center;font-size:.82rem;color:var(--gray2);margin-top:20px;font-family:'Barlow',sans-serif; }
.links a {
  color:var(--green);text-decoration:none;transition:opacity .2s;
  font-family:'Rajdhani',sans-serif;font-size:.8rem;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
}
.links a:hover { opacity:.8; }
@media(max-width:480px){ .card{padding:32px 20px 28px;} }
</style>
</head>
<body>
<div id="shader-bg"></div>
<div class="wrap">
  <div class="card">
    <div class="logo-wrap">
      <a href="../index.php" class="logo">MINI<em>FUT</em></a>
      <div class="tagline">Premium Mini Soccer Arena</div>
    </div>

    <h2>Buat Akun Baru</h2>
    <p class="sub">Booking lebih cepat dan mudah</p>

    <?php if ($success): ?>
    <div class="alert-ok">
      <strong>Akun Berhasil Dibuat!</strong>
      <p>Silakan login dengan email dan password Anda.</p>
    </div>
    <div class="links">
      <a href="login.php">Masuk ke akun Anda</a>
    </div>

    <?php else: ?>

    <?php if (!empty($errors)): ?>
    <div class="alert-err">
      <ul>
        <?php foreach($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
      <div class="field">
        <label for="nama">Nama Lengkap</label>
        <input type="text" id="nama" name="nama"
               placeholder="Nama sesuai KTP" value="<?= e($values['nama']) ?>" required>
      </div>

      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               placeholder="nama@email.com" value="<?= e($values['email']) ?>" required>
      </div>

      <div class="field">
        <label for="notelp">Nomor Telepon / WhatsApp</label>
        <input type="tel" id="notelp" name="notelp"
               placeholder="08xxxxxxxxxx" value="<?= e($values['notelp']) ?>" required>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="Min. 8 karakter" required>
        <div class="pw-hint">Minimal 8 karakter, 1 huruf kapital, dan 1 angka.</div>
      </div>

      <div class="field">
        <label for="konfirm">Konfirmasi Password</label>
        <input type="password" id="konfirm" name="konfirm"
               placeholder="Ulangi password" required>
      </div>

      <button type="submit" class="btn-submit">DAFTAR SEKARANG</button>
    </form>

    <div class="links">
      Sudah punya akun? <a href="login.php">Masuk di sini</a>
    </div>

    <?php endif; ?>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function(){
  var c=document.getElementById('shader-bg');if(!c)return;
  var vs='void main(){gl_Position=vec4(position,1.0);}';
  var fs=[
    'precision highp float;',
    'uniform vec2 resolution;uniform float time;',
    'void main(void){',
    '  vec2 uv=(gl_FragCoord.xy*2.0-resolution.xy)/min(resolution.x,resolution.y);',
    '  float t=time*0.05;float lw=0.002;',
    '  vec3 col=vec3(0.0);',
    '  for(int j=0;j<3;j++){for(int i=0;i<5;i++){',
    '    col[j]+=lw*float(i*i)/abs(fract(t-0.01*float(j)+float(i)*0.01)*5.0-length(uv)+mod(uv.x+uv.y,0.2));',
    '  }}',
    '  float b=(col.r+col.g+col.b)/3.0;',
    '  gl_FragColor=vec4(b*0.02,b*0.95,b*0.45,1.0);',
    '}'
  ].join('\n');
  var cam=new THREE.Camera();cam.position.z=1;
  var sc=new THREE.Scene();
  var geo=new THREE.PlaneGeometry(2,2);
  var uni={time:{value:1.0},resolution:{value:new THREE.Vector2()}};
  var mat=new THREE.ShaderMaterial({uniforms:uni,vertexShader:vs,fragmentShader:fs});
  sc.add(new THREE.Mesh(geo,mat));
  var r=new THREE.WebGLRenderer({antialias:true});
  r.setPixelRatio(window.devicePixelRatio);c.appendChild(r.domElement);
  function onR(){r.setSize(window.innerWidth,window.innerHeight);uni.resolution.value.set(r.domElement.width,r.domElement.height);}
  onR();window.addEventListener('resize',onR);
  (function anim(){requestAnimationFrame(anim);uni.time.value+=0.05;r.render(sc,cam);})();
})();
</script>
</body>
</html>