<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid method');
}

CSRF::check($_POST['csrf_token'] ?? '');

$db = Database::connect();

// Whitelist allowed keys to prevent pollution
$allowed_keys = [
    'business_name', 'business_address', 'business_email', 'business_phone',
    'time_zone', 'currency_name', 'google_review_link', 'favicon_url',
    'whatsapp_enabled', 'whatsapp_endpoint', 'whatsapp_api_token', 'whatsapp_device_id'
];

if (!empty($_FILES['business_logo']['name'])) {
    $file = $_FILES['business_logo'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($mime, $allowedMimes) || !in_array($ext, $allowedExts)) {
        flash('error', 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.');
        header('Location: index.php');
        exit;
    }

    if ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
        flash('error', 'File too large. Max 2MB.');
        header('Location: index.php');
        exit;
    }

    $uploadDir = '../../../storage/uploads/settings/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Get old logo to delete
    try {
        $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = ?");
        $stmt->execute(['business_logo']);
        $oldLogo = $stmt->fetchColumn();

        $imageName = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $imageName)) {
            $_POST['business_logo'] = $imageName;
            $allowed_keys[] = 'business_logo';
            
            // Delete old logo
            if ($oldLogo && file_exists($uploadDir . $oldLogo)) {
                unlink($uploadDir . $oldLogo);
            }
        } else {
            flash('error', 'Failed to upload file.');
            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        // Continue but maybe log error? 
        // For now just fail upload
        flash('error', 'Database error during upload check.');
        header('Location: index.php');
        exit;
    }
}

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
    
    // Clear cache if needed or just redirect
    flash('success', 'Settings updated successfully!');
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    $db->rollBack();
    flash('error', 'Error updating settings: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}
