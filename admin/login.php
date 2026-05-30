<?php
// admin/login.php — Halaman Login Admin MiniFut
require_once __DIR__ . '/../config.php';
startSecureSession();

if (isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // ── Kredensial admin disimpan di sini (ganti sesuai kebutuhan) ──
    // Untuk keamanan produksi, simpan di database atau env file
    $ADMIN_USER = 'admin';
    $ADMIN_PASS = 'minifut2026'; // Ganti dengan password Anda

    if (empty($username) || empty($password)) {
        $error = 'Username dan password tidak boleh kosong.';
    } elseif ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = 0;
        $_SESSION['user_name'] = 'Administrator';
        $_SESSION['user_role'] = 'admin';
        header('Location: dashboard.php');
        exit;
    } else {
        // Tambah delay untuk mencegah brute-force
        sleep(1);
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MiniFut Admin — Login</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@400;500;600;700&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
  --black:#060608; --card:#111318;
  --border2:rgba(255,255,255,0.1);
  --green:#00ff88; --gray:#6b7080; --gray2:#9aa0b0; --white:#eceef2;
}
*{margin:0;padding:0;box-sizing:border-box;}
html,body{height:100%;font-family:'Barlow',sans-serif;background:var(--black);color:var(--white);overflow:hidden;}
body::before{content:'';position:fixed;inset:0;z-index:0;
  background-image:linear-gradient(rgba(0,255,136,.02) 1px,transparent 1px),linear-gradient(90deg,rgba(0,255,136,.02) 1px,transparent 1px);
  background-size:56px 56px;animation:gridDrift 30s linear infinite;}
@keyframes gridDrift{0%{transform:translateY(0)}100%{transform:translateY(56px)}}
#noise{position:fixed;inset:0;opacity:.018;pointer-events:none;z-index:1;
  background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)'/%3E%3C/svg%3E");}
.wrap{position:relative;z-index:2;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;}
.card{width:100%;max-width:400px;background:var(--card);border:1px solid var(--border2);border-radius:20px;padding:44px 40px 40px;position:relative;box-shadow:0 32px 80px rgba(0,0,0,.65);animation:slideUp .5s cubic-bezier(.22,1,.36,1) both;}
@keyframes slideUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}
.card::before{content:'';position:absolute;top:0;left:12%;right:12%;height:1px;background:linear-gradient(90deg,transparent,var(--green),transparent);}

/* Admin badge */
.admin-badge {
  display:inline-flex;align-items:center;gap:6px;
  font-family:'Rajdhani',sans-serif;font-size:.6rem;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;
  color:var(--green);border:1px solid rgba(0,255,136,.25);
  background:rgba(0,255,136,.05);padding:4px 12px;border-radius:100px;
  margin-bottom:20px;
}
.logo-wrap{text-align:center;margin-bottom:28px;}
.logo{font-family:'Orbitron',monospace;font-size:1.5rem;font-weight:900;color:var(--green);letter-spacing:6px;text-decoration:none;}
.logo em{color:var(--white);font-style:normal;}
h2{font-family:'Orbitron',monospace;font-size:.95rem;font-weight:700;color:var(--white);margin-bottom:5px;}
.sub{font-size:.82rem;color:var(--gray2);margin-bottom:24px;}
.alert-err{background:rgba(255,59,92,.08);border:1px solid rgba(255,59,92,.25);border-radius:8px;padding:10px 14px;margin-bottom:18px;font-size:.82rem;color:#ff7096;}
.field{margin-bottom:16px;}
label{display:block;font-family:'Rajdhani',sans-serif;font-size:.65rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gray2);margin-bottom:7px;}
input[type=text],input[type=password]{width:100%;background:rgba(255,255,255,.03);border:1px solid var(--border2);border-radius:8px;padding:12px 14px;font-family:'Barlow',sans-serif;font-size:.88rem;color:var(--white);outline:none;transition:border-color .25s,box-shadow .25s;}
input:focus{border-color:rgba(0,255,136,.45);box-shadow:0 0 0 3px rgba(0,255,136,.08);}
input::placeholder{color:var(--gray);}
.btn-submit{width:100%;margin-top:8px;font-family:'Rajdhani',sans-serif;font-size:.85rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--black);background:var(--green);border:none;border-radius:8px;padding:14px;cursor:pointer;transition:all .3s;box-shadow:0 4px 16px rgba(0,255,136,.25);position:relative;overflow:hidden;}
.btn-submit::after{content:'';position:absolute;inset:0;background:rgba(255,255,255,.18);transform:translateX(-100%);transition:.3s;}
.btn-submit:hover::after{transform:translateX(0);}
.btn-submit:hover{box-shadow:0 8px 28px rgba(0,255,136,.45);transform:translateY(-2px);}
.back-link{display:block;text-align:center;margin-top:20px;font-family:'Rajdhani',sans-serif;font-size:.65rem;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:var(--gray);text-decoration:none;transition:color .2s;}
.back-link:hover{color:var(--gray2);}
@media(max-width:480px){.card{padding:32px 20px 28px;}}
</style>
</head>
<body>
<div id="noise"></div>
<div class="wrap">
  <div class="card">
    <div class="logo-wrap">
      <a href="../index.php" class="logo">MINI<em>FUT</em></a>
      <div style="display:flex;justify-content:center;margin-top:12px;">
        <span class="admin-badge">⚙ Panel Admin</span>
      </div>
    </div>

    <h2>Admin Access</h2>
    <p class="sub">Masuk ke dashboard pengelolaan MiniFut</p>

    <?php if ($error): ?>
    <div class="alert-err">⚠ <?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               placeholder="Username admin"
               value="<?= e($_POST['username'] ?? '') ?>" required autocomplete="username">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="Password admin" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-submit">MASUK ADMIN</button>
    </form>

    <a href="../auth/login.php" class="back-link">← Kembali ke login pelanggan</a>
  </div>
</div>
</body>
</html>