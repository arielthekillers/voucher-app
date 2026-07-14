<?php
session_start();
require_once '../../../vendor/autoload.php';

role_required('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check($_POST['csrf_token'] ?? '');
    
    $id = (int) $_POST['id'];
    $db = Database::connect();
    
    try {
        $stmt = $db->prepare("UPDATE purchase_nominals SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash_success'] = "Status nominal berhasil diubah.";
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Gagal mengubah status: " . $e->getMessage();
    }
}

header('Location: index.php');
exit;
