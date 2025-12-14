<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$db = Database::connect();

$q = $_GET['q'] ?? '';
$customer = null;

// Search customer logic
if ($q) {
    $stmt = $db->prepare("
        SELECT c.*, 
        (
            COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'EARN'), 0) -
            COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'REDEEM'), 0)
        ) as current_points
        FROM customers c
        WHERE c.name LIKE ? OR c.phone LIKE ?
        LIMIT 1
    ");
    $stmt->execute(["%$q%", "%$q%"]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch active promos
$promos = $db->query("SELECT * FROM promos WHERE is_active = 1 ORDER BY point_cost ASC")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Redeem Promo</h2>

<div class="card">
    <form method="GET" style="display: flex; gap: 1rem;">
        <div style="flex: 1;">
            <input type="text" name="q" placeholder="Cari nama / no HP customer..." value="<?= htmlspecialchars($q) ?>" style="margin-bottom: 0;">
        </div>
        <button class="btn btn-primary">
            <i class='bx bx-search'></i> Cari
        </button>
    </form>
</div>

<?php if ($q && !$customer): ?>
    <div class="alert alert-danger" style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
        Customer tidak ditemukan.
    </div>
<?php endif; ?>

<?php if ($customer): ?>
    <div class="card">
        <h3 style="margin-bottom: 0.5rem;"><?= htmlspecialchars($customer['name']) ?></h3>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;"><?= htmlspecialchars($customer['phone']) ?></p>
        
        <div style="display: inline-block; padding: 0.5rem 1rem; background: #eef2ff; color: var(--primary); border-radius: var(--radius); font-weight: 600; margin-bottom: 1.5rem;">
            Current <?= CURRENCY_NAME ?>: <?= number_format($customer['current_points']) ?>
        </div>

        <form action="redeem_store.php" method="POST" onsubmit="return confirm('Proses Redeem promo ini? <?= CURRENCY_NAME ?> customer akan dipotong.')">
            <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
            <?= csrf_field() ?>
            
            <h4 style="margin-bottom: 1rem;">Pilih Promo</h4>
            
            <div class="promo-grid">
                <?php foreach ($promos as $p): ?>
                    <?php 
                        $can_afford = $customer['current_points'] >= $p['point_cost'];
                        $opacity = $can_afford ? '1' : '0.5';
                        $cursor = $can_afford ? 'pointer' : 'not-allowed';
                    ?>
                    <label class="promo-card" style="cursor: <?= $cursor ?>; opacity: <?= $opacity ?>; position: relative;">
                        <input type="radio" name="promo_id" value="<?= $p['id'] ?>" style="position: absolute; opacity: 0;" required <?= !$can_afford ? 'disabled' : '' ?>>
                        
                        <?php if ($p['image']): ?>
                            <img src="<?= BASE_URL ?>/storage/uploads/promos/<?= $p['image'] ?>" class="promo-image" alt="<?= htmlspecialchars($p['title']) ?>">
                        <?php else: ?>
                            <div class="promo-image" style="display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                <i class='bx bx-image' style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>

                        <div class="promo-content" style="border-top: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <h4 class="promo-title" style="font-size: 1rem; margin-bottom: 0;"><?= htmlspecialchars($p['title']) ?></h4>
                                <div style="font-weight: 600; color: var(--primary); white-space: nowrap;">
                                    <?= number_format($p['point_cost']) ?> <?= CURRENCY_NAME ?>
                                </div>
                            </div>
                            <p class="promo-desc" style="font-size: 0.8rem; margin-bottom: 0;">
                                <?= htmlspecialchars($p['description'] ?? '') ?>
                            </p>
                            
                            <div class="selected-indicator" style="margin-top: 1rem; text-align: center; display: none;">
                                <span class="btn btn-sm btn-primary" style="width: 100%;">Pilih Promo Ini</span>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 1.5rem; text-align: right;">
                <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                    <i class='bx bx-gift'></i> Proses Redeem
                </button>
            </div>
        </form>
    </div>

    <style>
        /* Style for selected promo */
        input[type="radio"]:checked + .promo-image {
            box-shadow: inset 0 0 0 4px var(--primary);
        }
        input[type="radio"]:checked ~ .promo-content {
            background-color: #f5f3ff;
            border-color: var(--primary);
        }
        input[type="radio"]:checked ~ .promo-content .selected-indicator {
            display: block;
        }
    </style>

    <script>
        // Enable submit button when a promo is selected
        const radios = document.querySelectorAll('input[name="promo_id"]');
        const submitBtn = document.getElementById('submit-btn');

        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                submitBtn.disabled = false;
            });
        });
    </script>
<?php endif; ?>

<?php include '../../../resources/views/layouts/footer.php'; ?>
