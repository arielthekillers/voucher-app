<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$db = Database::connect();

// Filters
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
// Use +1 day for end_date default to ensure we cover "today" even if PHP timezone is behind DB/Server time
$end_date   = $_GET['end_date'] ?? date('Y-m-d', strtotime('+1 day'));
$page       = (int) ($_GET['page'] ?? 1);
$limit      = 20;
$offset     = ($page - 1) * $limit;

// Build Query
$where = "WHERE DATE(t.created_at) BETWEEN ? AND ?";
$params = [$start_date, $end_date];

// Count Total for Pagination
$count_stmt = $db->prepare("SELECT COUNT(*) FROM transactions t $where");
$count_stmt->execute($params);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$sql = "
    SELECT t.*, u.name as admin_name 
    FROM transactions t
    LEFT JOIN users u ON t.created_by = u.id
    $where
    ORDER BY t.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Riwayat Transaksi</h2>

<div class="card">
    <form method="GET" style="display: flex; gap: 1rem; align-items: end;">
        <div style="flex: 1;">
            <label>Dari Tanggal</label>
            <input type="date" name="start_date" value="<?= $start_date ?>" style="margin-bottom: 0;">
        </div>
        <div style="flex: 1;">
            <label>Sampai Tanggal</label>
            <input type="date" name="end_date" value="<?= $end_date ?>" style="margin-bottom: 0;">
        </div>
        <button class="btn btn-primary" style="height: 42px;">
            <i class='bx bx-filter-alt'></i> Filter
        </button>
    </form>
</div>

<div class="card" style="padding: 0;">
    <div class="table-container" style="box-shadow: none; border: none;">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Tipe</th>
                    <th>Customer</th>
                    <th>Detail</th>
                    <th style="text-align: right;">Total / Point</th>
                    <th>Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            Tidak ada data transaksi pada periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td style="white-space: nowrap; color: var(--text-muted);">
                                <?= date('d M Y H:i', strtotime($t['created_at'])) ?>
                            </td>
                            <td>
                                <?php if ($t['type'] === 'EARN'): ?>
                                    <span class="promo-badge" style="background: #eff6ff; color: #1d4ed8;">EARN</span>
                                <?php else: ?>
                                    <span class="promo-badge" style="background: #fff1f2; color: #be123c;">REDEEM</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?= htmlspecialchars($t['customer_name_snapshot']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($t['customer_phone_snapshot']) ?></div>
                            </td>
                            <td>
                                <?php if ($t['type'] === 'EARN'): ?>
                                    <div>Outlet: <?= htmlspecialchars($t['outlet_name_snapshot']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($t['outlet_code_snapshot']) ?></div>
                                <?php else: ?>
                                    <div>Promo: <?= htmlspecialchars($t['promo_title_snapshot']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($t['type'] === 'EARN'): ?>
                                    <div style="font-weight: 500;">Rp <?= number_format($t['purchase_amount']) ?></div>
                                    <div style="color: #166534; font-size: 0.8rem;">+<?= number_format($t['point_amount']) ?> <?= CURRENCY_NAME ?></div>
                                <?php else: ?>
                                    <div style="color: #991b1b; font-weight: 500;">-<?= number_format($t['point_amount']) ?> <?= CURRENCY_NAME ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.875rem; color: var(--text-muted);">
                                <?= htmlspecialchars($t['admin_name'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
        <div style="color: var(--text-muted); font-size: 0.875rem;">
            Halaman <?= $page ?> dari <?= $total_pages ?>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <?php if ($page > 1): ?>
                <a href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&page=<?= $page - 1 ?>" class="btn btn-sm" style="background: #fff; border: 1px solid var(--border-color);">
                    <i class='bx bx-chevron-left'></i> Prev
                </a>
            <?php endif; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&page=<?= $page + 1 ?>" class="btn btn-sm" style="background: #fff; border: 1px solid var(--border-color);">
                    Next <i class='bx bx-chevron-right'></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include '../../../resources/views/layouts/footer.php'; ?>
