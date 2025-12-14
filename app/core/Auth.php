<?php

class Auth
{
    public static function attempt($username, $password)
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT * FROM users 
            WHERE username = :username 
            AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        unset($user['password']);
        $_SESSION['user'] = $user;

        return true;
    }

    public static function check()
    {
        return isset($_SESSION['user']);
    }

    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }

    public static function role($role)
    {
        return self::check() && $_SESSION['user']['role'] === $role;
    }

    public static function logout()
    {
        session_destroy();
    }
}
