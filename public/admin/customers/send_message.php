<?php
session_start();
require_once '../../../vendor/autoload.php';
auth_required();

$id = $_GET['id'] ?? null;
if (!$id) exit('Invalid ID');

$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) exit('Customer not found');
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Kirim Pesan WhatsApp</h2>

<div class="card">
    <div style="margin-bottom: 2rem;">
        <h4 style="margin-bottom: 0.5rem;"><?= htmlspecialchars($customer['name']) ?></h4>
        <div style="color: var(--text-muted);"><?= htmlspecialchars($customer['phone']) ?></div>
    </div>

    <form action="send_message_process.php" method="POST">
        <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
        
        <label>Isi Pesan</label>
        <textarea name="message" rows="5" required placeholder="Halo kak..."></textarea>
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1.5rem;">
            Tips: Gunakan *text* untuk tebal, _text_ untuk miring.
        </div>

        <button type="submit" class="btn btn-primary">
            <i class='bx bxl-whatsapp'></i> Kirim Pesan
        </button>
        <a href="index.php" class="btn btn-secondary" style="margin-left: 0.5rem;">Batal</a>
    </form>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>
