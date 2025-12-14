<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$db = Database::connect();
$stmt = $db->prepare("DELETE FROM outlets WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php');
exit;
