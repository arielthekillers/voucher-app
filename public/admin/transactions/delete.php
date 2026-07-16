<?php
session_start();
require_once '../../../vendor/autoload.php';

role_required('super_admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid method');
}

CSRF::check($_POST['csrf_token'] ?? '');

$id = $_POST['id'] ?? null;
$return_url = $_POST['return_url'] ?? $_SERVER['HTTP_REFERER'] ?? 'history.php';

if (!$id) {
    header('Location: ' . $return_url);
    exit;
}

$db = Database::connect();

try {
    $stmt = $db->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['flash_success'] = "Transaksi berhasil dihapus.";
} catch (Exception $e) {
    $_SESSION['flash_error'] = "Gagal menghapus transaksi: " . $e->getMessage();
}

header('Location: ' . $return_url);
exit;
