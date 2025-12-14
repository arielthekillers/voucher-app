<?php
require_once __DIR__ . '/../app/config/app.php';

$db = Database::connect();

$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $db->prepare("
    INSERT INTO users (username, password, role, status)
    VALUES (?, ?, 'super_admin', 'active')
");

$stmt->execute([$username, $password]);

echo "✅ Super admin created\n";
echo "Username: admin\n";
echo "Password: admin123\n";
