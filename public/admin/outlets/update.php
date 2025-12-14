<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$id   = $_POST['id'] ?? null;
$code = trim($_POST['outlet_code'] ?? '');
$name = trim($_POST['outlet_name'] ?? '');

if (!$id || !$code || !$name) {
    die('Data tidak lengkap');
}

$db = Database::connect();

$stmt = $db->prepare("
    UPDATE outlets
    SET outlet_code = ?, outlet_name = ?
    WHERE id = ?
");
$stmt->execute([$code, $name, $id]);

header('Location: index.php');
exit;
