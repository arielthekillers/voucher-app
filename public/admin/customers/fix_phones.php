<?php
session_start();
require_once '../../../vendor/autoload.php';
auth_required();

$db = Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check($_POST['csrf_token'] ?? '');
    
    $customers = $db->query("SELECT id, phone FROM customers")->fetchAll();
    $updated = 0;
    $skipped = 0;

    foreach ($customers as $row) {
        $original = $row['phone'];
        if (empty($original)) continue;
        
        // Remove all non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', $original);
        
        // Ensure it starts with 62 instead of 0 or just 8
        if (strpos($clean, '08') === 0) {
            $clean = '62' . substr($clean, 1);
        } elseif (strpos($clean, '8') === 0) {
            $clean = '62' . $clean;
        }
        
        if ($clean !== $original) {
            try {
                $stmt = $db->prepare("UPDATE customers SET phone = ? WHERE id = ?");
                $stmt->execute([$clean, $row['id']]);
                $updated++;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    // Duplicate entry constraint violation
                    $skipped++;
                } else {
                    throw $e;
                }
            }
        }
    }
    
    $msg = "$updated nomor HP berhasil distandarisasi.";
    if ($skipped > 0) {
        $msg .= " Namun, $skipped nomor dilewati karena format jadinya akan bentrok (duplikat) dengan customer lain.";
    }
    
    $_SESSION['flash_success'] = $msg;
    header('Location: index.php');
    exit;
}
