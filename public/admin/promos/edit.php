<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();
role_required('super_admin');

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

<div class="card">
    <form action="update.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $p['id'] ?>">

        <label>Judul</label>
        <input type="text" name="title" value="<?= htmlspecialchars($p['title']) ?>" required>

        <label>Deskripsi</label>
        <textarea name="description"><?= htmlspecialchars($p['description']) ?></textarea>

        <?php if ($p['image']): ?>
            <div style="margin-bottom: 1rem;">
                <img src="<?= BASE_URL; ?>/storage/uploads/promos/<?= $p['image'] ?>" width="120" style="border-radius: 8px;">
            </div>
        <?php endif; ?>

        <label>Ganti Gambar</label>
        <input type="file" name="image">

        <label><?= CURRENCY_NAME ?> Dibutuhkan</label>
        <input type="number" name="point_cost" value="<?= $p['point_cost'] ?>" required>

        <label>Status</label>
        <select name="is_active">
            <option value="1" <?= $p['is_active'] ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= !$p['is_active'] ? 'selected' : '' ?>>Nonaktif</option>
        </select>

        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>