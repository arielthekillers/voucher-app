<?php
session_start();
require_once '../../../vendor/autoload.php';
auth_required();

$db = Database::connect();
$current_user = Auth::user();

// Pastikan hanya super_admin
if ($current_user['role'] !== 'super_admin') {
    die("Akses ditolak.");
}

$date_range = $_GET['date_range'] ?? '';

if ($date_range && strpos($date_range, ' to ') !== false) {
    list($start_date, $end_date) = explode(' to ', $date_range);
} else {
    $start_date = $_GET['start_date'] ?? date('Y-m-d');
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    // Jika user hanya memilih 1 tanggal di kalender (double click / select single)
    if ($date_range && strpos($date_range, ' to ') === false) { 
        $start_date = $end_date = $date_range;
    }
}
$display_date = ($start_date !== $end_date) ? "$start_date to $end_date" : $start_date;

// --- SUMMARY METRICS ---
// Total transaksi
$stmt_trx = $db->prepare("SELECT COUNT(*) FROM transactions WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt_trx->execute([$start_date, $end_date]);
$total_transactions = $stmt_trx->fetchColumn();

// Total earn
$stmt_earn = $db->prepare("SELECT COALESCE(SUM(point_amount), 0) FROM transactions WHERE type='EARN' AND DATE(created_at) BETWEEN ? AND ?");
$stmt_earn->execute([$start_date, $end_date]);
$total_earn = $stmt_earn->fetchColumn();

// Total redeem
$stmt_redeem = $db->prepare("SELECT COALESCE(SUM(point_amount), 0) FROM transactions WHERE type='REDEEM' AND DATE(created_at) BETWEEN ? AND ?");
$stmt_redeem->execute([$start_date, $end_date]);
$total_redeem = $stmt_redeem->fetchColumn();

// Total Pendapatan Rupiah
$stmt_income = $db->prepare("SELECT COALESCE(SUM(purchase_amount), 0) FROM transactions WHERE type='EARN' AND DATE(created_at) BETWEEN ? AND ?");
$stmt_income->execute([$start_date, $end_date]);
$total_income = $stmt_income->fetchColumn();

// Total customer baru
$stmt_new_cust = $db->prepare("SELECT COUNT(*) FROM customers WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt_new_cust->execute([$start_date, $end_date]);
$total_new_customers = $stmt_new_cust->fetchColumn();

// --- CAPSTER PERFORMANCE ---
$stmt_capster = $db->prepare("
    SELECT u.name as capster_name, 
           COUNT(t.id) as total_trx, 
           SUM(CASE WHEN t.type = 'EARN' THEN t.point_amount ELSE 0 END) as total_earn,
           SUM(CASE WHEN t.type = 'EARN' THEN t.purchase_amount ELSE 0 END) as total_income,
           SUM(CASE WHEN t.type = 'REDEEM' THEN t.point_amount ELSE 0 END) as total_redeem
    FROM transactions t
    LEFT JOIN users u ON t.created_by = u.id
    WHERE DATE(t.created_at) BETWEEN ? AND ?
    GROUP BY u.id
    ORDER BY total_trx DESC
");
$stmt_capster->execute([$start_date, $end_date]);
$capsters = $stmt_capster->fetchAll();

// --- TOP PROMOS ---
$stmt_promo = $db->prepare("
    SELECT promo_title_snapshot as promo_name, COUNT(*) as claim_count
    FROM transactions
    WHERE type='REDEEM' AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY promo_title_snapshot
    ORDER BY claim_count DESC LIMIT 5
");
$stmt_promo->execute([$start_date, $end_date]);
$top_promos = $stmt_promo->fetchAll();
?>
<?php include '../../../resources/views/layouts/header.php'; ?>
<!-- Include Flatpickr CSS (Airbnb Theme) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">Reports</h2>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Ringkasan performa pada rentang waktu tertentu</p>
    </div>
    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <form method="GET" style="margin: 0;">
            <label for="date_range" style="display: flex; align-items: center; background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 0 1rem; height: 42px; cursor: pointer; box-shadow: var(--shadow-sm); margin: 0;">
                <i class='bx bx-calendar' style="color: var(--text-muted); font-size: 1.2rem; margin-right: 0.5rem; display: flex; align-items: center;"></i>
                <input type="text" name="date_range" id="date_range" value="<?= htmlspecialchars($display_date) ?>" placeholder="Pilih Rentang Tanggal..." style="border: none; outline: none; background: transparent; font-family: inherit; font-size: 0.9rem; color: var(--text-main); font-weight: 600; width: 220px; cursor: pointer; padding: 0; margin: 0; height: 100%;">
            </label>
        </form>
        
        <a href="export_daily.php?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" class="btn btn-secondary" style="background: #fff; border: 1px solid var(--border-color); color: var(--text-main); display: flex; align-items: center; gap: 0.5rem; height: 42px;">
            <i class='bx bx-export'></i> <span class="d-none-sm">Export CSV</span>
        </a>
    </div>
</div>

<!-- ROW 1: CARDS -->
<div class="summary-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="summary-card" style="background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius); padding: 1.25rem; display: flex; flex-direction: column; box-shadow: var(--shadow-sm);">
        <div class="summary-icon" style="width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; background-color: #e0f2fe; color: #0284c7;"><i class='bx bx-transfer'></i></div>
        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Total Transaksi</p>
        <h3 style="margin: 0; font-size: 1.5rem; color: var(--text-main); font-weight: 700;"><?= number_format($total_transactions) ?></h3>
    </div>
    
    <div class="summary-card" style="background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius); padding: 1.25rem; display: flex; flex-direction: column; box-shadow: var(--shadow-sm);">
        <div class="summary-icon" style="width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; background-color: #fef3c7; color: #d97706;"><i class='bx bx-money'></i></div>
        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Pendapatan (Rp)</p>
        <h3 style="margin: 0; font-size: 1.5rem; color: var(--text-main); font-weight: 700;">Rp <?= number_format($total_income, 0, ',', '.') ?></h3>
    </div>

    <div class="summary-card" style="background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius); padding: 1.25rem; display: flex; flex-direction: column; box-shadow: var(--shadow-sm);">
        <div class="summary-icon" style="width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; background-color: #dcfce7; color: #15803d;"><i class='bx bx-trending-up'></i></div>
        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Stamp Diberikan (Earn)</p>
        <h3 style="margin: 0; font-size: 1.5rem; color: var(--text-main); font-weight: 700;"><?= number_format($total_earn) ?></h3>
    </div>
    
    <div class="summary-card" style="background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius); padding: 1.25rem; display: flex; flex-direction: column; box-shadow: var(--shadow-sm);">
        <div class="summary-icon" style="width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; background-color: #fee2e2; color: #b91c1c;"><i class='bx bx-gift'></i></div>
        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Stamp Dipakai (Redeem)</p>
        <h3 style="margin: 0; font-size: 1.5rem; color: var(--text-main); font-weight: 700;"><?= number_format($total_redeem) ?></h3>
    </div>
    
    <div class="summary-card" style="background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius); padding: 1.25rem; display: flex; flex-direction: column; box-shadow: var(--shadow-sm);">
        <div class="summary-icon" style="width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; background-color: #e0e7ff; color: #4338ca;"><i class='bx bx-user-plus'></i></div>
        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Customer Baru</p>
        <h3 style="margin: 0; font-size: 1.5rem; color: var(--text-main); font-weight: 700;"><?= number_format($total_new_customers) ?></h3>
    </div>
</div>

<!-- ROW 2: TABLES -->
<div class="reports-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    
    <!-- Table 1: Capster Performance -->
    <div class="card" style="padding: 1.25rem; margin: 0; overflow-x: auto;">
        <h4 style="margin: 0 0 1rem 0; font-size: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">Performa Capster / Kasir</h4>
        <table style="width: 100%; border-collapse: collapse; min-width: 500px;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <th style="text-align: left; padding: 0.75rem 0; font-size: 0.85rem; color: var(--text-muted);">Nama</th>
                    <th style="text-align: center; padding: 0.75rem 0; font-size: 0.85rem; color: var(--text-muted);">Total Trx</th>
                    <th style="text-align: right; padding: 0.75rem 0; font-size: 0.85rem; color: var(--text-muted);">Pendapatan (Rp)</th>
                    <th style="text-align: right; padding: 0.75rem 0; font-size: 0.85rem; color: var(--text-muted);">Total Earn</th>
                    <th style="text-align: right; padding: 0.75rem 0; font-size: 0.85rem; color: var(--text-muted);">Total Redeem</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($capsters) > 0): ?>
                    <?php foreach($capsters as $c): ?>
                    <tr style="border-bottom: 1px solid #f9fafb;">
                        <td style="padding: 0.75rem 0; font-weight: 600; color: var(--text-main); font-size: 0.95rem;">
                            <?= htmlspecialchars($c['capster_name'] ?: 'Sistem / Tidak Diketahui') ?>
                        </td>
                        <td style="padding: 0.75rem 0; text-align: center; font-weight: 500;">
                            <?= number_format($c['total_trx']) ?>
                        </td>
                        <td style="padding: 0.75rem 0; text-align: right; font-weight: 600; color: #d97706;">
                            <?= number_format($c['total_income'], 0, ',', '.') ?>
                        </td>
                        <td style="padding: 0.75rem 0; text-align: right; font-weight: 600; color: #15803d;">
                            +<?= number_format($c['total_earn']) ?>
                        </td>
                        <td style="padding: 0.75rem 0; text-align: right; font-weight: 600; color: #b91c1c;">
                            -<?= number_format($c['total_redeem']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem 0; color: var(--text-muted); font-size: 0.9rem;">
                            Tidak ada transaksi pada periode ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Table 2: Top Promos -->
    <div class="card" style="padding: 1.25rem; margin: 0; overflow-x: auto;">
        <h4 style="margin: 0 0 1rem 0; font-size: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">Promo Terpopuler</h4>
        <table style="width: 100%; border-collapse: collapse; min-width: 250px;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <th style="text-align: left; padding: 0.75rem 0; font-size: 0.85rem; color: var(--text-muted);">Nama Promo</th>
                    <th style="text-align: right; padding: 0.75rem 0; font-size: 0.85rem; color: var(--text-muted);">Klaim</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($top_promos) > 0): ?>
                    <?php foreach($top_promos as $p): ?>
                    <tr style="border-bottom: 1px solid #f9fafb;">
                        <td style="padding: 0.75rem 0; font-weight: 500; font-size: 0.9rem; color: var(--text-main);">
                            <?= htmlspecialchars($p['promo_name'] ?: 'Promo') ?>
                        </td>
                        <td style="padding: 0.75rem 0; text-align: right; font-weight: 600; color: var(--primary);">
                            <?= number_format($p['claim_count']) ?>x
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align: center; padding: 2rem 0; color: var(--text-muted); font-size: 0.9rem;">
                            Belum ada klaim promo.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<style>
    @media (max-width: 1024px) {
        .reports-grid { grid-template-columns: 1fr !important; }
    }
    
    /* Fix global select CSS overriding Flatpickr */
    select.flatpickr-monthDropdown-months {
        appearance: auto !important;
        -webkit-appearance: auto !important;
        -moz-appearance: auto !important;
        width: auto !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        background: transparent !important;
        color: inherit !important;
        font-family: inherit !important;
        box-shadow: none !important;
        outline: none !important;
        height: auto !important;
        line-height: inherit !important;
        display: inline-block !important;
        vertical-align: baseline !important;
    }
    .flatpickr-current-month {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 !important;
    }
</style>

<!-- Include Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#date_range", {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: [<?= json_encode($start_date) ?>, <?= json_encode($end_date) ?>],
        onClose: function(selectedDates, dateStr, instance) {
            // Submit otomatis saat popup kalender ditutup jika ada tanggal yang dipilih
            if (dateStr) {
                document.getElementById('date_range').form.submit();
            }
        }
    });
</script>

<?php include '../../../resources/views/layouts/footer.php'; ?>
