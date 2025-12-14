<?php
session_start();

require_once __DIR__ . '/../app/config/app.php';

header('Location: admin/');
exit;
