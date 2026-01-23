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
require_once '../../../resources/views/header.php';
require_once '../../../resources/views/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h3>Hapus Pelanggan</h3>
            <p>Konfirmasi penghapusan data pelanggan</p>
        </div>
        <a href="index.php" class="btn btn-outline">
            <i class='bx bx-arrow-back'></i> Kembali
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 600px; margin: 0 auto; text-align: center; padding: 3rem 2rem;">
        
        <div style="background: #fee2e2; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #dc2626;">
            <i class='bx bx-trash' style="font-size: 3rem;"></i>
        </div>

        <h2 style="margin-bottom: 1rem; color: #dc2626;">Hapus Pelanggan?</h2>
        
        <p style="color: #4b5563; font-size: 1.1rem; margin-bottom: 2rem; line-height: 1.6;">
            Anda akan menghapus data pelanggan <strong><?= htmlspecialchars($customer['name']) ?></strong> 
            (<?= htmlspecialchars($customer['phone']) ?>).
        </p>

        <div style="background: #f9fafb; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; text-align: left; border: 1px solid #e5e7eb;">
            <h4 style="margin-top: 0; margin-bottom: 1rem; font-size: 0.95rem; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px;">Statistik Pelanggan</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Sisa Poin</div>
                    <div style="font-size: 1.25rem; font-weight: 600; color: #d97706;"><?= number_format($currentPoints) ?></div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #6b7280;">Total Transaksi</div>
                    <div style="font-size: 1.25rem; font-weight: 600;"><?= number_format($stats['total_trx']) ?></div>
                </div>
            </div>
        </div>

        <div class="alert alert-warning" style="text-align: left; display: flex; gap: 0.75rem; margin-bottom: 2rem;">
            <i class='bx bx-error-circle' style="font-size: 1.5rem; flex-shrink: 0;"></i>
            <div>
                <strong>Perhatian:</strong><br>
                Menghapus data pelanggan ini akan turut menghapus seluruh riwayat transaksi dan poin yang dimilikinya secara permanen. Tindakan ini tidak dapat dibatalkan.
            </div>
        </div>

        <form action="" method="POST">
            <button type="submit" name="confirm_delete" class="btn" style="background: #dc2626; color: white; width: 100%; justify-content: center; padding: 1rem; font-size: 1rem;">
                Ya, Hapus Permanen
            </button>
            <a href="index.php" class="btn" style="background: white; border: 1px solid #d1d5db; color: #374151; width: 100%; justify-content: center; margin-top: 0.75rem; padding: 1rem; font-size: 1rem;">
                Batal
            </a>
        </form>

    </div>
</div>

<?php require_once '../../../resources/views/footer.php'; ?>
