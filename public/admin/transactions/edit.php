<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$db = Database::connect();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: history.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM transactions WHERE id = ?");
$stmt->execute([$id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    $_SESSION['flash_error'] = "Transaksi tidak ditemukan.";
    header('Location: history.php');
    exit;
}
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Edit Transaksi</h2>

<div class="card" style="max-width: 600px;">
    <h3 style="margin-bottom: 0.5rem;"><?= htmlspecialchars($transaction['customer_name_snapshot']) ?></h3>
    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
        Tanggal: <?= date('d M Y H:i', strtotime($transaction['created_at'])) ?><br>
        Tipe: <?= $transaction['type'] ?>
    </p>

    <form action="update.php" method="POST">
        <input type="hidden" name="id" value="<?= $transaction['id'] ?>">
        <?= csrf_field() ?>

        <?php if ($transaction['type'] === 'EARN'): ?>
            <label>Nominal Belanja (Rp)</label>
            <input type="number" name="purchase_amount" value="<?= $transaction['purchase_amount'] ?>" required>
        <?php else: ?>
            <!-- For REDEEM, purchase_amount is usually 0, but let's just make it hidden or readonly -->
            <input type="hidden" name="purchase_amount" value="<?= $transaction['purchase_amount'] ?>">
        <?php endif; ?>

        <label>Jumlah Stamp (<?= CURRENCY_NAME ?>)</label>
        <input type="number" name="point_amount" value="<?= $transaction['point_amount'] ?>" required>

        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="history.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>
