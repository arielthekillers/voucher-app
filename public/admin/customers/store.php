<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/middleware/auth.php';

auth_required();

$name  = trim($_POST['name']);
$phone = trim($_POST['phone']);

if (!$name || !$phone) {
    exit('Data tidak lengkap');
}

$db = Database::connect();

/* Cegah nomor HP dobel */
$check = $db->prepare("SELECT id FROM customers WHERE phone = ?");
$check->execute([$phone]);
if ($check->fetch()) {
    exit('Nomor HP sudah terdaftar');
}

$stmt = $db->prepare("
    INSERT INTO customers (name, phone)
    VALUES (?, ?)
");
$stmt->execute([$name, $phone]);

header('Location: index.php');
exit;
