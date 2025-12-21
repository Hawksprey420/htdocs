<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';

// Require login and Admin role
Auth::requireLogin();
if (!Auth::hasRole(1)) {
    header("Location: ../index.php");
    exit();
}

$db = $conn;

// Fetch users with employee names and role names
$query = "SELECT u.id, u.username, u.created_at, r.role_name, e.first_name, e.last_name 
          FROM users u 
          JOIN roles r ON u.role_id = r.id 
          JOIN employees e ON u.employee_id = e.idemployees 
          ORDER BY u.created_at DESC";
$result = $db->query($query);

$page_title = "User Management";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Management</h2>
            <a href="user-add.php" class="btn btn-primary">Add New User</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Employee Name</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['last_name'] . ', ' . $row['first_name']); ?></td>
                                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['role_name']); ?></span></td>
                                        <td><?php echo $row['created_at']; ?></td>
                                        <td>
                                            <!-- Placeholder for delete/edit -->
                                            <button class="btn btn-sm btn-danger" disabled>Delete</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
