<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$db = Database::connect();

$users = $db->query("
    SELECT u.*, o.outlet_name
    FROM users u
    LEFT JOIN outlets o ON u.outlet_id = o.id
    ORDER BY u.id DESC
")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>User Management</h2>
<a href="create.php" class="btn btn-primary" style="margin-bottom: 1rem;">
    <i class='bx bx-plus'></i> Tambah User
</a>

<div class="table-container">
    <table cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Username</th>
                <th>Role</th>
                <th>Outlet</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= $u['role'] ?></td>
                    <td><?= $u['outlet_name'] ?? '-' ?></td>
                    <td><?= $u['status'] ?></td>
                    <td>
                        <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-primary btn-sm" style="display:inline-flex;">Edit</a>
                        <a href="delete.php?id=<?= $u['id'] ?>"
                            class="btn btn-danger btn-sm"
                            style="display:inline-flex;"
                            onclick="return confirm('Hapus user ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>