<?php
session_start();
require_once '../../../vendor/autoload.php';
auth_required();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Tambah Promo</h2>

<div class="card">
    <form action="store.php" method="POST" enctype="multipart/form-data">
        <label>Judul Promo</label>
        <input type="text" name="title" required>

        <label>Deskripsi</label>
        <textarea name="description"></textarea>

        <label>Gambar (opsional)</label>
        <input type="file" name="image">

        <label>Point Dibutuhkan</label>
        <input type="number" name="point_cost" required>

        <label>Status</label>
        <select name="is_active">
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
        </select>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>