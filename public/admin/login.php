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
    <link rel="stylesheet" href="<?= ASSET_URL ?>/admin/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            /* Premium Mesh Gradient Background */
            background-color: #f0f4f8;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Outfit', sans-serif;
            color: #1e293b;
        }
        .login-wrapper {
            position: relative;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 2.5rem;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2), 
                        inset 0 1px 0 rgba(255,255,255,0.6);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 50px rgba(0, 0, 0, 0.3), 
                        inset 0 1px 0 rgba(255,255,255,0.6);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header img {
            max-height: 90px;
            margin-bottom: 1.2rem;
            border-radius: 16px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .login-header img:hover {
            transform: scale(1.05);
        }
        .login-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.5px;
        }
        .login-header p {
            color: #64748b;
            font-size: 0.95rem;
            margin: 0;
        }
        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        .input-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
        }
        .input-group input {
            display: block;
            width: 100%;
            padding: 0.875rem 1rem;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            color: #0f172a;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.2s ease;
            outline: none;
        }
        .input-group input:focus {
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        .password-wrapper {
            position: relative;
            display: block;
        }
        .password-wrapper input {
            padding-right: 3rem; /* Space for the icon */
        }
        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            outline: none;
        }
        .toggle-password:hover, .toggle-password:focus {
            color: #6366f1;
        }
        .btn-submit {
            width: 100%;
            padding: 1rem;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            margin-top: 0.5rem;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.4);
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 1px solid #fecaca;
        }
        .sample-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.2rem auto;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.2);
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <?php if (!empty($settings['business_logo'])): ?>
                <img src="<?= BASE_URL ?>/storage/uploads/settings/<?= htmlspecialchars($settings['business_logo']) ?>" alt="Logo">
            <?php else: ?>
                <div class="sample-logo">
                    <?= htmlspecialchars(substr(!empty($settings['business_name']) ? $settings['business_name'] : 'Sample Brand', 0, 1)) ?>
                </div>
            <?php endif; ?>
            
            <h2><?= htmlspecialchars(!empty($settings['business_name']) ? $settings['business_name'] : 'Sample Brand') ?></h2>
            <p>Masuk untuk mengelola <?= htmlspecialchars(!empty($settings['business_name']) ? $settings['business_name'] : 'aplikasi') ?></p>
        </div>

        <?php include '../../resources/views/layouts/flash_messages.php'; ?>

        <form method="post" action="process_login.php">
            <?= csrf_field() ?>
            
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                    <button type="button" class="toggle-password" id="togglePassword" aria-label="Toggle password visibility">
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">Masuk</button>
        </form>
    </div>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    togglePassword.addEventListener('click', function () {
        // Toggle the type attribute
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Toggle the icon
        if (type === 'text') {
            eyeIcon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            `;
        } else {
            eyeIcon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            `;
        }
    });
</script>
</body>
</html>