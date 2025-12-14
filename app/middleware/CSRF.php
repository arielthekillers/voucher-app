<?php

class CSRF {
    public static function generate() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function check($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            // Log attack attempt
            error_log("CSRF Mismatch: Session: " . ($_SESSION['csrf_token'] ?? 'null') . " vs Post: $token");
            die('Invalid CSRF Token');
        }
        return true;
    }
}

// Global Helper
function csrf_field() {
    $token = CSRF::generate();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}
