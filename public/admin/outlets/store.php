<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/core/Auth.php';
require_once '../../../app/middleware/auth.php';

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
