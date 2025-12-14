<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Tambah Outlet</h2>

<form action="store.php" method="POST">
    <label>Kode Outlet</label><br>
    <input type="text" name="outlet_code" required><br><br>

    <label>Nama Outlet</label><br>
    <input type="text" name="outlet_name" required><br><br>

    <button type="submit">Simpan</button>
</form>

<?php include '../../../resources/views/layouts/footer.php'; ?>