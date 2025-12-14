<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$id = (int) $_GET['id'];

$db = Database::connect();
$stmt = $db->prepare("DELETE FROM promos WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php');
exit;
