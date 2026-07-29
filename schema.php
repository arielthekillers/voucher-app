<?php
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'voucher';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
$stmt = $pdo->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo "Table: $table\n";
    $stmt2 = $pdo->query("SHOW COLUMNS FROM $table");
    $columns = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo " - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
