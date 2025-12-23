<?php
session_start();

class Auth {
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header("Location: login.php");
            exit();
        }
    }

    public static function login($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role_id'] = $user->role_id;
        $_SESSION['employee_id'] = $user->employee_id;
    }

    public static function logout() {
        session_destroy();
        header("Location: login.php");
        exit();
    }
    
    public static function user() {
        if(self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role_id' => $_SESSION['role_id'],
                'employee_id' => $_SESSION['employee_id'] ?? null
            ];
        }
        return null;
    }

    public static function hasRole($role_id) {
        return isset($_SESSION['role_id']) && $_SESSION['role_id'] == $role_id;
    }
}
?>
