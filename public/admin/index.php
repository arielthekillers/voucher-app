<?php
session_start();

require_once '../../app/config/app.php';
require_once '../../app/core/Database.php';
require_once '../../app/core/Auth.php';

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

header('Location: dashboard.php');
exit;
