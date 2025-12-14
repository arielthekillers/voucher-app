<?php
session_start();

require_once '../../vendor/autoload.php';

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

header('Location: dashboard.php');
exit;
