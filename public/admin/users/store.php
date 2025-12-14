<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$name   = trim($_POST['name']);
$user   = trim($_POST['username']);
$pass   = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role   = $_POST['role'];
$outlet = $_POST['outlet_id'] ?: null;

$db = Database::connect();

$stmt = $db->prepare("
    INSERT INTO users (name, username, password, role, outlet_id)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$name, $user, $pass, $role, $outlet]);

header('Location: index.php');
exit;
