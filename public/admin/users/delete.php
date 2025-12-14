<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$id = (int) $_GET['id'];

$db = Database::connect();

$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php');
exit;
