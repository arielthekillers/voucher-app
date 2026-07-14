<?php
session_start();

require_once '../../vendor/autoload.php';

auth_required();

$current_user = Auth::user();
$db = Database::connect();

// Make sure upload directory exists
$upload_dir = ROOT_PATH . '/storage/uploads/avatars';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $avatar_file = $_FILES['avatar'] ?? null;

    if (empty($name)) {
        $_SESSION['flash_error'] = "Nama tidak boleh kosong.";
    } elseif (!empty($password) && $password !== $password_confirm) {
        $_SESSION['flash_error'] = "Password konfirmasi tidak cocok.";
    } else {
        $avatar_name = $current_user['avatar'] ?? null;

        if ($avatar_file && $avatar_file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($avatar_file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $new_avatar = 'avatar_' . $current_user['id'] . '_' . time() . '.' . $ext;
                if (move_uploaded_file($avatar_file['tmp_name'], $upload_dir . '/' . $new_avatar)) {
                    // Delete old avatar if exists
                    if ($avatar_name && file_exists($upload_dir . '/' . $avatar_name)) {
                        unlink($upload_dir . '/' . $avatar_name);
                    }
                    $avatar_name = $new_avatar;
                }
            } else {
                $_SESSION['flash_error'] = "Format file gambar tidak didukung.";
                header('Location: profile.php');
                exit;
            }
        }

        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET name = ?, avatar = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $avatar_name, $hashed, $current_user['id']]);
        } else {
            $stmt = $db->prepare("UPDATE users SET name = ?, avatar = ? WHERE id = ?");
            $stmt->execute([$name, $avatar_name, $current_user['id']]);
        }

        // Update session
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['avatar'] = $avatar_name; // update avatar in session
        $_SESSION['flash_success'] = "Profil berhasil diupdate.";
        
        header('Location: profile.php');
        exit;
    }
}
?>

<?php include ROOT_PATH . '/resources/views/layouts/header.php'; ?>
<?php include ROOT_PATH . '/resources/views/layouts/sidebar.php'; ?>

<style>
    .profile-form-group {
        margin-bottom: 1.2rem;
    }
    .profile-form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #334155;
    }
    .avatar-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #64748b;
        margin-bottom: 1rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
</style>

<h2>Edit Profile</h2>

<div class="card" style="max-width: 500px;">
    <form action="profile.php" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="profile-form-group" style="text-align: center;">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <?php if (!empty($current_user['avatar'])): ?>
                    <img src="<?= BASE_URL ?>/storage/uploads/avatars/<?= htmlspecialchars($current_user['avatar']) ?>" alt="Avatar" class="avatar-preview">
                <?php else: ?>
                    <div class="avatar-preview">
                        <?= substr($current_user['name'] ?? 'U', 0, 1) ?>
                    </div>
                <?php endif; ?>
                <label for="avatar" class="btn btn-sm" style="background: #f1f5f9; color: #475569; cursor: pointer; border: 1px solid #cbd5e1;">
                    <i class='bx bx-camera'></i> Ganti Foto
                </label>
                <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;" onchange="previewImage(this)">
            </div>
        </div>

        <div class="profile-form-group">
            <label>Username</label>
            <input type="text" value="<?= htmlspecialchars($current_user['username']) ?>" readonly title="Username tidak dapat diubah" style="background: #f1f5f9; cursor: not-allowed; color: #64748b; width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0;">
        </div>

        <div class="profile-form-group">
            <label>Nama</label>
            <input type="text" name="name" value="<?= htmlspecialchars($current_user['name']) ?>" required style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0;">
        </div>

        <div class="profile-form-group">
            <label>Password Baru (kosongkan jika tidak diubah)</label>
            <input type="password" name="password" style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0;">
        </div>

        <div class="profile-form-group">
            <label>Konfirmasi Password Baru</label>
            <input type="password" name="password_confirm" style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0;">
        </div>

        <div class="form-actions" style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Update Profile</button>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            const previewContainer = input.parentElement;
            const existingImg = previewContainer.querySelector('.avatar-preview');
            
            if (existingImg.tagName === 'IMG') {
                existingImg.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'avatar-preview';
                img.alt = 'Avatar';
                previewContainer.insertBefore(img, existingImg);
                existingImg.remove();
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include ROOT_PATH . '/resources/views/layouts/footer.php'; ?>
