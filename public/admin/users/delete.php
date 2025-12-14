<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/core/Auth.php';
require_once '../../../app/middleware/auth.php';

role_required('super_admin');

$id = (int) $_GET['id'];

$db = Database::connect();

$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php');
exit;
