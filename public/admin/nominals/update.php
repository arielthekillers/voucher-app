<?php
session_start();
require_once '../../../vendor/autoload.php';

role_required('super_admin');
CSRF::check($_POST['csrf_token'] ?? '');

$id = (int) $_POST['id'];
$amount = (float) $_POST['amount'];

if ($amount <= 0) {
    $_SESSION['flash_error'] = "Nominal harus lebih dari 0";
    header('Location: edit.php?id=' . $id);
    exit;
}

$db = Database::connect();

try {
    $stmt = $db->prepare("UPDATE purchase_nominals SET amount = ? WHERE id = ?");
    $stmt->execute([$amount, $id]);
    $_SESSION['flash_success'] = "Nominal belanja berhasil diperbarui!";
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "Gagal menyimpan: " . $e->getMessage();
}

header('Location: index.php');
exit;
