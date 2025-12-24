<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';
require_once '../classes/Logger.php';

Auth::requireLogin();
$user = Auth::user();

// Log the activity
$logger = new Logger($conn);
// $logger->log($user['id'], 'View', 'Viewed employee list');

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
$params = [];
$types = "";

if (!empty($search)) {
    $search_condition = " WHERE e.last_name LIKE ? OR e.first_name LIKE ? OR e.employee_no LIKE ?";
    $search_term = "%$search%";
    $params = [$search_term, $search_term, $search_term];
    $types = "sss";
}

// Count total records
$count_query = "SELECT COUNT(*) as total FROM employees e" . $search_condition;
$stmt = $conn->prepare($count_query);
if (!empty($search)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch employees
$query = "SELECT e.idemployees, e.employee_no, e.first_name, e.last_name, 
                 d.dept_name, jp.job_category
          FROM employees e
          LEFT JOIN employees_unitassignments eu ON e.idemployees = eu.employees_idemployees
          LEFT JOIN departments d ON eu.departments_iddepartments = d.iddepartments
          LEFT JOIN service_records sr ON e.idemployees = sr.employees_idemployees
          LEFT JOIN job_positions jp ON sr.job_positions_idjob_positions = jp.idjob_positions
          $search_condition
          ORDER BY e.last_name ASC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    $types .= "ii";
    $params[] = $limit;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$page_title = "Employee List";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                if ($_GET['msg'] == 'added') echo "Employee added successfully.";
                if ($_GET['msg'] == 'updated') echo "Employee updated successfully.";
                if ($_GET['msg'] == 'deleted') echo "Employee deleted successfully.";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Employee List</h2>
            <?php if (Auth::hasRole(1) || Auth::hasRole(2)): ?>
            <a href="add-employee.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Employee</a>
            <?php endif; ?>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search by Name or Employee No" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if(!empty($search)): ?>
                        <a href="employee-list.php" class="btn btn-secondary ms-2">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Employee No</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['employee_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name'] . ', ' . $row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['dept_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['job_category'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="employee-data-view.php?id=<?php echo $row['idemployees']; ?>" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>
                                    
                                    <?php if (Auth::hasRole(1) || Auth::hasRole(2) || (isset($user['employee_id']) && $user['employee_id'] == $row['idemployees'])): ?>
                                    <a href="employee-data-edit.php?id=<?php echo $row['idemployees']; ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>

                                    <?php if (Auth::hasRole(1) || Auth::hasRole(2)): ?>
                                    <a href="#" onclick="confirmDelete(<?php echo $row['idemployees']; ?>)" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No employees found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                </li>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<script>
function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
        window.location.href = '../operations/delete_employee.php?id=' + id;
    }
}
</script>

<?php include '../includes/footer.php'; ?>
