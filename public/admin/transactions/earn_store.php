<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$customer_id = (int) $_POST['customer_id'];
$purchase    = (float) $_POST['amount'];
$point       = (int) $_POST['point'];

$user = Auth::user();

/* VALIDASI USER HARUS PUNYA OUTLET */
if (empty($user['outlet_id'])) {
    exit('Akun ini tidak terikat ke outlet');
}

$db = Database::connect();

/* SNAPSHOT CUSTOMER */
$c = $db->prepare("SELECT name, phone FROM customers WHERE id = ?");
$c->execute([$customer_id]);
$customer = $c->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    exit('Customer tidak ditemukan');
}

/* SNAPSHOT OUTLET (PAKAI id) */
$o = $db->prepare("SELECT outlet_code, outlet_name FROM outlets WHERE id = ?");
$o->execute([$user['outlet_id']]);
$outlet = $o->fetch(PDO::FETCH_ASSOC);

if (!$outlet) {
    exit('Outlet tidak valid');
}

/* INSERT TRANSAKSI */
$stmt = $db->prepare("
    INSERT INTO transactions (
        type,
        customer_id,
        customer_name_snapshot,
        customer_phone_snapshot,
        outlet_id,
        outlet_code_snapshot,
        outlet_name_snapshot,
        purchase_amount,
        point_amount,
        created_by
    ) VALUES (
        'EARN', ?, ?, ?, ?, ?, ?, ?, ?, ?
    )
");

$stmt->execute([
    $customer_id,
    $customer['name'],
    $customer['phone'],
    $user['outlet_id'],
    $outlet['outlet_code'],
    $outlet['outlet_name'],
    $purchase,
    $point,
    $user['id']
]);

header('Location: earn.php');
exit;
