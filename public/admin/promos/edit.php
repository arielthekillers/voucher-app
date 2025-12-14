<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$id = (int) $_GET['id'];

$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM promos WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) exit('Promo tidak ditemukan');
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Edit Promo</h2>

<form action="update.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $p['id'] ?>">

    <label>Judul</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($p['title']) ?>" required><br><br>

    <label>Deskripsi</label><br>
    <textarea name="description"><?= htmlspecialchars($p['description']) ?></textarea><br><br>

    <?php if ($p['image']): ?>
        <img src="<?= BASE_URL; ?>/storage/uploads/promos/<?= $p['image'] ?>" width="120"><br><br>
    <?php endif; ?>

    <label>Ganti Gambar</label><br>
    <input type="file" name="image"><br><br>

    <label>Point Dibutuhkan</label><br>
    <input type="number" name="point_cost" value="<?= $p['point_cost'] ?>" required><br><br>

    <label>Status</label><br>
    <select name="is_active">
        <option value="1" <?= $p['is_active'] ? 'selected' : '' ?>>Aktif</option>
        <option value="0" <?= !$p['is_active'] ? 'selected' : '' ?>>Nonaktif</option>
    </select><br><br>

    <button type="submit">Update</button>
</form>

<?php include '../../../resources/views/layouts/footer.php'; ?>