<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$id = (int) $_GET['id'];

$db = Database::connect();

$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['flash_success'] = "User berhasil dihapus.";
header('Location: index.php');
exit;
