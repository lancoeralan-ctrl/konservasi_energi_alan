<?php
require 'config.php'; need_login();
csrf_check();
$lv = $_SESSION['user']['level'];
$tahapan = ['SEBELUM' => 'Sebelum Pembelajaran', 'SELAMA' => 'Selama Pembelajaran', 'SESUDAH' => 'Sesudah Pembelajaran', '5R' => '5R'];
$perangkat = $pdo->query("SELECT * FROM perangkat ORDER BY nama_perangkat")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis = $lv === 'Siswa' ? (int)$_SESSION['user']['username'] : (int)($_POST['nis'] ?? 0);
    $cek = $pdo->prepare("SELECT 1 FROM siswa WHERE nis=?"); $cek->execute([$nis]);
    if (!$cek->fetch()) { flash('NIS tidak ditemukan di data siswa.'); header('Location: checklist_form.php'); exit; }

    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO checklist (nis,tanggal,jam) VALUES (?,?,?)")
            ->execute([$nis, $_POST['tanggal'], $_POST['jam']]);
        $idc = $pdo->lastInsertId();
        $ins = $pdo->prepare("INSERT INTO detail_checklist (id_checklist,id_perangkat,tahapan,kondisi,status,keterangan) VALUES (?,?,?,?,?,?)");
        $n = 0;
        foreach ($tahapan as $t => $_) {
            foreach ($_POST['d'][$t] ?? [] as $idp => $v) {
                if (!in_array($v['status'] ?? '', ['Ya','Tidak'])) continue;
                $ins->execute([$idc, (int)$idp, $t, trim($v['kondisi'] ?? '') ?: null, $v['status'], trim($v['ket'] ?? '') ?: null]);
                $n++;
            }
        }
        if ($n === 0) throw new Exception('Isi minimal satu item.');
        $pdo->commit();
        flash("Checklist tersimpan ($n item)."); header('Location: checklist.php'); exit;
    } catch (Exception $ex) {
        $pdo->rollBack(); flash('Gagal: ' . $ex->getMessage()); header('Location: checklist_form.php'); exit;
    }
}
$siswa = $lv === 'Siswa' ? [] : $pdo->query("SELECT * FROM siswa ORDER BY kelas, nama")->fetchAll();
include 'inc/header.php';
?>
<h3>Isi Checklist Konservasi Energi</h3>
<?php if (!$perangkat): ?>
  <div class="alert alert-warning">Belum ada data perangkat. Tambahkan dulu di menu Perangkat.</div>
<?php else: ?>
<form method="post" class="mt-3">
  <?= csrf_field() ?>
  <div class="row g-2 mb-3">
    <?php if ($lv !== 'Siswa'): ?>
    <div class="col-md-5"><label class="form-label">Siswa</label>
      <select name="nis" class="form-select" required><option value="">-- pilih --</option>
        <?php foreach ($siswa as $s): ?><option value="<?= $s['nis'] ?>"><?= e($s['kelas'].' - '.$s['nama'].' ('.$s['nis'].')') ?></option><?php endforeach; ?>
      </select></div>
    <?php endif; ?>
    <div class="col-md-3"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
    <div class="col-md-2"><label class="form-label">Jam</label><input type="time" name="jam" class="form-control" value="<?= date('H:i') ?>" required></div>
  </div>

  <?php foreach ($tahapan as $t => $label): ?>
  <div class="card mb-3">
    <div class="card-header"><?= e($label) ?></div>
    <div class="table-responsive"><table class="table mb-0 align-middle">
      <thead><tr><th>Perangkat</th><th>Status</th><th>Kondisi</th><th>Keterangan</th></tr></thead>
      <tbody>
      <?php foreach ($perangkat as $p): $n = "d[$t][{$p['id_perangkat']}]"; ?>
        <tr>
          <td><?= e($p['nama_perangkat']) ?></td>
          <td class="text-nowrap">
            <label class="me-2"><input type="radio" name="<?= $n ?>[status]" value="Ya"> Ya</label>
            <label><input type="radio" name="<?= $n ?>[status]" value="Tidak"> Tidak</label>
          </td>
          <td><input name="<?= $n ?>[kondisi]" maxlength="100" class="form-control form-control-sm"></td>
          <td><input name="<?= $n ?>[ket]" class="form-control form-control-sm"></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
  <?php endforeach; ?>
  <button class="btn btn-success">Simpan Checklist</button>
  <a href="checklist.php" class="btn btn-secondary">Batal</a>
</form>
<?php endif; ?>
<?php include 'inc/footer.php'; ?>
