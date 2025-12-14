<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$db = Database::connect();
$promos = $db->query("SELECT * FROM promos ORDER BY id DESC")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Promo</h2>
<a href="create.php" class="btn btn-primary" style="margin-bottom: 1rem;">
    <i class='bx bx-plus'></i> Tambah Promo
</a>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Judul</th>
                <th>Point</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($promos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['title']) ?></td>
                    <td><?= $p['point_cost'] ?></td>
                    <td><?= $p['is_active'] ? 'Aktif' : 'Nonaktif' ?></td>
                    <td>
                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm" style="display:inline-flex;">Edit</a>
                        <a href="delete.php?id=<?= $p['id'] ?>"
                            class="btn btn-danger btn-sm"
                            style="display:inline-flex;"
                            onclick="return confirm('Hapus promo ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>