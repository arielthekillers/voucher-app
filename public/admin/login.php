<?php
session_start();
require_once '../../vendor/autoload.php';

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = $_GET['error'] ?? null;

$db = Database::connect();
$settings = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - <?= htmlspecialchars($settings['business_name'] ?? APP_NAME) ?></title>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/admin/assets/css/admin.css">
    <style>
        body {
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }
        .login-card {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header img {
            max-height: 80px;
            margin-bottom: 1rem;
            border-radius: 8px;
        }
        .login-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <?php if (!empty($settings['business_logo'])): ?>
            <img src="<?= BASE_URL ?>/storage/uploads/settings/<?= htmlspecialchars($settings['business_logo']) ?>" alt="Logo">
        <?php endif; ?>
        
        <h2><?= htmlspecialchars($settings['business_name'] ?? 'Login Admin') ?></h2>
        <p style="color: #6b7280; font-size: 0.875rem;">Masuk untuk mengelola <?= htmlspecialchars($settings['business_name'] ?? 'aplikasi') ?></p>
    </div>

    <?php if ($error): ?>
        <div class="alert-error">
            <?php
            if ($error === 'invalid') echo 'Username atau password salah';
            elseif ($error === 'empty') echo 'Form tidak boleh kosong';
            else echo htmlspecialchars($error);
            ?>
        </div>
    <?php endif; ?>

    <form method="post" action="process_login.php">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Masuk</button>
    </form>
</div>

</body>
</html>