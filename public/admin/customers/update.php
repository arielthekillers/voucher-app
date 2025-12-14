<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$id    = $_POST['id'];
$name  = trim($_POST['name']);
$phone = trim($_POST['phone']);

$db = Database::connect();

/* Cegah duplikat nomor */
$check = $db->prepare("
    SELECT id FROM customers WHERE phone = ? AND id != ?
");
$check->execute([$phone, $id]);

if ($check->fetch()) {
    exit('Nomor HP sudah digunakan customer lain');
}

$stmt = $db->prepare("
    UPDATE customers SET name=?, phone=?
    WHERE id=?
");
$stmt->execute([$name, $phone, $id]);

header('Location: index.php');
exit;
