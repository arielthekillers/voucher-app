<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

$db = Database::connect();

// Fetch Settings
$settings = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$businessName = $settings['business_name'] ?? APP_NAME;
$businessLogo = $settings['business_logo'] ?? '';

// Handle Form Submission
$error = null;
$success = null;
$name = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (!$name || !$phone) {
        $error = 'Semua field harus diisi';
    } else {
        // Check for duplicate phone number
        $check = $db->prepare("SELECT id FROM customers WHERE phone = ?");
        $check->execute([$phone]);
        
        if ($check->fetch()) {
            $error = 'Nomor HP sudah terdaftar. Silakan login dengan nomor tersebut.';
        } else {
            // Insert new customer
            $stmt = $db->prepare("INSERT INTO customers (name, phone) VALUES (?, ?)");
            
            if ($stmt->execute([$name, $phone])) {
                // Redirect to login with success message
                $_SESSION['registration_success'] = true;
                header('Location: index.php?phone=' . urlencode($phone));
                exit;
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title><?= htmlspecialchars($businessName) ?> - Daftar Member</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <style>
        :root {
            --primary: #111827;
            --gold: #d4af37;
            --gold-gradient: linear-gradient(135deg, #fce38a 0%, #d4af37 100%);
            --bg: #f9fafb;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --error: #ef4444;
            --success: #10b981;
        }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: 'Outfit', sans-serif;
            background: #f3f4f6;
            color: var(--text-main);
        }
        body {
            display: flex;
            justify-content: center;
        }
        .app-container {
            width: 100%;
            max-width: 480px;
            height: 100%;
            background: #fff;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .register-view {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
            overflow-y: auto;
        }
        .logo-img {
            max-width: 120px;
            margin-bottom: 1.5rem;
            border-radius: 12px;
        }
        .input-group {
            width: 100%;
            margin-bottom: 1rem;
            text-align: left;
        }
        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .form-input {
            width: 100%;
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-input:focus { border-color: var(--gold); }
        .form-input.error { border-color: var(--error); }
        
        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: opacity 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
        }
        .btn-secondary {
            background: #fff;
            color: var(--text-main);
            border: 1px solid var(--border);
        }
        .btn:active { opacity: 0.9; }

        .alert {
            width: 100%;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.2s;
        }
        .link:hover { opacity: 0.7; }
    </style>
</head>
<body>

<div class="app-container">
    <div class="register-view">
        <?php if ($businessLogo): ?>
            <img src="/storage/uploads/settings/<?= htmlspecialchars($businessLogo) ?>" alt="Logo" class="logo-img">
        <?php else: ?>
            <div style="font-size: 2rem; margin-bottom: 1rem; font-weight: bold;"><?= htmlspecialchars($businessName) ?></div>
        <?php endif; ?>
        
        <h2 style="margin-bottom: 0.5rem;">Daftar Member</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Daftarkan diri Anda untuk mendapatkan Loyalty E-Card</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class='bx bxs-error-circle'></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST" style="width: 100%;">
            <div class="input-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" class="form-input <?= $error ? 'error' : '' ?>" 
                       placeholder="Masukkan nama lengkap" 
                       value="<?= htmlspecialchars($name) ?>" 
                       required autofocus>
            </div>
            
            <div class="input-group">
                <label>Nomor WhatsApp / HP</label>
                <input type="tel" name="phone" class="form-input <?= $error ? 'error' : '' ?>" 
                       placeholder="08xxxxxxxxxx" 
                       value="<?= htmlspecialchars($phone) ?>" 
                       required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                Daftar Sekarang <i class='bx bx-right-arrow-alt'></i>
            </button>
        </form>
        
        <div style="margin-top: 1.5rem; color: var(--text-muted); font-size: 0.9rem;">
            Sudah punya akun? <a href="index.php" class="link">Login di sini</a>
        </div>
    </div>
</div>

</body>
</html>
