<?php

require_once __DIR__ . '/../app/middleware/auth.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($uri) {

    // =====================
    // AUTH
    // =====================
    case '/login':
        require_once __DIR__ . '/../app/modules/auth/LoginController.php';
        (new LoginController)->login();
        break;

    case '/logout':
        auth_required();
        require_once __DIR__ . '/../app/modules/auth/LoginController.php';
        (new LoginController)->logout();
        break;

    // =====================
    // DASHBOARD (TEST)
    // =====================
    case '/dashboard':
        auth_required();
        echo json_encode([
            'message' => 'Welcome',
            'user' => Auth::user()
        ]);
        break;

    // =====================
    // SUPER ADMIN ONLY
    // =====================
    case '/users':
        role_required('super_admin');
        echo json_encode(['message' => 'User management']);
        break;

    case '/outlets':
        role_required('super_admin');
        echo json_encode(['message' => 'Outlet management']);
        break;

    default:
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint not found']);
}
