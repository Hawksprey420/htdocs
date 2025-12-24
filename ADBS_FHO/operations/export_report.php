<?php
require_once '../classes/conn.php';
require_once '../config/auth.php';

Auth::requireLogin();

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=system_report_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Fetch Data (Same as system-report.php)

// 1. Employees by Gender
$sql_gender = "SELECT sex, COUNT(*) as count FROM employees GROUP BY sex";
$result_gender = $conn->query($sql_gender);

// 2. Employees by Civil Status
$sql_civil = "SELECT civil_status, COUNT(*) as count FROM employees GROUP BY civil_status";
$result_civil = $conn->query($sql_civil);

// 3. Employees by Department
$sql_dept = "SELECT d.dept_name, COUNT(eua.employees_idemployees) as count
             FROM departments d
             LEFT JOIN employees_unitassignments eua ON d.iddepartments = eua.departments_iddepartments
             GROUP BY d.dept_name
             HAVING count > 0
             ORDER BY count DESC";
$result_dept = $conn->query($sql_dept);

// 4. Recent Trainings
$sql_trainings = "SELECT training_title, training_type, start_date, end_date FROM trainings ORDER BY start_date DESC LIMIT 5";
$result_trainings = $conn->query($sql_trainings);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { margin-top: 20px; }
    </style>
</head>
<body>
    <h1>System Report - <?php echo date('F d, Y'); ?></h1>

    <h2>Employees by Gender</h2>
    <table>
        <thead>
            <tr>
                <th>Gender</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result_gender) {
                while ($row = $result_gender->fetch_assoc()) {
                    echo "<tr><td>" . htmlspecialchars($row['sex']) . "</td><td>" . $row['count'] . "</td></tr>";
                }
            }
            ?>
        </tbody>
    </table>

    <h2>Employees by Civil Status</h2>
    <table>
        <thead>
            <tr>
                <th>Civil Status</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result_civil) {
                while ($row = $result_civil->fetch_assoc()) {
                    echo "<tr><td>" . htmlspecialchars($row['civil_status']) . "</td><td>" . $row['count'] . "</td></tr>";
                }
            }
            ?>
        </tbody>
    </table>

    <h2>Employees by Department</h2>
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Employee Count</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result_dept) {
                while ($row = $result_dept->fetch_assoc()) {
                    echo "<tr><td>" . htmlspecialchars($row['dept_name']) . "</td><td>" . $row['count'] . "</td></tr>";
                }
            }
            ?>
        </tbody>
    </table>

    <h2>Recent Trainings</h2>
    <table>
        <thead>
            <tr>
                <th>Training Title</th>
                <th>Type</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result_trainings) {
                while ($row = $result_trainings->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['training_title']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['training_type']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['start_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['end_date']) . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
</body>
</html>
