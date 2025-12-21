<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';

// Require login and Admin role (role_id 1)
Auth::requireLogin();
if (!Auth::hasRole(1)) {
    header("Location: ../index.php");
    exit();
}

$db = $conn;

// Fetch employees who don't have a user account yet
$query_emp = "SELECT e.idemployees, e.first_name, e.last_name 
              FROM employees e 
              LEFT JOIN users u ON e.idemployees = u.employee_id 
              WHERE u.id IS NULL 
              ORDER BY e.last_name ASC";
$result_emp = $db->query($query_emp);

// Fetch roles
$query_roles = "SELECT * FROM roles";
$result_roles = $db->query($query_roles);

$page_title = "Add User";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Create New User Account</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        if (isset($_GET['error'])) {
                            echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
                        }
                        if (isset($_GET['success'])) {
                            echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
                        }
                        ?>
                        <form action="../operations/user_add_op.php" method="POST">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee</label>
                                <select class="form-select" id="employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    <?php while ($row = $result_emp->fetch_assoc()): ?>
                                        <option value="<?php echo $row['idemployees']; ?>">
                                            <?php echo htmlspecialchars($row['last_name'] . ', ' . $row['first_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="form-text">Only employees without an account are listed.</div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <div class="mb-3">
                                <label for="role_id" class="form-label">Role</label>
                                <select class="form-select" id="role_id" name="role_id" required>
                                    <option value="">Select Role</option>
                                    <?php while ($row = $result_roles->fetch_assoc()): ?>
                                        <option value="<?php echo $row['id']; ?>">
                                            <?php echo htmlspecialchars($row['role_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Create User</button>
                                <a href="user-list.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
