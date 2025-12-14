<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$code = trim($_POST['outlet_code']);
$name = trim($_POST['outlet_name']);

$db = Database::connect();

$stmt = $db->prepare("
    INSERT INTO outlets (outlet_code, outlet_name)
    VALUES (?, ?)
");
$stmt->execute([$code, $name]);

header('Location: index.php');
exit;
