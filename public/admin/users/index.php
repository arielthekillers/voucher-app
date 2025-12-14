<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/core/Auth.php';
require_once '../../../app/middleware/auth.php';

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
<a href="create.php">+ Tambah User</a>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Nama</th>
        <th>Username</th>
        <th>Role</th>
        <th>Outlet</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= $u['role'] ?></td>
            <td><?= $u['outlet_name'] ?? '-' ?></td>
            <td><?= $u['status'] ?></td>
            <td>
                <a href="edit.php?id=<?= $u['id'] ?>">Edit</a> |
                <a href="delete.php?id=<?= $u['id'] ?>"
                    onclick="return confirm('Hapus user ini?')">Hapus</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include '../../../resources/views/layouts/footer.php'; ?>