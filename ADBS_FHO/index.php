<?php
require_once 'config/auth.php';

Auth::requireLogin();
$user = Auth::user();

$is_root = true;
$page_title = "Dashboard";

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="mb-4">Dashboard</h1>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h5>
                        <p class="card-text">Welcome to the HR Management System. Use the sidebar to navigate through the application.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Employees</h5>
                        <p class="card-text">Manage employee records.</p>
                        <a href="views/employee-list.php" class="btn btn-primary">View Employees</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-user-plus fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Add Employee</h5>
                        <p class="card-text">Register a new employee.</p>
                        <a href="views/add-employee.php" class="btn btn-success">Add New</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Reports</h5>
                        <p class="card-text">View system reports.</p>
                        <a href="views/system-report.php" class="btn btn-info text-white">View Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
