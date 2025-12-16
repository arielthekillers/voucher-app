<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();
role_required('super_admin');

CSRF::check($_POST['csrf_token'] ?? '');

$title  = trim($_POST['title']);
$desc   = $_POST['description'];
$point  = (int) $_POST['point_cost'];
$active = (int) $_POST['is_active'];

$imageName = null;

$imageName = null;

if (!empty($_FILES['image']['name'])) {
    $file = $_FILES['image'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($mime, $allowedMimes) || !in_array($ext, $allowedExts)) {
        die('Invalid file type. Only JPG, PNG, GIF, WEBP allowed.');
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        die('File too large. Max 5MB.');
    }

    $imageName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    move_uploaded_file(
        $file['tmp_name'],
        '../../../storage/uploads/promos/' . $imageName
    );
}

$db = Database::connect();

$stmt = $db->prepare("
    INSERT INTO promos (title, description, image, point_cost, is_active)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$title, $desc, $imageName, $point, $active]);

header('Location: index.php');
exit;
