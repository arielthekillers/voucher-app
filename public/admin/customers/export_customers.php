<?php
session_start();
require_once '../../../vendor/autoload.php';
auth_required();

$db = Database::connect();
$current_user = Auth::user();

// Fetch data
$stmt = $db->query("
    SELECT c.name, c.phone, c.created_at,
       (
           COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'EARN'), 0) -
           COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'REDEEM'), 0)
       ) as current_stamp
    FROM customers c
    ORDER BY c.id DESC
");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Data_Customer_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Tulis UTF-8 BOM untuk kompatibilitas Microsoft Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header kolom
fputcsv($output, ['Nama Pelanggan', 'Nomor HP', 'Total Saldo Stamp Saat Ini', 'Tanggal Bergabung']);

// Baris data
if (count($customers) > 0) {
    foreach ($customers as $row) {
        fputcsv($output, [
            $row['name'],
            $row['phone'] . ' ', // Tambah spasi agar Excel tidak mengonversi no HP 085 jadi 85
            $row['current_stamp'],
            date('Y-m-d H:i:s', strtotime($row['created_at']))
        ]);
    }
} else {
    fputcsv($output, ['Tidak ada data customer']);
}

fclose($output);
exit;
