<?php
session_start();
require_once '../../../vendor/autoload.php';
role_required('super_admin');
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<div style="max-width: 500px; width: 100%;">
    <div style="margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Tambah Nominal</h2>
    </div>

    <div class="card" style="padding: 1.5rem;">
        <form action="store.php" method="POST">
            <?= csrf_field() ?>
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nominal Belanja (Rp)</label>
            <input type="number" name="amount" min="1000" step="1000" required placeholder="Contoh: 50000" style="width: 100%; margin-bottom: 1.5rem; padding: 0.5rem;">

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center; display: flex; align-items: center; gap: 0.5rem;">
                    <i class='bx bx-save'></i> Simpan
                </button>
                <a href="index.php" class="btn btn-secondary" style="flex: 1; justify-content: center; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>
