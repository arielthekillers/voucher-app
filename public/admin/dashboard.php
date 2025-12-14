<?php
session_start();

require_once '../../app/config/app.php';
require_once ROOT_PATH . '/app/core/Database.php';
require_once ROOT_PATH . '/app/core/Auth.php';
require_once ROOT_PATH . '/app/middleware/auth.php';

auth_required();

include ROOT_PATH . '/resources/views/layouts/header.php';
include ROOT_PATH . '/resources/views/layouts/sidebar.php';
?>

<h2>Dashboard</h2>
<p>Login berhasil 🎉</p>

<?php include ROOT_PATH . '/resources/views/layouts/footer.php'; ?>