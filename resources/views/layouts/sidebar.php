<?php
$user = Auth::user(); 
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="brand-logo">
            <i class='bx bxs-coupon'></i> <?= APP_NAME ?>
        </div>
    </div>

    <?php
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    function isActive($path) {
        global $current_path;
        return strpos($current_path, $path) !== false ? 'active' : '';
    }
    ?>

    <nav class="sidebar-menu">
        <div class="menu-label">Main Menu</div>
        
        <a href="<?= BASE_URL ?>/public/admin/dashboard.php" class="nav-link <?= isActive('/admin/dashboard.php') ?>">
            <i class='bx bxs-dashboard'></i> Dashboard
        </a>

        <?php if ($user && $user['role'] === 'super_admin'): ?>
            <div class="menu-label" style="margin-top: 1rem;">Admin</div>
            
            <a href="<?= BASE_URL ?>/public/admin/users/index.php" class="nav-link <?= isActive('/admin/users/') ?>">
                <i class='bx bxs-user-account'></i> User Management
            </a>
            <a href="<?= BASE_URL ?>/public/admin/outlets/index.php" class="nav-link <?= isActive('/admin/outlets/') ?>">
                <i class='bx bxs-store'></i> Master Outlet
            </a>
            <a href="<?= BASE_URL ?>/public/admin/settings/index.php" class="nav-link <?= isActive('/admin/settings/index.php') ?>">
                <i class='bx bx-cog'></i> Settings
            </a>
            <a href="<?= BASE_URL ?>/public/admin/reports/index.php" class="nav-link <?= isActive('/admin/reports/index.php') ?>">
                <i class='bx bx-bar-chart-alt-2'></i> Reports
            </a>
        <?php endif; ?>

        <div class="menu-label" style="margin-top: 1rem;">Data</div>

        <a href="<?= BASE_URL ?>/public/admin/customers/index.php" class="nav-link <?= isActive('/admin/customers/') ?>">
            <i class='bx bxs-user-detail'></i> Customers
        </a>
        <a href="<?= BASE_URL ?>/public/admin/promos/index.php" class="nav-link <?= isActive('/admin/promos/') ?>">
            <i class='bx bxs-offer'></i> Promos
        </a>

        <div class="menu-label" style="margin-top: 1rem;">Transaction</div>

        <a href="<?= BASE_URL ?>/public/admin/transactions/earn.php" class="nav-link <?= isActive('/admin/transactions/earn.php') ?>">
            <i class='bx bx-plus-circle'></i> Tambah <?= CURRENCY_NAME ?>
        </a>
        <a href="<?= BASE_URL ?>/public/admin/transactions/redeem.php" class="nav-link <?= isActive('/admin/transactions/redeem.php') ?>">
            <i class='bx bx-gift'></i> Redeem Promo
        </a>
        <a href="<?= BASE_URL ?>/public/admin/transactions/history.php" class="nav-link <?= isActive('/admin/transactions/history.php') ?>">
            <i class='bx bx-history'></i> Riwayat
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/public/admin/logout.php" class="btn btn-danger btn-sm" style="width: 100%; justify-content: center;">
            <i class='bx bx-log-out'></i> Logout
        </a>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <button onclick="toggleSidebar()" class="btn btn-sm" style="font-size: 1.5rem; padding: 0;">
                <i class='bx bx-menu'></i>
            </button>
            <h3 style="margin: 0; font-size: 1.1rem;"><?= APP_NAME ?></h3>
        </div>
        
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <div style="text-align: right;">
                <div style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= ucfirst($user['role'] ?? '') ?></div>
            </div>
            <div style="width: 36px; height: 36px; background: var(--primary); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                <?= substr($user['name'] ?? 'U', 0, 1) ?>
            </div>
        </div>
    </header>

    <div class="page-content">