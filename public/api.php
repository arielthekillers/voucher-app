<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../routes/api.php';
