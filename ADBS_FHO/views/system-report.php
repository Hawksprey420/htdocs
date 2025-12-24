<?php
require_once '../classes/conn.php';
require_once '../config/auth.php';

Auth::requireLogin();

// Fetch Employees by Gender
$sql_gender = "SELECT sex, COUNT(*) as count FROM employees GROUP BY sex";
$result_gender = $conn->query($sql_gender);
$gender_data = [];
if ($result_gender) {
    while ($row = $result_gender->fetch_assoc()) {
        $gender_data[] = $row;
    }
}

// Fetch Employees by Civil Status
$sql_civil = "SELECT civil_status, COUNT(*) as count FROM employees GROUP BY civil_status";
$result_civil = $conn->query($sql_civil);
$civil_data = [];
if ($result_civil) {
    while ($row = $result_civil->fetch_assoc()) {
        $civil_data[] = $row;
    }
}

// Fetch Employees by Department
$sql_dept = "SELECT d.dept_name, COUNT(eua.employees_idemployees) as count
             FROM departments d
             LEFT JOIN employees_unitassignments eua ON d.iddepartments = eua.departments_iddepartments
             GROUP BY d.dept_name
             HAVING count > 0
             ORDER BY count DESC";
$result_dept = $conn->query($sql_dept);
$dept_data = [];
if ($result_dept) {
    while ($row = $result_dept->fetch_assoc()) {
        $dept_data[] = $row;
    }
}

// Fetch Recent Trainings
$sql_trainings = "SELECT training_title, training_type, start_date, end_date FROM trainings ORDER BY start_date DESC LIMIT 5";
$result_trainings = $conn->query($sql_trainings);
$recent_trainings = [];
if ($result_trainings) {
    while ($row = $result_trainings->fetch_assoc()) {
        $recent_trainings[] = $row;
    }
}

$page_title = "System Reports";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>System Reports</h2>
            <a href="../operations/export_report.php" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export to Excel
            </a>
        </div>

        <div class="row">
            <!-- Employee Demographics -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Employees by Gender</div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Gender</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_gender = array_sum(array_column($gender_data, 'count'));
                                foreach ($gender_data as $row): 
                                    $percentage = $total_gender > 0 ? round(($row['count'] / $total_gender) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['sex']); ?></td>
                                    <td><?php echo $row['count']; ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $percentage; ?>%</div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Employees by Civil Status</div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Civil Status</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($civil_data as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['civil_status']); ?></td>
                                    <td><?php echo $row['count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Employees by Department -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Employees by Department</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Employee Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dept_data as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['dept_name']); ?></td>
                                        <td><?php echo $row['count']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Trainings -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Recent Trainings</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Training Title</th>
                                        <th>Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recent_trainings) > 0): ?>
                                        <?php foreach ($recent_trainings as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['training_title']); ?></td>
                                            <td><?php echo htmlspecialchars($row['training_type']); ?></td>
                                            <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No recent trainings found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
