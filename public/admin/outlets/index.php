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

<a href="create.php">+ Tambah Outlet</a>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Kode Outlet</th>
        <th>Nama Outlet</th>
        <th>Aksi</th>
    </tr>

    <?php foreach ($outlets as $outlet): ?>
        <tr>
            <td><?= $outlet['id'] ?></td>
            <td><?= $outlet['outlet_code'] ?></td>
            <td><?= $outlet['outlet_name'] ?></td>
            <td>
                <a href="edit.php?id=<?= $outlet['id'] ?>">Edit</a> |
                <a href="delete.php?id=<?= $outlet['id'] ?>"
                    onclick="return confirm('Hapus outlet ini?')">Hapus</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include '../../../resources/views/layouts/footer.php'; ?>