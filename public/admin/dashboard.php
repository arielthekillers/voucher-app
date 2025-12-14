<?php
session_start();

require_once '../../vendor/autoload.php';

auth_required();

include ROOT_PATH . '/resources/views/layouts/header.php';
include ROOT_PATH . '/resources/views/layouts/sidebar.php';
?>

<h2>Dashboard</h2>
<p>Login berhasil 🎉</p>

<?php include ROOT_PATH . '/resources/views/layouts/footer.php'; ?>