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

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Kode Outlet</th>
                <th>Nama Outlet</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($outlets as $outlet): ?>
                <tr>
                    <td><?= $outlet['id'] ?></td>
                    <td><?= $outlet['outlet_code'] ?></td>
                    <td><?= $outlet['outlet_name'] ?></td>
                    <td>
                        <a href="edit.php?id=<?= $outlet['id'] ?>" class="btn btn-primary btn-sm" style="display:inline-flex;">Edit</a>
                        <a href="delete.php?id=<?= $outlet['id'] ?>"
                            class="btn btn-danger btn-sm"
                            style="display:inline-flex;"
                            onclick="return confirm('Hapus outlet ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>