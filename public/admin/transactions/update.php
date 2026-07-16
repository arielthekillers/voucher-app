<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid method');
}

CSRF::check($_POST['csrf_token'] ?? '');

$id = $_POST['id'] ?? null;
$purchase_amount = (float) $_POST['purchase_amount'];
$point_amount = (int) $_POST['point_amount'];

if (!$id) {
    header('Location: history.php');
    exit;
}

$db = Database::connect();

try {
    $stmt = $db->prepare("UPDATE transactions SET purchase_amount = ?, point_amount = ? WHERE id = ?");
    $stmt->execute([$purchase_amount, $point_amount, $id]);
    
    $_SESSION['flash_success'] = "Data transaksi berhasil diperbarui.";
} catch (Exception $e) {
    $_SESSION['flash_error'] = "Gagal memperbarui transaksi: " . $e->getMessage();
}

$return_url = $_POST['return_url'] ?? 'history.php';
header('Location: ' . $return_url);
exit;
