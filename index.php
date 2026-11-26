<?php
require 'config.php'; need_login();
$lv = $_SESSION['user']['level'];
$own = $lv === 'Siswa' ? " WHERE c.nis = " . (int)$_SESSION['user']['username'] : '';
$tot = $pdo->query("SELECT COUNT(*) FROM checklist c $own")->fetchColumn();
$stat = $pdo->query("SELECT d.status, COUNT(*) n FROM detail_checklist d JOIN checklist c ON c.id_checklist=d.id_checklist $own GROUP BY d.status")->fetchAll(PDO::FETCH_KEY_PAIR);
$ya = $stat['Ya'] ?? 0; $tdk = $stat['Tidak'] ?? 0;
$pct = ($ya+$tdk) ? round($ya/($ya+$tdk)*100) : 0;
include 'inc/header.php';
?>
<h3>Dashboard</h3>
<div class="row g-3 mt-1">
  <div class="col-6 col-md-3"><div class="card stat-card text-center"><div class="card-body"><div class="angka"><?= $tot ?></div>Total Checklist</div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card text-center"><div class="card-body"><div class="angka hijau"><?= $ya ?></div>Jawaban "Ya"</div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card hitam text-center"><div class="card-body"><div class="angka"><?= $tdk ?></div>Jawaban "Tidak"</div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card biru text-center"><div class="card-body"><div class="angka biru"><?= $pct ?>%</div>Kepatuhan</div></div></div>
</div>
<div class="card mt-3"><div class="card-body">
  <div class="d-flex justify-content-between small fw-bold mb-1"><span>Tingkat kepatuhan konservasi energi</span><span><?= $pct ?>%</span></div>
  <div class="progress" style="height:18px"><div class="progress-bar" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#dc2626,#1d4ed8)"></div></div>
</div></div>
<?php if ($lv !== 'Siswa'): ?>
<div class="row g-3 mt-1">
  <div class="col-md-6"><div class="card stat-card biru text-center"><div class="card-body"><div class="angka biru"><?= $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn() ?></div>Siswa</div></div></div>
  <div class="col-md-6"><div class="card stat-card text-center"><div class="card-body"><div class="angka hijau"><?= $pdo->query("SELECT COUNT(*) FROM perangkat")->fetchColumn() ?></div>Perangkat</div></div></div>
</div>
<?php endif; ?>
<a href="checklist_form.php" class="btn btn-success mt-4">+ Isi Checklist Baru</a>
<?php include 'inc/footer.php'; ?>
