<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';

Auth::requireLogin();
$user = Auth::user();

if (!isset($_GET['id'])) {
    header("Location: employee-list.php");
    exit();
}

$emp_id = $_GET['id'];

// Fetch Employee Data
$sql = "SELECT * FROM employees WHERE idemployees = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$emp) {
    die("Employee not found.");
}

// Fetch Latest Unit Assignment
$sql_dept = "SELECT d.dept_name, ua.transfer_date 
             FROM employees_unitassignments ua 
             JOIN departments d ON ua.departments_iddepartments = d.iddepartments 
             WHERE ua.employees_idemployees = ? 
             ORDER BY ua.transfer_date DESC LIMIT 1";
$stmt = $conn->prepare($sql_dept);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$assign = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch Latest Service Record
$sql_srv = "SELECT sr.*, jp.job_category, i.institution_name, ct.contract_type 
            FROM service_records sr 
            JOIN job_positions jp ON sr.job_positions_idjob_positions = jp.idjob_positions 
            JOIN institutions i ON sr.institutions_idinstitutions = i.idinstitutions 
            JOIN contract_types ct ON sr.contract_types_idcontract_types = ct.idcontract_types 
            WHERE sr.employees_idemployees = ? 
            ORDER BY sr.appointment_start_date DESC LIMIT 1";
$stmt = $conn->prepare($sql_srv);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$srv = $stmt->get_result()->fetch_assoc();
$stmt->close();

$page_title = "View Employee";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Employee Details: <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></h2>
            <div>
                <a href="employee-data-edit.php?id=<?php echo $emp_id; ?>" class="btn btn-warning">Edit</a>
                <a href="employee-list.php" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">I. Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>First Name:</strong> <?php echo htmlspecialchars($emp['first_name']); ?></div>
                    <div class="col-md-3"><strong>Middle Name:</strong> <?php echo htmlspecialchars($emp['middle_name']); ?></div>
                    <div class="col-md-3"><strong>Last Name:</strong> <?php echo htmlspecialchars($emp['last_name']); ?></div>
                    <div class="col-md-3"><strong>Extension:</strong> <?php echo htmlspecialchars($emp['name_extension']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Date of Birth:</strong> <?php echo $emp['birthdate']; ?></div>
                    <div class="col-md-3"><strong>Place of Birth:</strong> <?php echo htmlspecialchars($emp['birth_city'] . ', ' . $emp['birth_province']); ?></div>
                    <div class="col-md-3"><strong>Sex:</strong> <?php echo htmlspecialchars($emp['sex']); ?></div>
                    <div class="col-md-3"><strong>Civil Status:</strong> <?php echo htmlspecialchars($emp['civil_status']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Height:</strong> <?php echo $emp['height_in_meter']; ?> m</div>
                    <div class="col-md-3"><strong>Weight:</strong> <?php echo $emp['weight_in_kg']; ?> kg</div>
                    <div class="col-md-3"><strong>Blood Type:</strong> <?php echo htmlspecialchars($emp['blood_type']); ?></div>
                    <div class="col-md-3"><strong>Citizenship:</strong> <?php echo htmlspecialchars($emp['citizenship']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Mobile:</strong> <?php echo htmlspecialchars($emp['mobile_no']); ?></div>
                    <div class="col-md-3"><strong>Email:</strong> <?php echo htmlspecialchars($emp['email']); ?></div>
                    <div class="col-md-3"><strong>Employee No:</strong> <?php echo htmlspecialchars($emp['employee_no']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>GSIS:</strong> <?php echo htmlspecialchars($emp['gsis_no']); ?></div>
                    <div class="col-md-3"><strong>SSS:</strong> <?php echo htmlspecialchars($emp['sss_no']); ?></div>
                    <div class="col-md-3"><strong>PhilHealth:</strong> <?php echo htmlspecialchars($emp['philhealthno']); ?></div>
                    <div class="col-md-3"><strong>TIN:</strong> <?php echo htmlspecialchars($emp['tin']); ?></div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Address</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Residential:</strong> 
                        <?php echo htmlspecialchars($emp['res_barangay_address'] . ', ' . $emp['res_city'] . ', ' . $emp['res_province'] . ' ' . $emp['res_zipcode']); ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Permanent:</strong> 
                        <?php echo htmlspecialchars($emp['perm_barangay_address'] . ', ' . $emp['perm_city'] . ', ' . $emp['perm_province'] . ' ' . $emp['perm_zipcode']); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Current Assignment & Position</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Department:</strong> <?php echo $assign ? htmlspecialchars($assign['dept_name']) : 'N/A'; ?></div>
                    <div class="col-md-6"><strong>Position:</strong> <?php echo $srv ? htmlspecialchars($srv['job_category']) : 'N/A'; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Monthly Salary:</strong> <?php echo $srv ? number_format($srv['monthly_salary'], 2) : 'N/A'; ?></div>
                    <div class="col-md-4"><strong>Pay Grade:</strong> <?php echo $srv ? htmlspecialchars($srv['pay_grade']) : 'N/A'; ?></div>
                    <div class="col-md-4"><strong>Contract Type:</strong> <?php echo $srv ? htmlspecialchars($srv['contract_type']) : 'N/A'; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Appointment Date:</strong> <?php echo $srv ? $srv['appointment_start_date'] . ' to ' . $srv['appointment_end_date'] : 'N/A'; ?></div>
                    <div class="col-md-6"><strong>Institution:</strong> <?php echo $srv ? htmlspecialchars($srv['institution_name']) : 'N/A'; ?></div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
