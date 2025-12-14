<?php
session_start();

require_once '../../../vendor/autoload.php';

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

<div class="card">
    <form action="update.php" method="POST">
        <input type="hidden" name="id" value="<?= $c['id'] ?>">

        <label>Nama</label>
        <input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>" required>

        <label>No WhatsApp</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($c['phone']) ?>" required>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>