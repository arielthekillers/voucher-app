<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Tambah Outlet</h2>

<div class="card">
    <form action="store.php" method="POST">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label>Kode Outlet</label>
                <input type="text" name="outlet_code" required>
            </div>

            <div class="form-group">
                <label>Nama Outlet</label>
                <input type="text" name="outlet_name" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>