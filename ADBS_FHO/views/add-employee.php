<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';

Auth::requireLogin();
$user = Auth::user();

// Fetch Departments
$dept_query = "SELECT iddepartments, dept_name FROM departments ORDER BY dept_name";
$dept_result = $conn->query($dept_query);

// Fetch Positions
$pos_query = "SELECT idjob_positions, job_category FROM job_positions ORDER BY job_category";
$pos_result = $conn->query($pos_query);

// Fetch Institutions (for Service Record)
$inst_query = "SELECT idinstitutions, institution_name FROM institutions ORDER BY institution_name";
$inst_result = $conn->query($inst_query);

// Fetch Contract Types
$contract_query = "SELECT idcontract_types, contract_classification FROM contract_types ORDER BY contract_classification";
$contract_result = $conn->query($contract_query);

$page_title = "Add Employee";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Helper function for sticky forms
function old($key, $default = '') {
    return isset($_SESSION['form_data'][$key]) ? htmlspecialchars($_SESSION['form_data'][$key]) : $default;
}
?>

<div class="main-content">
    <div class="container-fluid">
        <h2>Add New Employee (CSC Form 212)</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="../operations/add_employee_op.php" method="POST">
            
            <!-- Personal Information -->
            <h4 class="mt-4 mb-3">I. Personal Information</h4>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?php echo old('first_name'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" value="<?php echo old('middle_name'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?php echo old('last_name'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Name Extension (Jr, Sr)</label>
                    <input type="text" name="name_extension" class="form-control" value="<?php echo old('name_extension'); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="birthdate" class="form-control" value="<?php echo old('birthdate'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Place of Birth (City/Mun)</label>
                    <input type="text" name="birth_city" class="form-control" value="<?php echo old('birth_city'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Province</label>
                    <input type="text" name="birth_province" class="form-control" value="<?php echo old('birth_province'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <input type="text" name="birth_country" class="form-control" value="<?php echo old('birth_country', 'Philippines'); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Sex</label>
                    <select name="sex" class="form-select" required>
                        <option value="Male" <?php echo (old('sex') == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (old('sex') == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Civil Status</label>
                    <select name="civil_status" class="form-select" required>
                        <option value="Single" <?php echo (old('civil_status') == 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo (old('civil_status') == 'Married') ? 'selected' : ''; ?>>Married</option>
                        <option value="Widowed" <?php echo (old('civil_status') == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                        <option value="Separated" <?php echo (old('civil_status') == 'Separated') ? 'selected' : ''; ?>>Separated</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Height (m)</label>
                    <input type="number" step="0.01" name="height_in_meter" class="form-control" value="<?php echo old('height_in_meter'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Weight (kg)</label>
                    <input type="number" step="0.01" name="weight_in_kg" class="form-control" value="<?php echo old('weight_in_kg'); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Blood Type</label>
                    <input type="text" name="blood_type" class="form-control" value="<?php echo old('blood_type'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Citizenship</label>
                    <input type="text" name="citizenship" class="form-control" value="<?php echo old('citizenship', 'Filipino'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mobile No.</label>
                    <input type="text" name="mobile_no" class="form-control" value="<?php echo old('mobile_no'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo old('email'); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">GSIS ID No.</label>
                    <input type="text" name="gsis_no" class="form-control" value="<?php echo old('gsis_no'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">SSS No.</label>
                    <input type="text" name="sss_no" class="form-control" value="<?php echo old('sss_no'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">PhilHealth No.</label>
                    <input type="text" name="philhealthno" class="form-control" value="<?php echo old('philhealthno'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">TIN</label>
                    <input type="text" name="tin" class="form-control" value="<?php echo old('tin'); ?>" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Employee No.</label>
                    <input type="number" name="employee_no" class="form-control" value="<?php echo old('employee_no'); ?>" required>
                </div>
            </div>

            <!-- Address -->
            <h4 class="mt-4 mb-3">Address</h4>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Residential Address (Barangay, City, Province, Zip)</label>
                    <div class="input-group">
                        <input type="text" name="res_barangay_address" class="form-control" placeholder="Barangay" value="<?php echo old('res_barangay_address'); ?>" required>
                        <input type="text" name="res_city" class="form-control" placeholder="City" value="<?php echo old('res_city'); ?>" required>
                        <input type="text" name="res_province" class="form-control" placeholder="Province" value="<?php echo old('res_province'); ?>" required>
                        <input type="text" name="res_zipcode" class="form-control" placeholder="Zip Code" value="<?php echo old('res_zipcode'); ?>" required>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Permanent Address (Barangay, City, Province, Zip)</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="copyAddress">
                        <label class="form-check-label" for="copyAddress">
                            Same as Residential Address
                        </label>
                    </div>
                    <div class="input-group">
                        <input type="text" name="perm_barangay_address" class="form-control" placeholder="Barangay" value="<?php echo old('perm_barangay_address'); ?>" required>
                        <input type="text" name="perm_city" class="form-control" placeholder="City" value="<?php echo old('perm_city'); ?>" required>
                        <input type="text" name="perm_province" class="form-control" placeholder="Province" value="<?php echo old('perm_province'); ?>" required>
                        <input type="text" name="perm_zipcode" class="form-control" placeholder="Zip Code" value="<?php echo old('perm_zipcode'); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Assignment & Position -->
            <h4 class="mt-4 mb-3">Assignment & Position</h4>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Department / Unit</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select Department</option>
                        <?php while($row = $dept_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['iddepartments']; ?>" <?php echo (old('department_id') == $row['iddepartments']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['dept_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Position</label>
                    <select name="job_position_id" class="form-select" required>
                        <option value="">Select Position</option>
                        <?php while($row = $pos_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['idjob_positions']; ?>" <?php echo (old('job_position_id') == $row['idjob_positions']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['job_category']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Monthly Salary</label>
                    <input type="number" step="0.01" name="monthly_salary" class="form-control" value="<?php echo old('monthly_salary'); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pay Grade</label>
                    <input type="text" name="pay_grade" class="form-control" value="<?php echo old('pay_grade'); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contract Type</label>
                    <select name="contract_type_id" class="form-select" required>
                        <option value="">Select Contract Type</option>
                        <?php while($row = $contract_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['idcontract_types']; ?>" <?php echo (old('contract_type_id') == $row['idcontract_types']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['contract_classification']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Appointment Start Date</label>
                    <input type="date" name="appointment_start_date" class="form-control" value="<?php echo old('appointment_start_date'); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Appointment End Date</label>
                    <input type="date" name="appointment_end_date" class="form-control" value="<?php echo old('appointment_end_date'); ?>" required>
                </div>
            </div>
            
            <div class="row mb-3">
                 <div class="col-md-6">
                    <label class="form-label">Institution (Employer)</label>
                    <select name="institution_id" class="form-select" required>
                        <option value="">Select Institution</option>
                        <?php while($row = $inst_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['idinstitutions']; ?>" <?php echo (old('institution_id') == $row['idinstitutions']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['institution_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gov Service?</label>
                    <select name="gov_service" class="form-select" required>
                        <option value="1" <?php echo (old('gov_service') == '1') ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?php echo (old('gov_service') == '0') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg mt-3">Save Employee</button>
            <a href="employee-list.php" class="btn btn-secondary btn-lg mt-3">Cancel</a>
        </form>
    </div>
</div>

<script>
    document.getElementById('copyAddress').addEventListener('change', function() {
        if(this.checked) {
            document.getElementsByName('perm_barangay_address')[0].value = document.getElementsByName('res_barangay_address')[0].value;
            document.getElementsByName('perm_city')[0].value = document.getElementsByName('res_city')[0].value;
            document.getElementsByName('perm_province')[0].value = document.getElementsByName('res_province')[0].value;
            document.getElementsByName('perm_zipcode')[0].value = document.getElementsByName('res_zipcode')[0].value;
        } else {
            document.getElementsByName('perm_barangay_address')[0].value = '';
            document.getElementsByName('perm_city')[0].value = '';
            document.getElementsByName('perm_province')[0].value = '';
            document.getElementsByName('perm_zipcode')[0].value = '';
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
