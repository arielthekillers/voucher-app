<?php
session_start();

require_once '../../app/config/app.php';
require_once '../../app/core/Database.php';
require_once '../../app/core/Auth.php';

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header('Location: login.php?error=empty');
    exit;
}

if (!Auth::attempt($username, $password)) {
    header('Location: login.php?error=invalid');
    exit;
}

header('Location: dashboard.php');
exit;
