<?php
$pageTitle = 'Booking';
require_once __DIR__ . '/_header.php';

$pdo   = getDB();
$alert = '';

// ── CRUD Actions ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── EDIT STATUS ──
    if ($action === 'edit_status') {
        $id     = trim($_POST['id']     ?? '');
        $status = $_POST['status']      ?? '';
        $validStatus = ['LUNAS','DP','BATAL','PENDING'];

        if (!$id) {
            $alert = 'error|ID Booking tidak boleh kosong.';
        } elseif (!preg_match('/^MF-[A-Z0-9]{6}$/', $id)) {
            $alert = 'error|Format ID Booking tidak valid.';
        } elseif (!in_array($status, $validStatus)) {
            $alert = 'error|Status tidak valid. Pilih: ' . implode(', ', $validStatus);
        } else {
            // Cek booking exists
            $check = $pdo->prepare("SELECT ID_BOOKING, STATUS_BOOKING FROM Booking WHERE ID_BOOKING = ?");
            $check->execute([$id]);
            $existing = $check->fetch();
            if (!$existing) {
                $alert = 'error|Booking dengan ID ' . e($id) . ' tidak ditemukan.';
            } elseif ($existing['STATUS_BOOKING'] === $status) {
                $alert = 'error|Status sudah ' . e($status) . ', tidak ada perubahan.';
            } else {
                $pdo->prepare("UPDATE Booking SET STATUS_BOOKING=? WHERE ID_BOOKING=?")->execute([$status,$id]);
                $alert = 'success|Status booking ' . e($id) . ' berhasil diubah dari ' . e($existing['STATUS_BOOKING']) . ' → ' . e($status) . '.';
            }
        }
    }

    // ── HAPUS ──
    if ($action === 'hapus') {
        $id = trim($_POST['id'] ?? '');
        if (!$id) {
            $alert = 'error|ID Booking tidak boleh kosong.';
        } elseif (!preg_match('/^MF-[A-Z0-9]{6}$/', $id)) {
            $alert = 'error|Format ID Booking tidak valid.';
        } else {
            $pdo->beginTransaction();
            try {
                // Cek booking ada
                $checkStmt = $pdo->prepare("SELECT ID_BOOKING FROM Booking WHERE ID_BOOKING = ?");
                $checkStmt->execute([$id]);
                if (!$checkStmt->fetch()) {
                    throw new Exception('Booking dengan ID ' . $id . ' tidak ditemukan.');
                }
                $pdo->prepare("DELETE FROM Pembayaran WHERE ID_BOOKING=?")->execute([$id]);
                // Ambil ID_JADWAL
                $j = $pdo->prepare("SELECT ID_JADWAL FROM Booking WHERE ID_BOOKING=?");
                $j->execute([$id]);
                $jadwalId = $j->fetchColumn();
                $pdo->prepare("DELETE FROM Booking WHERE ID_BOOKING=?")->execute([$id]);
                if ($jadwalId) $pdo->prepare("DELETE FROM Jadwal WHERE ID_JADWAL=?")->execute([$jadwalId]);
                $pdo->commit();
                $alert = 'success|Booking ' . e($id) . ' berhasil dihapus beserta data terkait.';
            } catch (Exception $e) {
                $pdo->rollBack();
                $alert = 'error|Gagal menghapus booking: ' . $e->getMessage();
            }
        }
    }
}


$search  = trim($_GET['search']    ?? '');
$statusF = trim($_GET['status']    ?? '');
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$conditions = [];
$params = [];

if ($search) {
    $conditions[] = "(b.ID_BOOKING LIKE ? OR p.U_NAMA LIKE ? OR p.U_EMAIL LIKE ? OR l.NAMA_LAPANGAN LIKE ?)";
    $params = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
}
if ($statusF) {
    $conditions[] = "b.STATUS_BOOKING = ?";
    $params[] = $statusF;
}
$where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

$baseQuery = "FROM Booking b
              JOIN Pelanggan p  ON b.ID_PELANGGAN = p.ID_PELANGGAN
              JOIN Jadwal j     ON b.ID_JADWAL    = j.ID_JADWAL
              JOIN Lapangan l   ON j.ID_LAPANGAN  = l.ID_LAPANGAN
              $where";

$totalStmt = $pdo->prepare("SELECT COUNT(*) $baseQuery");
$totalStmt->execute($params);
$totalRows  = (int)$totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

$stmt = $pdo->prepare(
    "SELECT b.*, p.U_NAMA, p.U_EMAIL, p.U_NOTELP,
            l.NAMA_LAPANGAN, l.HARGA_PER_JAM,
            j.TANGGAL, j.JAM_MULAI, j.JAM_SELESAI
     $baseQuery
     ORDER BY b.TANGGAL_BOOKING DESC LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

[$alertType,$alertMsg] = $alert ? explode('|',$alert,2) : ['',''];
?>

<?php if ($alertMsg): ?>
<div class="alert alert-<?= $alertType==='success'?'success':'error' ?>"><?= e($alertMsg) ?></div>
<?php endif; ?>

<div class="table-card">
  <div class="table-header">
    <div class="table-title">Data Booking</div>
    <div class="table-actions">
      <!-- Filter Status -->
      <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <select name="status" class="form-select" style="width:130px;padding:8px;" onchange="this.form.submit()">
          <option value="">Semua Status</option>
          <?php foreach(['LUNAS','DP','PENDING','BATAL'] as $st): ?>
          <option value="<?= $st ?>" <?= $statusF===$st?'selected':'' ?>><?= $st ?></option>
          <?php endforeach; ?>
        </select>
        <div class="search-wrap">
          <span class="search-icon"><i class="bi bi-search"></i></span>
          <input type="text" name="search" placeholder="Cari kode, nama, lapangan..." value="<?= e($search) ?>">
        </div>
        <?php if ($search||$statusF): ?>
        <a href="booking.php" class="btn btn-outline btn-sm"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <div style="overflow-x:auto;">
  <table>
    <thead>
      <tr>
        <th>Kode Booking</th><th>Pelanggan</th><th>Lapangan</th>
        <th>Tgl Main</th><th>Jam</th><th>Tgl Booking</th>
        <th>Status</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($bookings)): ?>
      <tr><td colspan="8"><div class="empty-state"><div class="empty-icon"><i class="bi bi-journal-bookmark"></i></div><div class="empty-text">Tidak ada data booking</div></div></td></tr>
      <?php else: ?>
      <?php foreach($bookings as $b): ?>
      <?php
        $slots   = explode(',', $b['JAM_MULAI']);
        $jamStr  = implode(', ', array_map(fn($s) => sprintf('%02d:00', trim($s)), $slots));
        $total   = count($slots) * (int)$b['HARGA_PER_JAM'];
        $stClass = match($b['STATUS_BOOKING']) {
          'LUNAS'=>'badge-green','DP'=>'badge-amber',
          'BATAL'=>'badge-red',default=>'badge-gray'
        };
      ?>
      <tr>
        <td class="td-green" style="font-size:.75rem;"><?= e($b['ID_BOOKING']) ?></td>
        <td>
          <div class="td-white" style="font-size:.82rem;"><?= e($b['U_NAMA']) ?></div>
          <div style="font-size:.7rem;"><?= e($b['U_EMAIL']) ?></div>
        </td>
        <td><?= e($b['NAMA_LAPANGAN']) ?></td>
        <td><?= date('d M Y', strtotime($b['TANGGAL'])) ?></td>
        <td style="font-size:.78rem;"><?= e($jamStr) ?></td>
        <td style="font-size:.75rem;"><?= date('d M Y H:i', strtotime($b['TANGGAL_BOOKING'])) ?></td>
        <td><span class="badge <?= $stClass ?>"><?= e($b['STATUS_BOOKING']) ?></span></td>
        <td>
          <button class="btn btn-blue btn-sm"
            onclick="openEditModal('<?= e($b['ID_BOOKING']) ?>','<?= e($b['STATUS_BOOKING']) ?>')"><i class="bi bi-pencil-square"></i> Status</button>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus booking ini beserta jadwal dan pembayarannya?')">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id"     value="<?= e($b['ID_BOOKING']) ?>">
            <button type="submit" class="btn btn-red btn-sm"><i class="bi bi-trash3"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
  </div>

  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <a href="?page=<?= max(1,$page-1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusF) ?>"
       class="page-btn <?= $page<=1?'disabled':'' ?>">‹ Prev</a>
    <?php for($i=1;$i<=$totalPages;$i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusF) ?>"
       class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="?page=<?= min($totalPages,$page+1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusF) ?>"
       class="page-btn <?= $page>=$totalPages?'disabled':'' ?>">Next ›</a>
    <span class="page-info"><?= count($bookings) ?> dari <?= $totalRows ?> booking</span>
  </div>
  <?php endif; ?>
</div>

<!-- ── MODAL EDIT STATUS ──────────────────────────────────── -->
<div class="modal-overlay" id="modal-edit">
  <div class="modal" style="max-width:380px;">
    <div class="modal-head">
      <div class="modal-title">Ubah Status Booking</div>
      <button class="modal-close" onclick="closeModal('modal-edit')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <form method="POST">
        <input type="hidden" name="action" value="edit_status">
        <input type="hidden" name="id" id="edit-booking-id">
        <div class="form-group">
          <label class="form-label">Kode Booking</label>
          <div id="edit-booking-code" style="font-family:'Orbitron',monospace;color:var(--green);font-size:.88rem;"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Status Baru</label>
          <select name="status" id="edit-status" class="form-select">
            <option value="LUNAS">LUNAS</option>
            <option value="DP">DP (50%)</option>
            <option value="PENDING">PENDING</option>
            <option value="BATAL">BATAL</option>
          </select>
        </div>
        <button type="submit" class="btn btn-green" style="width:100%;padding:12px;">Simpan Perubahan</button>
      </form>
    </div>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(function(el) {
  el.addEventListener('click', function(e) { if (e.target===el) el.classList.remove('open'); });
});

function openEditModal(bookingId, currentStatus) {
  document.getElementById('edit-booking-id').value   = bookingId;
  document.getElementById('edit-booking-code').textContent = bookingId;
  document.getElementById('edit-status').value = currentStatus;
  openModal('modal-edit');
}
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>