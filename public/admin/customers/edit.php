<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/middleware/auth.php';

auth_required();

$id = (int) $_GET['id'];

$db = Database::connect();

$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) {
    exit('Customer tidak ditemukan');
}
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Edit Customer</h2>

<form action="update.php" method="POST">
    <input type="hidden" name="id" value="<?= $c['id'] ?>">

    <label>Nama</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>" required><br><br>

    <label>No WhatsApp</label><br>
    <input type="text" name="phone" value="<?= htmlspecialchars($c['phone']) ?>" required><br><br>

    <button type="submit">Update</button>
</form>

<?php include '../../../resources/views/layouts/footer.php'; ?>