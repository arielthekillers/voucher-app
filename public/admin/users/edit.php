<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$id = (int) $_GET['id'];

$db = Database::connect();

$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$id]);
$user = $user->fetch();

if (!$user) {
    exit('User tidak ditemukan');
}

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

<h2>Edit User</h2>

<div class="card" style="max-width: 800px;">
    <form action="update.php" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $user['id'] ?>">

        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= BASE_URL ?>/storage/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="avatar-preview">
                <?php else: ?>
                    <div class="avatar-preview">
                        <?= substr($user['name'] ?? 'U', 0, 1) ?>
                    </div>
                <?php endif; ?>
                <label for="avatar" class="btn btn-sm" style="background: #f1f5f9; color: #475569; cursor: pointer; border: 1px solid #cbd5e1; margin-top: 0.5rem; transition: all 0.3s ease;">
                    <i class='bx bx-camera'></i> Ganti Foto
                </label>
                <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;" onchange="previewImage(this)">
            </div>
            <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.5rem;">Format didukung: JPG, PNG, WEBP (Max 2MB)</p>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required placeholder="Masukkan nama pengguna">
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" readonly title="Username tidak dapat diubah" style="background: #f1f5f9; cursor: not-allowed; color: #64748b;">
            </div>

            <div class="form-group">
                <label>Password (kosongkan jika tidak diubah)</label>
                <input type="password" name="password">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="super_admin" <?= $user['role'] == 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label>Outlet</label>
                <select name="outlet_id">
                    <option value="">-- Tidak ada --</option>
                    <?php foreach ($outlets as $o): ?>
                        <option value="<?= $o['id'] ?>"
                            <?= $user['outlet_id'] == $o['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($o['outlet_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $user['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
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