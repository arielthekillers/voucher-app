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
        <label>Kode Outlet</label>
        <input type="text" name="outlet_code" required>

        <label>Nama Outlet</label>
        <input type="text" name="outlet_name" required>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>