<?php
session_start();

require_once '../../vendor/autoload.php';

auth_required();

$db = Database::connect();
$current_user = Auth::user();
$is_super = $current_user['role'] === 'super_admin';

$today = date('Y-m-d');
$month = date('Y-m');

// --- STATS ---
if ($is_super) {
    // Super Admin Stats
    // Hari ini
    $income_today = $db->query("SELECT COALESCE(SUM(purchase_amount), 0) FROM transactions WHERE type = 'EARN' AND DATE(created_at) = '$today'")->fetchColumn();
    $tx_today = $db->query("SELECT COUNT(*) FROM transactions WHERE DATE(created_at) = '$today'")->fetchColumn();
    $new_cust_today = $db->query("SELECT COUNT(*) FROM customers WHERE DATE(created_at) = '$today'")->fetchColumn();
    // Lifetime
    $total_customers = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    
    // Top Capsters this month
    $top_capsters = $db->query("
        SELECT u.name, COUNT(t.id) as total_tx, SUM(CASE WHEN t.type = 'EARN' THEN t.purchase_amount ELSE 0 END) as total_income 
        FROM transactions t 
        JOIN users u ON t.created_by = u.id 
        WHERE DATE_FORMAT(t.created_at, '%Y-%m') = '$month' 
        GROUP BY u.id 
        ORDER BY total_tx DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

} else {
    // Admin (Kasir) Stats
    $my_id = $current_user['id'];
    $tx_today = $db->query("SELECT COUNT(*) FROM transactions WHERE created_by = $my_id AND DATE(created_at) = '$today'")->fetchColumn();
    $earn_today = $db->query("SELECT COALESCE(SUM(point_amount), 0) FROM transactions WHERE created_by = $my_id AND type = 'EARN' AND DATE(created_at) = '$today'")->fetchColumn();
    $redeem_today = $db->query("SELECT COALESCE(SUM(point_amount), 0) FROM transactions WHERE created_by = $my_id AND type = 'REDEEM' AND DATE(created_at) = '$today'")->fetchColumn();
}

// --- RECENT ACTIVITY (Global) ---
$recent_tx = $db->query("
    SELECT t.*
    FROM transactions t 
    ORDER BY t.created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);


include ROOT_PATH . '/resources/views/layouts/header.php';
include ROOT_PATH . '/resources/views/layouts/sidebar.php';
?>

<!-- Menambahkan CSS kustom untuk menyembunyikan scrollbar tapi tetap bisa scroll horizontal -->
<style>
.quick-actions-container {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    /* Menyembunyikan scrollbar standar */
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
.quick-actions-container::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}
.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.25rem 1.5rem;
    border-radius: 12px;
    min-width: 130px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: var(--shadow-sm);
}
.quick-action-btn:active {
    transform: scale(0.97);
}
</style>

<!-- Welcome Header -->
<div style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0;">Halo, <?= htmlspecialchars(explode(' ', trim($current_user['name']))[0]) ?>! 👋</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.25rem;">Selamat bekerja hari ini, mari raih target pelayanan terbaik!</p>
</div>

<!-- Quick Actions -->
<div class="quick-actions-container">
    <a href="<?= BASE_URL ?>/public/admin/transactions/earn.php" class="quick-action-btn" style="background: var(--primary); color: #fff; border: 1px solid transparent;">
        <i class='bx bx-scan' style="font-size: 2.25rem; margin-bottom: 0.5rem;"></i>
        <span style="font-size: 0.9rem; font-weight: 600;">Tambah Stamp</span>
    </a>
    <a href="<?= BASE_URL ?>/public/admin/transactions/redeem.php" class="quick-action-btn" style="background: #fff; border: 1px solid #fecdd3; color: #be123c;">
        <i class='bx bx-gift' style="font-size: 2.25rem; margin-bottom: 0.5rem;"></i>
        <span style="font-size: 0.9rem; font-weight: 600;">Tukar Promo</span>
    </a>
    <a href="<?= BASE_URL ?>/public/admin/customers/create.php" class="quick-action-btn" style="background: #fff; border: 1px solid #bbf7d0; color: #166534;">
        <i class='bx bx-user-plus' style="font-size: 2.25rem; margin-bottom: 0.5rem;"></i>
        <span style="font-size: 0.9rem; font-weight: 600;">Member Baru</span>
    </a>
</div>

<!-- Statistik Cards -->
<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem;">
    <h3 style="font-size: 1.1rem; margin: 0;">Statistik <?= $is_super ? 'Bisnis' : 'Kinerja Saya' ?> <span style="color: var(--text-muted); font-size: 0.9rem; font-weight: normal;">(Hari Ini)</span></h3>
    <?php if ($is_super): ?>
        <a href="reports/daily.php" style="font-size: 0.85rem; color: var(--primary); font-weight: 600; text-decoration: none;">Lihat Laporan Lengkap &rarr;</a>
    <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem;">
    
    <?php if ($is_super): ?>
        <!-- Card 1: Omzet -->
        <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Pendapatan</div>
                    <div style="font-size: 1.35rem; font-weight: 700; margin-top: 0.25rem; color: #d97706;">Rp <?= number_format($income_today, 0, ',', '.') ?></div>
                </div>
                <div style="background: #fef3c7; color: #d97706; padding: 0.5rem; border-radius: 0.5rem;">
                    <i class='bx bx-money' style="font-size: 1.25rem;"></i>
                </div>
            </div>
        </div>
        <!-- Card 2: Transaksi -->
        <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Total Transaksi</div>
                    <div style="font-size: 1.35rem; font-weight: 700; margin-top: 0.25rem; color: var(--primary);"><?= number_format($tx_today) ?></div>
                </div>
                <div style="background: #eef2ff; color: var(--primary); padding: 0.5rem; border-radius: 0.5rem;">
                    <i class='bx bx-cart' style="font-size: 1.25rem;"></i>
                </div>
            </div>
        </div>
        <!-- Card 3: Cust Baru -->
        <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Member Baru</div>
                    <div style="font-size: 1.35rem; font-weight: 700; margin-top: 0.25rem; color: #166534;"><?= number_format($new_cust_today) ?></div>
                </div>
                <div style="background: #dcfce7; color: #166534; padding: 0.5rem; border-radius: 0.5rem;">
                    <i class='bx bx-user-plus' style="font-size: 1.25rem;"></i>
                </div>
            </div>
        </div>
        <!-- Card 4: Total Cust -->
        <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Total Semua Member</div>
                    <div style="font-size: 1.35rem; font-weight: 700; margin-top: 0.25rem; color: #475569;"><?= number_format($total_customers) ?></div>
                </div>
                <div style="background: #f1f5f9; color: #475569; padding: 0.5rem; border-radius: 0.5rem;">
                    <i class='bx bx-group' style="font-size: 1.25rem;"></i>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Cards -->
        <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Total Transaksi</div>
                    <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem; color: var(--primary);"><?= number_format($tx_today) ?></div>
                </div>
                <div style="background: #eef2ff; color: var(--primary); padding: 0.5rem; border-radius: 0.5rem;">
                    <i class='bx bx-cart' style="font-size: 1.25rem;"></i>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Stamp Diberikan</div>
                    <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem; color: #166534;">+<?= number_format($earn_today) ?></div>
                </div>
                <div style="background: #dcfce7; color: #166534; padding: 0.5rem; border-radius: 0.5rem;">
                    <i class='bx bx-plus-circle' style="font-size: 1.25rem;"></i>
                </div>
            </div>
        </div>
        <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Stamp Dicairkan</div>
                    <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem; color: #dc2626;">-<?= number_format($redeem_today) ?></div>
                </div>
                <div style="background: #fef2f2; color: #dc2626; padding: 0.5rem; border-radius: 0.5rem;">
                    <i class='bx bx-gift' style="font-size: 1.25rem;"></i>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: <?= $is_super ? '2fr 1fr' : '1fr' ?>; gap: 1.5rem;">
    <!-- Recent Activity -->
    <div class="card" style="padding: 1.25rem;">
        <h3 style="font-size: 1.1rem; margin-bottom: 1rem;">Aktivitas Terakhir</h3>
        <div class="table-container" style="box-shadow: none; border: none; overflow: hidden; margin: 0; border-radius: 0;">
            <table style="width: 100%;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <th style="padding-left: 0; background: transparent;">Customer</th>
                        <th style="background: transparent;">Tipe</th>
                        <th style="text-align: right; padding-right: 0; background: transparent;">Total / Stamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_tx)): ?>
                        <tr><td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">Belum ada transaksi</td></tr>
                    <?php else: ?>
                        <?php foreach ($recent_tx as $tx): ?>
                        <tr style="border-bottom: 1px solid #f9fafb;">
                            <td style="padding-left: 0; padding-top: 0.75rem; padding-bottom: 0.75rem;">
                                <a href="customers/profile.php?id=<?= $tx['customer_id'] ?>" style="font-weight: 600; font-size: 0.9rem; color: var(--primary); text-decoration: none; display: block; margin-bottom: 0.1rem;">
                                    <?= htmlspecialchars($tx['customer_name_snapshot'] ?? 'Deleted User') ?>
                                </a>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('d M H:i', strtotime($tx['created_at'])) ?></div>
                            </td>
                            <td style="padding-top: 0.75rem; padding-bottom: 0.75rem;">
                                <?php if ($tx['type'] == 'EARN'): ?>
                                    <span class="promo-badge" style="background: #eff6ff; color: #1d4ed8; font-size: 0.7rem; padding: 0.25rem 0.5rem;">EARN</span>
                                <?php else: ?>
                                    <span class="promo-badge" style="background: #fff1f2; color: #be123c; font-size: 0.7rem; padding: 0.25rem 0.5rem;">REDEEM</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; padding-right: 0; padding-top: 0.75rem; padding-bottom: 0.75rem;">
                                <?php if ($tx['type'] == 'EARN'): ?>
                                    <div style="font-weight: 600; font-size: 0.875rem;">Rp <?= number_format($tx['purchase_amount'], 0, ',', '.') ?></div>
                                    <div style="color: #166534; font-size: 0.75rem; font-weight: 500;">+<?= number_format($tx['point_amount']) ?> <?= CURRENCY_NAME ?></div>
                                <?php else: ?>
                                    <div style="color: #991b1b; font-weight: 600; font-size: 0.875rem;">-<?= number_format($tx['point_amount']) ?> <?= CURRENCY_NAME ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1.25rem; text-align: center;">
            <a href="transactions/history.php" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center; background: #f8fafc; border: 1px solid #e2e8f0; color: #475569;">
                Lihat Semua Transaksi
            </a>
        </div>
    </div>

    <?php if ($is_super): ?>
    <!-- Top Capsters -->
    <div class="card" style="padding: 1.25rem;">
        <h3 style="font-size: 1.1rem; margin-bottom: 1.25rem;">Top Kasir (Bulan Ini)</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php if (empty($top_capsters)): ?>
                <div style="text-align: center; color: var(--text-muted); padding: 2rem;">Belum ada data</div>
            <?php else: ?>
                <?php $rank = 1; foreach ($top_capsters as $cap): ?>
                <div style="display: flex; align-items: center; gap: 1rem; padding-bottom: 0.8rem; border-bottom: 1px solid #f8fafc;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: <?= $rank == 1 ? '#fef08a' : ($rank == 2 ? '#e5e7eb' : ($rank == 3 ? '#ffedd5' : '#f3f4f6')) ?>; display: flex; align-items: center; justify-content: center; font-weight: 700; color: <?= $rank == 1 ? '#a16207' : ($rank == 2 ? '#4b5563' : ($rank == 3 ? '#c2410c' : '#9ca3af')) ?>; font-size: 0.9rem;">
                        #<?= $rank ?>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.15rem; color: var(--text-main);"><?= htmlspecialchars($cap['name']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;"><?= $cap['total_tx'] ?> Transaksi</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 700; font-size: 0.9rem; color: #d97706;">Rp <?= number_format($cap['total_income'] / 1000, 0, ',', '.') ?>k</div>
                    </div>
                </div>
                <?php $rank++; endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include ROOT_PATH . '/resources/views/layouts/footer.php'; ?>