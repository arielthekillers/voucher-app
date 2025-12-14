<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/middleware/auth.php';

auth_required();

$id = (int) $_GET['id'];

$db = Database::connect();
$stmt = $db->prepare("DELETE FROM promos WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php');
exit;
