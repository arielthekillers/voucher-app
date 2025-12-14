<?php
$user = Auth::user(); // ambil user login dari session
?>

<div class="sidebar">
    <div class="menu">
        <a href="<?= BASE_URL ?>/public/admin/dashboard.php">Dashboard</a>

        <?php if ($user && $user['role'] === 'super_admin'): ?>
            <a href="<?= BASE_URL ?>/public/admin/users/index.php">User Management</a>
            <a href="<?= BASE_URL ?>/public/admin/outlets/index.php">Master Outlet</a>
            <a href="<?= BASE_URL ?>/public/admin/settings/index.php">Settings</a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/public/admin/customers/index.php">Customers</a>
        <a href="<?= BASE_URL ?>/public/admin/promos/index.php">Promos</a>

        <a href="<?= BASE_URL ?>/public/admin/transactions/earn.php">Tambah Point</a>
        <a href="<?= BASE_URL ?>/public/admin/transactions/redeem.php">Redeem Promo</a>
        <a href="<?= BASE_URL ?>/public/admin/transactions/history.php">Riwayat</a>

        <hr>
        <a href="<?= BASE_URL ?>/public/admin/logout.php">Logout</a>
    </div>
</div>

<div class="content">