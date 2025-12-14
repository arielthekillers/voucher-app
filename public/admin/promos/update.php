<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/middleware/auth.php';

auth_required();

$id     = $_POST['id'];
$title  = $_POST['title'];
$desc   = $_POST['description'];
$point  = $_POST['point_cost'];
$active = $_POST['is_active'];

$db = Database::connect();

$imageSql = '';
$params = [$title, $desc, $point, $active];

if (!empty($_FILES['image']['name'])) {
    $imageName = time() . '_' . $_FILES['image']['name'];
    move_uploaded_file(
        $_FILES['image']['tmp_name'],
        '../../../storage/uploads/promos/' . $imageName
    );
    $imageSql = ', image=?';
    $params[] = $imageName;
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
