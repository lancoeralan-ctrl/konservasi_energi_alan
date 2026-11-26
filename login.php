<?php
require 'config.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $st = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $st->execute([trim($_POST['username'] ?? '')]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        unset($u['password']);
        $_SESSION['user'] = $u;
        header('Location: index.php'); exit;
    }
    $err = 'Username atau password salah.';
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Konservasi Energi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet"></head>
<body>
<div class="login-wrap"><div class="container" style="max-width:420px">
  <div class="card login-card"><div class="card-body p-4">
    <div class="login-logo">⚡</div>
    <h4 class="text-center text-dark mb-1" style="color:#0b1f4d !important">Konservasi Energi</h4>
    <p class="text-center small mb-3" style="color:#dc2626">Silakan masuk untuk melanjutkan</p>
    <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
    <form method="post"><?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control" required autofocus></div>
      <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      <button class="btn btn-success w-100 py-2">Masuk</button>
    </form>
  </div></div>
</div></div></body></html>
