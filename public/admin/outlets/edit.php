<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/core/Auth.php';
require_once '../../../app/middleware/auth.php';

role_required('super_admin');

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM outlets WHERE id = ?");
$stmt->execute([$id]);
$outlet = $stmt->fetch();

if (!$outlet) {
    echo "Outlet tidak ditemukan";
    exit;
}
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Edit Outlet</h2>

<form action="update.php" method="POST">
    <input type="hidden" name="id" value="<?= $outlet['id'] ?>">

    <label>Kode Outlet</label><br>
    <input type="text" name="outlet_code"
        value="<?= htmlspecialchars($outlet['outlet_code']) ?>" required><br><br>

    <label>Nama Outlet</label><br>
    <input type="text" name="outlet_name"
        value="<?= htmlspecialchars($outlet['outlet_name']) ?>" required><br><br>

    <button type="submit">Update</button>
    <a href="index.php">Batal</a>
</form>

<?php include '../../../resources/views/layouts/footer.php'; ?>