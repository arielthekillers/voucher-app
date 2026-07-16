<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$db = Database::connect();

// Filters
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date   = $_GET['end_date'] ?? date('Y-m-d', strtotime('+1 day'));
$q          = $_GET['q'] ?? '';
$page       = (int) ($_GET['page'] ?? 1);
$limit      = 20;
$offset     = ($page - 1) * $limit;

// Build Query
$where = "WHERE DATE(t.created_at) BETWEEN ? AND ?";
$params = [$start_date, $end_date];

if ($q) {
    $where .= " AND (t.customer_name_snapshot LIKE ? OR t.customer_phone_snapshot LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

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

$is_super_admin = Auth::user()['role'] === 'super_admin';
$current_url = $_SERVER['REQUEST_URI'];
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Riwayat Transaksi</h2>

<div class="card">
    <form method="GET" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <label>Cari Customer</label>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nama / No HP" style="margin-bottom: 0;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label>Dari Tanggal</label>
            <input type="date" name="start_date" value="<?= $start_date ?>" style="margin-bottom: 0;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label>Sampai Tanggal</label>
            <input type="date" name="end_date" value="<?= $end_date ?>" style="margin-bottom: 0;">
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-primary" style="height: 42px;">
                <i class='bx bx-filter-alt'></i> Filter
            </button>
            <?php if ($q || isset($_GET['start_date'])): ?>
                <a href="history.php" class="btn btn-secondary" style="height: 42px; display: flex; align-items: center; text-decoration: none;">
                    <i class='bx bx-reset'></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow-x: auto;">
    <div class="table-container" style="box-shadow: none; border: none; min-width: 800px;">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Tipe</th>
                    <th>Customer</th>
                    <th>Detail</th>
                    <th style="text-align: right;">Total / Point</th>
                    <th>Admin</th>
                    <?php if ($is_super_admin): ?>
                        <th style="text-align: center;">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="<?= $is_super_admin ? '7' : '6' ?>" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            Tidak ada data transaksi pada periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td style="white-space: nowrap; color: var(--text-muted); font-size: 0.85rem;">
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
                                <a href="../customers/profile.php?id=<?= $t['customer_id'] ?>" style="font-weight: 600; font-size: 0.9rem; color: var(--primary); text-decoration: none; display: block; margin-bottom: 0.1rem;"><?= htmlspecialchars($t['customer_name_snapshot']) ?></a>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($t['customer_phone_snapshot']) ?></div>
                            </td>
                            <td>
                                <?php if ($t['type'] === 'EARN'): ?>
                                    <div style="font-size: 0.9rem;">Outlet: <?= htmlspecialchars($t['outlet_name_snapshot']) ?></div>
                                <?php else: ?>
                                    <div style="font-size: 0.9rem;">Promo: <?= htmlspecialchars($t['promo_title_snapshot']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($t['type'] === 'EARN'): ?>
                                    <div style="font-weight: 600; font-size: 0.9rem;">Rp <?= number_format($t['purchase_amount']) ?></div>
                                    <div style="color: #166534; font-size: 0.85rem; font-weight: 500;">+<?= number_format($t['point_amount']) ?> <?= CURRENCY_NAME ?></div>
                                <?php else: ?>
                                    <div style="color: #991b1b; font-weight: 600; font-size: 0.9rem;">-<?= number_format($t['point_amount']) ?> <?= CURRENCY_NAME ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">
                                <?= htmlspecialchars($t['admin_name'] ?? '-') ?>
                            </td>
                            
                            <?php if ($is_super_admin): ?>
                                <td style="text-align: center; white-space: nowrap;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <a href="edit.php?id=<?= $t['id'] ?>&return_url=<?= urlencode($current_url) ?>" class="btn btn-sm btn-secondary" title="Edit">
                                            <i class='bx bx-edit'></i>
                                        </a>
                                        <form action="delete.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus transaksi ini? Saldo customer akan dikoreksi secara otomatis.')" style="margin:0;">
                                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                            <input type="hidden" name="return_url" value="<?= htmlspecialchars($current_url) ?>">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;" title="Hapus">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <?php 
        $query_string = http_build_query([
            'start_date' => $start_date,
            'end_date' => $end_date,
            'q' => $q
        ]); 
    ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
        <div style="color: var(--text-muted); font-size: 0.875rem;">
            Halaman <?= $page ?> dari <?= $total_pages ?>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <?php if ($page > 1): ?>
                <a href="?<?= $query_string ?>&page=<?= $page - 1 ?>" class="btn btn-sm" style="background: #fff; border: 1px solid var(--border-color);">
                    <i class='bx bx-chevron-left'></i> Prev
                </a>
            <?php endif; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?<?= $query_string ?>&page=<?= $page + 1 ?>" class="btn btn-sm" style="background: #fff; border: 1px solid var(--border-color);">
                    Next <i class='bx bx-chevron-right'></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include '../../../resources/views/layouts/footer.php'; ?>
