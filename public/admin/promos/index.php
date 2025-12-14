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
<a href="create.php">+ Tambah Promo</a>

<table border="1" cellpadding="8">
    <tr>
        <th>Judul</th>
        <th>Point</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

    <?php foreach ($promos as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['title']) ?></td>
            <td><?= $p['point_cost'] ?></td>
            <td><?= $p['is_active'] ? 'Aktif' : 'Nonaktif' ?></td>
            <td>
                <a href="edit.php?id=<?= $p['id'] ?>">Edit</a> |
                <a href="delete.php?id=<?= $p['id'] ?>"
                    onclick="return confirm('Hapus promo ini?')">Hapus</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include '../../../resources/views/layouts/footer.php'; ?>