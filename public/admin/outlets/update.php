<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

CSRF::check($_POST['csrf_token'] ?? '');

$id   = $_POST['id'] ?? null;
$name = trim($_POST['outlet_name'] ?? '');

if (!$id || !$name) {
    $_SESSION['flash_error'] = "Data tidak lengkap";
    header('Location: edit.php?id=' . $id);
    exit;
}

$db = Database::connect();

$stmt = $db->prepare("
    UPDATE outlets
    SET outlet_name = ?
    WHERE id = ?
");
$stmt->execute([$name, $id]);

$_SESSION['flash_success'] = "Outlet berhasil diperbarui.";
header('Location: index.php');
exit;
