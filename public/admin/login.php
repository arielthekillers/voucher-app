<?php
session_start();
require_once '../../app/config/app.php';

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f6f8;
            font-family: system-ui, sans-serif;
        }

        .login-box {
            width: 100%;
            max-width: 360px;
            background: #fff;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
        }

        .error {
            background: #ffecec;
            color: #b20000;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 12px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="login-box">
        <h2>Admin Login</h2>

        <?php if ($error): ?>
            <div class="error">
                <?php
                if ($error === 'invalid') echo 'Username atau password salah';
                if ($error === 'empty') echo 'Form tidak boleh kosong';
                ?>
            </div>
        <?php endif; ?>

        <form method="post" action="process_login.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Masuk</button>
        </form>
    </div>

</body>

</html>