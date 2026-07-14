<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$db = Database::connect();
$nominals = $db->query("SELECT * FROM purchase_nominals ORDER BY amount ASC")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Master Nominal Belanja</h2>
<p style="color: var(--text-muted); margin-top: -0.5rem; margin-bottom: 1.5rem;">Atur tombol pilihan nominal belanja yang akan muncul di halaman Tambah Stamp.</p>

<a href="create.php" class="btn btn-primary" style="margin-bottom: 1rem;">
    <i class='bx bx-plus'></i> Tambah Nominal
</a>

<div style="max-width: 600px; width: 100%;">
    <div class="nominal-list" style="display: flex; flex-direction: column; gap: 0.5rem;">
        <?php if (count($nominals) > 0): ?>
            <?php foreach ($nominals as $nom): ?>
                <?php 
                    $amount = (float) $nom['amount'];
                    // Format label: 35000 -> 35K
                    $label = ($amount >= 1000 && $amount % 1000 == 0) ? ($amount / 1000) . 'K' : number_format($amount, 0, ',', '.');
                ?>
                <div class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; margin-bottom: 0;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="font-weight: 700; font-size: 1.15rem; color: var(--primary);">
                            <?= htmlspecialchars($label) ?>
                        </div>
                        <div style="color: var(--text-muted); font-size: 0.85rem;">
                            Rp <?= number_format($amount, 0, ',', '.') ?>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <!-- Toggle Aktif/Non-Aktif -->
                        <form action="toggle.php" method="POST" style="margin: 0; display: flex; align-items: center;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $nom['id'] ?>">
                            <?php $toggle_color = $nom['is_active'] ? '#22c55e' : 'var(--text-muted)'; ?>
                            <button type="submit" style="background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center; color: <?= $toggle_color ?>;" title="Ubah Status">
                                <i class='bx <?= $nom['is_active'] ? 'bx-toggle-right' : 'bx-toggle-left' ?>' style="font-size: 2rem; line-height: 1;"></i>
                            </button>
                        </form>
                        
                        <a href="edit.php?id=<?= $nom['id'] ?>" class="btn-icon text-primary" style="background: #eef2ff; padding: 0.4rem;" title="Edit">
                            <i class='bx bx-edit-alt'></i>
                        </a>
                        <form action="delete.php" method="POST" style="margin: 0;" onsubmit="return confirm('Yakin ingin menghapus nominal ini?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $nom['id'] ?>">
                            <button type="submit" class="btn-icon text-danger" style="background: #fef2f2; padding: 0.4rem;" title="Hapus">
                                <i class='bx bx-trash'></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card" style="padding: 2rem; text-align: center; color: var(--text-muted); margin-bottom: 0;">
                Belum ada data nominal.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>
