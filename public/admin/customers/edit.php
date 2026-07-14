<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$id = (int) $_GET['id'];

$db = Database::connect();

$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) {
    exit('Customer tidak ditemukan');
}
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<div style="max-width: 500px; width: 100%;">
    <div style="margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Edit Customer</h2>
    </div>

    <div class="card" style="padding: 1.5rem;">
        <form action="update.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $c['id'] ?>">

            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nama</label>
            <input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>" required style="width: 100%; margin-bottom: 1rem; padding: 0.5rem;">

            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">No WhatsApp</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($c['phone']) ?>" required style="width: 100%; margin-bottom: 1.5rem; padding: 0.5rem;">

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