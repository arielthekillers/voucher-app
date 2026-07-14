<?php
session_start();
require_once '../../../vendor/autoload.php';

role_required('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check($_POST['csrf_token'] ?? '');
    
    $id = (int) $_POST['id'];
    $db = Database::connect();
    
    try {
        $stmt = $db->prepare("DELETE FROM purchase_nominals WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash_success'] = "Nominal berhasil dihapus.";
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Gagal menghapus nominal: " . $e->getMessage();
    }
}

header('Location: index.php');
exit;
