<?php
session_start();

require_once '../../../vendor/autoload.php';

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

<form method="GET" style="display: flex; gap: 1rem; align-items: center; background: #fff; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
    <div style="flex: 1;">
        <input type="text" name="q" placeholder="Cari nama / no HP" value="<?= htmlspecialchars($q) ?>" style="margin-bottom: 0;">
    </div>
    <button type="submit" class="btn btn-primary">
        <i class='bx bx-search'></i> Cari
    </button>
    <a href="create.php" class="btn btn-primary">
        <i class='bx bx-plus'></i> Tambah Customer
    </a>
</form>

<br>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>No HP</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['phone']) ?></td>
                    <td><?= $c['created_at'] ?></td>
                    <td>
                        <a href="edit.php?id=<?= $c['id'] ?>" class="btn btn-primary btn-sm" style="display:inline-flex;">Edit</a>
                        <a href="delete.php?id=<?= $c['id'] ?>"
                            class="btn btn-danger btn-sm"
                            style="display:inline-flex;"
                            onclick="return confirm('Hapus customer ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>