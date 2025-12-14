<?php
session_start();
require_once '../../../vendor/autoload.php';
auth_required();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit('Method not allowed');

$customer_id = $_POST['customer_id'];
$message = $_POST['message'];

$db = Database::connect();
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if ($customer && $message) {
    require_once ROOT_PATH . '/app/services/WhatsAppService.php';
    $wa = new WhatsAppService();
    $result = $wa->send($customer['phone'], $message);

    if ($result && ($result['status'] ?? false)) {
        header('Location: index.php?status=sent');
    } else {
        // Log error or show message
        header('Location: index.php?status=error');
    }
} else {
    header('Location: index.php?status=error');
}
exit;
