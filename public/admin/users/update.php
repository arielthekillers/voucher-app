<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

CSRF::check($_POST['csrf_token'] ?? '');

$id      = $_POST['id'];
$name    = $_POST['name'];
$role    = $_POST['role'];
$status  = $_POST['status'];
$outlet  = $_POST['outlet_id'] ?: null;
$pass    = $_POST['password'];

$db = Database::connect();

if (!empty($pass)) {
    $pass = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        UPDATE users
        SET name=?, password=?, role=?, outlet_id=?, status=?
        WHERE id=?
    ");
    $stmt->execute([$name, $pass, $role, $outlet, $status, $id]);
} else {
    $stmt = $db->prepare("
        UPDATE users
        SET name=?, role=?, outlet_id=?, status=?
        WHERE id=?
    ");
    $stmt->execute([$name, $role, $outlet, $status, $id]);
}

$_SESSION['flash_success'] = "User berhasil diperbarui.";
header('Location: index.php');
exit;
