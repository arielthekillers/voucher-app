<?php
session_start();

require_once '../../../app/config/app.php';
require_once '../../../app/core/Database.php';
require_once '../../../app/core/Auth.php';
require_once '../../../app/middleware/auth.php';

auth_required();

$db = Database::connect();

$q = $_GET['q'] ?? '';
$customers = [];

if ($q) {
    $stmt = $db->prepare("
        SELECT * FROM customers
        WHERE name LIKE ? OR phone LIKE ?
        LIMIT 10
    ");
    $stmt->execute(["%$q%", "%$q%"]);
    $customers = $stmt->fetchAll();
}
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Tambah Point</h2>

<form method="GET">
    <input type="text" name="q" placeholder="Cari nama / no HP" value="<?= htmlspecialchars($q) ?>">
    <button>Cari</button>
</form>

<?php if ($customers): ?>
    <hr>
    <form action="earn_store.php" method="POST">
        <label>Pilih Customer</label><br>
        <select name="customer_id" required>
            <?php foreach ($customers as $c): ?>
                <option value="<?= $c['id'] ?>">
                    <?= $c['name'] ?> - <?= $c['phone'] ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Nominal Belanja</label><br>
        <input type="number" name="amount" required><br><br>

        <label>Jumlah Point</label><br>
        <input type="number" name="point" required><br><br>

        <button type="submit">Simpan Point</button>
    </form>
<?php endif; ?>

<?php include '../../../resources/views/layouts/footer.php'; ?>