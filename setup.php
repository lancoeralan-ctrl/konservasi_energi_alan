<?php
// Jalankan sekali untuk membuat akun admin awal, lalu HAPUS file ini.
require 'config.php';
$n = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($n == 0) {
    $pdo->prepare("INSERT INTO users (username,password,nama,level) VALUES (?,?,?,'Admin')")
        ->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'Administrator']);
    echo 'Admin dibuat. Username: admin | Password: admin123. Segera hapus setup.php!';
} else {
    echo 'Sudah ada pengguna. Hapus file setup.php.';
}
