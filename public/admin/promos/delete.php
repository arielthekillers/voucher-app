<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();
role_required('super_admin');

$id = (int) $_GET['id'];

$db = Database::connect();

// Get image filename first
$stmt = $db->prepare("SELECT image FROM promos WHERE id = ?");
$stmt->execute([$id]);
$promo = $stmt->fetch();

try {
    // Check if promo is used in transactions first to avoid deleting image if DB delete fails
    // or rely on DB transaction? Better: catch exception, then delete image only if successful? 
    // Actually, simple constraint check via try/catch is enough. 
    // BUT we shouldn't delete the image BEFORE the DB delete if DB delete might fail.
    
    $stmt = $db->prepare("DELETE FROM promos WHERE id = ?");
    $stmt->execute([$id]);

    // Only delete image if DB delete successful
    if ($promo && $promo['image']) {
        $filePath = '../../../storage/uploads/promos/' . $promo['image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    flash('success', 'Promo berhasil dihapus');
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        flash('error', 'Gagal menghapus: Promo ini sudah digunakan dalam transaksi.');
    } else {
        flash('error', 'Gagal menghapus: ' . $e->getMessage());
    }
    header('Location: index.php');
    exit;
}
