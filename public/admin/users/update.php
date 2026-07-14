<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

CSRF::check($_POST['csrf_token'] ?? '');

$id      = $_POST['id'];
$name    = $_POST['name'];
$role    = $_POST['role'];
$status  = $_POST['status'];
$outlet  = $_POST['outlet_id'] ?: null;
$pass    = $_POST['password'];

$db = Database::connect();

// Fetch current user avatar
$stmt_curr = $db->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt_curr->execute([$id]);
$current_user_data = $stmt_curr->fetch();
$avatar_name = $current_user_data['avatar'] ?? null;

$avatar_file = $_FILES['avatar'] ?? null;
if ($avatar_file && $avatar_file['error'] === UPLOAD_ERR_OK) {
    $upload_dir = ROOT_PATH . '/storage/uploads/avatars';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $ext = strtolower(pathinfo($avatar_file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($ext, $allowed)) {
        $new_avatar = 'avatar_' . $id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($avatar_file['tmp_name'], $upload_dir . '/' . $new_avatar)) {
            if ($avatar_name && file_exists($upload_dir . '/' . $avatar_name)) {
                unlink($upload_dir . '/' . $avatar_name);
            }
            $avatar_name = $new_avatar;
        }
    }
}

if (!empty($pass)) {
    $pass = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        UPDATE users
        SET name=?, password=?, role=?, outlet_id=?, status=?, avatar=?
        WHERE id=?
    ");
    $stmt->execute([$name, $pass, $role, $outlet, $status, $avatar_name, $id]);
} else {
    $stmt = $db->prepare("
        UPDATE users
        SET name=?, role=?, outlet_id=?, status=?, avatar=?
        WHERE id=?
    ");
    $stmt->execute([$name, $role, $outlet, $status, $avatar_name, $id]);
}

$_SESSION['flash_success'] = "User berhasil diperbarui.";
header('Location: index.php');
exit;
