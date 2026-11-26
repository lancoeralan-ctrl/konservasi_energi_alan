<?php
require 'config.php'; need_login(['Admin']);
csrf_check();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['aksi'] ?? '';
    $lvls = ['Admin','Guru','Siswa'];
    try {
        if ($a === 'tambah' && in_array($_POST['level'], $lvls)) {
            $pdo->prepare("INSERT INTO users (username,password,nama,level) VALUES (?,?,?,?)")
                ->execute([trim($_POST['username']), password_hash($_POST['password'], PASSWORD_DEFAULT), trim($_POST['nama']), $_POST['level']]);
            flash('Pengguna ditambahkan.');
        } elseif ($a === 'ubah' && in_array($_POST['level'], $lvls)) {
            $id = (int)$_POST['id'];
            $pdo->prepare("UPDATE users SET username=?, nama=?, level=? WHERE id_user=?")
                ->execute([trim($_POST['username']), trim($_POST['nama']), $_POST['level'], $id]);
            if ($_POST['password'] !== '')
                $pdo->prepare("UPDATE users SET password=? WHERE id_user=?")->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), $id]);
            flash('Pengguna diperbarui.');
        } elseif ($a === 'hapus') {
            if ((int)$_POST['id'] === (int)$_SESSION['user']['id_user']) flash('Tidak dapat menghapus akun sendiri.');
            else { $pdo->prepare("DELETE FROM users WHERE id_user=?")->execute([(int)$_POST['id']]); flash('Pengguna dihapus.'); }
        }
    } catch (PDOException $ex) { flash('Gagal: ' . ($ex->getCode()==23000 ? 'Username sudah dipakai.' : $ex->getMessage())); }
    header('Location: users.php'); exit;
}
$edit = null;
if (isset($_GET['edit'])) { $s = $pdo->prepare("SELECT * FROM users WHERE id_user=?"); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(); }
$rows = $pdo->query("SELECT id_user,username,nama,level FROM users ORDER BY level, nama")->fetchAll();
include 'inc/header.php';
?>
<h3>Data Pengguna</h3>
<p class="text-muted small">Untuk akun Siswa, isi <b>username = NIS</b> siswa agar checklist terhubung otomatis.</p>
<form method="post" class="row g-2 my-3">
  <?= csrf_field() ?>
  <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
  <input type="hidden" name="id" value="<?= e($edit['id_user'] ?? '') ?>">
  <div class="col-md-2"><input name="username" class="form-control" placeholder="Username" required value="<?= e($edit['username'] ?? '') ?>"></div>
  <div class="col-md-3"><input name="nama" class="form-control" placeholder="Nama" required value="<?= e($edit['nama'] ?? '') ?>"></div>
  <div class="col-md-2"><input type="password" name="password" class="form-control" placeholder="<?= $edit ? 'Password (kosongkan)' : 'Password' ?>" <?= $edit ? '' : 'required' ?>></div>
  <div class="col-md-2"><select name="level" class="form-select">
    <?php foreach (['Admin','Guru','Siswa'] as $l): ?><option <?= ($edit['level'] ?? '') === $l ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><button class="btn btn-success"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
    <?php if ($edit): ?><a href="users.php" class="btn btn-secondary">Batal</a><?php endif; ?></div>
</form>
<table class="table table-striped bg-white">
  <thead><tr><th>Username</th><th>Nama</th><th>Level</th><th width="160">Aksi</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e($r['username']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['level']) ?></td>
    <td>
      <a class="btn btn-sm btn-warning" href="?edit=<?= $r['id_user'] ?>">Ubah</a>
      <form method="post" class="d-inline" onsubmit="return confirm('Hapus pengguna ini?')">
        <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $r['id_user'] ?>">
        <button class="btn btn-sm btn-danger">Hapus</button></form>
    </td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php include 'inc/footer.php'; ?>
