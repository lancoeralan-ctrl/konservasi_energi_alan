<?php
require 'config.php'; need_login();
$lv = $_SESSION['user']['level'];
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT c.*, s.nama, s.kelas FROM checklist c JOIN siswa s ON s.nis=c.nis WHERE c.id_checklist=?");
$st->execute([$id]); $c = $st->fetch();
if (!$c) { http_response_code(404); exit('Data tidak ditemukan.'); }
if ($lv === 'Siswa' && (int)$c['nis'] !== (int)$_SESSION['user']['username']) { http_response_code(403); exit('Akses ditolak.'); }

$d = $pdo->prepare("SELECT d.*, p.nama_perangkat FROM detail_checklist d JOIN perangkat p ON p.id_perangkat=d.id_perangkat
                    WHERE d.id_checklist=? ORDER BY FIELD(d.tahapan,'SEBELUM','SELAMA','SESUDAH','5R'), p.nama_perangkat");
$d->execute([$id]);
$grp = [];
foreach ($d->fetchAll() as $r) $grp[$r['tahapan']][] = $r;
include 'inc/header.php';
?>
<a href="checklist.php" class="btn btn-sm btn-secondary mb-3">&larr; Kembali</a>
<h3>Detail Checklist</h3>
<table class="table table-sm w-auto">
  <tr><th>Siswa</th><td><?= e($c['nama']) ?> (<?= e($c['nis']) ?>)</td></tr>
  <tr><th>Kelas</th><td><?= e($c['kelas']) ?></td></tr>
  <tr><th>Waktu</th><td><?= e($c['tanggal']) ?> <?= e(substr($c['jam'],0,5)) ?></td></tr>
</table>
<?php foreach ($grp as $t => $items): ?>
  <div class="card mb-3">
    <div class="card-header"><?= e($t) ?></div>
    <table class="table mb-0">
      <thead><tr><th>Perangkat</th><th>Status</th><th>Kondisi</th><th>Keterangan</th></tr></thead>
      <tbody>
      <?php foreach ($items as $r): ?>
        <tr><td><?= e($r['nama_perangkat']) ?></td>
        <td><span class="badge bg-<?= $r['status']==='Ya' ? 'success' : 'danger' ?>"><?= e($r['status']) ?></span></td>
        <td><?= e($r['kondisi']) ?></td><td><?= e($r['keterangan']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>
<?php include 'inc/footer.php'; ?>
