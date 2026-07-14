<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$db = Database::connect();
$outlets = $db->query("SELECT * FROM outlets ORDER BY outlet_name")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<style>
    .avatar-preview {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #64748b;
        margin-bottom: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
</style>

<h2>Tambah User</h2>

<div class="card" style="max-width: 800px;">
    <form action="store.php" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div class="avatar-preview">
                    <i class='bx bx-user'></i>
                </div>
                <label for="avatar" class="btn btn-sm" style="background: #f1f5f9; color: #475569; cursor: pointer; border: 1px solid #cbd5e1; margin-top: 0.5rem; transition: all 0.3s ease;">
                    <i class='bx bx-camera'></i> Pilih Foto
                </label>
                <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;" onchange="previewImage(this)">
            </div>
            <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.5rem;">Format didukung: JPG, PNG, WEBP (Max 2MB)</p>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" required placeholder="Masukkan nama pengguna">
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Pilih username unik">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label>Outlet</label>
                <select name="outlet_id">
                    <option value="">-- Tidak ada (Super Admin) --</option>
                    <?php foreach ($outlets as $o): ?>
                        <option value="<?= $o['id'] ?>">
                            <?= htmlspecialchars($o['outlet_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            const previewContainer = input.parentElement;
            const existingPreview = previewContainer.querySelector('.avatar-preview');
            
            if (existingPreview.tagName === 'IMG') {
                existingPreview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'avatar-preview';
                img.alt = 'Avatar';
                previewContainer.insertBefore(img, existingPreview);
                existingPreview.remove();
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../../../resources/views/layouts/footer.php'; ?>