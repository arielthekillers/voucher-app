<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$db = Database::connect();
$promos = $db->query("SELECT * FROM promos ORDER BY id DESC")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Promo</h2>
<a href="create.php" class="btn btn-primary" style="margin-bottom: 1rem;">
    <i class='bx bx-plus'></i> Tambah Promo
</a>

<div class="promo-grid">
    <?php foreach ($promos as $p): ?>
        <div class="promo-card">
            <?php if ($p['image']): ?>
                <img src="<?= BASE_URL ?>/storage/uploads/promos/<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['title']) ?>" class="promo-image">
            <?php else: ?>
                <div class="promo-image" style="display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                    <i class='bx bx-image' style="font-size: 3rem;"></i>
                </div>
            <?php endif; ?>
            
            <div class="promo-content">
                <h3 class="promo-title"><?= htmlspecialchars($p['title']) ?></h3>
                <p class="promo-desc"><?= htmlspecialchars($p['description'] ?? 'Tidak ada deskripsi') ?></p>
                
                <div style="font-weight: 600; color: var(--primary); margin-bottom: 0.5rem;">
                    <?= number_format($p['point_cost']) ?> Points
                </div>

                <div class="promo-footer">
                    <span class="promo-badge <?= $p['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                        <?= $p['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">
                            <i class='bx bx-edit'></i>
                        </a>
                        <a href="delete.php?id=<?= $p['id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Hapus promo ini?')">
                            <i class='bx bx-trash'></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>