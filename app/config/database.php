<?php
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'db'   => getenv('DB_NAME') ?: 'voucher',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4'
];
