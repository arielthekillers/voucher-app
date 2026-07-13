<?php
session_start();

require_once '../../vendor/autoload.php';

if (!Auth::check()) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

header('Location: ' . BASE_URL . '/admin/dashboard.php');
exit;
