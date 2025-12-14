<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$id = (int) $_GET['id'];

$db = Database::connect();

// Get image filename first
$stmt = $db->prepare("SELECT image FROM promos WHERE id = ?");
$stmt->execute([$id]);
$promo = $stmt->fetch();

if ($promo && $promo['image']) {
    $filePath = '../../../storage/uploads/promos/' . $promo['image'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

$stmt = $db->prepare("DELETE FROM promos WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php?status=deleted');
exit;
