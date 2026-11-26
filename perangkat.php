<?php
require 'config.php'; need_login(['Admin','Guru']);
csrf_check();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['aksi'] ?? '';
    $p = [trim($_POST['nama_perangkat'] ?? ''), trim($_POST['jenis'] ?? '') ?: null, trim($_POST['keterangan'] ?? '') ?: null];
    if ($a === 'tambah') {
        $pdo->prepare("INSERT INTO perangkat (nama_perangkat,jenis,keterangan) VALUES (?,?,?)")->execute($p);
        flash('Perangkat ditambahkan.');
    } elseif ($a === 'ubah') {
        $pdo->prepare("UPDATE perangkat SET nama_perangkat=?, jenis=?, keterangan=? WHERE id_perangkat=?")->execute([...$p, (int)$_POST['id']]);
        flash('Perangkat diperbarui.');
    } elseif ($a === 'hapus') {
        $pdo->prepare("DELETE FROM perangkat WHERE id_perangkat=?")->execute([(int)$_POST['id']]);
        flash('Perangkat dihapus.');
    }
    header('Location: perangkat.php'); exit;
}
$edit = null;
if (isset($_GET['edit'])) { $s = $pdo->prepare("SELECT * FROM perangkat WHERE id_perangkat=?"); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(); }
$rows = $pdo->query("SELECT * FROM perangkat ORDER BY nama_perangkat")->fetchAll();
include 'inc/header.php';
?>
<h3>Data Perangkat</h3>
<form method="post" class="row g-2 my-3">
  <?= csrf_field() ?>
  <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
  <input type="hidden" name="id" value="<?= e($edit['id_perangkat'] ?? '') ?>">
  <div class="col-md-3"><input name="nama_perangkat" class="form-control" placeholder="Nama perangkat" required value="<?= e($edit['nama_perangkat'] ?? '') ?>"></div>
  <div class="col-md-2"><input name="jenis" class="form-control" placeholder="Jenis" value="<?= e($edit['jenis'] ?? '') ?>"></div>
  <div class="col-md-4"><input name="keterangan" class="form-control" placeholder="Keterangan" value="<?= e($edit['keterangan'] ?? '') ?>"></div>
  <div class="col-md-3"><button class="btn btn-success"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
    <?php if ($edit): ?><a href="perangkat.php" class="btn btn-secondary">Batal</a><?php endif; ?></div>
</form>
<table class="table table-striped bg-white">
  <thead><tr><th>#</th><th>Nama</th><th>Jenis</th><th>Keterangan</th><th width="160">Aksi</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $i => $r): ?>
    <tr><td><?= $i+1 ?></td><td><?= e($r['nama_perangkat']) ?></td><td><?= e($r['jenis']) ?></td><td><?= e($r['keterangan']) ?></td>
    <td>
      <a class="btn btn-sm btn-warning" href="?edit=<?= $r['id_perangkat'] ?>">Ubah</a>
      <form method="post" class="d-inline" onsubmit="return confirm('Hapus perangkat ini?')">
        <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $r['id_perangkat'] ?>">
        <button class="btn btn-sm btn-danger">Hapus</button></form>
    </td></tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="5" class="text-center">Belum ada data.</td></tr>'; ?>
  </tbody>
</table>
<?php include 'inc/footer.php'; ?>
