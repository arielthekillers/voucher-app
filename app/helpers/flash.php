<?php

if (!function_exists('flash')) {
    function flash($name = '', $message = '', $class = 'success')
    {
        // Simple setter compatible with flash_display
        if (!empty($name) && !empty($message)) {
            $_SESSION['flash_' . $name] = $message;
        } 
        // Getter (optional usage: flash('success'))
        elseif (!empty($name) && empty($message)) {
            if (isset($_SESSION['flash_' . $name])) {
                $class = ($name == 'error') ? 'danger' : $name;
                echo '<div class="alert alert-' . $class . '">' . $_SESSION['flash_' . $name] . '</div>';
                unset($_SESSION['flash_' . $name]);
            }
        }
    }
}

if (!function_exists('flash_display')) {
    function flash_display() {
        // Automatically check for common flash keys
        $keys = ['success', 'error', 'info', 'warning'];
        foreach ($keys as $key) {
            if (isset($_SESSION['flash_' . $key])) {
                $msg = $_SESSION['flash_' . $key];
                $class = ($key === 'error') ? 'danger' : $key; // Bootstrap mapping
                echo '<div class="alert alert-' . $class . '" role="alert" style="margin-bottom: 1rem;">' . htmlspecialchars($msg) . '</div>';
                unset($_SESSION['flash_' . $key]);
            }
        }
        
        // Also check if user passed specific named flash
        // This is a simpler usage: flash('my_custom_msg')
    }
}

// Helper to set flash easily
if (!function_exists('set_flash')) {
    function set_flash($type, $message) {
        $_SESSION['flash_' . $type] = $message;
    }
}
