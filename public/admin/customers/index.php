<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$db = Database::connect();

$q = $_GET['q'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Order logic
$order_sql = "c.id DESC";
if ($sort === 'oldest') {
    $order_sql = "c.id ASC";
} elseif ($sort === 'highest') {
    $order_sql = "current_stamp DESC, c.id DESC";
} elseif ($sort === 'lowest') {
    $order_sql = "current_stamp ASC, c.id DESC";
}

// Count total for pagination
$count_stmt = $db->prepare("SELECT COUNT(*) FROM customers WHERE name LIKE ? OR phone LIKE ?");
$count_stmt->execute(["%$q%", "%$q%"]);
$total_data = $count_stmt->fetchColumn();
$total_pages = ceil($total_data / $limit);

// Fetch data with total stamp
$stmt = $db->prepare("
    SELECT c.*, 
       (
           COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'EARN'), 0) -
           COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'REDEEM'), 0)
       ) as current_stamp
    FROM customers c
    WHERE c.name LIKE ? OR c.phone LIKE ?
    ORDER BY $order_sql
    LIMIT $limit OFFSET $offset
");
$stmt->execute(["%$q%", "%$q%"]);
$customers = $stmt->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>



<style>
.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}
@media (max-width: 768px) {
    .header-actions {
        flex-direction: column;
        align-items: flex-start;
    }
    .header-actions .btn-group {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .header-actions .btn-group > * {
        width: 100%;
        display: flex;
    }
    .header-actions .btn-group .btn {
        width: 100%;
        justify-content: center;
    }
    .search-form {
        flex-direction: column;
        align-items: stretch !important;
        padding: 1rem !important;
    }
    .search-form > div {
        width: 100% !important;
        min-width: 100% !important;
    }
    .search-form .btn {
        width: 100%;
        justify-content: center;
    }

    /* Hide desktop table on mobile, show mobile list */
    .table-container {
        display: none !important;
    }
    .mobile-customer-list {
        display: block !important;
    }

    /* Responsive Pagination */
    .pagination-wrapper {
        flex-direction: column !important;
        align-items: center !important;
        gap: 1rem !important;
    }
    .pagination-links {
        justify-content: center !important;
        flex-wrap: wrap !important;
    }
}
.mobile-customer-list {
    display: none;
}
</style>

<div class="header-actions">
    <h2 style="margin: 0;">Data Customer</h2>
    <div class="btn-group" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <?php if ($current_user && $current_user['role'] === 'super_admin'): ?>
            <a href="merge.php" class="btn btn-secondary" style="background: #fff; color: #b45309; border: 1px solid #fcd34d; display: flex; align-items: center; gap: 0.5rem; height: 38px;" title="Cari & Gabungkan Nomor Kembar">
                <i class='bx bx-git-merge'></i> <span class="d-none-sm">Merge Data</span>
            </a>
        <?php endif; ?>
        
        <form action="fix_phones.php" method="POST" style="margin: 0; display: flex;" onsubmit="return confirm('Proses ini akan merapikan semua No HP (menghapus spasi/simbol dan mengubah awalan menjadi 628). Lanjutkan?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-secondary" style="background: #fff; color: #475569; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; gap: 0.5rem; height: 38px; width: 100%;">
                <i class='bx bx-wrench'></i> <span class="d-none-sm">Fix Format No HP</span>
            </button>
        </form>
        
        <a href="export_customers.php" class="btn btn-secondary" style="background: #fff; border: 1px solid var(--border-color); color: var(--text-main); display: flex; align-items: center; justify-content: center; gap: 0.5rem; height: 38px;">
            <i class='bx bx-export'></i> <span class="d-none-sm">Export CSV</span>
        </a>
        
        <a href="create.php" class="btn btn-primary" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; height: 38px;">
            <i class='bx bx-plus'></i> <span>Tambah Customer</span>
        </a>
    </div>
</div>

<form method="GET" class="search-form" style="display: flex; gap: 1rem; align-items: center; background: #fff; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); flex-wrap: wrap;">
    <div style="flex: 1; min-width: 250px;">
        <input type="text" name="q" placeholder="Cari nama / no HP" value="<?= htmlspecialchars($q) ?>" style="margin-bottom: 0; width: 100%;">
    </div>
    
    <div style="width: 200px;">
        <select name="sort" style="margin-bottom: 0; width: 100%;" onchange="this.form.submit()">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Pendaftaran Terbaru</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Pendaftaran Terlama</option>
            <option value="highest" <?= $sort === 'highest' ? 'selected' : '' ?>>Saldo Stamp Terbanyak</option>
            <option value="lowest" <?= $sort === 'lowest' ? 'selected' : '' ?>>Saldo Stamp Paling Sedikit</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary" style="height: 42px;">
        <i class='bx bx-search'></i> Cari
    </button>
    
    <?php if ($q || $sort !== 'newest'): ?>
        <a href="index.php" class="btn btn-secondary" style="height: 42px; display: flex; align-items: center; text-decoration: none;">
            <i class='bx bx-reset'></i> Reset
        </a>
    <?php endif; ?>
</form>

<br>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>No HP</th>
                <th>Total Stamp</th>
                <th>Tanggal Daftar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($customers) > 0): ?>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>
                            <a href="profile.php?id=<?= $c['id'] ?>" style="font-weight: 600; color: var(--primary); text-decoration: none;"><?= htmlspecialchars($c['name']) ?></a>
                        </td>
                        <td><?= htmlspecialchars($c['phone']) ?></td>
                        <td>
                            <span class="badge" style="background-color: #e0e7ff; color: #3730a3; font-size: 0.85rem; padding: 0.35rem 0.75rem;">
                                <?= number_format($c['current_stamp']) ?> Stamp
                            </span>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;"><?= date('d M Y', strtotime($c['created_at'])) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('H:i', strtotime($c['created_at'])) ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        Tidak ada data customer ditemukan.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mobile-customer-list">
    <?php if (count($customers) > 0): ?>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($customers as $c): ?>
                <div style="background: #fff; padding: 1.25rem; border-radius: 12px; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                        <div style="flex: 1; min-width: 0;">
                            <a href="profile.php?id=<?= $c['id'] ?>" style="display: block; font-weight: 600; font-size: 1.1rem; color: var(--primary); margin-bottom: 0.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-decoration: none;"><?= htmlspecialchars($c['name']) ?></a>
                            <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($c['phone']) ?> &bull; <?= date('d M y', strtotime($c['created_at'])) ?></div>
                        </div>
                        <span class="badge" style="background-color: #e0e7ff; color: #3730a3; font-size: 0.8rem; padding: 0.35rem 0.75rem; white-space: nowrap; border-radius: 20px;">
                            <?= number_format($c['current_stamp']) ?> Stamp
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); background: #fff; border-radius: 12px; border: 1px solid var(--border-color);">
            Tidak ada data customer ditemukan.
        </div>
    <?php endif; ?>
</div>

<!-- Pagination UI -->
<?php if ($total_pages > 1): ?>
<div class="pagination-wrapper" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div style="font-size: 0.875rem; color: var(--text-muted); text-align: center;">
        Menampilkan <?= min($offset + 1, $total_data) ?> - <?= min($offset + $limit, $total_data) ?> dari <?= $total_data ?> customer
    </div>
    <div class="pagination-links" style="display: flex; gap: 0.25rem;">
        <?php
        function buildQueryString($page_num) {
            $params = $_GET;
            $params['page'] = $page_num;
            return '?' . http_build_query($params);
        }
        ?>
        
        <?php if ($page > 1): ?>
            <a href="<?= buildQueryString($page - 1) ?>" class="btn btn-secondary btn-sm" style="padding: 0.5rem 0.75rem;">
                <i class='bx bx-chevron-left'></i> Sebelumnya
            </a>
        <?php else: ?>
            <button class="btn btn-secondary btn-sm" style="padding: 0.5rem 0.75rem; opacity: 0.5; cursor: not-allowed;" disabled>
                <i class='bx bx-chevron-left'></i> Sebelumnya
            </button>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php 
                // Simple logic to not show too many page numbers
                if ($total_pages > 7 && ($i < $page - 2 || $i > $page + 2)) {
                    if ($i == 1 || $i == $total_pages) {
                        // show first and last
                    } elseif ($i == $page - 3 || $i == $page + 3) {
                        echo '<span style="padding: 0.5rem 0.75rem; color: var(--text-muted);">...</span>';
                        continue;
                    } else {
                        continue;
                    }
                }
            ?>
            <a href="<?= buildQueryString($i) ?>" class="btn btn-sm" style="padding: 0.5rem 0.75rem; <?= $i === $page ? 'background: var(--primary); color: white;' : 'background: #fff; border: 1px solid var(--border-color); color: var(--text-main);' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="<?= buildQueryString($page + 1) ?>" class="btn btn-secondary btn-sm" style="padding: 0.5rem 0.75rem;">
                Selanjutnya <i class='bx bx-chevron-right'></i>
            </a>
        <?php else: ?>
            <button class="btn btn-secondary btn-sm" style="padding: 0.5rem 0.75rem; opacity: 0.5; cursor: not-allowed;" disabled>
                Selanjutnya <i class='bx bx-chevron-right'></i>
            </button>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<br><br>

<?php include '../../../resources/views/layouts/footer.php'; ?>