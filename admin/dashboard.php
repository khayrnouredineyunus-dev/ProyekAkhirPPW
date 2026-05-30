<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/_header.php';

$pdo = getDB();

// ── Stats ───────────────────────────────────────────────
$totalBooking   = $pdo->query("SELECT COUNT(*) FROM Booking")->fetchColumn();
$totalPelanggan = $pdo->query("SELECT COUNT(*) FROM Pelanggan")->fetchColumn();
$totalLapangan  = $pdo->query("SELECT COUNT(*) FROM Lapangan")->fetchColumn();
$pendingBayar   = $pdo->query("SELECT COUNT(*) FROM Pembayaran WHERE STATUS_PEMBAYARAN = 'PENDING'")->fetchColumn();

$revenueRow = $pdo->query(
    "SELECT COUNT(*) * 1000000 as rev FROM Booking WHERE STATUS_BOOKING = 'LUNAS'"
)->fetchColumn();

// ── 5 Booking Terbaru ───────────────────────────────────
$recentBookings = $pdo->query(
    "SELECT b.ID_BOOKING, p.U_NAMA, p.U_EMAIL, l.NAMA_LAPANGAN,
            j.TANGGAL, j.JAM_MULAI, b.STATUS_BOOKING, b.TANGGAL_BOOKING
     FROM Booking b
     JOIN Pelanggan p  ON b.ID_PELANGGAN = p.ID_PELANGGAN
     JOIN Jadwal j     ON b.ID_JADWAL    = j.ID_JADWAL
     JOIN Lapangan l   ON j.ID_LAPANGAN  = l.ID_LAPANGAN
     ORDER BY b.TANGGAL_BOOKING DESC LIMIT 8"
)->fetchAll();
?>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-label">Total Booking</div>
    <div class="stat-value"><?= $totalBooking ?></div>
    <div class="stat-sub">Semua waktu</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Pelanggan</div>
    <div class="stat-value"><?= $totalPelanggan ?></div>
    <div class="stat-sub">Terdaftar</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Lapangan</div>
    <div class="stat-value"><?= $totalLapangan ?></div>
    <div class="stat-sub">Aktif</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Pembayaran Pending</div>
    <div class="stat-value" style="color:var(--amber)"><?= $pendingBayar ?></div>
    <div class="stat-sub">Menunggu konfirmasi</div>
  </div>
</div>

<!-- Quick Nav -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:28px;">
  <?php
  $shortcuts = [
    ['lapangan.php',   '⬟', 'Lapangan',    'Kelola & foto lapangan'],
    ['booking.php',    '📋', 'Booking',     'Semua reservasi'],
    ['pembayaran.php', '💳', 'Pembayaran',  'Konfirmasi bayar'],
    ['pelanggan.php',  '👤', 'Pelanggan',   'Data & foto profil'],
    ['jadwal.php',     '📅', 'Jadwal',      'Manajemen slot'],
  ];
  foreach ($shortcuts as [$url, $icon, $title, $desc]): ?>
  <a href="<?= $url ?>" style="background:var(--card);border:1px solid var(--border);border-radius:10px;padding:18px;text-decoration:none;transition:all .2s;display:block;" onmouseover="this.style.borderColor='rgba(0,255,136,.25)'" onmouseout="this.style.borderColor='var(--border)'">
    <div style="font-size:1.4rem;margin-bottom:8px;"><?= $icon ?></div>
    <div style="font-family:'Orbitron',monospace;font-size:.8rem;font-weight:700;color:var(--white);margin-bottom:4px;"><?= $title ?></div>
    <div style="font-size:.75rem;color:var(--gray);"><?= $desc ?></div>
  </a>
  <?php endforeach; ?>
</div>

<!-- Recent Bookings -->
<div class="table-card">
  <div class="table-header">
    <div class="table-title">Booking Terbaru</div>
    <a href="booking.php" class="btn btn-outline">Lihat Semua</a>
  </div>
  <table>
    <thead>
      <tr>
        <th>Kode</th><th>Pelanggan</th><th>Lapangan</th>
        <th>Tanggal Main</th><th>Jam</th><th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($recentBookings)): ?>
      <tr><td colspan="6" class="empty-state">
        <div class="empty-icon">📋</div>
        <div class="empty-text">Belum ada booking</div>
      </td></tr>
      <?php else: ?>
      <?php foreach($recentBookings as $r): ?>
      <tr>
        <td class="td-green"><?= e($r['ID_BOOKING']) ?></td>
        <td>
          <div class="td-white" style="font-size:.84rem;"><?= e($r['U_NAMA']) ?></div>
          <div style="font-size:.72rem;"><?= e($r['U_EMAIL']) ?></div>
        </td>
        <td><?= e($r['NAMA_LAPANGAN']) ?></td>
        <td><?= date('d M Y', strtotime($r['TANGGAL'])) ?></td>
        <td><?= e($r['JAM_MULAI']) ?>:00</td>
        <td>
          <?php
          $st = $r['STATUS_BOOKING'];
          $cls = match($st) { 'LUNAS'=>'badge-green','DP'=>'badge-amber',default=>'badge-gray' };
          ?>
          <span class="badge <?= $cls ?>"><?= e($st) ?></span>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>