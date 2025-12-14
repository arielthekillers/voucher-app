<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Tambah Customer</h2>

<div class="card">
    <form action="store.php" method="POST">
        <label>Nama</label>
        <input type="text" name="name" required>

        <label>No WhatsApp</label>
        <input type="text" name="phone" required placeholder="08xxxx">

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>