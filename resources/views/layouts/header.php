<?php
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= APP_NAME ?></title>

    <style>
        /* ===== RESET & BASE ===== */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f4f6f8;
            color: #333;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0d6efd;
            color: #fff;
            padding: 12px 16px;
        }

        .topbar button {
            background: none;
            border: none;
            color: #fff;
            font-size: 18px;
        }

        /* ===== LAYOUT ===== */
        .wrapper {
            display: flex;
        }

        .sidebar {
            background: #fff;
            width: 240px;
            min-height: 100vh;
            border-right: 1px solid #ddd;
            padding: 10px;
        }

        .content {
            flex: 1;
            padding: 16px;
        }

        /* ===== MENU ===== */
        .menu a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
        }

        .menu a:hover {
            background: #e9ecef;
        }

        /* ===== MOBILE ===== */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -260px;
                top: 0;
                transition: 0.3s;
                z-index: 1000;
            }

            .sidebar.active {
                left: 0;
            }

            .wrapper {
                flex-direction: column;
            }
        }
    </style>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
</head>

<body>

    <div class="topbar">
        <button onclick="toggleSidebar()">☰</button>
        <div><?= APP_NAME ?></div>
        <div><?= htmlspecialchars($user['name'] ?? '') ?></div>
    </div>

    <div class="wrapper">