<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$id    = $_POST['id'];
$name  = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');

$db = Database::connect();

if (!$name || !$phone) {
    $_SESSION['flash_error'] = "Nama dan Nomor HP wajib diisi.";
    header('Location: edit.php?id=' . $id);
    exit;
}

/* Cegah duplikat nomor */
$check = $db->prepare("
    SELECT id FROM customers WHERE phone = ? AND id != ?
");
$check->execute([$phone, $id]);

if ($check->fetch()) {
    $_SESSION['flash_error'] = "Nomor HP sudah digunakan customer lain.";
    header('Location: edit.php?id=' . $id);
    exit;
}

$stmt = $db->prepare("
    UPDATE customers SET name=?, phone=?
    WHERE id=?
");
$stmt->execute([$name, $phone, $id]);

$_SESSION['flash_success'] = "Data Customer berhasil diperbarui.";
header('Location: index.php');
exit;
