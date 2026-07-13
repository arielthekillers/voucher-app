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



<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <h2 style="margin: 0;">Data Customer</h2>
</div>

<form method="GET" style="display: flex; gap: 1rem; align-items: center; background: #fff; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); flex-wrap: wrap;">
    <div style="flex: 1; min-width: 250px;">
        <input type="text" name="q" placeholder="Cari nama / no HP" value="<?= htmlspecialchars($q) ?>" style="margin-bottom: 0;">
    </div>
    
    <div style="width: 200px;">
        <select name="sort" style="margin-bottom: 0;" onchange="this.form.submit()">
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

    <a href="create.php" class="btn btn-primary" style="height: 42px; margin-left: auto;">
        <i class='bx bx-plus'></i> Tambah
    </a>
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
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($customers) > 0): ?>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($c['name']) ?></div>
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
                        <td>
                            <div style="display: flex; gap: 0.25rem;">
                                <a href="send_message.php?id=<?= $c['id'] ?>" class="btn-icon" style="color: #25D366; background: #dcfce7;" title="Chat WhatsApp">
                                    <i class='bx bxl-whatsapp'></i>
                                </a>
                                <a href="edit.php?id=<?= $c['id'] ?>" class="btn-icon text-primary" style="background: #eef2ff;" title="Edit">
                                    <i class='bx bx-edit-alt'></i>
                                </a>
                                <a href="delete.php?id=<?= $c['id'] ?>" class="btn-icon text-danger" style="background: #fef2f2;" title="Hapus" onclick="return confirm('Yakin ingin menghapus customer ini? Semua riwayat transaksinya akan hilang!')">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </div>
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

<!-- Pagination UI -->
<?php if ($total_pages > 1): ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div style="font-size: 0.875rem; color: var(--text-muted);">
        Menampilkan <?= min($offset + 1, $total_data) ?> - <?= min($offset + $limit, $total_data) ?> dari <?= $total_data ?> customer
    </div>
    <div style="display: flex; gap: 0.25rem;">
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