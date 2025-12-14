<?php
session_start();
require_once '../../vendor/autoload.php';

Auth::logout();

header('Location: login.php');
exit;
