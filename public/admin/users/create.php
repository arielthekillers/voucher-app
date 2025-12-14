<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$db = Database::connect();
$outlets = $db->query("SELECT * FROM outlets ORDER BY outlet_name")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Tambah User</h2>

<div class="card">
    <form action="store.php" method="POST">
        <label>Nama</label>
        <input type="text" name="name" required>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Role</label>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="super_admin">Super Admin</option>
        </select>

        <label>Outlet</label>
        <select name="outlet_id">
            <option value="">-- Tidak ada (Super Admin) --</option>
            <?php foreach ($outlets as $o): ?>
                <option value="<?= $o['id'] ?>">
                    <?= htmlspecialchars($o['outlet_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>