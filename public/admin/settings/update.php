<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid method');
}

$db = Database::connect();

// Whitelist allowed keys to prevent pollution
$allowed_keys = [
    'business_name', 'business_address', 'business_email', 'business_phone',
    'time_zone', 'currency_name', 'favicon_url',
    'whatsapp_enabled', 'whatsapp_endpoint', 'whatsapp_api_token', 'whatsapp_device_id'
];

try {
    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO settings (`key`, `value`) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
    ");

    foreach ($allowed_keys as $key) {
        $value = $_POST[$key] ?? '';
        
        // Sanitize logic if needed
        if ($key === 'whatsapp_enabled') {
            $value = isset($_POST[$key]) ? '1' : '0'; // Handle checkbox
        }

        $stmt->execute([$key, $value]);
    }

    $db->commit();
    
    // Redirect with success (simplistic/naive implementation)
    header('Location: index.php?status=success');

} catch (Exception $e) {
    $db->rollBack();
    exit('Error saving settings: ' . $e->getMessage());
}
