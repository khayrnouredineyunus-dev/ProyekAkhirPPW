<?php
$pageTitle = 'Pelanggan';
require_once __DIR__ . '/_header.php';

$pdo   = getDB();
$alert = '';

// ── Tambahkan kolom FOTO_PROFIL jika belum ada ──────────
try { $pdo->query("SELECT FOTO_PROFIL FROM Pelanggan LIMIT 1"); }
catch (PDOException) { $pdo->exec("ALTER TABLE Pelanggan ADD COLUMN FOTO_PROFIL VARCHAR(255) NULL"); }

// ── CRUD Actions ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── TAMBAH ──
    if ($action === 'tambah') {
        $nama     = trim($_POST['nama']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $notelp   = trim($_POST['notelp']   ?? '');
        $password = $_POST['password']      ?? '';
        $foto     = null;

        $errArr = [];
        if (!$nama)   $errArr[] = 'Nama tidak boleh kosong.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errArr[] = 'Format email tidak valid.';
        if (!preg_match('/^[0-9+\-\s]{8,20}$/', $notelp)) $errArr[] = 'Nomor telepon tidak valid.';
        if (mb_strlen($password) < 6) $errArr[] = 'Password minimal 6 karakter.';

        if (empty($errArr)) {
            // Cek email duplikat
            $cek = $pdo->prepare("SELECT ID_PELANGGAN FROM Pelanggan WHERE U_EMAIL=?");
            $cek->execute([$email]);
            if ($cek->fetch()) {
                $alert = 'error|Email sudah terdaftar.';
            } else {
                if (!empty($_FILES['foto']['name'])) {
                    $up = uploadFoto($_FILES['foto'], 'profil');
                    if ($up) $foto = $up;
                }
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO Pelanggan (U_NAMA,U_EMAIL,U_PASSWORD,U_NOTELP,FOTO_PROFIL) VALUES(?,?,?,?,?)")
                    ->execute([$nama, $email, $hash, $notelp, $foto]);
                $alert = 'success|Pelanggan berhasil ditambahkan.';
            }
        } else {
            $alert = 'error|' . implode(' ', $errArr);
        }
    }

    // ── EDIT ──
    if ($action === 'edit') {
        $id     = (int)($_POST['id']     ?? 0);
        $nama   = trim($_POST['nama']    ?? '');
        $email  = trim($_POST['email']   ?? '');
        $notelp = trim($_POST['notelp']  ?? '');
        $pw     = $_POST['password']     ?? '';

        if ($id && $nama && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fotoClause = '';
            $params = [$nama, $email, $notelp];

            // Ganti foto jika ada
            if (!empty($_FILES['foto']['name'])) {
                $up = uploadFoto($_FILES['foto'], 'profil');
                if ($up) {
                    $old = $pdo->prepare("SELECT FOTO_PROFIL FROM Pelanggan WHERE ID_PELANGGAN=?");
                    $old->execute([$id]);
                    $oldFoto = $old->fetchColumn();
                    if ($oldFoto && file_exists(UPLOAD_DIR . $oldFoto)) @unlink(UPLOAD_DIR . $oldFoto);
                    $fotoClause = ', FOTO_PROFIL=?';
                    $params[] = $up;
                }
            }

            // Ganti password jika diisi
            $pwClause = '';
            if (!empty($pw) && mb_strlen($pw) >= 6) {
                $pwClause = ', U_PASSWORD=?';
                $params[] = password_hash($pw, PASSWORD_DEFAULT);
            }

            $params[] = $id;
            $pdo->prepare("UPDATE Pelanggan SET U_NAMA=?,U_EMAIL=?,U_NOTELP=? $fotoClause $pwClause WHERE ID_PELANGGAN=?")
                ->execute($params);
            $alert = 'success|Data pelanggan berhasil diperbarui.';
        } else {
            $alert = 'error|Data tidak lengkap atau email tidak valid.';
        }
    }

    // ── HAPUS ──
    if ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            // Cek booking terkait
            $cek = $pdo->prepare("SELECT COUNT(*) FROM Booking WHERE ID_PELANGGAN=?");
            $cek->execute([$id]);
            if ($cek->fetchColumn() > 0) {
                $alert = 'error|Pelanggan tidak bisa dihapus karena masih memiliki riwayat booking.';
            } else {
                $old = $pdo->prepare("SELECT FOTO_PROFIL FROM Pelanggan WHERE ID_PELANGGAN=?");
                $old->execute([$id]);
                $oldFoto = $old->fetchColumn();
                if ($oldFoto && file_exists(UPLOAD_DIR . $oldFoto)) @unlink(UPLOAD_DIR . $oldFoto);
                $pdo->prepare("DELETE FROM Pelanggan WHERE ID_PELANGGAN=?")->execute([$id]);
                $alert = 'success|Pelanggan berhasil dihapus.';
            }
        }
    }
}

// ── Search & Pagination ──────────────────────────────────
$search  = trim($_GET['search'] ?? '');
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$where  = $search ? "WHERE U_NAMA LIKE ? OR U_EMAIL LIKE ? OR U_NOTELP LIKE ?" : '';
$params = $search ? ["%$search%","%$search%","%$search%"] : [];

$total = $pdo->prepare("SELECT COUNT(*) FROM Pelanggan $where");
$total->execute($params);
$totalRows  = (int)$total->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

$stmt = $pdo->prepare("SELECT p.*, (SELECT COUNT(*) FROM Booking b WHERE b.ID_PELANGGAN=p.ID_PELANGGAN) as total_booking
                        FROM Pelanggan p $where ORDER BY p.ID_PELANGGAN DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$pelangganList = $stmt->fetchAll();

// Edit data
$editData = null;
if (isset($_GET['edit_id'])) {
    $s = $pdo->prepare("SELECT * FROM Pelanggan WHERE ID_PELANGGAN=?");
    $s->execute([(int)$_GET['edit_id']]);
    $editData = $s->fetch();
}

[$alertType, $alertMsg] = $alert ? explode('|', $alert, 2) : ['',''];
?>

<?php if ($alertMsg): ?>
<div class="alert alert-<?= $alertType==='success'?'success':'error' ?>"><?= e($alertMsg) ?></div>
<?php endif; ?>

<div class="table-card">
  <div class="table-header">
    <div class="table-title">Data Pelanggan</div>
    <div class="table-actions">
      <form method="GET">
        <div class="search-wrap">
          <span class="search-icon"><i class="bi bi-search"></i></span>
          <input type="text" name="search" placeholder="Cari nama, email, telepon..." value="<?= e($search) ?>">
        </div>
      </form>
      <button class="btn btn-green" onclick="openModal('modal-tambah')"><i class="bi bi-plus-lg"></i> Tambah Pelanggan</button>
    </div>
  </div>

  <table>
    <thead>
      <tr><th>Foto</th><th>Nama</th><th>Email</th><th>No. Telepon</th><th>Total Booking</th><th>Aksi</th></tr>
    </thead>
    <tbody>
      <?php if (empty($pelangganList)): ?>
      <tr><td colspan="6"><div class="empty-state"><div class="empty-icon"><i class="bi bi-person"></i></div><div class="empty-text">Tidak ada pelanggan</div></div></td></tr>
      <?php else: ?>
      <?php foreach($pelangganList as $p): ?>
      <tr>
        <td>
          <?php if (!empty($p['FOTO_PROFIL'])): ?>
            <img src="../<?= UPLOAD_URL . e($p['FOTO_PROFIL']) ?>" class="avatar-sm" alt="foto">
          <?php else: ?>
            <div class="avatar-sm" style="display:flex;align-items:center;justify-content:center;font-family:'Orbitron',monospace;font-size:.75rem;color:var(--green);">
              <?= strtoupper(substr($p['U_NAMA'],0,1)) ?>
            </div>
          <?php endif; ?>
        </td>
        <td class="td-white"><?= e($p['U_NAMA']) ?></td>
        <td><?= e($p['U_EMAIL']) ?></td>
        <td><?= e($p['U_NOTELP']) ?></td>
        <td>
          <span class="badge <?= $p['total_booking']>0?'badge-green':'badge-gray' ?>">
            <?= $p['total_booking'] ?> booking
          </span>
        </td>
        <td>
          <a href="?edit_id=<?= $p['ID_PELANGGAN'] ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>"
             class="btn btn-amber btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus pelanggan ini?')">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id"     value="<?= $p['ID_PELANGGAN'] ?>">
            <button type="submit" class="btn btn-red btn-sm"><i class="bi bi-trash3"></i> Hapus</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <a href="?page=<?= max(1,$page-1) ?>&search=<?= urlencode($search) ?>" class="page-btn <?= $page<=1?'disabled':'' ?>">‹ Prev</a>
    <?php for($i=1;$i<=$totalPages;$i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="?page=<?= min($totalPages,$page+1) ?>&search=<?= urlencode($search) ?>" class="page-btn <?= $page>=$totalPages?'disabled':'' ?>">Next ›</a>
    <span class="page-info"><?= count($pelangganList) ?> dari <?= $totalRows ?> pelanggan</span>
  </div>
  <?php endif; ?>
</div>

<!-- ── MODAL TAMBAH ──────────────────────────────────────── -->
<div class="modal-overlay" id="modal-tambah">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">Tambah Pelanggan</div>
      <button class="modal-close" onclick="closeModal('modal-tambah')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="tambah">
        <div style="text-align:center;margin-bottom:20px;">
          <div id="avatar-preview-wrap" style="width:72px;height:72px;border-radius:50%;background:rgba(0,255,136,.1);border:2px solid rgba(0,255,136,.25);margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:1.8rem;overflow:hidden;">
            <i class="bi bi-person-fill"></i>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Foto Profil</label>
          <div class="upload-area">
            <input type="file" name="foto" accept="image/*" onchange="previewAvatar(this,'avatar-preview-wrap')">
            <div class="upload-text"><i class="bi bi-camera"></i> Upload foto profil (opsional)</div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="nama" class="form-input" placeholder="Nama pelanggan" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-input" placeholder="email@example.com" required>
        </div>
        <div class="form-group">
          <label class="form-label">No. Telepon / WhatsApp</label>
          <input type="tel" name="notelp" class="form-input" placeholder="08xxxxxxxxxx" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-input" placeholder="Min. 6 karakter" required>
        </div>
        <button type="submit" class="btn btn-green" style="width:100%;padding:12px;">Simpan Pelanggan</button>
      </form>
    </div>
  </div>
</div>

<!-- ── MODAL EDIT ────────────────────────────────────────── -->
<?php if ($editData): ?>
<div class="modal-overlay open" id="modal-edit">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">Edit Pelanggan #<?= e($editData['ID_PELANGGAN']) ?></div>
      <a href="pelanggan.php?search=<?= urlencode($search) ?>&page=<?= $page ?>" class="modal-close" style="text-decoration:none;"><i class="bi bi-x-lg"></i></a>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id"     value="<?= e($editData['ID_PELANGGAN']) ?>">

        <div style="text-align:center;margin-bottom:20px;">
          <?php if (!empty($editData['FOTO_PROFIL'])): ?>
          <img src="../<?= UPLOAD_URL . e($editData['FOTO_PROFIL']) ?>"
               style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid rgba(0,255,136,.25);" id="avatar-edit">
          <?php else: ?>
          <div id="avatar-edit" style="width:72px;height:72px;border-radius:50%;background:rgba(0,255,136,.1);border:2px solid rgba(0,255,136,.25);margin:0 auto;display:flex;align-items:center;justify-content:center;font-family:'Orbitron',monospace;font-size:1.4rem;color:var(--green);">
            <?= strtoupper(substr($editData['U_NAMA'],0,1)) ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label">Ganti Foto Profil</label>
          <div class="upload-area">
            <input type="file" name="foto" accept="image/*" onchange="previewAvatarImg(this,'avatar-edit')">
            <div class="upload-text"><i class="bi bi-camera"></i> Upload foto baru (kosongkan jika tidak ingin ganti)</div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="nama" class="form-input" value="<?= e($editData['U_NAMA']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-input" value="<?= e($editData['U_EMAIL']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">No. Telepon</label>
          <input type="tel" name="notelp" class="form-input" value="<?= e($editData['U_NOTELP']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Password Baru <span style="color:var(--gray);font-weight:400;text-transform:none;letter-spacing:0;">(kosongkan jika tidak ingin ganti)</span></label>
          <input type="password" name="password" class="form-input" placeholder="Password baru">
        </div>
        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn btn-green" style="flex:1;padding:12px;">Simpan</button>
          <a href="pelanggan.php" class="btn btn-outline" style="padding:12px 20px;">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(function(el) {
  el.addEventListener('click', function(e) { if (e.target===el) el.classList.remove('open'); });
});

function previewAvatar(input, wrapId) {
  const wrap = document.getElementById(wrapId);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      wrap.innerHTML = '<img src="'+e.target.result+'" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
function previewAvatarImg(input, imgId) {
  const el = document.getElementById(imgId);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      if (el.tagName === 'IMG') {
        el.src = e.target.result;
      } else {
        el.style.overflow = 'hidden';
        el.innerHTML = '<img src="'+e.target.result+'" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>