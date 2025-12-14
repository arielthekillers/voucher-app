<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$title  = trim($_POST['title']);
$desc   = $_POST['description'];
$point  = (int) $_POST['point_cost'];
$active = (int) $_POST['is_active'];

$imageName = null;

if (!empty($_FILES['image']['name'])) {
    $imageName = time() . '_' . $_FILES['image']['name'];
    move_uploaded_file(
        $_FILES['image']['tmp_name'],
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
