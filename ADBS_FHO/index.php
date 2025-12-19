<?php
require_once 'config/auth.php';

Auth::requireLogin();
$user = Auth::user();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - HRMIS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">HRMIS</a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($user['username']); ?>
                </span>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h1>Dashboard</h1>
        <p>Welcome to the HR Management System.</p>
        <div class="list-group">
            <a href="views/employee-list.php" class="list-group-item list-group-item-action">Manage Employees</a>
            <a href="views/add-employee.php" class="list-group-item list-group-item-action">Add Employee</a>
            <a href="views/system-report.php" class="list-group-item list-group-item-action">System Reports</a>
        </div>
    </div>
</body>
</html>
