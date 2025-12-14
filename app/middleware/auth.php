<?php

require_once __DIR__ . '/../core/Auth.php';

/**
 * Pastikan user sudah login
 */
function auth_required()
{
    if (!Auth::check()) {
        flash('error', 'Silakan login terlebih dahulu.');
        header('Location: /voucher/public/admin/login.php');
        exit;
    }
}

/**
 * Batasi akses berdasarkan role
 * contoh: role_required('super_admin')
 */
function role_required(string $role)
{
    auth_required();

    if (!Auth::role($role)) {
        http_response_code(403);
        exit('Forbidden - insufficient role');
    }
}
