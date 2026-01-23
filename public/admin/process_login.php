<?php
session_start();

require_once '../../vendor/autoload.php';

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    $_SESSION['flash_error'] = "Username dan Password wajib diisi";
    header('Location: login.php');
    exit;
}

if (!Auth::attempt($username, $password)) {
    $_SESSION['flash_error'] = "Username atau password salah";
    header('Location: login.php');
    exit;
}

$user = Auth::user();
$_SESSION['flash_success'] = "Selamat datang, " . htmlspecialchars($user['name']) . "!";
header('Location: dashboard.php');
exit;
