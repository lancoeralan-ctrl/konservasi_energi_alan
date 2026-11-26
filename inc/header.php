<?php
$u = $_SESSION['user']; $lv = $u['level'];
$cur = basename($_SERVER['PHP_SELF']);
function nav($file, $label, $cur) {
    return '<li class="nav-item"><a class="nav-link '.($cur===$file?'active':'').'" href="'.$file.'">'.$label.'</a></li>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Konservasi Energi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">⚡ Konservasi Energi</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#m"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="m">
      <ul class="navbar-nav me-auto">
        <?= nav('index.php','Dashboard',$cur) ?>
        <?= nav('checklist.php','Checklist',$cur) ?>
        <?php if (in_array($lv,['Admin','Guru'])): ?>
          <?= nav('siswa.php','Siswa',$cur) ?>
          <?= nav('perangkat.php','Perangkat',$cur) ?>
        <?php endif; ?>
        <?php if ($lv==='Admin') echo nav('users.php','Pengguna',$cur); ?>
      </ul>
      <span class="navbar-text me-3"><?= e($u['nama']) ?> (<?= e($lv) ?>)</span>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Keluar</a>
    </div>
  </div>
</nav>
<div class="container py-4">
<?php if ($m = flash()): ?><div class="alert alert-info"><?= e($m) ?></div><?php endif; ?>
