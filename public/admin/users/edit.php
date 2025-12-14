<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$id = (int) $_GET['id'];

$db = Database::connect();

$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$id]);
$user = $user->fetch();

if (!$user) {
    exit('User tidak ditemukan');
}

$outlets = $db->query("SELECT * FROM outlets ORDER BY outlet_name")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Edit User</h2>

<div class="card">
    <form action="update.php" method="POST">
        <input type="hidden" name="id" value="<?= $user['id'] ?>">

        <label>Nama</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Password (kosongkan jika tidak diubah)</label>
        <input type="password" name="password">

        <label>Role</label>
        <select name="role">
            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="super_admin" <?= $user['role'] == 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
        </select>

        <label>Outlet</label>
        <select name="outlet_id">
            <option value="">-- Tidak ada --</option>
            <?php foreach ($outlets as $o): ?>
                <option value="<?= $o['id'] ?>"
                    <?= $user['outlet_id'] == $o['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($o['outlet_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Status</label>
        <select name="status">
            <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $user['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>