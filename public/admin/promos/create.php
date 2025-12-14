<?php
session_start();
require_once '../../../vendor/autoload.php';
auth_required();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Tambah Promo</h2>

<form action="store.php" method="POST" enctype="multipart/form-data">
    <label>Judul Promo</label><br>
    <input type="text" name="title" required><br><br>

    <label>Deskripsi</label><br>
    <textarea name="description"></textarea><br><br>

    <label>Gambar (opsional)</label><br>
    <input type="file" name="image"><br><br>

    <label>Point Dibutuhkan</label><br>
    <input type="number" name="point_cost" required><br><br>

    <label>Status</label><br>
    <select name="is_active">
        <option value="1">Aktif</option>
        <option value="0">Nonaktif</option>
    </select><br><br>

    <button type="submit">Simpan</button>
</form>

<?php include '../../../resources/views/layouts/footer.php'; ?>