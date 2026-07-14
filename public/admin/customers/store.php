<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

CSRF::check($_POST['csrf_token'] ?? '');

$name  = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');

$phone = preg_replace('/[^0-9]/', '', $phone);
if (strpos($phone, '08') === 0) {
    $phone = '62' . substr($phone, 1);
} elseif (strpos($phone, '8') === 0) {
    $phone = '62' . $phone;
}

if (!$name || !$phone) {
    $_SESSION['flash_error'] = "Nama dan Nomor HP wajib diisi.";
    header('Location: create.php');
    exit;
}

if (strlen($phone) < 9 || strlen($phone) > 15) {
    $_SESSION['flash_error'] = "Format Nomor HP tidak valid (9-15 angka).";
    header('Location: create.php');
    exit;
}

$db = Database::connect();

/* Cegah nomor HP dobel */
$check = $db->prepare("SELECT id FROM customers WHERE phone = ?");
$check->execute([$phone]);
if ($check->fetch()) {
    $_SESSION['flash_error'] = "Nomor HP sudah terdaftar. Gunakan nomor lain.";
    header('Location: create.php');
    exit;
}

$stmt = $db->prepare("
    INSERT INTO customers (name, phone)
    VALUES (?, ?)
");
$stmt->execute([$name, $phone]);

$_SESSION['flash_success'] = "Customer berhasil ditambahkan.";
header('Location: index.php');
exit;
