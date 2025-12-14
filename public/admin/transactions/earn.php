<?php
session_start();

require_once '../../../vendor/autoload.php';

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

<div class="card">
    <form method="GET" style="display: flex; gap: 1rem;">
        <div style="flex: 1;">
            <input type="text" name="q" placeholder="Cari nama / no HP" value="<?= htmlspecialchars($q) ?>" style="margin-bottom: 0;">
        </div>
        <button class="btn btn-primary">
            <i class='bx bx-search'></i> Cari
        </button>
    </form>
</div>

<?php if ($customers): ?>
    <div class="card">
        <form action="earn_store.php" method="POST">
            <label>Pilih Customer</label>
            <select name="customer_id" required>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>">
                        <?= $c['name'] ?> - <?= $c['phone'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Nominal Belanja</label>
            <input type="number" name="amount" required>

            <label>Jumlah Point</label>
            <input type="number" name="point" required>

            <button type="submit" class="btn btn-primary">Simpan Point</button>
        </form>
    </div>
<?php endif; ?>

<?php include '../../../resources/views/layouts/footer.php'; ?>