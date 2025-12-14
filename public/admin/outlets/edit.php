<?php
session_start();

require_once '../../../vendor/autoload.php';

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

<div class="card">
    <form action="update.php" method="POST">
        <input type="hidden" name="id" value="<?= $outlet['id'] ?>">

        <label>Kode Outlet</label>
        <input type="text" name="outlet_code"
            value="<?= htmlspecialchars($outlet['outlet_code']) ?>" required>

        <label>Nama Outlet</label>
        <input type="text" name="outlet_name"
            value="<?= htmlspecialchars($outlet['outlet_name']) ?>" required>

        <div style="margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-danger" style="margin-left: 0.5rem;">Batal</a>
        </div>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>