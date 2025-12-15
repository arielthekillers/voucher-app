<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

$db = Database::connect();

// Fetch Settings
$settings = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$businessName = $settings['business_name'] ?? APP_NAME;
$businessLogo = $settings['business_logo'] ?? '';
$currency = $settings['currency_name'] ?? CURRENCY_NAME;
$whatsapp = $settings['business_phone'] ?? '';

// Handle Phone Input
$phone = isset($_GET['phone']) ? trim($_GET['phone']) : null;
$customer = null;
$promos = [];
$history = [];
$error = null;

if ($phone) {
    // Basic sanitization
    // $phone = preg_replace('/[^0-9]/', '', $phone); 

    // Fetch Customer
    $stmt = $db->prepare("SELECT * FROM customers WHERE phone = ?");
    $stmt->execute([$phone]);
    $customer = $stmt->fetch();

    if ($customer) {
        // Calculate Points
        $stmtP = $db->prepare("
            SELECT 
            (
                COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = ? AND type = 'EARN'), 0) -
                COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = ? AND type = 'REDEEM'), 0)
            ) as total_points
        ");
        $stmtP->execute([$customer['id'], $customer['id']]);
        $customer['points'] = $stmtP->fetchColumn() ?: 0;
        
        // Fetch Active Promos
        $promos = $db->query("SELECT * FROM promos WHERE is_active = 1 ORDER BY point_cost ASC")->fetchAll();

        // Fetch History (Redeem)
        $stmtH = $db->prepare("
            SELECT t.*, p.title as promo_title
            FROM transactions t
            LEFT JOIN promos p ON t.promo_id = p.id
            WHERE t.customer_id = ? AND t.type = 'REDEEM'
            ORDER BY t.created_at DESC
        ");
        $stmtH->execute([$customer['id']]);
        $history = $stmtH->fetchAll();
    } else {
        $error = 'Pelanggan Belum terdaftar';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title><?= htmlspecialchars($businessName) ?> - Member Area</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <style>
        :root {
            --primary: #111827; /* Dark */
            --gold: #d4af37; /* Gold */
            --gold-gradient: linear-gradient(135deg, #fce38a 0%, #d4af37 100%);
            --bg: #f9fafb;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            background: #f3f4f6;
            color: var(--text-main);
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }
        .app-container {
            width: 100%;
            max-width: 480px; /* Mobile width constraint on desktop */
            background: #fff;
            min-height: 100vh;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }

        /* Login View */
        .login-view {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
        }
        .logo-img {
            max-width: 120px;
            margin-bottom: 1.5rem;
            border-radius: 12px;
        }
        .input-group {
            width: 100%;
            margin-bottom: 1rem;
            text-align: left;
        }
        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .form-input {
            width: 100%;
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-input:focus { border-color: var(--gold); }
        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: opacity 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
        }
        .btn-whatsapp {
            background: #25D366;
            color: #fff;
            margin-top: 1rem;
        }
        .btn:active { opacity: 0.9; }

        /* Dashboard View */
        .dashboard-view {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .content-area {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            padding-bottom: 5rem; /* Space for navbar */
        }
        
        /* Loyalty Card */
        .loyalty-card {
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            position: relative;
        }
        .card-top {
            height: 180px; /* Approx 30% of typical screen height feel, fixed for consistency */
            background: var(--gold-gradient);
            padding: 1.5rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            color: #000;
        }
        .card-bottom {
            background: #1f1f1f; /* Dark Grey/Black */
            padding: 1.5rem;
            color: #fff;
            min-height: 120px;
        }
        .card-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
            margin-bottom: 0.25rem;
        }
        .card-value {
            font-size: 1.1rem;
            font-weight: 600;
        }
        .point-display {
            text-align: center;
            margin-top: -3rem; /* Pull up into gold area slightly or just center in bottom */
        }
        .point-amount {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1;
            color: var(--gold);
            background: -webkit-linear-gradient(#fce38a, #d4af37);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .currency-label {
            opacity: 0.7;
            font-size: 0.9rem;
        }

        /* Promos */
        .promo-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }
        .promo-card.disabled {
            opacity: 0.7;
            filter: grayscale(0.8);
        }
        .promo-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: #eee;
        }
        .promo-body {
            padding: 1rem;
        }
        .promo-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .promo-cost {
            color: #d4af37; /* Goldish for cost */
            font-weight: 700;
            font-size: 0.9rem;
        }
        .promo-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0.5rem 0 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* History List */
        .history-item {
            background: #fff;
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .history-left {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .history-title {
            font-weight: 600;
            font-size: 0.95rem;
        }
        .history-date {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        .history-cost {
            color: #ef4444; /* Red for minus */
            font-weight: 600;
        }

        /* Bottom Nav */
        .navbar {
            position: fixed;
            bottom: 0;
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-around;
            padding: 0.75rem 0;
            z-index: 10;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.75rem;
            cursor: pointer;
            background: none;
            border: none;
        }
        .nav-item.active {
            color: var(--primary);
            font-weight: 600;
        }
        .nav-item i {
            font-size: 1.5rem;
        }

        /* Utils */
        .hidden { display: none !important; }
        .text-center { text-align: center; }
        .mt-2 { margin-top: 1rem; }
    </style>
</head>
<body>

<div class="app-container">
    
    <?php if (!$phone): ?>
        <!-- LOGIN VIEW -->
        <div class="login-view">
            <?php if ($businessLogo): ?>
                <img src="../storage/uploads/settings/<?= htmlspecialchars($businessLogo) ?>" alt="Logo" class="logo-img">
            <?php else: ?>
                <div style="font-size: 2rem; margin-bottom: 1rem; font-weight: bold;"><?= htmlspecialchars($businessName) ?></div>
            <?php endif; ?>
            
            <h2 style="margin-bottom: 0.5rem;">Selamat Datang</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Masukkan nomor HP untuk melihat member card</p>
            
            <form action="" method="GET" style="width: 100%;">
                <div class="input-group">
                    <label>Nomor WhatsApp / HP</label>
                    <input type="tel" name="phone" class="form-input" placeholder="08xxxxxxxxxx" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary">
                    Lihat Member Card <i class='bx bx-right-arrow-alt'></i>
                </button>
            </form>
        </div>

    <?php elseif ($error || !$customer): ?>
        <!-- ERROR VIEW -->
        <div class="login-view">
            <i class='bx bxs-error-circle' style="font-size: 4rem; color: #ef4444; margin-bottom: 1rem;"></i>
            <h3>Pelanggan Belum Terdaftar</h3>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">
                Nomor <strong><?= htmlspecialchars($phone) ?></strong> belum terdaftar sebagai member kami.
            </p>
            
            <a href="?phone=" class="btn" style="border: 1px solid var(--border); margin-bottom: 1rem;">Coba Nomor Lain</a>
            
            <?php if ($whatsapp): ?>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsapp) ?>?text=Halo%20Admin,%20saya%20ingin%20daftar%20member." 
                   class="btn btn-whatsapp" target="_blank">
                    <i class='bx bxl-whatsapp'></i> Hubungi Admin
                </a>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- DASHBOARD VIEW -->
        <div class="dashboard-view">
            
            <!-- TABS CONTENT -->
            <div id="tab-home" class="content-area">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem;">Member Card</h3>
                
                <div class="loyalty-card">
                    <div class="card-top">
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%; text-align: center;">
                            <div style="font-weight: 700; font-size: 1.2rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($businessName) ?></div>
                            <?php if ($businessLogo): ?>
                                <img src="../storage/uploads/settings/<?= htmlspecialchars($businessLogo) ?>" style="height: 60px; width: auto; max-width: 100%; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                            <div>
                                <div class="card-label">MEMBER</div>
                                <div class="card-value"><?= htmlspecialchars($customer['name']) ?></div>
                                <div style="font-size: 0.85rem; opacity: 0.7; margin-top: 5px;"><?= htmlspecialchars($customer['phone']) ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div class="card-label">TOTAL <?= strtoupper($currency) ?></div>
                                <div class="card-value" style="font-size: 1.5rem; color: var(--gold);">
                                    <?= number_format($customer['points']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="background: #fff; padding: 1.5rem; border-radius: 12px; text-align: center; border: 1px solid var(--border);">
                        <i class='bx bxs-star' style="font-size: 2rem; color: var(--gold); margin-bottom: 0.5rem;"></i>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Status</div>
                        <div style="font-weight: 600;">Active</div>
                    </div>
                    <div style="background: #fff; padding: 1.5rem; border-radius: 12px; text-align: center; border: 1px solid var(--border);">
                        <i class='bx bxs-coupon' style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"></i>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Joined</div>
                        <div style="font-weight: 600;"><?= date('M Y', strtotime($customer['created_at'])) ?></div>
                    </div>
                </div>
            </div>

            <div id="tab-promos" class="content-area hidden">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem;">Tukar <?= htmlspecialchars($currency) ?></h3>
                
                <?php if(empty($promos)): ?>
                    <div class="text-center" style="padding: 2rem; color: var(--text-muted);">
                        Belum ada promo tersedia saat ini.
                    </div>
                <?php else: ?>
                    <?php foreach($promos as $promo): ?>
                        <?php 
                        $canRedeem = $customer['points'] >= $promo['point_cost']; 
                        ?>
                        <div class="promo-card <?= !$canRedeem ? 'disabled' : '' ?>">
                            <?php if($promo['image']): ?>
                                <img src="../storage/uploads/promos/<?= htmlspecialchars($promo['image']) ?>" class="promo-img">
                            <?php else: ?>
                                <div class="promo-img" style="display: flex; align-items: center; justify-content: center; color: #ccc;">No Image</div>
                            <?php endif; ?>
                            
                            <div class="promo-body">
                                <div class="promo-title"><?= htmlspecialchars($promo['title']) ?></div>
                                <div class="promo-cost"><?= number_format($promo['point_cost']) ?> <?= $currency ?></div>
                                <div class="promo-desc"><?= htmlspecialchars($promo['description']) ?></div>
                                
                                <?php if($canRedeem): ?>
                                    <div style="padding: 0.5rem; background: #ecfdf5; color: #047857; text-align: center; border-radius: 8px; font-size: 0.85rem; font-weight: 500;">
                                        Tersedia untuk ditukar di Outlet
                                    </div>
                                <?php else: ?>
                                    <div style="padding: 0.5rem; background: #f3f4f6; color: var(--text-muted); text-align: center; border-radius: 8px; font-size: 0.85rem;">
                                        <?= $currency ?> tidak mencukupi
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="tab-history" class="content-area hidden">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem;">Riwayat Penukaran</h3>
                
                <?php if(empty($history)): ?>
                    <div class="text-center" style="padding: 2rem; color: var(--text-muted);">
                        Belum ada riwayat penukaran.
                    </div>
                <?php else: ?>
                    <?php foreach($history as $h): ?>
                        <div class="history-item">
                            <div class="history-left">
                                <div class="history-title"><?= htmlspecialchars($h['promo_title_snapshot'] ?? $h['promo_title'] ?? 'Redeem') ?></div>
                                <div class="history-date"><?= date('d M Y H:i', strtotime($h['created_at'])) ?></div>
                            </div>
                            <div class="history-cost">
                                -<?= number_format($h['point_amount']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- NAVBAR -->
            <nav class="navbar">
                <button class="nav-item active" onclick="switchTab('home')">
                    <i class='bx bxs-id-card'></i>
                    <span>Member</span>
                </button>
                <button class="nav-item" onclick="switchTab('promos')">
                    <i class='bx bxs-gift'></i>
                    <span>Promo</span>
                </button>
                <button class="nav-item" onclick="switchTab('history')">
                    <i class='bx bx-history'></i>
                    <span>Riwayat</span>
                </button>
            </nav>
        </div>

        <script>
            function switchTab(tabName) {
                // Hide all content
                document.querySelectorAll('.content-area').forEach(el => el.classList.add('hidden'));
                // Show target content
                document.getElementById('tab-' + tabName).classList.remove('hidden');
                
                // Update nav state
                document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
                // Simple index matching for now or logic to find button
                // Finding button by onclick attribute value
                const buttons = document.querySelectorAll('.nav-item');
                if (tabName === 'home') buttons[0].classList.add('active');
                if (tabName === 'promos') buttons[1].classList.add('active');
                if (tabName === 'history') buttons[2].classList.add('active');
            }
        </script>
    <?php endif; ?>

</div>

</body>
</html>
