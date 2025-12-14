<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/core/Auth.php';
require_once '../../../app/middleware/auth.php';

auth_required();

$db = Database::connect();

$q = $_GET['q'] ?? '';

$stmt = $db->prepare("
    SELECT * FROM customers
    WHERE name LIKE ? OR phone LIKE ?
    ORDER BY id DESC
");
$stmt->execute(["%$q%", "%$q%"]);
$customers = $stmt->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Customer</h2>

<form method="GET">
    <input type="text" name="q" placeholder="Cari nama / no HP" value="<?= htmlspecialchars($q) ?>">
    <button type="submit">Cari</button>
    <a href="create.php">+ Tambah Customer</a>
</form>

<br>

<table border="1" cellpadding="8">
    <tr>
        <th>Nama</th>
        <th>No HP</th>
        <th>Tanggal</th>
        <th>Aksi</th>
    </tr>

    <?php foreach ($customers as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= htmlspecialchars($c['phone']) ?></td>
            <td><?= $c['created_at'] ?></td>
            <td>
                <a href="edit.php?id=<?= $c['id'] ?>">Edit</a> |
                <a href="delete.php?id=<?= $c['id'] ?>"
                    onclick="return confirm('Hapus customer ini?')">Hapus</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include '../../../resources/views/layouts/footer.php'; ?>