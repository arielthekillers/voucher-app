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
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $user['id'] ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" readonly title="Username tidak dapat diubah">
            </div>

            <div class="form-group">
                <label>Password (kosongkan jika tidak diubah)</label>
                <input type="password" name="password">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="super_admin" <?= $user['role'] == 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
            </div>

            <div class="form-group">
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
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $user['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>