<?php
session_start();

require_once '../../../vendor/autoload.php';

role_required('super_admin');

$db = Database::connect();

$users = $db->query("
    SELECT u.*, o.outlet_name
    FROM users u
    LEFT JOIN outlets o ON u.outlet_id = o.id
    ORDER BY u.id DESC
")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<h2>User Management</h2>
<a href="create.php" class="btn btn-primary" style="margin-bottom: 1rem;">
    <i class='bx bx-plus'></i> Tambah User
</a>

<div class="user-grid">
    <?php foreach ($users as $u): ?>
        <?php 
            $initial = strtoupper(substr($u['name'], 0, 1)); 
        ?>
        <div class="user-card">
            <div class="user-header">
                <div class="user-avatar" <?php if(!empty($u['avatar'])) echo 'style="background: transparent;"'; ?>>
                    <?php if (!empty($u['avatar'])): ?>
                        <img src="<?= BASE_URL ?>/storage/uploads/avatars/<?= htmlspecialchars($u['avatar']) ?>" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <?php else: ?>
                        <?= $initial ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <h3><?= htmlspecialchars($u['name']) ?></h3>
                    <p><?= htmlspecialchars($u['username']) ?></p>
                </div>
                <div class="user-actions">
                    <a href="edit.php?id=<?= $u['id'] ?>" class="btn-icon text-primary" title="Edit">
                        <i class='bx bx-edit'></i>
                    </a>
                    <?php if ($u['role'] !== 'super_admin'): ?>
                    <form action="delete.php" method="POST" style="margin:0;" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn-icon text-danger" title="Hapus">
                            <i class='bx bx-trash'></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="user-tags">
                <span class="badge badge-role-<?= $u['role'] ?>"><?= str_replace('_', ' ', $u['role']) ?></span>
                <span class="badge badge-status-<?= $u['status'] ?>"><?= $u['status'] ?></span>
                <?php if ($u['outlet_name']): ?>
                    <span class="text-sm text-muted" style="margin-left: auto;">
                        <i class='bx bx-store-alt'></i> <?= htmlspecialchars($u['outlet_name']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include '../../../resources/views/layouts/footer.php'; ?>