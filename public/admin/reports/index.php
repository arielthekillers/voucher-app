<?php
session_start();
require_once '../../../vendor/autoload.php';
auth_required();

$db = Database::connect();

// --- SUMMARY METRICS ---
$total_customers = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_earn = $db->query("SELECT COALESCE(SUM(point_amount), 0) FROM transactions WHERE type='EARN'")->fetchColumn();
$total_redeem = $db->query("SELECT COALESCE(SUM(point_amount), 0) FROM transactions WHERE type='REDEEM'")->fetchColumn();
$total_balance = $total_earn - $total_redeem;

// --- CHART DATA ---
function extractChartData($data) {
    return [
        'labels' => array_column($data, 'label'),
        'earns' => array_column($data, 'earn'),
        'redeems' => array_column($data, 'redeem'),
    ];
}

$daily_data = $db->query("
    SELECT DATE_FORMAT(created_at, '%d %b') as label,
           COALESCE(SUM(CASE WHEN type='EARN' THEN point_amount ELSE 0 END), 0) as earn,
           COALESCE(SUM(CASE WHEN type='REDEEM' THEN point_amount ELSE 0 END), 0) as redeem
    FROM transactions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY label ORDER BY MIN(created_at) ASC
")->fetchAll();

$weekly_data = $db->query("
    SELECT CONCAT('Wk ', WEEK(created_at)) as label,
           COALESCE(SUM(CASE WHEN type='EARN' THEN point_amount ELSE 0 END), 0) as earn,
           COALESCE(SUM(CASE WHEN type='REDEEM' THEN point_amount ELSE 0 END), 0) as redeem
    FROM transactions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
    GROUP BY label ORDER BY MIN(created_at) ASC
")->fetchAll();

$monthly_data = $db->query("
    SELECT DATE_FORMAT(created_at, '%b %y') as label,
           COALESCE(SUM(CASE WHEN type='EARN' THEN point_amount ELSE 0 END), 0) as earn,
           COALESCE(SUM(CASE WHEN type='REDEEM' THEN point_amount ELSE 0 END), 0) as redeem
    FROM transactions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY label ORDER BY MIN(created_at) ASC
")->fetchAll();

$chartDataJson = json_encode([
    'daily' => extractChartData($daily_data),
    'weekly' => extractChartData($weekly_data),
    'monthly' => extractChartData($monthly_data)
]);

// --- TABLES DATA (LIMIT 7) ---
$latest_customers = $db->query("SELECT * FROM customers ORDER BY created_at DESC LIMIT 7")->fetchAll();

$top_customers = $db->query("
    SELECT c.name, c.phone, COALESCE(SUM(CASE WHEN t.type = 'EARN' THEN t.point_amount ELSE -t.point_amount END), 0) as balance
    FROM customers c LEFT JOIN transactions t ON c.id = t.customer_id
    GROUP BY c.id ORDER BY balance DESC LIMIT 7
")->fetchAll();

$latest_transactions = $db->query("
    SELECT t.*, c.name as customer_name, p.title as promo_title
    FROM transactions t JOIN customers c ON t.customer_id = c.id LEFT JOIN promos p ON t.promo_id = p.id
    ORDER BY t.created_at DESC LIMIT 7
")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- ROW 1: CARDS & CHART -->
<div class="row-top">
    <!-- Left: 4 Cards in 2x2 Grid -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #e0e7ff; color: #4338ca;"><i class='bx bx-group'></i></div>
            <div class="summary-info">
                <p>Total Customer</p>
                <h3><?= number_format($total_customers) ?></h3>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #fef08a; color: #a16207;"><i class='bx bx-coin-stack'></i></div>
            <div class="summary-info">
                <p>Point Beredar</p>
                <h3><?= number_format($total_balance) ?></h3>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #dcfce7; color: #15803d;"><i class='bx bx-trending-up'></i></div>
            <div class="summary-info">
                <p>Total Earn</p>
                <h3><?= number_format($total_earn) ?></h3>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon" style="background-color: #fee2e2; color: #b91c1c;"><i class='bx bx-gift'></i></div>
            <div class="summary-info">
                <p>Total Redeem</p>
                <h3><?= number_format($total_redeem) ?></h3>
            </div>
        </div>
    </div>

    <!-- Right: Chart -->
    <div class="chart-card card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.125rem; margin: 0;">Grafik Transaksi</h3>
            <div class="chart-filters">
                <button class="filter-btn active" onclick="switchChart('daily', this)">Daily</button>
                <button class="filter-btn" onclick="switchChart('weekly', this)">Weekly</button>
                <button class="filter-btn" onclick="switchChart('monthly', this)">Monthly</button>
            </div>
        </div>
        <div style="position: relative; height: 180px; width: 100%;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>

<!-- ROW 2: 3 TABLES -->
<div class="row-bottom">
    
    <!-- Table 1: Transactions -->
    <div class="card table-wrapper">
        <h4>Transaksi Terakhir</h4>
        <table class="table-sm">
            <tbody>
                <?php foreach($latest_transactions as $t): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500; font-size: 0.85rem;"><?= htmlspecialchars($t['customer_name']) ?></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);"><?= date('d/m/y H:i', strtotime($t['created_at'])) ?></div>
                    </td>
                    <td style="text-align: right; font-weight: 600; font-size: 0.85rem; color: <?= $t['type'] == 'EARN' ? '#15803d' : '#b91c1c' ?>;">
                        <?= $t['type'] == 'EARN' ? '+' : '-' ?><?= number_format($t['point_amount']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Table 2: Top Customers -->
    <div class="card table-wrapper">
        <h4>Top Customer</h4>
        <table class="table-sm">
            <tbody>
                <?php foreach($top_customers as $c): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500; font-size: 0.85rem;"><?= htmlspecialchars($c['name']) ?></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);"><?= htmlspecialchars($c['phone']) ?></div>
                    </td>
                    <td style="text-align: right; font-weight: 600; font-size: 0.85rem; color: var(--primary);">
                        <?= number_format($c['balance']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Table 3: New Customers -->
    <div class="card table-wrapper">
        <h4>Customer Baru</h4>
        <table class="table-sm">
            <tbody>
                <?php foreach($latest_customers as $c): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500; font-size: 0.85rem;"><?= htmlspecialchars($c['name']) ?></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);"><?= htmlspecialchars($c['phone']) ?></div>
                    </td>
                    <td style="text-align: right; font-size: 0.75rem; color: var(--text-muted);">
                        <?= date('d/m/y', strtotime($c['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<style>
    /* Row 1 Layout */
    .row-top {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .summary-cards {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .summary-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 1rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        box-shadow: var(--shadow-sm);
    }
    .summary-icon {
        width: 36px; height: 36px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin-bottom: 0.5rem;
    }
    .summary-info p { margin: 0; font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }
    .summary-info h3 { margin: 0; font-size: 1.25rem; color: var(--text-main); font-weight: 700; }
    
    .chart-card {
        display: flex; flex-direction: column;
        margin-bottom: 0; /* Remove default card margin */
    }
    
    .chart-filters {
        display: flex; gap: 0.25rem;
        background: #f3f4f6; padding: 0.25rem; border-radius: 6px;
    }
    .filter-btn {
        border: none; background: transparent; padding: 0.25rem 0.75rem;
        font-size: 0.75rem; font-weight: 600; color: var(--text-muted);
        border-radius: 4px; cursor: pointer; transition: 0.2s;
    }
    .filter-btn.active { background: #fff; color: var(--primary); box-shadow: var(--shadow-sm); }
    
    /* Row 2 Layout */
    .row-bottom {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }
    .table-wrapper {
        margin-bottom: 0;
        padding: 1rem;
    }
    .table-wrapper h4 { margin-bottom: 0.75rem; font-size: 0.9rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; }
    
    .table-sm { width: 100%; border-collapse: collapse; }
    .table-sm td { padding: 0.5rem 0; border-bottom: 1px solid #f9fafb; }
    .table-sm tr:last-child td { border-bottom: none; }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .row-top { grid-template-columns: 1fr; }
        .row-bottom { grid-template-columns: 1fr; }
    }
</style>

<script>
    const chartDataSets = <?= $chartDataJson ?>;
    let trendChart;

    function initChart(type) {
        const data = chartDataSets[type];
        const ctx = document.getElementById('trendChart').getContext('2d');
        
        if (trendChart) { trendChart.destroy(); }
        
        trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Earn (+)',
                        data: data.earns,
                        borderColor: '#15803d',
                        backgroundColor: 'rgba(21, 128, 61, 0.1)',
                        borderWidth: 2, tension: 0.3, fill: true, pointRadius: 2
                    },
                    {
                        label: 'Redeem (-)',
                        data: data.redeems,
                        borderColor: '#b91c1c',
                        backgroundColor: 'rgba(185, 28, 28, 0.1)',
                        borderWidth: 2, tension: 0.3, fill: true, pointRadius: 2
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 10 } } } },
                scales: {
                    x: { ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 } } }
                }
            }
        });
    }

    function switchChart(type, btnElement) {
        // Update active class
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');
        // Re-init chart
        initChart(type);
    }

    // Initialize with daily
    initChart('daily');
</script>

<?php include '../../../resources/views/layouts/footer.php'; ?>
