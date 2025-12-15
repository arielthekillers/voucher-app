<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$db = Database::connect();

// Fetch all settings
$data = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$settings = $data; // format: ['key' => 'value']

?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>Settings</h2>

<div style="margin-bottom: 1.5rem;">
    <?php flash_display(); ?>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <!-- Tabs Header -->
    <div style="display: flex; border-bottom: 1px solid var(--border-color); background: #f9fafb;">
        <button class="tab-btn active" onclick="switchTab(event, 'bisnis')">
            <i class='bx bxs-business'></i> Bisnis
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'app')">
            <i class='bx bxs-cog'></i> App
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'whatsapp')">
            <i class='bx bxl-whatsapp'></i> WhatsApp
        </button>
    </div>

    <!-- Tab Content -->
    <form action="update.php" method="POST" enctype="multipart/form-data" style="padding: 2rem;">
        <?= csrf_field() ?>
        
        <!-- Bisnis Settings -->
        <div id="bisnis" class="tab-content">
            <h3 style="margin-bottom: 1.5rem;">Informasi Bisnis</h3>

            <label>Logo Bisnis</label>
            <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                <?php if (!empty($settings['business_logo'])): ?>
                    <img src="../../../storage/uploads/settings/<?= htmlspecialchars($settings['business_logo']) ?>" 
                         alt="Business Logo" 
                         style="max-width: 100px; max-height: 100px; border-radius: 8px; border: 1px solid var(--border-color);">
                <?php endif; ?>
                <input type="file" name="business_logo" accept="image/*">
            </div>
            
            <label>Nama Bisnis</label>
            <input type="text" name="business_name" value="<?= htmlspecialchars($settings['business_name'] ?? '') ?>" placeholder="Contoh: My Awesome Store">

            <label>Alamat</label>
            <textarea name="business_address" rows="3"><?= htmlspecialchars($settings['business_address'] ?? '') ?></textarea>

            <label>Google Review Link</label>
            <input type="url" name="google_review_link" value="<?= htmlspecialchars($settings['google_review_link'] ?? '') ?>" placeholder="https://g.page/r/...">

            <label>Email Owner / Kontak</label>
            <input type="email" name="business_email" value="<?= htmlspecialchars($settings['business_email'] ?? '') ?>">

            <label>Nomor Telepon</label>
            <input type="text" name="business_phone" value="<?= htmlspecialchars($settings['business_phone'] ?? '') ?>">
        </div>

        <!-- App Settings -->
        <div id="app" class="tab-content" style="display: none;">
            <h3 style="margin-bottom: 1.5rem;">Konfigurasi Aplikasi</h3>

            <label>Timezone</label>
            <select name="time_zone">
                <?php 
                $zones = ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'UTC'];
                $current_zone = $settings['time_zone'] ?? 'Asia/Jakarta';
                ?>
                <?php foreach($zones as $zone): ?>
                    <option value="<?= $zone ?>" <?= $current_zone == $zone ? 'selected' : '' ?>><?= $zone ?></option>
                <?php endforeach; ?>
            </select>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: -0.5rem; margin-bottom: 1rem;">
                Zona waktu ini akan mempengaruhi pencatatan transaksi.
            </div>

            <label>Nama Mata Uang Point</label>
            <input type="text" name="currency_name" value="<?= htmlspecialchars($settings['currency_name'] ?? 'Point') ?>" placeholder="Contoh: Point, Coin, Stamp">
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: -0.5rem; margin-bottom: 1rem;">
                Sebutan untuk reward yang didapatkan customer.
            </div>
            
            <label>Favicon URL (Optional)</label>
            <input type="text" name="favicon_url" value="<?= htmlspecialchars($settings['favicon_url'] ?? '') ?>" placeholder="https://...">
        </div>

        <!-- WhatsApp Settings -->
        <div id="whatsapp" class="tab-content" style="display: none;">
            <h3 style="margin-bottom: 1.5rem;">Integrasi WhatsApp</h3>
            
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 1.5rem;">
                <input type="checkbox" name="whatsapp_enabled" value="1" <?= ($settings['whatsapp_enabled'] ?? '0') == '1' ? 'checked' : '' ?> style="width: auto; margin: 0;">
                Aktifkan Notifikasi WhatsApp
            </label>

            <label>API Endpoint</label>
            <input type="text" name="whatsapp_endpoint" value="<?= htmlspecialchars($settings['whatsapp_endpoint'] ?? '') ?>" placeholder="https://api.whatsapp-gateway.com/send">

            <label>API Token / Key</label>
            <input type="password" name="whatsapp_api_token" value="<?= htmlspecialchars($settings['whatsapp_api_token'] ?? '') ?>">

            <label>Device Key (ID)</label>
            <input type="text" name="whatsapp_device_id" value="<?= htmlspecialchars($settings['whatsapp_device_id'] ?? '') ?>">
        </div>

        <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary">
                <i class='bx bx-save'></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<style>
    .tab-btn {
        background: transparent;
        border: none;
        padding: 1rem 1.5rem;
        cursor: pointer;
        font-weight: 600;
        color: var(--text-muted);
        border-bottom: 2px solid transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .tab-btn:hover {
        background: rgba(0,0,0,0.02);
        color: var(--text-main);
    }
    .tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
        background: #fff;
    }
    .tab-btn i {
        font-size: 1.2rem;
    }
</style>

<script>
    function switchTab(evt, tabName) {
        evt.preventDefault();
        
        // Hide all tab content
        const tabContents = document.getElementsByClassName("tab-content");
        for (let i = 0; i < tabContents.length; i++) {
            tabContents[i].style.display = "none";
        }

        // Remove active class from buttons
        const tabBtns = document.getElementsByClassName("tab-btn");
        for (let i = 0; i < tabBtns.length; i++) {
            tabBtns[i].className = tabBtns[i].className.replace(" active", "");
        }

        // Show current tab and add active class
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }
</script>

<?php include '../../../resources/views/layouts/footer.php'; ?>
