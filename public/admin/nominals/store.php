<?php
session_start();
require_once '../../../vendor/autoload.php';

role_required('super_admin');
CSRF::check($_POST['csrf_token'] ?? '');

$amount = (float) $_POST['amount'];

if ($amount <= 0) {
    $_SESSION['flash_error'] = "Nominal harus lebih dari 0";
    header('Location: create.php');
    exit;
}

$db = Database::connect();

try {
    $stmt = $db->prepare("INSERT INTO purchase_nominals (amount, is_active) VALUES (?, 1)");
    $stmt->execute([$amount]);
    $_SESSION['flash_success'] = "Nominal belanja berhasil ditambahkan!";
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "Gagal menyimpan: " . $e->getMessage();
}

header('Location: index.php');
exit;
