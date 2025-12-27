<?php
require_once 'classes/conn.php';
require_once 'classes/User.php';

$user = new User($conn);
$user->employee_id = 0; 
$user->username = 'admin';
$user->password = 'admin123';
$user->role_id = 1; // Admin role
$user->is_active = 1;

if($user->usernameExists()) {
    echo "User already exists.";
} else {
    if($user->create()) {
        echo "Admin user created successfully. Username: admin, Password: admin123";
    } else {
        echo "Failed to create admin user.";
    }
}
?>
