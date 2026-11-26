<?php
require 'config.php'; need_login(['Admin','Guru']);
csrf_check();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['aksi'] ?? '';
    try {
        if ($a === 'tambah') {
            $pdo->prepare("INSERT INTO siswa (nis,nama,kelas) VALUES (?,?,?)")
                ->execute([(int)$_POST['nis'], trim($_POST['nama']), trim($_POST['kelas'])]);
            flash('Siswa ditambahkan.');
        } elseif ($a === 'ubah') {
            $pdo->prepare("UPDATE siswa SET nama=?, kelas=? WHERE nis=?")
                ->execute([trim($_POST['nama']), trim($_POST['kelas']), (int)$_POST['nis']]);
            flash('Siswa diperbarui.');
        } elseif ($a === 'hapus') {
            $pdo->prepare("DELETE FROM siswa WHERE nis=?")->execute([(int)$_POST['nis']]);
            flash('Siswa dihapus (beserta checklist-nya).');
        }
    } catch (PDOException $ex) { flash('Gagal: ' . ($ex->getCode()==23000 ? 'NIS sudah ada.' : $ex->getMessage())); }
    header('Location: siswa.php'); exit;
}
$edit = null;
if (isset($_GET['edit'])) { $s = $pdo->prepare("SELECT * FROM siswa WHERE nis=?"); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(); }
$rows = $pdo->query("SELECT * FROM siswa ORDER BY kelas, nama")->fetchAll();
include 'inc/header.php';
?>
<h3>Data Siswa</h3>
<form method="post" class="row g-2 my-3">
  <?= csrf_field() ?>
  <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
  <div class="col-md-2"><input name="nis" class="form-control" placeholder="NIS" type="number" required value="<?= e($edit['nis'] ?? '') ?>" <?= $edit ? 'readonly' : '' ?>></div>
  <div class="col-md-5"><input name="nama" class="form-control" placeholder="Nama" required maxlength="100" value="<?= e($edit['nama'] ?? '') ?>"></div>
  <div class="col-md-2"><input name="kelas" class="form-control" placeholder="Kelas" required maxlength="10" value="<?= e($edit['kelas'] ?? '') ?>"></div>
  <div class="col-md-3"><button class="btn btn-success"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
    <?php if ($edit): ?><a href="siswa.php" class="btn btn-secondary">Batal</a><?php endif; ?></div>
</form>
<table class="table table-striped bg-white">
  <thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th width="160">Aksi</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e($r['nis']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kelas']) ?></td>
    <td>
      <a class="btn btn-sm btn-warning" href="?edit=<?= $r['nis'] ?>">Ubah</a>
      <form method="post" class="d-inline" onsubmit="return confirm('Hapus siswa ini?')">
        <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="nis" value="<?= $r['nis'] ?>">
        <button class="btn btn-sm btn-danger">Hapus</button></form>
    </td></tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="4" class="text-center">Belum ada data.</td></tr>'; ?>
  </tbody>
</table>
<?php include 'inc/footer.php'; ?>
