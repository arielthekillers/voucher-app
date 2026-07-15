<?php
session_start();
require_once '../../../vendor/autoload.php';
auth_required();

$db = Database::connect();
$current_user = Auth::user();

// Pastikan hanya super_admin
if ($current_user['role'] !== 'super_admin') {
    die("Akses ditolak.");
}

$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$stmt_capster = $db->prepare("
    SELECT u.name as capster_name, 
           COUNT(t.id) as total_trx, 
           SUM(CASE WHEN t.type = 'EARN' THEN t.point_amount ELSE 0 END) as total_earn,
           SUM(CASE WHEN t.type = 'EARN' THEN t.purchase_amount ELSE 0 END) as total_income,
           SUM(CASE WHEN t.type = 'REDEEM' THEN t.point_amount ELSE 0 END) as total_redeem
    FROM transactions t
    LEFT JOIN users u ON t.created_by = u.id
    WHERE DATE(t.created_at) BETWEEN ? AND ?
    GROUP BY u.id
    ORDER BY total_trx DESC
");
$stmt_capster->execute([$start_date, $end_date]);
$capsters = $stmt_capster->fetchAll(PDO::FETCH_ASSOC);

// Output CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Laporan_Performa_Kasir_' . $start_date . '_to_' . $end_date . '.csv');

$output = fopen('php://output', 'w');

// Tulis UTF-8 BOM untuk kompatibilitas Microsoft Excel agar bisa baca karakter khusus
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header kolom
fputcsv($output, ['Nama Kasir', 'Total Trx', 'Pendapatan (Rp)', 'Total Earn Stamp', 'Total Redeem Stamp']);

// Baris data
if (count($capsters) > 0) {
    foreach ($capsters as $row) {
        fputcsv($output, [
            $row['capster_name'] ?: 'Sistem',
            $row['total_trx'],
            $row['total_income'],
            $row['total_earn'],
            $row['total_redeem']
        ]);
    }
} else {
    fputcsv($output, ['Tidak ada data pada periode ini']);
}

fclose($output);
exit;
