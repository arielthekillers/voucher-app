<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid method');
}

$customer_id = (int) $_POST['customer_id'];
$promo_id    = (int) $_POST['promo_id'];

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

/* CEK POINT CUSTOMER */
$stmt = $db->prepare("
    SELECT 
    (
        COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = ? AND type = 'EARN'), 0) -
        COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = ? AND type = 'REDEEM'), 0)
    ) as total_points
");
$stmt->execute([$customer_id, $customer_id]);
$balance = $stmt->fetchColumn();

/* AMBIL INFO PROMO */
$p = $db->prepare("SELECT * FROM promos WHERE id = ? AND is_active = 1");
$p->execute([$promo_id]);
$promo = $p->fetch(PDO::FETCH_ASSOC);

if (!$promo) {
    exit('Promo tidak valid atau nonaktif');
}

/* VALIDASI POINT CUKUP */
if ($balance < $promo['point_cost']) {
    exit('Point customer tidak mencukupi');
}

/* SNAPSHOT OUTLET */
$o = $db->prepare("SELECT outlet_code, outlet_name FROM outlets WHERE id = ?");
$o->execute([$user['outlet_id']]);
$outlet = $o->fetch(PDO::FETCH_ASSOC);

if (!$outlet) {
    exit('Outlet tidak valid');
}

/* INSERT TRANSAKSI REDEEM */
$stmt = $db->prepare("
    INSERT INTO transactions (
        type,
        customer_id,
        customer_name_snapshot,
        customer_phone_snapshot,
        outlet_id,
        outlet_code_snapshot,
        outlet_name_snapshot,
        promo_id,
        promo_title_snapshot,
        point_amount,
        created_by
    ) VALUES (
        'REDEEM', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )
");

$stmt->execute([
    $customer_id,
    $customer['name'],
    $customer['phone'],
    $user['outlet_id'],
    $outlet['outlet_code'],
    $outlet['outlet_name'],
    $promo['id'],
    $promo['title'],
    $promo['point_cost'], // Disimpan sebagai nilai positif di field point_amount, tapi type=REDEEM menandakan pengurangan
    $user['id']
]);

header('Location: redeem.php?q=' . urlencode($customer['phone']));
exit;
