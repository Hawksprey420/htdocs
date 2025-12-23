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
$sql_dept = "SELECT * FROM employees_unitassignments WHERE employees_idemployees = ? ORDER BY transfer_date DESC LIMIT 1";
$stmt = $conn->prepare($sql_dept);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$assign = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch Latest Service Record
$sql_srv = "SELECT * FROM service_records WHERE employees_idemployees = ? ORDER BY appointment_start_date DESC LIMIT 1";
$stmt = $conn->prepare($sql_srv);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$srv = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch Dropdowns (same as add)
$dept_result = $conn->query("SELECT iddepartments, dept_name FROM departments ORDER BY dept_name");
$pos_result = $conn->query("SELECT idjob_positions, job_category FROM job_positions ORDER BY job_category");
$inst_result = $conn->query("SELECT idinstitutions, institution_name FROM institutions ORDER BY institution_name");
$contract_result = $conn->query("SELECT idcontract_types, contract_classification FROM contract_types ORDER BY contract_classification");

$page_title = "Edit Employee";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Helper function for sticky forms
function old($key, $default = null) {
    return isset($_SESSION['form_data'][$key]) ? htmlspecialchars($_SESSION['form_data'][$key]) : $default;
}
?>

<div class="main-content">
    <div class="container-fluid">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <h2>Edit Employee: <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></h2>
        <form action="../operations/edit_employee_op.php" method="POST">
            <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>">
            <input type="hidden" name="original_job_position_id" value="<?php echo $srv ? $srv['job_positions_idjob_positions'] : ''; ?>">
            <input type="hidden" name="original_department_id" value="<?php echo $assign ? $assign['departments_iddepartments'] : ''; ?>">
            
            <!-- Personal Information -->
            <h4 class="mt-4 mb-3">I. Personal Information</h4>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?php echo old('first_name', $emp['first_name']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" value="<?php echo old('middle_name', $emp['middle_name']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?php echo old('last_name', $emp['last_name']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Name Extension</label>
                    <input type="text" name="name_extension" class="form-control" value="<?php echo old('name_extension', $emp['name_extension']); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="birthdate" class="form-control" value="<?php echo old('birthdate', $emp['birthdate']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Place of Birth</label>
                    <input type="text" name="birth_city" class="form-control" value="<?php echo old('birth_city', $emp['birth_city']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Province</label>
                    <input type="text" name="birth_province" class="form-control" value="<?php echo old('birth_province', $emp['birth_province']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <input type="text" name="birth_country" class="form-control" value="<?php echo old('birth_country', $emp['birth_country']); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Sex</label>
                    <select name="sex" class="form-select" required>
                        <option value="Male" <?php echo (old('sex', $emp['sex']) == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (old('sex', $emp['sex']) == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Civil Status</label>
                    <select name="civil_status" class="form-select" required>
                        <option value="Single" <?php echo (old('civil_status', $emp['civil_status']) == 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo (old('civil_status', $emp['civil_status']) == 'Married') ? 'selected' : ''; ?>>Married</option>
                        <option value="Widowed" <?php echo (old('civil_status', $emp['civil_status']) == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                        <option value="Separated" <?php echo (old('civil_status', $emp['civil_status']) == 'Separated') ? 'selected' : ''; ?>>Separated</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Height (m)</label>
                    <input type="number" step="0.01" name="height_in_meter" class="form-control" value="<?php echo old('height_in_meter', $emp['height_in_meter']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Weight (kg)</label>
                    <input type="number" step="0.01" name="weight_in_kg" class="form-control" value="<?php echo old('weight_in_kg', $emp['weight_in_kg']); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Blood Type</label>
                    <input type="text" name="blood_type" class="form-control" value="<?php echo old('blood_type', $emp['blood_type']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Citizenship</label>
                    <input type="text" name="citizenship" class="form-control" value="<?php echo old('citizenship', $emp['citizenship']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mobile No.</label>
                    <input type="text" name="mobile_no" class="form-control" value="<?php echo old('mobile_no', $emp['mobile_no']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo old('email', $emp['email']); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">GSIS ID No.</label>
                    <input type="text" name="gsis_no" class="form-control" value="<?php echo old('gsis_no', $emp['gsis_no']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">SSS No.</label>
                    <input type="text" name="sss_no" class="form-control" value="<?php echo old('sss_no', $emp['sss_no']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">PhilHealth No.</label>
                    <input type="text" name="philhealthno" class="form-control" value="<?php echo old('philhealthno', $emp['philhealthno']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">TIN</label>
                    <input type="text" name="tin" class="form-control" value="<?php echo old('tin', $emp['tin']); ?>" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Employee No.</label>
                    <input type="number" name="employee_no" class="form-control" value="<?php echo old('employee_no', $emp['employee_no']); ?>" required>
                </div>
            </div>

            <!-- Address -->
            <h4 class="mt-4 mb-3">Address</h4>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Residential Address</label>
                    <div class="input-group">
                        <input type="text" name="res_barangay_address" class="form-control" value="<?php echo old('res_barangay_address', $emp['res_barangay_address']); ?>" placeholder="Barangay" required>
                        <input type="text" name="res_city" class="form-control" value="<?php echo old('res_city', $emp['res_city']); ?>" placeholder="City" required>
                        <input type="text" name="res_province" class="form-control" value="<?php echo old('res_province', $emp['res_province']); ?>" placeholder="Province" required>
                        <input type="text" name="res_zipcode" class="form-control" value="<?php echo old('res_zipcode', $emp['res_zipcode']); ?>" placeholder="Zip Code" required>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Permanent Address</label>
                    <div class="input-group">
                        <input type="text" name="perm_barangay_address" class="form-control" value="<?php echo old('perm_barangay_address', $emp['perm_barangay_address']); ?>" placeholder="Barangay" required>
                        <input type="text" name="perm_city" class="form-control" value="<?php echo old('perm_city', $emp['perm_city']); ?>" placeholder="City" required>
                        <input type="text" name="perm_province" class="form-control" value="<?php echo old('perm_province', $emp['perm_province']); ?>" placeholder="Province" required>
                        <input type="text" name="perm_zipcode" class="form-control" value="<?php echo old('perm_zipcode', $emp['perm_zipcode']); ?>" placeholder="Zip Code" required>
                    </div>
                </div>
            </div>

            <!-- Assignment & Position -->
            <h4 class="mt-4 mb-3">Assignment & Position (Latest)</h4>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Department / Unit</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select Department</option>
                        <?php 
                        $dept_result->data_seek(0);
                        while($row = $dept_result->fetch_assoc()): 
                            $db_val = $assign ? $assign['departments_iddepartments'] : '';
                            $selected = (old('department_id', $db_val) == $row['iddepartments']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $row['iddepartments']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($row['dept_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Position</label>
                    <select name="job_position_id" class="form-select" required>
                        <option value="">Select Position</option>
                        <?php 
                        $pos_result->data_seek(0);
                        while($row = $pos_result->fetch_assoc()): 
                            $db_val = $srv ? $srv['job_positions_idjob_positions'] : '';
                            $selected = (old('job_position_id', $db_val) == $row['idjob_positions']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $row['idjob_positions']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($row['job_category']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Monthly Salary</label>
                    <input type="number" step="0.01" name="monthly_salary" class="form-control" value="<?php echo old('monthly_salary', $srv ? $srv['monthly_salary'] : ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pay Grade</label>
                    <input type="text" name="pay_grade" class="form-control" value="<?php echo old('pay_grade', $srv ? $srv['pay_grade'] : ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contract Type</label>
                    <select name="contract_type_id" class="form-select" required>
                        <option value="">Select Contract Type</option>
                        <?php 
                        $contract_result->data_seek(0);
                        while($row = $contract_result->fetch_assoc()): 
                            $db_val = $srv ? $srv['contract_types_idcontract_types'] : '';
                            $selected = (old('contract_type_id', $db_val) == $row['idcontract_types']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $row['idcontract_types']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($row['contract_classification']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Appointment Start Date</label>
                    <input type="date" name="appointment_start_date" class="form-control" value="<?php echo old('appointment_start_date', $srv ? $srv['appointment_start_date'] : ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Appointment End Date</label>
                    <input type="date" name="appointment_end_date" class="form-control" value="<?php echo old('appointment_end_date', $srv ? $srv['appointment_end_date'] : ''); ?>" required>
                </div>
            </div>
            
            <div class="row mb-3">
                 <div class="col-md-6">
                    <label class="form-label">Institution (Employer)</label>
                    <select name="institution_id" class="form-select" required>
                        <option value="">Select Institution</option>
                        <?php 
                        $inst_result->data_seek(0);
                        while($row = $inst_result->fetch_assoc()): 
                            $db_val = $srv ? $srv['institutions_idinstitutions'] : '';
                            $selected = (old('institution_id', $db_val) == $row['idinstitutions']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $row['idinstitutions']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($row['institution_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gov Service?</label>
                    <select name="gov_service" class="form-select" required>
                        <?php $db_val = $srv ? $srv['gov_service'] : ''; ?>
                        <option value="1" <?php echo (old('gov_service', $db_val) == '1') ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?php echo (old('gov_service', $db_val) == '0') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg mt-3">Update Employee</button>
            <a href="employee-list.php" class="btn btn-secondary btn-lg mt-3">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
