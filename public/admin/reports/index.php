<?php
session_start();
require_once '../../../vendor/autoload.php';
auth_required();

$db = Database::connect();

// 1. Latest Registered Customers
$latest_customers = $db->query("SELECT * FROM customers ORDER BY created_at DESC LIMIT 10")->fetchAll();

// 2. Highest Points (Dynamic Calculation)
// Calculate balance based on Earn - Redeem transactions
$top_customers = $db->query("
    SELECT c.name, c.phone, 
           COALESCE(SUM(CASE WHEN t.type = 'EARN' THEN t.point_amount ELSE -t.point_amount END), 0) as balance
    FROM customers c
    LEFT JOIN transactions t ON c.id = t.customer_id
    GROUP BY c.id
    ORDER BY balance DESC
    LIMIT 10
")->fetchAll();

// 3. Latest Earn Transactions
$latest_earn = $db->query("
    SELECT t.*, c.name as customer_name 
    FROM transactions t 
    JOIN customers c ON t.customer_id = c.id
    WHERE t.type = 'EARN' 
    ORDER BY t.created_at DESC 
    LIMIT 10
")->fetchAll();

// 4. Latest Redeem Transactions
$latest_redeem = $db->query("
    SELECT t.*, c.name as customer_name, p.title as promo_title
    FROM transactions t 
    JOIN customers c ON t.customer_id = c.id
    LEFT JOIN promos p ON t.promo_id = p.id
    WHERE t.type = 'REDEEM' 
    ORDER BY t.created_at DESC 
    LIMIT 10
")->fetchAll();

?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Laporan & Statistik</h2>

<div class="reports-grid">
    
    <!-- Latest Registered -->
    <div class="card">
        <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            Customer Terbaru
        </h4>
        <table class="table-sm">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($latest_customers as $c): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500;"><?= htmlspecialchars($c['name']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($c['phone']) ?></div>
                    </td>
                    <td style="font-size: 0.8rem;"><?= date('d/m/y', strtotime($c['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Highest Points -->
    <div class="card">
        <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            Top Customer (<?= CURRENCY_NAME ?>)
        </h4>
        <table class="table-sm">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th style="text-align: right;">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($top_customers as $c): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500;"><?= htmlspecialchars($c['name']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($c['phone']) ?></div>
                    </td>
                    <td style="text-align: right; font-weight: 600; color: var(--primary);">
                        <?= number_format($c['balance']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Latest Earn -->
    <div class="card">
        <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            Transaksi <?= CURRENCY_NAME ?> Masuk Terakhir
        </h4>
        <table class="table-sm">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th style="text-align: right;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($latest_earn as $t): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500;"><?= htmlspecialchars($t['customer_name']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('d/m H:i', strtotime($t['created_at'])) ?></div>
                    </td>
                    <td style="text-align: right; color: #166534; font-weight: 500;">
                        +<?= number_format($t['point_amount']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Latest Redeem -->
    <div class="card">
        <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            Penukaran Terakhir
        </h4>
        <table class="table-sm">
            <thead>
                <tr>
                    <th>Customer / Promo</th>
                    <th style="text-align: right;">Biaya</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($latest_redeem as $t): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500;"><?= htmlspecialchars($t['customer_name']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($t['promo_title'] ?? 'Unknown Promo') ?></div>
                    </td>
                    <td style="text-align: right; color: #991b1b; font-weight: 500;">
                        -<?= number_format($t['point_amount']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<style>
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    .table-sm {
        width: 100%;
        border-collapse: collapse;
    }
    .table-sm th, .table-sm td {
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid #f3f4f6;
        text-align: left;
    }
    .table-sm tr:last-child td {
        border-bottom: none;
    }
</style>

<?php include '../../../resources/views/layouts/footer.php'; ?>
