<?php
$pageTitle = 'Jadwal';
require_once __DIR__ . '/_header.php';

$pdo   = getDB();
$alert = '';

// Create
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $lapanganId  = (int)($_POST['lapangan_id'] ?? 0);
        $tanggal     = $_POST['tanggal']  ?? '';
        $jamMulai    = trim($_POST['jam_mulai']  ?? '');
        $jamSelesai  = trim($_POST['jam_selesai'] ?? '');
        $status      = $_POST['status']  ?? 'YA';

        if ($lapanganId && $tanggal && $jamMulai && $jamSelesai) {
            // Validasi: Batasan Jam Operasional 08:00 - 24:00
            if ((int)$jamMulai < 8 || (int)$jamSelesai > 24) {
                $alert = 'error|Jam operasional hanya diperbolehkan antara pukul 08:00 hingga 24:00.';
            } // Validasi: jam mulai harus sebelum jam selesai
            elseif ((int)$jamMulai >= (int)$jamSelesai) {
                $alert = 'error|Jam mulai harus lebih awal dari jam selesai.';
            } else {
                $pdo->prepare("INSERT INTO Jadwal (ID_LAPANGAN,TANGGAL,JAM_MULAI,JAM_SELESAI,STATUS_JADWAL) VALUES(?,?,?,?,?)")
                    ->execute([$lapanganId,$tanggal,$jamMulai,$jamSelesai,$status]);
                $alert = 'success|Jadwal berhasil ditambahkan.';
            }
        } else {
            $alert = 'error|Data tidak lengkap.';
        }
    }

    if ($action === 'edit') {
        $id         = (int)($_POST['id'] ?? 0);
        $tanggal    = $_POST['tanggal']   ?? '';
        $jamMulai   = trim($_POST['jam_mulai']   ?? '');
        $jamSelesai = trim($_POST['jam_selesai']  ?? '');
        $status     = $_POST['status']    ?? 'YA';
        if ($id && $tanggal && $jamMulai && $jamSelesai) {
            // Validasi: Batasan Jam Operasional 08:00 - 24:00
            if ((int)$jamMulai < 8 || (int)$jamSelesai > 24) {
                $alert = 'error|Jam operasional hanya diperbolehkan antara pukul 08:00 hingga 24:00.';
            } elseif ((int)$jamMulai >= (int)$jamSelesai) {
                $alert = 'error|Jam mulai harus lebih awal dari jam selesai.';
            } else {
                $pdo->prepare("UPDATE Jadwal SET TANGGAL=?,JAM_MULAI=?,JAM_SELESAI=?,STATUS_JADWAL=? WHERE ID_JADWAL=?")
                    ->execute([$tanggal,$jamMulai,$jamSelesai,$status,$id]);
                $alert = 'success|Jadwal berhasil diperbarui.';
            }
        }
    }

    if ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $cek = $pdo->prepare("SELECT COUNT(*) FROM Booking WHERE ID_JADWAL=?");
            $cek->execute([$id]);
            if ($cek->fetchColumn() > 0) {
                $alert = 'error|Jadwal tidak bisa dihapus karena masih ada booking terkait.';
            } else {
                $pdo->prepare("DELETE FROM Jadwal WHERE ID_JADWAL=?")->execute([$id]);
                $alert = 'success|Jadwal berhasil dihapus.';
            }
        }
    }
}

// ── Lapangan list untuk form ─────────────────────────────
$lapangans = $pdo->query("SELECT ID_LAPANGAN, NAMA_LAPANGAN FROM Lapangan ORDER BY ID_LAPANGAN")->fetchAll();

// ── Search & Pagination ──────────────────────────────────
$search  = trim($_GET['search'] ?? '');
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$where  = $search ? "WHERE l.NAMA_LAPANGAN LIKE ? OR j.TANGGAL LIKE ? OR j.STATUS_JADWAL LIKE ?" : '';
$params = $search ? ["%$search%","%$search%","%$search%"] : [];

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM Jadwal j JOIN Lapangan l ON j.ID_LAPANGAN=l.ID_LAPANGAN $where");
$totalStmt->execute($params);
$totalRows  = (int)$totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows/$perPage));

$stmt = $pdo->prepare(
    "SELECT j.*, l.NAMA_LAPANGAN FROM Jadwal j
     JOIN Lapangan l ON j.ID_LAPANGAN=l.ID_LAPANGAN
     $where ORDER BY j.TANGGAL DESC, j.JAM_MULAI ASC LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$jadwals = $stmt->fetchAll();

$editData = null;
if (isset($_GET['edit_id'])) {
    $s = $pdo->prepare("SELECT * FROM Jadwal WHERE ID_JADWAL=?");
    $s->execute([(int)$_GET['edit_id']]);
    $editData = $s->fetch();
}

[$alertType,$alertMsg] = $alert ? explode('|',$alert,2) : ['',''];
?>

<?php if ($alertMsg): ?>
<div class="alert alert-<?= $alertType==='success'?'success':'error' ?>"><?= e($alertMsg) ?></div>
<?php endif; ?>

<div class="table-card">
  <div class="table-header">
    <div class="table-title">Data Jadwal</div>
    <div class="table-actions">
      <form method="GET">
        <div class="search-wrap">
          <span class="search-icon"><i class="bi bi-search"></i></span>
          <input type="text" name="search" placeholder="Cari lapangan, tanggal, status..." value="<?= e($search) ?>">
        </div>
      </form>
      <button class="btn btn-green" onclick="openModal('modal-tambah')"><i class="bi bi-plus-lg"></i> Tambah Jadwal</button>
    </div>
  </div>

  <table>
    <thead>
      <tr><th>ID</th><th>Lapangan</th><th>Tanggal</th><th>Jam Mulai</th><th>Jam Selesai</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody>
      <?php if (empty($jadwals)): ?>
      <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><i class="bi bi-calendar-x"></i></div><div class="empty-text">Tidak ada jadwal</div></div></td></tr>
      <?php else: ?>
      <?php foreach($jadwals as $j): ?>
      <tr>
        <td class="td-green">#<?= e($j['ID_JADWAL']) ?></td>
        <td class="td-white"><?= e($j['NAMA_LAPANGAN']) ?></td>
        <td><?= date('d M Y', strtotime($j['TANGGAL'])) ?></td>
        <td><?= e($j['JAM_MULAI']) ?>:00</td>
        <td><?= e($j['JAM_SELESAI']) ?>:00</td>
        <td>
          <?php $sc = $j['STATUS_JADWAL']==='YA' ? 'badge-green' : 'badge-red'; ?>
          <span class="badge <?= $sc ?>"><?= $j['STATUS_JADWAL']==='YA' ? 'TERSEDIA' : 'TERISI' ?></span>
        </td>
        <td>
          <a href="?edit_id=<?= $j['ID_JADWAL'] ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>"
             class="btn btn-amber btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus jadwal ini?')">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id"     value="<?= $j['ID_JADWAL'] ?>">
            <button type="submit" class="btn btn-red btn-sm"><i class="bi bi-trash3"></i></button>
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
    <span class="page-info"><?= count($jadwals) ?> dari <?= $totalRows ?> jadwal</span>
  </div>
  <?php endif; ?>
</div>

<div class="modal-overlay" id="modal-tambah">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">Tambah Jadwal</div>
      <button class="modal-close" onclick="closeModal('modal-tambah')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <form method="POST">
        <input type="hidden" name="action" value="tambah">
        <div class="form-group">
          <label class="form-label">Lapangan</label>
          <select name="lapangan_id" class="form-select" required>
            <option value="">-- Pilih Lapangan --</option>
            <?php foreach($lapangans as $l): ?>
            <option value="<?= $l['ID_LAPANGAN'] ?>"><?= e($l['NAMA_LAPANGAN']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Tanggal</label>
          <input type="date" name="tanggal" class="form-input" required min="<?= date('Y-m-d') ?>">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Jam Mulai</label>
            <input type="number" name="jam_mulai" class="form-input" placeholder="8" min="8" max="23" required>
          </div>
          <div class="form-group">
            <label class="form-label">Jam Selesai</label>
            <input type="number" name="jam_selesai" class="form-input" placeholder="9" min="7" max="24" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="YA">TERSEDIA</option>
            <option value="TIDAK">TERISI</option>
          </select>
        </div>
        <button type="submit" class="btn btn-green" style="width:100%;padding:12px;">Simpan Jadwal</button>
      </form>
    </div>
  </div>
</div>

<?php if ($editData): ?>
<div class="modal-overlay open" id="modal-edit">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">Edit Jadwal #<?= e($editData['ID_JADWAL']) ?></div>
      <a href="jadwal.php" class="modal-close" style="text-decoration:none;"><i class="bi bi-x-lg"></i></a>
    </div>
    <div class="modal-body">
      <form method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id"     value="<?= e($editData['ID_JADWAL']) ?>">
        <div class="form-group">
          <label class="form-label">Tanggal</label>
          <input type="date" name="tanggal" class="form-input" value="<?= e($editData['TANGGAL']) ?>" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Jam Mulai</label>
            <input type="number" name="jam_mulai" class="form-input" value="<?= e($editData['JAM_MULAI']) ?>" min="8" max="23" required>
          </div>
          <div class="form-group">
            <label class="form-label">Jam Selesai</label>
            <input type="number" name="jam_selesai" class="form-input" value="<?= e($editData['JAM_SELESAI']) ?>" min="7" max="24" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="YA" <?= $editData['STATUS_JADWAL']==='YA'?'selected':'' ?>>TERSEDIA</option>
            <option value="TIDAK" <?= $editData['STATUS_JADWAL']==='TIDAK'?'selected':'' ?>>TERISI</option>
          </select>
        </div>
        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn btn-green" style="flex:1;padding:12px;">Simpan</button>
          <a href="jadwal.php" class="btn btn-outline" style="padding:12px 20px;">Batal</a>
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
  el.addEventListener('click', function(e) { if(e.target===el) el.classList.remove('open'); });
});
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>