<?php
session_start();

require_once '../../vendor/autoload.php';

auth_required();

$db = Database::connect();

// 1. STATS
$total_customers = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_transactions = $db->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$points_earned = $db->query("SELECT SUM(point_amount) FROM transactions WHERE type = 'EARN'")->fetchColumn() ?: 0;
$points_redeemed = $db->query("SELECT SUM(point_amount) FROM transactions WHERE type = 'REDEEM'")->fetchColumn() ?: 0;

// 2. RECENT ACTIVITY
$recent_tx = $db->query("
    SELECT t.*, c.name as customer_name 
    FROM transactions t 
    LEFT JOIN customers c ON t.customer_id = c.id 
    ORDER BY t.created_at DESC 
    LIMIT 5
")->fetchAll();

// 3. TOP PROMOS
$top_promos = $db->query("
    SELECT p.title, p.image, COUNT(t.id) as redeem_count 
    FROM transactions t 
    JOIN promos p ON t.promo_id = p.id 
    WHERE t.type = 'REDEEM' 
    GROUP BY t.promo_id 
    ORDER BY redeem_count DESC 
    LIMIT 5
")->fetchAll();

include ROOT_PATH . '/resources/views/layouts/header.php';
include ROOT_PATH . '/resources/views/layouts/sidebar.php';
?>


<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Card 1 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Total Customers</div>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem;"><?= number_format($total_customers) ?></div>
            </div>
            <div style="background: #eef2ff; color: var(--primary); padding: 0.5rem; border-radius: 0.5rem;">
                <i class='bx bxs-user-account' style="font-size: 1.25rem;"></i>
            </div>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Total Transaksi</div>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem;"><?= number_format($total_transactions) ?></div>
            </div>
            <div style="background: #ecfdf5; color: #059669; padding: 0.5rem; border-radius: 0.5rem;">
                <i class='bx bxs-cart' style="font-size: 1.25rem;"></i>
            </div>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Points Distributed</div>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem; color: #166534;">+<?= number_format($points_earned) ?></div>
            </div>
            <div style="background: #f0fdf4; color: #166534; padding: 0.5rem; border-radius: 0.5rem;">
                <i class='bx bxs-plus-circle' style="font-size: 1.25rem;"></i>
            </div>
        </div>
    </div>

    <!-- Card 4 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Points Redeemed</div>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem; color: #dc2626;">-<?= number_format($points_redeemed) ?></div>
            </div>
            <div style="background: #fef2f2; color: #dc2626; padding: 0.5rem; border-radius: 0.5rem;">
                <i class='bx bxs-gift' style="font-size: 1.25rem;"></i>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Recent Activity -->
    <div class="card">
        <h3 style="font-size: 1.1rem; margin-bottom: 1rem;">Recent Activity</h3>
        <div class="table-container" style="box-shadow: none; border: none; overflow: hidden;">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th style="padding-left: 0;">Customer</th>
                        <th>Type</th>
                        <th style="text-align: right; padding-right: 0;">Amount/Point</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_tx)): ?>
                        <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">Belum ada transaksi</td></tr>
                    <?php else: ?>
                        <?php foreach ($recent_tx as $tx): ?>
                        <tr>
                            <td style="padding-left: 0;">
                                <div style="font-weight: 500; font-size: 0.875rem;"><?= htmlspecialchars($tx['customer_name_snapshot'] ?? 'Deleted User') ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('d M H:i', strtotime($tx['created_at'])) ?></div>
                            </td>
                            <td>
                                <?php if ($tx['type'] == 'EARN'): ?>
                                    <span class="promo-badge" style="background: #eff6ff; color: #1d4ed8; font-size: 0.7rem;">EARN</span>
                                <?php else: ?>
                                    <span class="promo-badge" style="background: #fff1f2; color: #be123c; font-size: 0.7rem;">REDEEM</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; padding-right: 0;">
                                <?php if ($tx['type'] == 'EARN'): ?>
                                    <div style="font-weight: 500; font-size: 0.875rem;">Rp <?= number_format($tx['purchase_amount']) ?></div>
                                    <div style="color: #166534; font-size: 0.75rem;">+<?= number_format($tx['point_amount']) ?></div>
                                <?php else: ?>
                                    <div style="color: #991b1b; font-weight: 500; font-size: 0.875rem;">-<?= number_format($tx['point_amount']) ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem; text-align: center;">
            <a href="transactions/history.php" style="font-size: 0.875rem; color: var(--primary); font-weight: 500;">Lihat Semua Transaksi &rarr;</a>
        </div>
    </div>

    <!-- Top Promos -->
    <div class="card">
        <h3 style="font-size: 1.1rem; margin-bottom: 1rem;">Top Promos</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php if (empty($top_promos)): ?>
                <div style="text-align: center; color: var(--text-muted);">Belum ada redeem</div>
            <?php else: ?>
                <?php foreach ($top_promos as $promo): ?>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <?php if ($promo['image']): ?>
                        <img src="<?= BASE_URL ?>/storage/uploads/promos/<?= $promo['image'] ?>" style="width: 48px; height: 48px; border-radius: 0.5rem; object-fit: cover; background: #f3f4f6;">
                    <?php else: ?>
                        <div style="width: 48px; height: 48px; border-radius: 0.5rem; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                            <i class='bx bx-image'></i>
                        </div>
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <div style="font-weight: 500; font-size: 0.875rem; line-height: 1.2; margin-bottom: 0.25rem;"><?= htmlspecialchars($promo['title']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= $promo['redeem_count'] ?>x Redeemed</div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/resources/views/layouts/footer.php'; ?>