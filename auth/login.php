<?php
// auth/login.php — Halaman Login Pelanggan MiniFut
require_once __DIR__ . '/../config.php';
startSecureSession();

// Jika sudah login, redirect ke index
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // ── Validasi input ──
    if (empty($email) || empty($password)) {
        $error = 'Email dan password tidak boleh kosong.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT ID_PELANGGAN, U_NAMA, U_EMAIL, U_PASSWORD FROM Pelanggan WHERE U_EMAIL = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['U_PASSWORD'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['ID_PELANGGAN'];
            $_SESSION['user_name']  = $user['U_NAMA'];
            $_SESSION['user_email'] = $user['U_EMAIL'];
            $_SESSION['user_role']  = 'pelanggan';
            header('Location: ../index.php');
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MiniFut — Login</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@400;500;600;700&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
:root {
  --black:#060608; --dark:#0c0d10; --card:#111318;
  --border:rgba(255,255,255,0.06); --border2:rgba(255,255,255,0.1);
  --green:#00ff88; --glow:rgba(0,255,136,0.18); --glow-sm:rgba(0,255,136,0.07);
  --gray:#6b7080; --gray2:#9aa0b0; --white:#eceef2; --red:#ff3b5c;
}
*{margin:0;padding:0;box-sizing:border-box;}
html,body{height:100%;font-family:'Barlow',sans-serif;background:var(--black);color:var(--white);overflow:hidden;}

/* Shader background */
#shader-bg{position:fixed;inset:0;z-index:0;opacity:.45;}
#shader-bg canvas{display:block;width:100%;height:100%;}

.wrap {
  position:relative;z-index:2;
  display:flex;align-items:center;justify-content:center;
  min-height:100vh;padding:24px;
}

.card {
  width:100%;max-width:420px;
  background:var(--card);
  border:1px solid var(--border2);
  border-radius:20px;
  padding:44px 40px 40px;
  position:relative;
  box-shadow:0 32px 80px rgba(0,0,0,.6), 0 0 40px rgba(0,255,136,.04);
  animation:slideUp .5s cubic-bezier(.22,1,.36,1) both;
}
@keyframes slideUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}

/* Top accent line */
.card::before {
  content:'';position:absolute;top:0;left:12%;right:12%;height:1px;
  background:linear-gradient(90deg,transparent,var(--green),transparent);
  border-radius:50%;
}

.logo-wrap { text-align:center; margin-bottom:32px; }
.logo {
  font-family:'Orbitron',monospace;font-size:1.6rem;font-weight:900;
  color:var(--green);letter-spacing:6px;text-decoration:none;
}
.logo em{color:var(--white);font-style:normal;}
.tagline {
  font-family:'Rajdhani',sans-serif;font-size:.68rem;letter-spacing:4px;
  text-transform:uppercase;color:var(--gray);margin-top:6px;
}

h2 {
  font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;
  color:var(--white);margin-bottom:6px;
}
.sub {
  font-family:'Barlow',sans-serif;font-size:.84rem;color:var(--gray2);
  margin-bottom:28px;
}

/* Error */
.alert-err {
  background:rgba(255,59,92,.08);border:1px solid rgba(255,59,92,.25);
  border-radius:8px;padding:10px 14px;margin-bottom:20px;
  font-size:.82rem;color:#ff7096;display:flex;align-items:center;gap:8px;
}

/* Form fields */
.field { margin-bottom:18px; }
label {
  display:block;font-family:'Rajdhani',sans-serif;font-size:.65rem;
  font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:var(--gray2);margin-bottom:8px;
}
input[type=email], input[type=password], input[type=text] {
  width:100%;background:rgba(255,255,255,.03);border:1px solid var(--border2);
  border-radius:8px;padding:12px 14px;
  font-family:'Barlow',sans-serif;font-size:.9rem;color:var(--white);
  outline:none;transition:border-color .25s, box-shadow .25s;
  -webkit-appearance:none;
}
input:focus {
  border-color:rgba(0,255,136,.45);
  box-shadow:0 0 0 3px rgba(0,255,136,.08);
}
input::placeholder { color:var(--gray); }

/* Submit */
.btn-submit {
  width:100%;margin-top:8px;
  font-family:'Rajdhani',sans-serif;font-size:.85rem;font-weight:700;
  letter-spacing:2.5px;text-transform:uppercase;
  color:var(--black);background:var(--green);
  border:none;border-radius:8px;padding:14px;
  cursor:pointer;transition:all .3s;
  box-shadow:0 4px 16px rgba(0,255,136,.25);
  position:relative;overflow:hidden;
}
.btn-submit::after {
  content:'';position:absolute;inset:0;
  background:rgba(255,255,255,.18);transform:translateX(-100%);transition:.3s;
}
.btn-submit:hover::after { transform:translateX(0); }
.btn-submit:hover { box-shadow:0 8px 28px rgba(0,255,136,.45);transform:translateY(-2px); }
.btn-submit:active { transform:translateY(0); }

.divider { display:flex;align-items:center;gap:12px;margin:22px 0; }
.divider hr { flex:1;border:none;border-top:1px solid var(--border); }
.divider span { font-size:.7rem;color:var(--gray);white-space:nowrap; }

/* Links */
.links { text-align:center;font-size:.82rem;color:var(--gray2);font-family:'Barlow',sans-serif; }
.links a {
  color:var(--green);text-decoration:none;transition:opacity .2s;
  font-family:'Rajdhani',sans-serif;font-size:.8rem;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
}
.links a:hover { opacity:.8; }

/* Admin link */
.admin-link {
  display:block;text-align:center;margin-top:20px;
  font-family:'Rajdhani',sans-serif;font-size:.65rem;font-weight:600;
  letter-spacing:3px;text-transform:uppercase;color:var(--gray);
  text-decoration:none;transition:color .2s;
}
.admin-link:hover { color:var(--gray2); }

@media(max-width:480px){ .card{padding:32px 24px 28px;} }
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

    <h2>Selamat Datang</h2>
    <p class="sub">Masuk untuk booking lapangan dan kelola reservasi Anda</p>

    <?php if ($error): ?>
    <div class="alert-err">
      <span>⚠</span> <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               placeholder="nama@email.com"
               value="<?= e($_POST['email'] ?? '') ?>" required>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="Masukkan password" required>
      </div>

      <button type="submit" class="btn-submit">MASUK</button>
    </form>

    <div class="divider"><hr><span>Belum punya akun?</span><hr></div>
    <div class="links">
      <a href="register.php">Daftar Sekarang</a>
    </div>

    <a href="../admin/login.php" class="admin-link">⚙ Masuk sebagai Admin</a>
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