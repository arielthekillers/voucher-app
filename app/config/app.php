<?php

/**
 * Global App Configuration
 */

date_default_timezone_set('Asia/Jakarta');

// ========================
// ENVIRONMENT
// ========================
define('APP_ENV', 'development'); // development | production
define('APP_DEBUG', true);
define('ROOT_PATH', realpath(__DIR__ . '/../../'));
// ========================
// APP INFO
// ========================
define('APP_NAME', 'Voucher App');
define('APP_VERSION', '1.0.0');

// ========================
// BASE URL
// ========================
// Sesuaikan jika pakai subfolder
define('BASE_URL', '/voucher');
// ========================


// ========================
// ERROR HANDLING
// ========================
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ========================
// AUTOLOAD CORE FILES
// ========================
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
