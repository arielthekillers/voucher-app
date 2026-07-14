<?php
session_start();
require_once '../../../vendor/autoload.php';

role_required('super_admin');

$id = (int) $_GET['id'];
$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM purchase_nominals WHERE id = ?");
$stmt->execute([$id]);
$nom = $stmt->fetch();

if (!$nom) {
    exit('Nominal tidak ditemukan');
}
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<div style="max-width: 500px; width: 100%;">
    <div style="margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Edit Nominal</h2>
    </div>

    <div class="card" style="padding: 1.5rem;">
        <form action="update.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $nom['id'] ?>">

            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nominal Belanja (Rp)</label>
            <input type="number" name="amount" min="1000" step="1000" value="<?= round($nom['amount']) ?>" required style="width: 100%; margin-bottom: 1.5rem; padding: 0.5rem;">

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center; display: flex; align-items: center; gap: 0.5rem;">
                    <i class='bx bx-check'></i> Update
                </button>
                <a href="<?= isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'index.php' ?>" class="btn btn-secondary" style="flex: 1; justify-content: center; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>
