<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Method not allowed');
}

CSRF::check($_POST['csrf_token'] ?? '');

$id = $_POST['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$db = Database::connect();
$stmt = $db->prepare("DELETE FROM outlets WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['flash_success'] = "Outlet berhasil dihapus.";
header('Location: index.php');
exit;
