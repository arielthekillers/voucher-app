<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/core/Auth.php';
require_once '../../../app/middleware/auth.php';

role_required('super_admin');

$id      = $_POST['id'];
$name    = $_POST['name'];
$user    = $_POST['username'];
$role    = $_POST['role'];
$status  = $_POST['status'];
$outlet  = $_POST['outlet_id'] ?: null;
$pass    = $_POST['password'];

$db = Database::connect();

if (!empty($pass)) {
    $pass = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        UPDATE users
        SET name=?, username=?, password=?, role=?, outlet_id=?, status=?
        WHERE id=?
    ");
    $stmt->execute([$name, $user, $pass, $role, $outlet, $status, $id]);
} else {
    $stmt = $db->prepare("
        UPDATE users
        SET name=?, username=?, role=?, outlet_id=?, status=?
        WHERE id=?
    ");
    $stmt->execute([$name, $user, $role, $outlet, $status, $id]);
}

header('Location: index.php');
exit;
