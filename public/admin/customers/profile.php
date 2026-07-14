<?php
session_start();
require_once '../../../vendor/autoload.php';
auth_required();

$db = Database::connect();
$id = $_GET['id'] ?? 0;

$stmt = $db->prepare("
    SELECT c.*, 
       (
           COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'EARN'), 0) -
           COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'REDEEM'), 0)
       ) as current_stamp
    FROM customers c WHERE c.id = ?
");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    die("Customer tidak ditemukan.");
}

// Fetch transactions
$tx_stmt = $db->prepare("
    SELECT t.*, u.name as admin_name 
    FROM transactions t 
    LEFT JOIN users u ON t.created_by = u.id 
    WHERE t.customer_id = ? 
    ORDER BY t.created_at DESC
");
$tx_stmt->execute([$id]);
$transactions = $tx_stmt->fetchAll();

?>
<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">Profil Customer</h2>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Detail dan riwayat transaksi</p>
    </div>
    <a href="<?= isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'index.php' ?>" class="btn" style="background: transparent; border: 1px solid #e2e8f0; color: var(--text-main); padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">
        Kembali
    </a>
</div>

<div class="grid" style="display: grid; gap: 1.5rem; grid-template-columns: 1fr 2fr;">
    <!-- Info Customer -->
    <div class="card" style="padding: 1.5rem; border: 1px solid #f1f5f9; box-shadow: 0 1px 3px rgba(0,0,0,0.05); align-self: start;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1rem; font-weight: bold;">
                <?= strtoupper(substr($customer['name'], 0, 1)) ?>
            </div>
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($customer['name']) ?></h3>
            <p style="color: var(--text-muted); margin-top: 0.25rem; font-size: 0.95rem;"><?= htmlspecialchars($customer['phone']) ?></p>
        </div>

        <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">Total Stamp</span>
                <span style="font-weight: 600; color: var(--primary); font-size: 1.1rem;"><?= number_format($customer['current_stamp']) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">Bergabung Sejak</span>
                <span style="color: var(--text-main); font-size: 0.9rem; font-weight: 500;"><?= date('d M Y', strtotime($customer['created_at'])) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">ID Sistem</span>
                <span style="color: var(--text-main); font-size: 0.9rem; font-family: monospace;">#<?= $customer['id'] ?></span>
            </div>
        </div>
        
        <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-direction: column;">
            <a href="send_message.php?id=<?= $customer['id'] ?>" class="btn" style="background: #25D366; color: white; border: none; padding: 0.6rem; text-align: center; border-radius: 6px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <i class='bx bxl-whatsapp'></i> WhatsApp
            </a>
            <a href="edit.php?id=<?= $customer['id'] ?>" class="btn" style="background: #f1f5f9; color: var(--text-main); border: none; padding: 0.6rem; text-align: center; border-radius: 6px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <i class='bx bx-edit-alt'></i> Edit Profil
            </a>
        </div>
    </div>

    <!-- Riwayat Transaksi -->
    <div class="card" style="padding: 1.5rem; border: 1px solid #f1f5f9; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin: 0 0 1.25rem 0; font-size: 1.1rem; font-weight: 600; color: var(--text-main);">Riwayat Transaksi</h3>
        
        <?php if (count($transactions) > 0): ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($transactions as $t): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                        <div>
                            <div style="font-weight: 600; color: var(--text-main); margin-bottom: 0.2rem; font-size: 0.95rem;">
                                <?php
                                    if ($t['type'] === 'EARN') {
                                        echo 'Outlet: ' . htmlspecialchars($t['outlet_name_snapshot'] ?? '-');
                                    } else {
                                        echo 'Promo: ' . htmlspecialchars($t['promo_title_snapshot'] ?? '-');
                                    }
                                ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                <?= date('d M Y, H:i', strtotime($t['created_at'])) ?> &bull; <?= htmlspecialchars($t['admin_name'] ?: '-') ?>
                            </div>
                        </div>
                        <div style="font-weight: 700; font-size: 1rem; width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: <?= $t['type'] === 'EARN' ? '#dcfce7' : '#fee2e2' ?>; color: <?= $t['type'] === 'EARN' ? '#166534' : '#991b1b' ?>;">
                            <?= $t['type'] === 'EARN' ? '+' : '-' ?><?= number_format($t['point_amount']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem 1rem; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                <i class='bx bx-history' style="font-size: 3rem; color: #94a3b8; margin-bottom: 0.75rem;"></i>
                <p style="color: var(--text-muted); margin: 0;">Belum ada transaksi untuk customer ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<style>
@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php include '../../../resources/views/layouts/footer.php'; ?>
