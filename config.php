<?php
// ====== KONFIGURASI DATABASE ======
session_start();
$DB_HOST = 'localhost';
$DB_NAME = 'konservasi_energi_alan';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    exit('Koneksi database gagal: ' . htmlspecialchars($ex->getMessage()));
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function need_login(array $levels = null) {
    if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
    if ($levels && !in_array($_SESSION['user']['level'], $levels)) {
        http_response_code(403); exit('Akses ditolak.');
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']))) {
        http_response_code(400); exit('Token tidak valid.');
    }
}
function flash($msg = null) {
    if ($msg !== null) { $_SESSION['flash'] = $msg; return; }
    $m = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']); return $m;
}
