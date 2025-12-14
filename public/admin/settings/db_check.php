<?php
require_once '../../../vendor/autoload.php';
$db = Database::connect();
try {
    $columns = $db->query("DESCRIBE settings");
    echo "COLUMNS:<pre>";
    foreach($columns as $row) {
        print_r($row);
    }
    echo "</pre>";

    $data = $db->query("SELECT * FROM settings");
    echo "DATA:<pre>";
    foreach($data as $row) {
        print_r($row);
    }
    echo "</pre>";
} catch (Exception $e) {
    echo $e->getMessage();
}
