<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';
require_once '../classes/Logger.php';

Auth::requireLogin();
$user = Auth::user();

// RBAC Check: Only Admin (Role ID 1) or HR Staff (Role ID 2) can delete employees
if (!Auth::hasRole(1) && !Auth::hasRole(2)) {
    header("Location: ../views/employee-list.php?error=" . urlencode("Access Denied: You do not have permission to delete records."));
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete from child tables manually where CASCADE might be missing
        $tables = [
            'employees_education',
            'employees_unitassignments',
            'service_records',
            'skills_has_employees'
        ];

        foreach ($tables as $table) {
            $query = "DELETE FROM $table WHERE employees_idemployees = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }

        // Delete from employees (Cascade should handle users and character_references)
        $query = "DELETE FROM employees WHERE idemployees = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Log the action
            $logger = new Logger($conn);
            $logger->log($user['id'], 'Delete', "Deleted employee ID: $id");

            $conn->commit();
            header("Location: ../views/employee-list.php?msg=deleted");
        } else {
            throw new Exception("Failed to delete employee.");
        }

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../views/employee-list.php?error=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: ../views/employee-list.php");
}
?>
