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
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label>Outlet</label>
                <select name="outlet_id">
                    <option value="">-- Tidak ada (Super Admin) --</option>
                    <?php foreach ($outlets as $o): ?>
                        <option value="<?= $o['id'] ?>">
                            <?= htmlspecialchars($o['outlet_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>