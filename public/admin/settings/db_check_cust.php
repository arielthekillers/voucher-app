<?php
require_once '../../../vendor/autoload.php';
$db = Database::connect();
try {
    $columns = $db->query("DESCRIBE customers")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} catch (Exception $e) {
    echo $e->getMessage();
}
