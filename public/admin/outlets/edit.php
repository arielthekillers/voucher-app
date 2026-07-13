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
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $outlet['id'] ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Kode Outlet</label>
                <input type="text" name="outlet_code" value="<?= htmlspecialchars($outlet['outlet_code']) ?>" readonly title="Kode Outlet tidak dapat diubah">
            </div>

            <div class="form-group">
                <label>Nama Outlet</label>
                <input type="text" name="outlet_name" value="<?= htmlspecialchars($outlet['outlet_name']) ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>