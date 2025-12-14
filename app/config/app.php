<?php

/**
 * Global App Configuration
 */

// ========================
// ENVIRONMENT
// ========================
define('APP_ENV', 'development'); // development | production
define('APP_DEBUG', true);
define('ROOT_PATH', realpath(__DIR__ . '/../../'));

// ========================
// DB CONNECTION & SETTINGS
// ========================
$db_settings = [];
try {
    // Database class is autoloaded via classmap
    $db = Database::connect();
    $db_settings = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    // Fail silently or use defaults if DB not ready
    error_log("Failed to load settings: " . $e->getMessage());
}

// ========================
// GLOBAL CONSTANTS
// ========================
define('APP_NAME', $db_settings['business_name'] ?? 'Voucher App');
define('BUSINESS_NAME', $db_settings['business_name'] ?? 'Voucher App');
define('CURRENCY_NAME', $db_settings['currency_name'] ?? 'Point');
define('TIMEZONE', $db_settings['time_zone'] ?? 'Asia/Jakarta');

date_default_timezone_set(TIMEZONE);

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


