<?php
session_start();

require_once '../../vendor/autoload.php';

auth_required();

include ROOT_PATH . '/resources/views/layouts/header.php';
include ROOT_PATH . '/resources/views/layouts/sidebar.php';
?>


<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Card 1 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Total Users</div>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem;">1,234</div>
            </div>
            <div style="background: #eef2ff; color: var(--primary); padding: 0.5rem; border-radius: 0.5rem;">
                <i class='bx bxs-user-account' style="font-size: 1.25rem;"></i>
            </div>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Total Transaksi</div>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem;">845</div>
            </div>
            <div style="background: #ecfdf5; color: #059669; padding: 0.5rem; border-radius: 0.5rem;">
                <i class='bx bxs-cart' style="font-size: 1.25rem;"></i>
            </div>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Active Promos</div>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem;">12</div>
            </div>
            <div style="background: #fffbeb; color: #d97706; padding: 0.5rem; border-radius: 0.5rem;">
                <i class='bx bxs-offer' style="font-size: 1.25rem;"></i>
            </div>
        </div>
    </div>

    <!-- Card 4 -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Revenue</div>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem;">Rp 45M</div>
            </div>
            <div style="background: #fef2f2; color: #dc2626; padding: 0.5rem; border-radius: 0.5rem;">
                <i class='bx bxs-wallet' style="font-size: 1.25rem;"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <h3>Selamat Datang di Dashboard</h3>
    <p style="color: var(--text-muted);">
        Anda login sebagai <strong><?= htmlspecialchars($_SESSION['user']['username'] ?? 'User') ?></strong>. 
        Gunakan menu di sbelah kiri untuk mengelola aplikasi.
    </p>
</div>


<?php include ROOT_PATH . '/resources/views/layouts/footer.php'; ?>