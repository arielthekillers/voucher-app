<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Tambah Customer</h2>

<form action="store.php" method="POST">
    <label>Nama</label><br>
    <input type="text" name="name" required><br><br>

    <label>No WhatsApp</label><br>
    <input type="text" name="phone" required placeholder="08xxxx"><br><br>

    <button type="submit">Simpan</button>
</form>

<?php include '../../../resources/views/layouts/footer.php'; ?>