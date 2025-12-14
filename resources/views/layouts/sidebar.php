<div class="sidebar">
    <div class="menu">
        <a href="/admin/dashboard.php">Dashboard</a>

        <?php if ($user['role'] === 'super_admin'): ?>
            <a href="/admin/users/index.php">User Management</a>
            <a href="/admin/outlets/index.php">Master Outlet</a>
            <a href="/admin/settings/index.php">Settings</a>
        <?php endif; ?>

        <a href="/admin/customers/index.php">Customers</a>
        <a href="/admin/promos/index.php">Promos</a>

        <a href="/admin/transactions/earn.php">Tambah Point</a>
        <a href="/admin/transactions/redeem.php">Redeem Promo</a>
        <a href="/admin/transactions/history.php">Riwayat</a>

        <hr>
        <a href="/admin/logout.php">Logout</a>
    </div>
</div>

<div class="content">