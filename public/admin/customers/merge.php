<?php
session_start();
require_once '../../../vendor/autoload.php';
role_required('super_admin');

$db = Database::connect();

// Fetch all customers
$customers = $db->query("
    SELECT c.*, 
        (
            COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'EARN'), 0) -
            COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'REDEEM'), 0)
        ) as current_stamp
    FROM customers c
")->fetchAll();

$normalized_groups = [];

foreach ($customers as $c) {
    $phone = $c['phone'];
    if (empty($phone)) continue;

    $clean = preg_replace('/[^0-9]/', '', $phone);
    if (strpos($clean, '08') === 0) {
        $clean = '62' . substr($clean, 1);
    } elseif (strpos($clean, '8') === 0) {
        $clean = '62' . $clean;
    }
    
    // Group by normalized phone
    $normalized_groups[$clean][] = $c;
}

// Filter to only groups that have duplicates
$duplicates = array_filter($normalized_groups, function($group) {
    return count($group) > 1;
});
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Merge Customer</h2>
        <a href="index.php" class="btn" style="background: transparent; border: 1px solid #e2e8f0; color: var(--text-main); padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.8rem;">
            Kembali
        </a>
    </div>

    <?php if (empty($duplicates)): ?>
        <div style="text-align: center; padding: 2rem 1rem;">
            <i class='bx bx-check-circle' style="font-size: 2.5rem; color: #10b981; margin-bottom: 0.75rem;"></i>
            <h3 style="margin-bottom: 0.25rem; font-weight: 500; font-size: 1.1rem;">Tidak ada duplikat</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Semua data customer sudah unik.</p>
        </div>
    <?php else: ?>
        <div style="background: #f8fafc; border-left: 3px solid #f59e0b; padding: 0.5rem 0.75rem; border-radius: 4px; margin-bottom: 1rem; color: var(--text-muted); font-size: 0.8rem;">
            Pilih satu akun utama untuk dipertahankan. Transaksi dari akun lain akan dipindahkan dan akun sisanya dihapus.
        </div>

        <div style="display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fill, minmax(600px, 1fr));">
            <?php foreach ($duplicates as $norm_phone => $group): ?>
                <form action="merge_process.php" method="POST" onsubmit="return confirm('Gabungkan ke akun terpilih?');" class="card" style="padding: 0.75rem; border: 1px solid #e2e8f0; box-shadow: none; border-radius: 6px; display: flex; flex-direction: column; gap: 0.5rem;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="normalized_phone" value="<?= htmlspecialchars($norm_phone) ?>">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">
                        <div>
                            <h4 style="margin: 0; font-size: 0.9rem; font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($norm_phone) ?></h4>
                            <span style="font-size: 0.7rem; color: var(--text-muted);"><?= count($group) ?> akun</span>
                        </div>
                        <button type="submit" class="btn" style="background: var(--primary); color: white; padding: 0.3rem 0.75rem; font-size: 0.75rem; border-radius: 4px; border: none; cursor: pointer;">
                            Gabungkan
                        </button>
                    </div>
                    
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem; margin-top: 0.25rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid #e2e8f0; color: var(--text-muted); text-align: left;">
                                <th style="padding: 0.25rem; width: 30px;">Utama</th>
                                <th style="padding: 0.25rem;">Nama</th>
                                <th style="padding: 0.25rem;">No. HP</th>
                                <th style="padding: 0.25rem;">Dibuat</th>
                                <th style="padding: 0.25rem; text-align: right;">Stamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($group as $index => $c): ?>
                                <tr class="merge-row" style="border-bottom: 1px solid #f1f5f9; background: <?= $index === 0 ? '#f8fafc' : '#fff' ?>; cursor: pointer; transition: background 0.2s;" onclick="selectMergeRow(this)">
                                    <td style="padding: 0.4rem 0.25rem; text-align: center;">
                                        <input type="radio" name="primary_id" value="<?= $c['id'] ?>" <?= $index === 0 ? 'checked' : '' ?> style="accent-color: var(--primary); margin: 0; cursor: pointer;">
                                        <input type="hidden" name="group_ids[]" value="<?= $c['id'] ?>">
                                    </td>
                                    <td style="padding: 0.4rem 0.25rem; font-weight: 500; color: var(--text-main);"><?= htmlspecialchars($c['name']) ?></td>
                                    <td style="padding: 0.4rem 0.25rem; color: var(--text-muted);"><?= htmlspecialchars($c['phone']) ?></td>
                                    <td style="padding: 0.4rem 0.25rem; color: var(--text-muted);"><?= date('d/m/y', strtotime($c['created_at'])) ?></td>
                                    <td style="padding: 0.4rem 0.25rem; text-align: right;">
                                        <span style="background: #e2e8f0; color: var(--text-main); padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 600;">
                                            <?= number_format($c['current_stamp']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function selectMergeRow(row) {
    const container = row.closest('tbody');
    container.querySelectorAll('.merge-row').forEach(el => {
        el.style.background = '#fff';
    });
    row.style.background = '#f8fafc';
    row.querySelector('input[type="radio"]').checked = true;
}
function selectMergeOption(label) {
    const container = label.closest('form');
    container.querySelectorAll('.merge-option').forEach(el => {
        el.style.borderColor = '#f1f5f9';
        el.style.background = '#ffffff';
    });
    label.style.borderColor = 'var(--primary)';
    label.style.background = '#f8fafc';
    label.querySelector('input[type="radio"]').checked = true;
}
</script>

<?php include '../../../resources/views/layouts/footer.php'; ?>
