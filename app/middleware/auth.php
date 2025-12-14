<?php

require_once __DIR__ . '/../core/Auth.php';

function auth_required()
{
    if (!Auth::check()) {
        header('Location: /voucher/public/admin/login.php');
        exit;
    }
}

function super_admin_only()
{
    auth_required();

    if (!Auth::role('super_admin')) {
        http_response_code(403);
        echo "Akses ditolak";
        exit;
    }
}
