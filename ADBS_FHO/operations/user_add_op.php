<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';
require_once '../classes/User.php';
require_once '../classes/Logger.php';

// Require login and Admin role
Auth::requireLogin();
if (!Auth::hasRole(1)) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role_id = $_POST['role_id'] ?? '';

    // Basic validation
    if (empty($employee_id) || empty($username) || empty($password) || empty($role_id)) {
        header("Location: ../views/user-add.php?error=All fields are required");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: ../views/user-add.php?error=Passwords do not match");
        exit();
    }

    // Use $conn from classes/conn.php
    $db = $conn;
    $user = new User($db);

    $user->username = $username;
    
    // Check if username exists
    if ($user->usernameExists()) {
        header("Location: ../views/user-add.php?error=Username already exists");
        exit();
    }

    // Set user properties
    $user->employee_id = $employee_id;
    $user->password = $password;
    $user->role_id = $role_id;
    $user->is_active = 1; // Default to active

    // Create user
    if ($user->create()) {
        // Log the activity
        $logger = new Logger($db);
        $current_user = Auth::user();
        $logger->log($current_user['id'], "Create User", "Created user account for employee ID: " . $employee_id . " with username: " . $username);

        header("Location: ../views/user-list.php?success=User created successfully");
        exit();
    } else {
        header("Location: ../views/user-add.php?error=Unable to create user");
    }
} else {
    header("Location: ../views/user-add.php");
}
?>
