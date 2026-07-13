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

<h2>Tambah <?= CURRENCY_NAME ?></h2>

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
        <form action="earn_store.php" method="POST" id="earn-form">
            <label>Pilih Customer</label>
            <select name="customer_id" required>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>">
                        <?= $c['name'] ?> - <?= $c['phone'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Nominal Belanja (Rp)</label>
            <input type="number" name="amount" id="amount" min="1000" required>

            <label>Jumlah <?= CURRENCY_NAME ?></label>
            <input type="number" name="point" id="point" min="1" max="50" required>
            
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary">Simpan <?= CURRENCY_NAME ?></button>
        </form>
    </div>

    <script>
    document.getElementById('earn-form').addEventListener('submit', function(e) {
        const amount = parseFloat(document.getElementById('amount').value);
        const point = parseInt(document.getElementById('point').value);
        
        // Asumsi harga standar 1 stamp = Rp 30.000
        let expectedPoints = Math.floor(amount / 30000);
        if (expectedPoints < 1) expectedPoints = 1;
        
        // Toleransi perbedaan adalah 2 stamp dari ekspektasi
        const maxTolerable = expectedPoints + 2;

        if (point > maxTolerable) {
            const confirmMsg = `🚨 PERINGATAN!\n\nNominal belanja Rp ${amount.toLocaleString('id-ID')} biasanya hanya mendapatkan sekitar ${expectedPoints} hingga ${expectedPoints + 1} <?= CURRENCY_NAME ?>.\n\nAnda akan memberikan ${point} <?= CURRENCY_NAME ?> sekaligus.\n\nApakah Anda YAKIN angka ini sudah benar dan bukan salah ketik?`;
            
            if (!confirm(confirmMsg)) {
                e.preventDefault();
            }
        }
    });
    </script>
<?php endif; ?>

<?php include '../../../resources/views/layouts/footer.php'; ?>