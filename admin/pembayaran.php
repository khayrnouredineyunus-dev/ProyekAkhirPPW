<?php
$pageTitle = 'Pembayaran';
require_once __DIR__ . '/_header.php';

$pdo   = getDB();
$alert = '';

// ── CRUD Actions ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── KONFIRMASI / EDIT STATUS PEMBAYARAN ──
    if ($action === 'konfirmasi') {
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status']   ?? '';
        $metode = trim($_POST['metode'] ?? '');
        $validStatus = ['LUNAS','PENDING','GAGAL','REFUND'];
        $validMetode = ['TRANSFER','CASH','QRIS','OVO','GOPAY','DANA'];

        if ($id && in_array($status, $validStatus)) {
            $metode = in_array(strtoupper($metode), $validMetode) ? strtoupper($metode) : 'TRANSFER';
            $pdo->prepare("UPDATE Pembayaran SET STATUS_PEMBAYARAN=?, METODE_PEMBAYARAN=?, TANGGAL_BAYAR=NOW() WHERE ID_PEMBAYARAN=?")
                ->execute([$status, $metode, $id]);

            // Sinkronkan status Booking juga
            if ($status === 'LUNAS') {
                $stmt = $pdo->prepare("SELECT ID_BOOKING FROM Pembayaran WHERE ID_PEMBAYARAN=?");
                $stmt->execute([$id]);
                $bookingId = $stmt->fetchColumn();
                if ($bookingId) {
                    $pdo->prepare("UPDATE Booking SET STATUS_BOOKING='LUNAS' WHERE ID_BOOKING=?")->execute([$bookingId]);
                }
            }

            $alert = 'success|Status pembayaran berhasil diperbarui.';
        } else {
            $alert = 'error|Data tidak valid.';
        }
    }

    // ── HAPUS ──
    if ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("DELETE FROM Pembayaran WHERE ID_PEMBAYARAN=?")->execute([$id]);
            $alert = 'success|Pembayaran berhasil dihapus.';
        }
    }
}

// ── Search & Pagination ──────────────────────────────────
$search  = trim($_GET['search']    ?? '');
$statusF = trim($_GET['status']    ?? '');
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$conditions = [];
$params     = [];

if ($search) {
    $conditions[] = "(pm.ID_BOOKING LIKE ? OR p.U_NAMA LIKE ? OR p.U_EMAIL LIKE ? OR pm.METODE_PEMBAYARAN LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($statusF) {
    $conditions[] = "pm.STATUS_PEMBAYARAN = ?";
    $params[] = $statusF;
}
$where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

$baseQuery = "FROM Pembayaran pm
              JOIN Booking b    ON pm.ID_BOOKING    = b.ID_BOOKING
              JOIN Pelanggan p  ON b.ID_PELANGGAN   = p.ID_PELANGGAN
              JOIN Jadwal j     ON b.ID_JADWAL       = j.ID_JADWAL
              JOIN Lapangan l   ON j.ID_LAPANGAN     = l.ID_LAPANGAN
              $where";

$totalStmt = $pdo->prepare("SELECT COUNT(*) $baseQuery");
$totalStmt->execute($params);
$totalRows  = (int)$totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

$stmt = $pdo->prepare(
    "SELECT pm.*, b.STATUS_BOOKING, b.TANGGAL_BOOKING,
            p.U_NAMA, p.U_EMAIL, p.U_NOTELP,
            l.NAMA_LAPANGAN, l.HARGA_PER_JAM,
            j.TANGGAL as TGL_MAIN, j.JAM_MULAI
     $baseQuery
     ORDER BY pm.ID_PEMBAYARAN DESC LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$payments = $stmt->fetchAll();

// Hitung total revenue dari LUNAS
$revenue = $pdo->query(
    "SELECT SUM(l.HARGA_PER_JAM * LENGTH(j.JAM_MULAI) - LENGTH(REPLACE(j.JAM_MULAI,',','')) * l.HARGA_PER_JAM)
     FROM Pembayaran pm
     JOIN Booking b ON pm.ID_BOOKING=b.ID_BOOKING
     JOIN Jadwal j  ON b.ID_JADWAL=j.ID_JADWAL
     JOIN Lapangan l ON j.ID_LAPANGAN=l.ID_LAPANGAN
     WHERE pm.STATUS_PEMBAYARAN='LUNAS'"
)->fetchColumn();

$pendingCount = $pdo->query("SELECT COUNT(*) FROM Pembayaran WHERE STATUS_PEMBAYARAN='PENDING'")->fetchColumn();
$lunasCount   = $pdo->query("SELECT COUNT(*) FROM Pembayaran WHERE STATUS_PEMBAYARAN='LUNAS'")->fetchColumn();

[$alertType,$alertMsg] = $alert ? explode('|',$alert,2) : ['',''];
?>

<?php if ($alertMsg): ?>
<div class="alert alert-<?= $alertType==='success'?'success':'error' ?>"><?= e($alertMsg) ?></div>
<?php endif; ?>

<!-- Stats mini -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-label">Menunggu Konfirmasi</div>
    <div class="stat-value" style="color:var(--amber);"><?= $pendingCount ?></div>
    <div class="stat-sub">Pembayaran pending</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Transaksi Lunas</div>
    <div class="stat-value"><?= $lunasCount ?></div>
    <div class="stat-sub">Dikonfirmasi</div>
  </div>
  <div class="stat-card" style="grid-column:span 2;">
    <div class="stat-label">Total Data Pembayaran</div>
    <div class="stat-value" style="font-size:1.4rem;"><?= $totalRows ?></div>
    <div class="stat-sub">Semua transaksi tercatat</div>
  </div>
</div>

<div class="table-card">
  <div class="table-header">
    <div class="table-title">Data Pembayaran</div>
    <div class="table-actions">
      <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <!-- Filter status -->
        <select name="status" class="form-select" style="width:130px;padding:8px;" onchange="this.form.submit()">
          <option value="">Semua Status</option>
          <?php foreach(['LUNAS','PENDING','GAGAL','REFUND'] as $st): ?>
          <option value="<?= $st ?>" <?= $statusF===$st?'selected':'' ?>><?= $st ?></option>
          <?php endforeach; ?>
        </select>
        <div class="search-wrap">
          <span class="search-icon"><i class="bi bi-search"></i></span>
          <input type="text" name="search" placeholder="Cari kode, nama, metode..." value="<?= e($search) ?>">
        </div>
        <?php if ($search || $statusF): ?>
        <a href="pembayaran.php" class="btn btn-outline btn-sm"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <div style="overflow-x:auto;">
  <table>
    <thead>
      <tr>
        <th>ID</th><th>Kode Booking</th><th>Pelanggan</th>
        <th>Lapangan</th><th>Tgl Main</th>
        <th>Metode</th><th>Tgl Bayar</th>
        <th>Status</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($payments)): ?>
      <tr><td colspan="9">
        <div class="empty-state"><div class="empty-icon"><i class="bi bi-credit-card"></i></div><div class="empty-text">Tidak ada data pembayaran</div></div>
      </td></tr>
      <?php else: ?>
      <?php foreach($payments as $pm): ?>
      <?php
        $slots  = explode(',', $pm['JAM_MULAI']);
        $jumlah = count($slots);
        $total  = $jumlah * (int)$pm['HARGA_PER_JAM'];

        $stClass = match($pm['STATUS_PEMBAYARAN']) {
          'LUNAS'  => 'badge-green',
          'PENDING' => 'badge-amber',
          'GAGAL'  => 'badge-red',
          'REFUND' => 'badge-blue',
          default  => 'badge-gray',
        };
      ?>
      <tr>
        <td class="td-green" style="font-size:.75rem;">#<?= e($pm['ID_PEMBAYARAN']) ?></td>
        <td style="font-size:.73rem;font-family:'Orbitron',monospace;color:var(--gray2);"><?= e($pm['ID_BOOKING']) ?></td>
        <td>
          <div class="td-white" style="font-size:.82rem;"><?= e($pm['U_NAMA']) ?></div>
          <div style="font-size:.7rem;"><?= e($pm['U_NOTELP']) ?></div>
        </td>
        <td>
          <div><?= e($pm['NAMA_LAPANGAN']) ?></div>
          <div style="font-size:.72rem;color:var(--green);"><?= $jumlah ?> jam · <?= formatRupiah($total) ?></div>
        </td>
        <td><?= $pm['TGL_MAIN'] ? date('d M Y', strtotime($pm['TGL_MAIN'])) : '-' ?></td>
        <td>
          <span class="badge badge-gray"><?= e($pm['METODE_PEMBAYARAN']) ?></span>
        </td>
        <td style="font-size:.75rem;">
          <?= $pm['TANGGAL_BAYAR'] ? date('d M Y H:i', strtotime($pm['TANGGAL_BAYAR'])) : '<span style="color:var(--gray)">Belum</span>' ?>
        </td>
        <td>
          <span class="badge <?= $stClass ?>"><?= e($pm['STATUS_PEMBAYARAN']) ?></span>
        </td>
        <td>
          <?php if ($pm['STATUS_PEMBAYARAN'] === 'PENDING'): ?>
          <button class="btn btn-green btn-sm"
            onclick="openKonfirmasi(<?= $pm['ID_PEMBAYARAN'] ?>,'<?= e($pm['ID_BOOKING']) ?>','<?= e($pm['METODE_PEMBAYARAN']) ?>')">
            <i class="bi bi-check-lg"></i> Konfirmasi
          </button>
          <?php else: ?>
          <button class="btn btn-blue btn-sm"
            onclick="openKonfirmasi(<?= $pm['ID_PEMBAYARAN'] ?>,'<?= e($pm['ID_BOOKING']) ?>','<?= e($pm['METODE_PEMBAYARAN']) ?>')">
            <i class="bi bi-pencil-square"></i> Edit
          </button>
          <?php endif; ?>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus data pembayaran ini?')">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id"     value="<?= $pm['ID_PEMBAYARAN'] ?>">
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
    <span class="page-info"><?= count($payments) ?> dari <?= $totalRows ?> transaksi</span>
  </div>
  <?php endif; ?>
</div>

<!-- ── MODAL KONFIRMASI / EDIT STATUS ───────────────────── -->
<div class="modal-overlay" id="modal-konfirmasi">
  <div class="modal" style="max-width:420px;">
    <div class="modal-head">
      <div class="modal-title" id="modal-konfirmasi-title">Konfirmasi Pembayaran</div>
      <button class="modal-close" onclick="closeModal('modal-konfirmasi')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <form method="POST">
        <input type="hidden" name="action" value="konfirmasi">
        <input type="hidden" name="id" id="konfirmasi-id">

        <div class="form-group">
          <label class="form-label">Kode Booking</label>
          <div id="konfirmasi-booking" style="font-family:'Orbitron',monospace;color:var(--green);font-size:.84rem;padding:8px 0;"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Metode Pembayaran</label>
          <select name="metode" id="konfirmasi-metode" class="form-select">
            <option value="TRANSFER">Transfer Bank</option>
            <option value="CASH">Cash</option>
            <option value="QRIS">QRIS</option>
            <option value="OVO">OVO</option>
            <option value="GOPAY">GoPay</option>
            <option value="DANA">Dana</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Status Pembayaran</label>
          <select name="status" id="konfirmasi-status" class="form-select">
            <option value="LUNAS">LUNAS — Konfirmasi pembayaran penuh</option>
            <option value="PENDING">PENDING — Masih menunggu</option>
            <option value="GAGAL">GAGAL — Pembayaran tidak valid</option>
            <option value="REFUND">REFUND — Dikembalikan</option>
          </select>
        </div>

        <!-- Info box -->
        <div style="background:rgba(0,255,136,.05);border:1px solid rgba(0,255,136,.15);border-radius:8px;padding:12px 14px;margin-bottom:18px;font-size:.78rem;color:var(--gray2);">
          <i class="bi bi-info-circle"></i> Mengkonfirmasi sebagai <strong style="color:var(--green);">LUNAS</strong> akan otomatis memperbarui status booking menjadi LUNAS.
        </div>

        <button type="submit" class="btn btn-green" style="width:100%;padding:12px;font-size:.8rem;">
          Simpan Status
        </button>
      </form>
    </div>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(function(el) {
  el.addEventListener('click', function(e) { if(e.target===el) el.classList.remove('open'); });
});

function openKonfirmasi(id, bookingCode, metode) {
  document.getElementById('konfirmasi-id').value      = id;
  document.getElementById('konfirmasi-booking').textContent = bookingCode;
  document.getElementById('konfirmasi-metode').value  = metode;
  openModal('modal-konfirmasi');
}
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>