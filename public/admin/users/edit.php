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

<form action="update.php" method="POST">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">

    <label>Nama</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br><br>

    <label>Username</label><br>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>

    <label>Password (kosongkan jika tidak diubah)</label><br>
    <input type="password" name="password"><br><br>

    <label>Role</label><br>
    <select name="role">
        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="super_admin" <?= $user['role'] == 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
    </select><br><br>

    <label>Outlet</label><br>
    <select name="outlet_id">
        <option value="">-- Tidak ada --</option>
        <?php foreach ($outlets as $o): ?>
            <option value="<?= $o['id'] ?>"
                <?= $user['outlet_id'] == $o['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($o['outlet_name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Status</label><br>
    <select name="status">
        <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Active</option>
        <option value="inactive" <?= $user['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
    </select><br><br>

    <button type="submit">Update</button>
</form>

<?php include '../../../resources/views/layouts/footer.php'; ?>