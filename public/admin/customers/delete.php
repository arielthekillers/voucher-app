<?php
session_start();

require_once '../../../vendor/autoload.php';

// Ensure user is logged in
auth_required();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$db = Database::connect();

// 1. Fetch Customer Data
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    // Customer not found, redirect back
    $_SESSION['flash_error'] = "Pelanggan tidak ditemukan.";
    header('Location: index.php');
    exit;
}

// 2. Fetch Transaction Stats for Confirmation
$stmtStats = $db->prepare("
    SELECT 
        COUNT(*) as total_trx,
        SUM(CASE WHEN type = 'EARN' THEN point_amount ELSE 0 END) as total_earned,
        SUM(CASE WHEN type = 'REDEEM' THEN point_amount ELSE 0 END) as total_redeemed
    FROM transactions 
    WHERE customer_id = ?
");
$stmtStats->execute([$id]);
$stats = $stmtStats->fetch();

$currentPoints = $stats['total_earned'] - $stats['total_redeemed'];


// 3. Handle Deletion (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $db->beginTransaction();

        // Delete Transactions first (Cascading delete manual)
        $stmtTrx = $db->prepare("DELETE FROM transactions WHERE customer_id = ?");
        $stmtTrx->execute([$id]);

        // Delete Customer
        $stmtCust = $db->prepare("DELETE FROM customers WHERE id = ?");
        $stmtCust->execute([$id]);

        $db->commit();

        $_SESSION['flash_success'] = "Pelanggan dan riwayat transaksinya berhasil dihapus.";
        header('Location: index.php');
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $error = "Gagal menghapus data: " . $e->getMessage();
    }
}

// 4. Render View (Confirmation Page)
$pageTitle = "Hapus Pelanggan";
require_once '../../../resources/views/layouts/header.php';
require_once '../../../resources/views/layouts/sidebar.php';
?>

<div style="max-width: 500px; margin: 2rem auto;">
    
    <!-- Link Kembali removed as requested -->

    <?php if (isset($error)): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class='bx bxs-error-circle'></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; border-radius: 16px; padding: 2.5rem; text-align: center; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
        
        <div style="background: #fee2e2; width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #dc2626;">
            <i class='bx bx-trash' style="font-size: 2.5rem;"></i>
        </div>

        <h2 style="margin-top: 0; margin-bottom: 0.5rem; font-size: 1.5rem; color: #111827;">Hapus Pelanggan?</h2>
        
        <p style="color: #6b7280; font-size: 1rem; margin-bottom: 2rem; line-height: 1.5;">
            Anda akan menghapus data pelanggan <br>
            <strong style="color: #111827;"><?= htmlspecialchars($customer['name']) ?></strong> 
            (<?= htmlspecialchars($customer['phone']) ?>).
        </p>

        <div style="background: #f9fafb; border-radius: 12px; padding: 1.25rem; margin-bottom: 2rem; text-align: left; border: 1px solid #f3f4f6;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; letter-spacing: 1px; font-weight: 600; margin-bottom: 1rem;">Statistik Pelanggan</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Sisa Poin</div>
                    <div style="font-size: 1.25rem; font-weight: 600; color: #d97706;"><?= number_format($currentPoints) ?></div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Total Transaksi</div>
                    <div style="font-size: 1.25rem; font-weight: 600; color: #111827;"><?= number_format($stats['total_trx']) ?></div>
                </div>
            </div>
        </div>

        <div style="background: #fff5f5; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem; text-align: left; display: flex; gap: 0.75rem; margin-bottom: 2rem; align-items: start;">
            <i class='bx bx-error-circle' style="font-size: 1.25rem; color: #dc2626; flex-shrink: 0; margin-top: 2px;"></i>
            <div style="color: #991b1b; font-size: 0.85rem; line-height: 1.5;">
                <strong>Peringatan Penting:</strong><br>
                Tindakan ini akan menghapus seluruh data transaksi, poin, dan riwayat penukaran pelanggan ini secara permanen. Data yang dihapus tidak dapat dikembalikan.
            </div>
        </div>

        <form action="" method="POST">
            <button type="submit" name="confirm_delete" class="btn" style="background: #dc2626; color: white; width: 100%; justify-content: center; padding: 0.875rem; font-size: 1rem; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                Hapus Pelanggan
            </button>
            <a href="index.php" style="display: block; width: 100%; padding: 0.875rem; margin-top: 0.75rem; color: #4b5563; text-decoration: none; font-weight: 500;">
                Batal
            </a>
        </form>

    </div>
</div>

<?php require_once '../../../resources/views/layouts/footer.php'; ?>
