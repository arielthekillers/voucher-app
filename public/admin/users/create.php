<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/core/Auth.php';
require_once '../../../app/middleware/auth.php';

role_required('super_admin');

$db = Database::connect();
$outlets = $db->query("SELECT * FROM outlets ORDER BY outlet_name")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Tambah User</h2>

<form action="store.php" method="POST">
    <label>Nama</label><br>
    <input type="text" name="name" required><br><br>

    <label>Username</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <label>Role</label><br>
    <select name="role" required>
        <option value="admin">Admin</option>
        <option value="super_admin">Super Admin</option>
    </select><br><br>

    <label>Outlet</label><br>
    <select name="outlet_id">
        <option value="">-- Tidak ada (Super Admin) --</option>
        <?php foreach ($outlets as $o): ?>
            <option value="<?= $o['id'] ?>">
                <?= htmlspecialchars($o['outlet_name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Simpan</button>
</form>

<?php include '../../../resources/views/layouts/footer.php'; ?>