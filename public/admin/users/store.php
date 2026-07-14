<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

CSRF::check($_POST['csrf_token'] ?? '');

$name   = trim($_POST['name']);
$user   = trim($_POST['username']);
$pass   = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role   = $_POST['role'];
$outlet = $_POST['outlet_id'] ?: null;
$avatar_file = $_FILES['avatar'] ?? null;
$avatar_name = null;

if ($avatar_file && $avatar_file['error'] === UPLOAD_ERR_OK) {
    $upload_dir = ROOT_PATH . '/storage/uploads/avatars';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $ext = strtolower(pathinfo($avatar_file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($ext, $allowed)) {
        $avatar_name = 'avatar_' . uniqid() . '.' . $ext;
        move_uploaded_file($avatar_file['tmp_name'], $upload_dir . '/' . $avatar_name);
    }
}

$db = Database::connect();

$stmt = $db->prepare("
    INSERT INTO users (name, username, password, role, outlet_id, avatar)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$name, $user, $pass, $role, $outlet, $avatar_name]);

$_SESSION['flash_success'] = "User berhasil ditambahkan.";
header('Location: index.php');
exit;
