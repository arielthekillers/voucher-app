<?php
require_once __DIR__ . '/../../core/Database.php';

class LoginController
{

    public function login()
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
        $stmt->execute([$_POST['username']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($_POST['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Login gagal']);
            return;
        }

        unset($user['password']);
        $_SESSION['user'] = $user;

        echo json_encode(['message' => 'Login sukses']);
    }

    public function logout()
    {
        session_destroy();
        echo json_encode(['message' => 'Logout sukses']);
    }
}
