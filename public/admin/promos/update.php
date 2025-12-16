<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();
role_required('super_admin');

CSRF::check($_POST['csrf_token'] ?? '');

$id     = (int) $_POST['id'];
$title  = $_POST['title'];
$desc   = $_POST['description'];
$point  = $_POST['point_cost'];
$active = $_POST['is_active'];

$db = Database::connect();

$imageSql = '';
$params = [$title, $desc, $point, $active]; // Handle Image Upload
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

    if ($file['size'] > 5 * 1024 * 1024) {
        die('File too large. Max 5MB.');
    }

    $imageName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    move_uploaded_file(
        $file['tmp_name'],
        '../../../storage/uploads/promos/' . $imageName
    );
    $imageSql = ', image=?';
    $params[] = $imageName;

    // Delete old image
    $stmt_old = $db->prepare("SELECT image FROM promos WHERE id = ?");
    $stmt_old->execute([$id]);
    $old_image = $stmt_old->fetchColumn();

    if ($old_image && file_exists('../../../storage/uploads/promos/' . $old_image)) {
        unlink('../../../storage/uploads/promos/' . $old_image);
    }
}

$params[] = $id;

$stmt = $db->prepare("
    UPDATE promos
    SET title=?, description=?, point_cost=?, is_active=?
    $imageSql
    WHERE id=?
");

$stmt->execute($params);

header('Location: index.php');
exit;
