<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$db = Database::connect();
$outlets = $db->query("SELECT * FROM outlets ORDER BY id ASC")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Master Outlet</h2>

<a href="create.php" class="btn btn-primary" style="margin-bottom: 1rem;">
    <i class='bx bx-plus'></i> Tambah Outlet
</a>

<div class="user-grid">
    <?php foreach ($outlets as $outlet): ?>
        <?php 
            $initial = strtoupper(substr($outlet['outlet_name'], 0, 1)); 
        ?>
        <div class="user-card">
            <div class="user-header">
                <div class="user-avatar">
                    <?= $initial ?>
                </div>
                <div class="user-info">
                    <h3><?= htmlspecialchars($outlet['outlet_name']) ?></h3>
                </div>
                <div class="user-actions">
                    <a href="edit.php?id=<?= $outlet['id'] ?>" class="btn-icon text-primary" title="Edit">
                        <i class='bx bx-edit'></i>
                    </a>
                    <form action="delete.php" method="POST" style="margin:0;" onsubmit="return confirm('Yakin ingin menghapus outlet ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $outlet['id'] ?>">
                        <button type="submit" class="btn-icon text-danger" title="Hapus">
                            <i class='bx bx-trash'></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="user-tags">
                <span class="text-sm text-muted">
                    <i class='bx bx-barcode'></i> Kode: <?= htmlspecialchars($outlet['outlet_code']) ?>
                </span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>