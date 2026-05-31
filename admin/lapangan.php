<?php
$pageTitle = 'Lapangan';
require_once __DIR__ . '/_header.php';

$pdo = getDB();
$alert = '';

// ── CRUD Actions ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── TAMBAH ──
    if ($action === 'tambah') {
        $nama   = trim($_POST['nama']   ?? '');
        $jenis  = trim($_POST['jenis']  ?? '');
        $harga  = (int)($_POST['harga'] ?? 0);
        $status = $_POST['status'] ?? 'TERSEDIA';
        $foto   = null;

        if ($nama && $jenis && $harga > 0) {
            // Upload foto
            if (!empty($_FILES['foto']['name'])) {
                $up = uploadFoto($_FILES['foto'], 'lapangan');
                if ($up) $foto = $up;
            }
            $stmt = $pdo->prepare("INSERT INTO Lapangan (ID_LAPANGAN, NAMA_LAPANGAN, JENIS_LAPANGAN, HARGA_PER_JAM, STATUS_LAPANGAN, FOTO) VALUES (NULL, ?, ?, ?, ?, ?)");
            // Note: ID_LAPANGAN pakai AUTO_INCREMENT di ALTER di bawah, atau set manual
            // Untuk aman, ambil MAX+1
            $nextId = (int)$pdo->query("SELECT COALESCE(MAX(ID_LAPANGAN),0)+1 FROM Lapangan")->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO Lapangan (ID_LAPANGAN, NAMA_LAPANGAN, JENIS_LAPANGAN, HARGA_PER_JAM, STATUS_LAPANGAN, FOTO) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$nextId, $nama, $jenis, $harga, $status, $foto]);
            $alert = 'success|Lapangan berhasil ditambahkan.';
        } else {
            $alert = 'error|Data tidak lengkap atau tidak valid.';
        }
    }

    // ── EDIT ──
    if ($action === 'edit') {
        $id     = (int)($_POST['id']     ?? 0);
        $nama   = trim($_POST['nama']    ?? '');
        $jenis  = trim($_POST['jenis']   ?? '');
        $harga  = (int)($_POST['harga']  ?? 0);
        $status = $_POST['status']       ?? 'TERSEDIA';

        if ($id && $nama && $jenis && $harga > 0) {
            $fotoClause = '';
            $params = [$nama, $jenis, $harga, $status];

            if (!empty($_FILES['foto']['name'])) {
                $up = uploadFoto($_FILES['foto'], 'lapangan');
                if ($up) {
                    // Hapus foto lama
                    $old = $pdo->prepare("SELECT FOTO FROM Lapangan WHERE ID_LAPANGAN=?");
                    $old->execute([$id]);
                    $oldFoto = $old->fetchColumn();
                    if ($oldFoto && file_exists(UPLOAD_DIR . $oldFoto)) {
                        @unlink(UPLOAD_DIR . $oldFoto);
                    }
                    $fotoClause = ', FOTO=?';
                    $params[] = $up;
                }
            }
            $params[] = $id;
            $stmt = $pdo->prepare("UPDATE Lapangan SET NAMA_LAPANGAN=?, JENIS_LAPANGAN=?, HARGA_PER_JAM=?, STATUS_LAPANGAN=? $fotoClause WHERE ID_LAPANGAN=?");
            $stmt->execute($params);
            $alert = 'success|Lapangan berhasil diperbarui.';
        } else {
            $alert = 'error|Data tidak lengkap.';
        }
    }

    // ── HAPUS ──
    if ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            // Cek apakah ada jadwal/booking terkait
            $cek = $pdo->prepare("SELECT COUNT(*) FROM Jadwal WHERE ID_LAPANGAN=?");
            $cek->execute([$id]);
            if ($cek->fetchColumn() > 0) {
                $alert = 'error|Lapangan tidak bisa dihapus karena masih ada jadwal terkait.';
            } else {
                // Hapus foto
                $old = $pdo->prepare("SELECT FOTO FROM Lapangan WHERE ID_LAPANGAN=?");
                $old->execute([$id]);
                $oldFoto = $old->fetchColumn();
                if ($oldFoto && file_exists(UPLOAD_DIR . $oldFoto)) @unlink(UPLOAD_DIR . $oldFoto);
                $pdo->prepare("DELETE FROM Lapangan WHERE ID_LAPANGAN=?")->execute([$id]);
                $alert = 'success|Lapangan berhasil dihapus.';
            }
        }
    }
}

// ── Tambahkan kolom FOTO jika belum ada ─────────────────
try {
    $pdo->query("SELECT FOTO FROM Lapangan LIMIT 1");
} catch (PDOException) {
    $pdo->exec("ALTER TABLE Lapangan ADD COLUMN FOTO VARCHAR(255) NULL");
}

// ── Search & Pagination ──────────────────────────────────
$search  = trim($_GET['search'] ?? '');
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$where  = $search ? "WHERE NAMA_LAPANGAN LIKE ? OR JENIS_LAPANGAN LIKE ? OR STATUS_LAPANGAN LIKE ?" : '';
$params = $search ? ["%$search%","%$search%","%$search%"] : [];

$total = $pdo->prepare("SELECT COUNT(*) FROM Lapangan $where");
$total->execute($params);
$totalRows = (int)$total->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

$stmt = $pdo->prepare("SELECT * FROM Lapangan $where ORDER BY ID_LAPANGAN ASC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$lapangans = $stmt->fetchAll();

// Edit data (untuk modal)
$editData = null;
if (isset($_GET['edit_id'])) {
    $s = $pdo->prepare("SELECT * FROM Lapangan WHERE ID_LAPANGAN=?");
    $s->execute([(int)$_GET['edit_id']]);
    $editData = $s->fetch();
}

[$alertType, $alertMsg] = $alert ? explode('|', $alert, 2) : ['', ''];
?>

<?php if ($alertMsg): ?>
<div class="alert alert-<?= $alertType === 'success' ? 'success' : 'error' ?>"><?= e($alertMsg) ?></div>
<?php endif; ?>

<div class="table-card">
  <div class="table-header">
    <div class="table-title">Data Lapangan</div>
    <div class="table-actions">
      <!-- Search -->
      <form method="GET" action="">
        <div class="search-wrap">
          <span class="search-icon"><i class="bi bi-search"></i></span>
          <input type="text" name="search" placeholder="Cari lapangan..." value="<?= e($search) ?>">
        </div>
      </form>
      <button class="btn btn-green" onclick="openModal('modal-tambah')"><i class="bi bi-plus-lg"></i> Tambah Lapangan</button>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Foto</th><th>ID</th><th>Nama</th><th>Jenis</th>
        <th>Harga/Jam</th><th>Status</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($lapangans)): ?>
      <tr><td colspan="7">
        <div class="empty-state"><div class="empty-icon"><i class="bi bi-hexagon"></i></div><div class="empty-text">Tidak ada data lapangan</div></div>
      </td></tr>
      <?php else: ?>
      <?php foreach($lapangans as $l): ?>
      <tr>
        <td>
          <?php if (!empty($l['FOTO'])): ?>
            <img src="../<?= UPLOAD_URL . e($l['FOTO']) ?>" class="field-photo" alt="foto">
          <?php else: ?>
            <div class="field-photo" style="display:flex;align-items:center;justify-content:center;font-size:1.2rem;"><i class="bi bi-image"></i></div>
          <?php endif; ?>
        </td>
        <td class="td-green"><?= e($l['ID_LAPANGAN']) ?></td>
        <td class="td-white"><?= e($l['NAMA_LAPANGAN']) ?></td>
        <td><?= e($l['JENIS_LAPANGAN']) ?></td>
        <td class="td-green"><?= formatRupiah((int)$l['HARGA_PER_JAM']) ?></td>
        <td>
          <?php $sc = $l['STATUS_LAPANGAN']==='TERSEDIA' ? 'badge-green' : 'badge-red'; ?>
          <span class="badge <?= $sc ?>"><?= e($l['STATUS_LAPANGAN']) ?></span>
        </td>
        <td>
          <a href="?edit_id=<?= $l['ID_LAPANGAN'] ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>"
             class="btn btn-amber btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus lapangan ini?')">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id"     value="<?= $l['ID_LAPANGAN'] ?>">
            <button type="submit" class="btn btn-red btn-sm"><i class="bi bi-trash3"></i> Hapus</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <a href="?page=<?= max(1,$page-1) ?>&search=<?= urlencode($search) ?>" class="page-btn <?= $page<=1?'disabled':'' ?>">‹ Prev</a>
    <?php for($i=1;$i<=$totalPages;$i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="?page=<?= min($totalPages,$page+1) ?>&search=<?= urlencode($search) ?>" class="page-btn <?= $page>=$totalPages?'disabled':'' ?>">Next ›</a>
    <span class="page-info">Menampilkan <?= count($lapangans) ?> dari <?= $totalRows ?> data</span>
  </div>
  <?php endif; ?>
</div>

<!-- ── MODAL TAMBAH ──────────────────────────────────────── -->
<div class="modal-overlay <?= (!$editData && $alertType==='') || isset($_GET['modal_tambah']) ? '' : '' ?>" id="modal-tambah">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">Tambah Lapangan</div>
      <button class="modal-close" onclick="closeModal('modal-tambah')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="tambah">
        <div class="form-group">
          <label class="form-label">Nama Lapangan</label>
          <input type="text" name="nama" class="form-input" placeholder="Lapangan 4" required>
        </div>
        <div class="form-group">
          <label class="form-label">Jenis / Tipe Rumput</label>
          <input type="text" name="jenis" class="form-input" placeholder="Rumput Sintetis Pro" required>
        </div>
        <div class="form-group">
          <label class="form-label">Harga per Jam (Rp)</label>
          <input type="number" name="harga" class="form-input" placeholder="1000000" min="0" required>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="TERSEDIA">TERSEDIA</option>
            <option value="MAINTENANCE">MAINTENANCE</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Foto Lapangan</label>
          <div class="upload-area">
            <input type="file" name="foto" accept="image/*" onchange="previewImg(this,'prev-tambah')">
            <img id="prev-tambah" src="" alt="" style="display:none;width:100%;max-height:150px;object-fit:cover;border-radius:6px;margin-bottom:8px;">
            <div class="upload-text"><i class="bi bi-camera"></i> Klik untuk upload foto (JPG/PNG/WebP, max 5MB)</div>
          </div>
        </div>
        <button type="submit" class="btn btn-green" style="width:100%;padding:12px;font-size:.8rem;">Simpan Lapangan</button>
      </form>
    </div>
  </div>
</div>

<!-- ── MODAL EDIT ────────────────────────────────────────── -->
<?php if ($editData): ?>
<div class="modal-overlay open" id="modal-edit">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">Edit Lapangan #<?= e($editData['ID_LAPANGAN']) ?></div>
      <a href="lapangan.php?search=<?= urlencode($search) ?>&page=<?= $page ?>" class="modal-close" style="text-decoration:none;"><i class="bi bi-x-lg"></i></a>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id"     value="<?= e($editData['ID_LAPANGAN']) ?>">

        <?php if (!empty($editData['FOTO'])): ?>
        <div class="form-group" style="text-align:center;">
          <img src="../<?= UPLOAD_URL . e($editData['FOTO']) ?>"
               style="width:100%;max-height:160px;object-fit:cover;border-radius:8px;border:1px solid var(--border2);" alt="Foto saat ini">
          <div style="font-size:.72rem;color:var(--gray);margin-top:6px;">Foto saat ini upload untuk mengubah foto</div>
        </div>
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Nama Lapangan</label>
          <input type="text" name="nama" class="form-input" value="<?= e($editData['NAMA_LAPANGAN']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Jenis / Tipe Rumput</label>
          <input type="text" name="jenis" class="form-input" value="<?= e($editData['JENIS_LAPANGAN']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Harga per Jam (Rp)</label>
          <input type="number" name="harga" class="form-input" value="<?= e($editData['HARGA_PER_JAM']) ?>" min="0" required>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="TERSEDIA"    <?= $editData['STATUS_LAPANGAN']==='TERSEDIA'?'selected':'' ?>>TERSEDIA</option>
            <option value="MAINTENANCE" <?= $editData['STATUS_LAPANGAN']==='MAINTENANCE'?'selected':'' ?>>MAINTENANCE</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Ganti Foto Lapangan</label>
          <div class="upload-area">
            <input type="file" name="foto" accept="image/*" onchange="previewImg(this,'prev-edit')">
            <img id="prev-edit" src="" alt="" style="display:none;width:100%;max-height:150px;object-fit:cover;border-radius:6px;margin-bottom:8px;">
            <div class="upload-text"><i class="bi bi-camera"></i> Klik untuk upload foto baru (kosongkan jika tidak ingin ganti)</div>
          </div>
        </div>
        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn btn-green" style="flex:1;padding:12px;font-size:.8rem;">Simpan Perubahan</button>
          <a href="lapangan.php" class="btn btn-outline" style="padding:12px 20px;font-size:.8rem;">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Close on backdrop click
document.querySelectorAll('.modal-overlay').forEach(function(el) {
  el.addEventListener('click', function(e) {
    if (e.target === el) el.classList.remove('open');
  });
});

function previewImg(input, previewId) {
  const preview = document.getElementById(previewId);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>