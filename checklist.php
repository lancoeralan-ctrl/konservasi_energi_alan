<?php
require 'config.php'; need_login();
csrf_check();
$lv = $_SESSION['user']['level'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus' && $lv !== 'Siswa') {
    $pdo->prepare("DELETE FROM checklist WHERE id_checklist=?")->execute([(int)$_POST['id']]);
    flash('Checklist dihapus.'); header('Location: checklist.php'); exit;
}
$where = []; $par = [];
if ($lv === 'Siswa') { $where[] = 'c.nis = ?'; $par[] = (int)$_SESSION['user']['username']; }
if (!empty($_GET['dari']))   { $where[] = 'c.tanggal >= ?'; $par[] = $_GET['dari']; }
if (!empty($_GET['sampai'])) { $where[] = 'c.tanggal <= ?'; $par[] = $_GET['sampai']; }
if (!empty($_GET['kelas']) && $lv !== 'Siswa') { $where[] = 's.kelas = ?'; $par[] = $_GET['kelas']; }
$sql = "SELECT c.*, s.nama, s.kelas,
          SUM(d.status='Ya') ya, SUM(d.status='Tidak') tidak
        FROM checklist c
        JOIN siswa s ON s.nis=c.nis
        LEFT JOIN detail_checklist d ON d.id_checklist=c.id_checklist"
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " GROUP BY c.id_checklist ORDER BY c.tanggal DESC, c.jam DESC";
$st = $pdo->prepare($sql); $st->execute($par); $rows = $st->fetchAll();
$kelasList = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas")->fetchAll(PDO::FETCH_COLUMN);
include 'inc/header.php';
?>
<div class="d-flex justify-content-between align-items-center">
  <h3>Checklist</h3>
  <a href="checklist_form.php" class="btn btn-success">+ Isi Checklist</a>
</div>
<form class="row g-2 my-3" method="get">
  <div class="col-md-3"><input type="date" name="dari" class="form-control" value="<?= e($_GET['dari'] ?? '') ?>"></div>
  <div class="col-md-3"><input type="date" name="sampai" class="form-control" value="<?= e($_GET['sampai'] ?? '') ?>"></div>
  <?php if ($lv !== 'Siswa'): ?>
  <div class="col-md-3"><select name="kelas" class="form-select"><option value="">Semua kelas</option>
    <?php foreach ($kelasList as $k): ?><option <?= ($_GET['kelas'] ?? '') === $k ? 'selected' : '' ?>><?= e($k) ?></option><?php endforeach; ?></select></div>
  <?php endif; ?>
  <div class="col-md-3"><button class="btn btn-outline-success">Filter</button> <a href="checklist.php" class="btn btn-outline-secondary">Reset</a></div>
</form>
<div class="table-responsive">
<table class="table table-striped bg-white">
  <thead><tr><th>Tanggal</th><th>Jam</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Ya</th><th>Tidak</th><th width="150">Aksi</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e($r['tanggal']) ?></td><td><?= e(substr($r['jam'],0,5)) ?></td><td><?= e($r['nis']) ?></td>
    <td><?= e($r['nama']) ?></td><td><?= e($r['kelas']) ?></td>
    <td class="text-success"><?= (int)$r['ya'] ?></td><td class="text-danger"><?= (int)$r['tidak'] ?></td>
    <td>
      <a class="btn btn-sm btn-info" href="checklist_detail.php?id=<?= $r['id_checklist'] ?>">Detail</a>
      <?php if ($lv !== 'Siswa'): ?>
      <form method="post" class="d-inline" onsubmit="return confirm('Hapus checklist ini?')">
        <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $r['id_checklist'] ?>">
        <button class="btn btn-sm btn-danger">Hapus</button></form>
      <?php endif; ?>
    </td></tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="8" class="text-center">Belum ada data.</td></tr>'; ?>
  </tbody>
</table></div>
<?php include 'inc/footer.php'; ?>
