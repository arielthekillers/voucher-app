<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$db = Database::connect();

$q = $_GET['q'] ?? '';
$phone_exact = $_GET['phone'] ?? '';

if ($phone_exact) {
    $stmt = $db->prepare("SELECT id, name, phone FROM customers WHERE phone = ?");
    $stmt->execute([$phone_exact]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($customer ? [$customer] : []);
    exit;
}

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $db->prepare("
    SELECT id, name, phone FROM customers
    WHERE name LIKE ? OR phone LIKE ?
    ORDER BY name ASC
    LIMIT 20
");
$stmt->execute(["%$q%", "%$q%"]);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($customers);
