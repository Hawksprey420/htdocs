<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';

Auth::requireLogin();
$user = Auth::user();

// Admin (1) and HR (2) can edit assignment. Employee (3) cannot.
$can_edit_assignment = ($user['role_id'] == 1 || $user['role_id'] == 2);
// Employees cannot add new data rows, only edit existing ones.
$can_add_data = ($user['role_id'] != 3);

if (!isset($_GET['id'])) {
    header("Location: employee-list.php");
    exit();
}

$emp_id = $_GET['id'];

// 1. Fetch Employee Data
$sql = "SELECT * FROM employees WHERE idemployees = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$emp) {
    die("Employee not found.");
}

// 2. Fetch Latest Unit Assignment (For Current Assignment Section)
$sql_dept = "SELECT * FROM employees_unitassignments WHERE employees_idemployees = ? ORDER BY transfer_date DESC LIMIT 1";
$stmt = $conn->prepare($sql_dept);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$assign = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 3. Fetch Latest Service Record (For Current Assignment Section)
$sql_srv = "SELECT * FROM service_records WHERE employees_idemployees = ? ORDER BY appointment_start_date DESC LIMIT 1";
$stmt = $conn->prepare($sql_srv);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$srv = $stmt->get_result()->fetch_assoc();
$stmt->close();
$current_job_pos_id = $srv ? $srv['job_positions_idjob_positions'] : 0;

// 4. Fetch Family Background
$sql_fam = "SELECT r.*, er.relationship 
            FROM relatives r 
            JOIN employees_relatives er ON r.idrelatives = er.Relatives_idrelatives 
            WHERE er.employees_idemployees = ?";
$stmt = $conn->prepare($sql_fam);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$family = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 5. Fetch Education
$sql_educ = "SELECT ee.*, i.institution_name 
             FROM employees_education ee 
             LEFT JOIN institutions i ON ee.institutions_idinstitutions = i.idinstitutions 
             WHERE ee.employees_idemployees = ?";
$stmt = $conn->prepare($sql_educ);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$education = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 6. Fetch Eligibility
$sql_elig = "SELECT epe.*, pe.Exam_description 
             FROM employees_prof_eligibility epe 
             LEFT JOIN professional_exams pe ON epe.professional_exams_idprofessional_exams = pe.idprofessional_exams 
             WHERE epe.employees_idemployees = ?";
$stmt = $conn->prepare($sql_elig);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$eligibility = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 7. Fetch Work Experience (Exclude Current Assignment)
$sql_work = "SELECT sr.*, jp.job_category, i.institution_name 
             FROM service_records sr 
             LEFT JOIN job_positions jp ON sr.job_positions_idjob_positions = jp.idjob_positions 
             LEFT JOIN institutions i ON sr.institutions_idinstitutions = i.idinstitutions 
             WHERE sr.employees_idemployees = ? AND sr.job_positions_idjob_positions != ? 
             ORDER BY sr.appointment_start_date DESC";
$stmt = $conn->prepare($sql_work);
$stmt->bind_param("ii", $emp_id, $current_job_pos_id);
$stmt->execute();
$work_exp = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 8. Fetch Voluntary Work
$sql_vol = "SELECT eei.*, i.institution_name 
            FROM employees_ext_involvements eei 
            LEFT JOIN institutions i ON eei.institutions_idinstitutions = i.idinstitutions 
            WHERE eei.employees_idemployees = ?";
$stmt = $conn->prepare($sql_vol);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$voluntary = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 9. Fetch Training
$sql_train = "SELECT t.*, i.institution_name 
              FROM employees_has_trainings eht 
              JOIN trainings t ON eht.trainings_idtrainings = t.idtrainings 
              LEFT JOIN institutions i ON t.institutions_idinstitutions = i.idinstitutions 
              WHERE eht.employees_idemployees = ?";
$stmt = $conn->prepare($sql_train);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$trainings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 10. Fetch Other Info (Skills)
$sql_skills = "SELECT s.skill_description, s.skill_type 
               FROM skills_has_employees she 
               JOIN skills s ON she.skills_idskills = s.idskills 
               WHERE she.employees_idemployees = ?";
$stmt = $conn->prepare($sql_skills);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 11. Fetch Government IDs
$sql_gov = "SELECT * FROM government_ids WHERE employee_id = ?";
$stmt = $conn->prepare($sql_gov);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$gov_ids = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 12. Fetch Character References
$sql_ref = "SELECT * FROM character_references WHERE employee_id = ?";
$stmt = $conn->prepare($sql_ref);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$refs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch Dropdowns for Assignment
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
        <h2 class="mb-4">Edit Employee: <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="../operations/edit_employee_op.php" method="POST" id="editEmployeeForm">
            <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>">
            <input type="hidden" name="original_job_position_id" value="<?php echo $srv ? $srv['job_positions_idjob_positions'] : ''; ?>">
            <input type="hidden" name="original_department_id" value="<?php echo $assign ? $assign['departments_iddepartments'] : ''; ?>">

            <ul class="nav nav-tabs" id="employeeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">I. Personal Info</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="family-tab" data-bs-toggle="tab" data-bs-target="#family" type="button" role="tab">II. Family</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="education-tab" data-bs-toggle="tab" data-bs-target="#education" type="button" role="tab">III. Education</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="eligibility-tab" data-bs-toggle="tab" data-bs-target="#eligibility" type="button" role="tab">IV. Eligibility</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="work-tab" data-bs-toggle="tab" data-bs-target="#work" type="button" role="tab">V. Work Exp</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="voluntary-tab" data-bs-toggle="tab" data-bs-target="#voluntary" type="button" role="tab">VI. Voluntary</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="training-tab" data-bs-toggle="tab" data-bs-target="#training" type="button" role="tab">VII. Training</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button" role="tab">VIII. Other Info</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="c4-tab" data-bs-toggle="tab" data-bs-target="#c4" type="button" role="tab">IX. C4 & Refs</button>
                </li>
            </ul>

            <div class="tab-content p-3 border border-top-0 bg-white" id="employeeTabsContent">
                
                <!-- I. Personal Information -->
                <div class="tab-pane fade show active" id="personal" role="tabpanel">
                    <h4 class="mt-3 mb-3">Personal Information</h4>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo old('first_name', $emp['first_name']); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" value="<?php echo old('middle_name', $emp['middle_name']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo old('last_name', $emp['last_name']); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Name Extension (Jr, Sr)</label>
                            <input type="text" name="name_extension" class="form-control" value="<?php echo old('name_extension', $emp['name_extension']); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="birthdate" class="form-control" value="<?php echo old('birthdate', $emp['birthdate']); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Place of Birth (City/Mun)</label>
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
                            <label class="form-label">Sex <span class="text-danger">*</span></label>
                            <select name="sex" class="form-select" required>
                                <option value="Male" <?php echo (old('sex', $emp['sex']) == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (old('sex', $emp['sex']) == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Civil Status <span class="text-danger">*</span></label>
                            <select name="civil_status" class="form-select" required>
                                <option value="Single" <?php echo (old('civil_status', $emp['civil_status']) == 'Single') ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo (old('civil_status', $emp['civil_status']) == 'Married') ? 'selected' : ''; ?>>Married</option>
                                <option value="Widowed" <?php echo (old('civil_status', $emp['civil_status']) == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                                <option value="Separated" <?php echo (old('civil_status', $emp['civil_status']) == 'Separated') ? 'selected' : ''; ?>>Separated</option>
                                <option value="Divorced" <?php echo (old('civil_status', $emp['civil_status']) == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Height (m)</label>
                            <input type="number" step="0.01" name="height_in_meter" class="form-control" value="<?php echo old('height_in_meter', $emp['height_in_meter']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" step="0.01" name="weight_in_kg" class="form-control" value="<?php echo old('weight_in_kg', $emp['weight_in_kg']); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Blood Type</label>
                            <input type="text" name="blood_type" class="form-control" value="<?php echo old('blood_type', $emp['blood_type']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">GSIS ID No.</label>
                            <input type="text" name="gsis_no" class="form-control" value="<?php echo old('gsis_no', $emp['gsis_no']); ?>">
                        </div>
                        <!-- PAG-IBIG ID No. removed as it does not exist in database -->
                        <div class="col-md-3">
                            <label class="form-label">PhilHealth No.</label>
                            <input type="text" name="philhealthno" class="form-control" value="<?php echo old('philhealthno', $emp['philhealthno']); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">SSS No.</label>
                            <input type="text" name="sss_no" class="form-control" value="<?php echo old('sss_no', $emp['sss_no']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">TIN No.</label>
                            <input type="text" name="tin" class="form-control" value="<?php echo old('tin', $emp['tin']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Employee No.</label>
                            <input type="text" name="employee_no" class="form-control" value="<?php echo old('employee_no', $emp['employee_no']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Citizenship</label>
                            <input type="text" name="citizenship" class="form-control" value="<?php echo old('citizenship', $emp['citizenship']); ?>">
                        </div>
                    </div>

                    <h5 class="mt-4">Residential Address</h5>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">House/Block/Lot No.</label>
                            <input type="text" name="res_spec_address" class="form-control" value="<?php echo old('res_spec_address', $emp['res_spec_address']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Street</label>
                            <input type="text" name="res_street_address" class="form-control" value="<?php echo old('res_street_address', $emp['res_street_address']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subdivision/Village</label>
                            <input type="text" name="res_vill_address" class="form-control" value="<?php echo old('res_vill_address', $emp['res_vill_address']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Barangay</label>
                            <input type="text" name="res_barangay_address" class="form-control" value="<?php echo old('res_barangay_address', $emp['res_barangay_address']); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">City/Municipality</label>
                            <input type="text" name="res_city" class="form-control" value="<?php echo old('res_city', $emp['res_city']); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Province</label>
                            <input type="text" name="res_province" class="form-control" value="<?php echo old('res_province', $emp['res_province']); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Zip Code</label>
                            <input type="text" name="res_zipcode" class="form-control" value="<?php echo old('res_zipcode', $emp['res_zipcode']); ?>" required>
                        </div>
                    </div>

                    <h5 class="mt-4">Permanent Address</h5>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="sameAsResidential">
                        <label class="form-check-label" for="sameAsResidential">
                            Same as Residential Address
                        </label>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">House/Block/Lot No.</label>
                            <input type="text" name="perm_spec_address" class="form-control" value="<?php echo old('perm_spec_address', $emp['perm_spec_address']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Street</label>
                            <input type="text" name="perm_street_address" class="form-control" value="<?php echo old('perm_street_address', $emp['perm_street_address']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subdivision/Village</label>
                            <input type="text" name="perm_vill_address" class="form-control" value="<?php echo old('perm_vill_address', $emp['perm_vill_address']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Barangay</label>
                            <input type="text" name="perm_barangay_address" class="form-control" value="<?php echo old('perm_barangay_address', $emp['perm_barangay_address']); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">City/Municipality</label>
                            <input type="text" name="perm_city" class="form-control" value="<?php echo old('perm_city', $emp['perm_city']); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Province</label>
                            <input type="text" name="perm_province" class="form-control" value="<?php echo old('perm_province', $emp['perm_province']); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Zip Code</label>
                            <input type="text" name="perm_zipcode" class="form-control" value="<?php echo old('perm_zipcode', $emp['perm_zipcode']); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Telephone No.</label>
                            <input type="text" name="telephone" class="form-control" value="<?php echo old('telephone', $emp['telephone']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mobile No. <span class="text-danger">*</span></label>
                            <input type="text" name="mobile_no" class="form-control" value="<?php echo old('mobile_no', $emp['mobile_no']); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?php echo old('email', $emp['email']); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- II. Family Background -->
                <div class="tab-pane fade" id="family" role="tabpanel">
                    <h4 class="mt-3 mb-3">Family Background</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="familyTable">
                            <thead>
                                <tr>
                                    <th>Name (Last, First, Middle)</th>
                                    <th>Relationship</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($family as $fam): ?>
                                <tr>
                                    <td><input type="text" name="family_name[]" class="form-control" value="<?php echo htmlspecialchars($fam['last_name'] . ', ' . $fam['first_name'] . ', ' . $fam['middle_name']); ?>"></td>
                                    <td><input type="text" name="family_relationship[]" class="form-control" value="<?php echo htmlspecialchars($fam['relationship']); ?>"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($family)): ?>
                                <tr>
                                    <td><input type="text" name="family_name[]" class="form-control"></td>
                                    <td><input type="text" name="family_relationship[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($can_add_data): ?>
                        <button type="button" class="btn btn-primary btn-sm" id="addFamilyRow">Add Row</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- III. Educational Background -->
                <div class="tab-pane fade" id="education" role="tabpanel">
                    <h4 class="mt-3 mb-3">Educational Background</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="educationTable">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>School Name</th>
                                    <th>Degree/Course</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Units</th>
                                    <th>Year Grad</th>
                                    <th>Honors</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($education as $educ): ?>
                                <tr>
                                    <td><input type="text" name="educ_level[]" class="form-control" value="<?php echo htmlspecialchars($educ['education_level']); ?>"></td>
                                    <td><input type="text" name="educ_school[]" class="form-control" value="<?php echo htmlspecialchars($educ['institution_name']); ?>"></td>
                                    <td><input type="text" name="educ_degree[]" class="form-control" value="<?php echo htmlspecialchars($educ['Education_degree']); ?>"></td>
                                    <td><input type="text" name="educ_start[]" class="form-control" value="<?php echo htmlspecialchars($educ['start_period']); ?>"></td>
                                    <td><input type="text" name="educ_end[]" class="form-control" value="<?php echo htmlspecialchars($educ['end_period']); ?>"></td>
                                    <td><input type="text" name="educ_units[]" class="form-control" value="<?php echo htmlspecialchars($educ['units_earned']); ?>"></td>
                                    <td><input type="text" name="educ_grad[]" class="form-control" value="<?php echo htmlspecialchars($educ['year_graduated']); ?>"></td>
                                    <td><input type="text" name="educ_honors[]" class="form-control" value="<?php echo htmlspecialchars($educ['acad_honors']); ?>"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($education)): ?>
                                <tr>
                                    <td><input type="text" name="educ_level[]" class="form-control"></td>
                                    <td><input type="text" name="educ_school[]" class="form-control"></td>
                                    <td><input type="text" name="educ_degree[]" class="form-control"></td>
                                    <td><input type="text" name="educ_start[]" class="form-control"></td>
                                    <td><input type="text" name="educ_end[]" class="form-control"></td>
                                    <td><input type="text" name="educ_units[]" class="form-control"></td>
                                    <td><input type="text" name="educ_grad[]" class="form-control"></td>
                                    <td><input type="text" name="educ_honors[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($can_add_data): ?>
                        <button type="button" class="btn btn-primary btn-sm" id="addEducRow">Add Row</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- IV. Civil Service Eligibility -->
                <div class="tab-pane fade" id="eligibility" role="tabpanel">
                    <h4 class="mt-3 mb-3">Civil Service Eligibility</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="eligibilityTable">
                            <thead>
                                <tr>
                                    <th>Exam Name</th>
                                    <th>Rating</th>
                                    <th>Date</th>
                                    <th>Place</th>
                                    <th>License No.</th>
                                    <th>Validity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eligibility as $elig): ?>
                                <tr>
                                    <td><input type="text" name="cse_name[]" class="form-control" value="<?php echo htmlspecialchars($elig['Exam_description']); ?>"></td>
                                    <td><input type="text" name="cse_rating[]" class="form-control" value="<?php echo htmlspecialchars($elig['rating']); ?>"></td>
                                    <td><input type="date" name="cse_date[]" class="form-control" value="<?php echo htmlspecialchars($elig['exam_date']); ?>"></td>
                                    <td><input type="text" name="cse_place[]" class="form-control" value="<?php echo htmlspecialchars($elig['exam_place']); ?>"></td>
                                    <td><input type="text" name="cse_license[]" class="form-control" value="<?php echo htmlspecialchars($elig['license_no']); ?>"></td>
                                    <td><input type="date" name="cse_validity[]" class="form-control" value="<?php echo htmlspecialchars($elig['license_validity']); ?>"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($eligibility)): ?>
                                <tr>
                                    <td><input type="text" name="cse_name[]" class="form-control"></td>
                                    <td><input type="text" name="cse_rating[]" class="form-control"></td>
                                    <td><input type="date" name="cse_date[]" class="form-control"></td>
                                    <td><input type="text" name="cse_place[]" class="form-control"></td>
                                    <td><input type="text" name="cse_license[]" class="form-control"></td>
                                    <td><input type="date" name="cse_validity[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($can_add_data): ?>
                        <button type="button" class="btn btn-primary btn-sm" id="addCseRow">Add Row</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- V. Work Experience -->
                <div class="tab-pane fade" id="work" role="tabpanel">
                    <h4 class="mt-3 mb-3">Work Experience</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="workTable">
                            <thead>
                                <tr>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Position</th>
                                    <th>Agency/Company</th>
                                    <th>Salary</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                    <th>Gov?</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($work_exp as $work): ?>
                                <tr>
                                    <td><input type="date" name="work_from[]" class="form-control" value="<?php echo htmlspecialchars($work['appointment_start_date']); ?>"></td>
                                    <td><input type="date" name="work_to[]" class="form-control" value="<?php echo htmlspecialchars($work['appointment_end_date']); ?>"></td>
                                    <td><input type="text" name="work_position[]" class="form-control" value="<?php echo htmlspecialchars($work['job_category']); ?>"></td>
                                    <td><input type="text" name="work_agency[]" class="form-control" value="<?php echo htmlspecialchars($work['institution_name']); ?>"></td>
                                    <td><input type="number" step="0.01" name="work_salary[]" class="form-control" value="<?php echo htmlspecialchars($work['monthly_salary']); ?>"></td>
                                    <td><input type="text" name="work_grade[]" class="form-control" value="<?php echo htmlspecialchars($work['pay_grade']); ?>"></td>
                                    <td><input type="text" name="work_status[]" class="form-control" value="N/A"></td> <!-- Status not in service_records -->
                                    <td>
                                        <select name="work_gov[]" class="form-select">
                                            <option value="1" <?php echo ($work['gov_service'] == 1) ? 'selected' : ''; ?>>Yes</option>
                                            <option value="0" <?php echo ($work['gov_service'] == 0) ? 'selected' : ''; ?>>No</option>
                                        </select>
                                    </td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($work_exp)): ?>
                                <tr>
                                    <td><input type="date" name="work_from[]" class="form-control"></td>
                                    <td><input type="date" name="work_to[]" class="form-control"></td>
                                    <td><input type="text" name="work_position[]" class="form-control"></td>
                                    <td><input type="text" name="work_agency[]" class="form-control"></td>
                                    <td><input type="number" step="0.01" name="work_salary[]" class="form-control"></td>
                                    <td><input type="text" name="work_grade[]" class="form-control"></td>
                                    <td><input type="text" name="work_status[]" class="form-control"></td>
                                    <td>
                                        <select name="work_gov[]" class="form-select">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($can_add_data): ?>
                        <button type="button" class="btn btn-primary btn-sm" id="addWorkRow">Add Row</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- VI. Voluntary Work -->
                <div class="tab-pane fade" id="voluntary" role="tabpanel">
                    <h4 class="mt-3 mb-3">Voluntary Work</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="voluntaryTable">
                            <thead>
                                <tr>
                                    <th>Organization</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Hours</th>
                                    <th>Position</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($voluntary as $vol): ?>
                                <tr>
                                    <td><input type="text" name="vol_org[]" class="form-control" value="<?php echo htmlspecialchars($vol['institution_name']); ?>"></td>
                                    <td><input type="date" name="vol_from[]" class="form-control" value="<?php echo htmlspecialchars($vol['start_date']); ?>"></td>
                                    <td><input type="date" name="vol_to[]" class="form-control" value="<?php echo htmlspecialchars($vol['end_date']); ?>"></td>
                                    <td><input type="number" name="vol_hours[]" class="form-control" value="<?php echo htmlspecialchars($vol['no_hours']); ?>"></td>
                                    <td><input type="text" name="vol_position[]" class="form-control" value="<?php echo htmlspecialchars($vol['work_nature']); ?>"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($voluntary)): ?>
                                <tr>
                                    <td><input type="text" name="vol_org[]" class="form-control"></td>
                                    <td><input type="date" name="vol_from[]" class="form-control"></td>
                                    <td><input type="date" name="vol_to[]" class="form-control"></td>
                                    <td><input type="number" name="vol_hours[]" class="form-control"></td>
                                    <td><input type="text" name="vol_position[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($can_add_data): ?>
                        <button type="button" class="btn btn-primary btn-sm" id="addVolRow">Add Row</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- VII. Learning & Development -->
                <div class="tab-pane fade" id="training" role="tabpanel">
                    <h4 class="mt-3 mb-3">Learning & Development (L&D)</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="trainingTable">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Hours</th>
                                    <th>Type</th>
                                    <th>Sponsor</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trainings as $train): ?>
                                <tr>
                                    <td><input type="text" name="ld_title[]" class="form-control" value="<?php echo htmlspecialchars($train['training_title']); ?>"></td>
                                    <td><input type="date" name="ld_from[]" class="form-control" value="<?php echo htmlspecialchars($train['start_date']); ?>"></td>
                                    <td><input type="date" name="ld_to[]" class="form-control" value="<?php echo htmlspecialchars($train['end_date']); ?>"></td>
                                    <td><input type="number" name="ld_hours[]" class="form-control" value="0"></td> <!-- Hours not in trainings table -->
                                    <td><input type="text" name="ld_type[]" class="form-control" value="<?php echo htmlspecialchars($train['training_type']); ?>"></td>
                                    <td><input type="text" name="ld_sponsor[]" class="form-control" value="<?php echo htmlspecialchars($train['institution_name']); ?>"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($trainings)): ?>
                                <tr>
                                    <td><input type="text" name="ld_title[]" class="form-control"></td>
                                    <td><input type="date" name="ld_from[]" class="form-control"></td>
                                    <td><input type="date" name="ld_to[]" class="form-control"></td>
                                    <td><input type="number" name="ld_hours[]" class="form-control"></td>
                                    <td><input type="text" name="ld_type[]" class="form-control"></td>
                                    <td><input type="text" name="ld_sponsor[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($can_add_data): ?>
                        <button type="button" class="btn btn-primary btn-sm" id="addLdRow">Add Row</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- VIII. Other Information -->
                <div class="tab-pane fade" id="other" role="tabpanel">
                    <h4 class="mt-3 mb-3">Other Information</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="otherTable">
                            <thead>
                                <tr>
                                    <th>Special Skill / Hobby / Recognition</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($skills as $skill): ?>
                                <tr>
                                    <td><input type="text" name="other_name[]" class="form-control" value="<?php echo htmlspecialchars($skill['skill_description']); ?>"></td>
                                    <td>
                                        <select name="other_type[]" class="form-select">
                                            <option value="Skill" <?php echo ($skill['skill_type'] == 'Skill') ? 'selected' : ''; ?>>Skill</option>
                                            <option value="Recognition" <?php echo ($skill['skill_type'] == 'Recognition') ? 'selected' : ''; ?>>Recognition</option>
                                            <option value="Organization" <?php echo ($skill['skill_type'] == 'Organization') ? 'selected' : ''; ?>>Organization</option>
                                        </select>
                                    </td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($skills)): ?>
                                <tr>
                                    <td><input type="text" name="other_name[]" class="form-control"></td>
                                    <td>
                                        <select name="other_type[]" class="form-select">
                                            <option value="Skill">Skill</option>
                                            <option value="Recognition">Recognition</option>
                                            <option value="Organization">Organization</option>
                                        </select>
                                    </td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($can_add_data): ?>
                        <button type="button" class="btn btn-primary btn-sm" id="addOtherRow">Add Row</button>
                        <?php endif; ?>
                    </div>

                    <hr class="my-4">
                    
                    <!-- Current Assignment (System Info) -->
                    <h4 class="mb-3">Current Assignment (System Info)</h4>
                    <?php if (!$can_edit_assignment): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You do not have permission to edit assignment details.
                        </div>
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Department / Unit</label>
                            <?php $dept_val = $assign ? $assign['departments_iddepartments'] : ''; ?>
                            <select name="department_id" class="form-select" required <?php echo $can_edit_assignment ? '' : 'disabled'; ?>>
                                <option value="">Select Department</option>
                                <?php 
                                $dept_result->data_seek(0);
                                while($row = $dept_result->fetch_assoc()): 
                                    $selected = (old('department_id', $dept_val) == $row['iddepartments']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $row['iddepartments']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($row['dept_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if (!$can_edit_assignment): ?>
                                <input type="hidden" name="department_id" value="<?php echo old('department_id', $dept_val); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <?php $pos_val = $srv ? $srv['job_positions_idjob_positions'] : ''; ?>
                            <select name="job_position_id" class="form-select" required <?php echo $can_edit_assignment ? '' : 'disabled'; ?>>
                                <option value="">Select Position</option>
                                <?php 
                                $pos_result->data_seek(0);
                                while($row = $pos_result->fetch_assoc()): 
                                    $selected = (old('job_position_id', $pos_val) == $row['idjob_positions']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $row['idjob_positions']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($row['job_category']); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if (!$can_edit_assignment): ?>
                                <input type="hidden" name="job_position_id" value="<?php echo old('job_position_id', $pos_val); ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Monthly Salary</label>
                            <input type="number" step="0.01" name="monthly_salary" class="form-control" value="<?php echo old('monthly_salary', $srv ? $srv['monthly_salary'] : ''); ?>" required <?php echo $can_edit_assignment ? '' : 'readonly'; ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pay Grade</label>
                            <input type="text" name="pay_grade" class="form-control" value="<?php echo old('pay_grade', $srv ? $srv['pay_grade'] : ''); ?>" required <?php echo $can_edit_assignment ? '' : 'readonly'; ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contract Type</label>
                            <?php $contract_val = $srv ? $srv['contract_types_idcontract_types'] : ''; ?>
                            <select name="contract_type_id" class="form-select" required <?php echo $can_edit_assignment ? '' : 'disabled'; ?>>
                                <option value="">Select Contract Type</option>
                                <?php 
                                $contract_result->data_seek(0);
                                while($row = $contract_result->fetch_assoc()): 
                                    $selected = (old('contract_type_id', $contract_val) == $row['idcontract_types']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $row['idcontract_types']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($row['contract_classification']); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if (!$can_edit_assignment): ?>
                                <input type="hidden" name="contract_type_id" value="<?php echo old('contract_type_id', $contract_val); ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Appointment Start Date</label>
                            <input type="date" name="appointment_start_date" class="form-control" value="<?php echo old('appointment_start_date', $srv ? $srv['appointment_start_date'] : ''); ?>" required <?php echo $can_edit_assignment ? '' : 'readonly'; ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Appointment End Date</label>
                            <input type="date" name="appointment_end_date" class="form-control" value="<?php echo old('appointment_end_date', $srv ? $srv['appointment_end_date'] : ''); ?>" <?php echo $can_edit_assignment ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                         <div class="col-md-6">
                            <label class="form-label">Institution (Employer)</label>
                            <?php $inst_val = $srv ? $srv['institutions_idinstitutions'] : ''; ?>
                            <select name="institution_id" class="form-select" required <?php echo $can_edit_assignment ? '' : 'disabled'; ?>>
                                <option value="">Select Institution</option>
                                <?php 
                                $inst_result->data_seek(0);
                                while($row = $inst_result->fetch_assoc()): 
                                    $selected = (old('institution_id', $inst_val) == $row['idinstitutions']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $row['idinstitutions']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($row['institution_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if (!$can_edit_assignment): ?>
                                <input type="hidden" name="institution_id" value="<?php echo old('institution_id', $inst_val); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gov Service?</label>
                            <?php $gov_val = $srv ? $srv['gov_service'] : ''; ?>
                            <select name="gov_service" class="form-select" required <?php echo $can_edit_assignment ? '' : 'disabled'; ?>>
                                <option value="1" <?php echo (old('gov_service', $gov_val) == '1') ? 'selected' : ''; ?>>Yes</option>
                                <option value="0" <?php echo (old('gov_service', $gov_val) == '0') ? 'selected' : ''; ?>>No</option>
                            </select>
                            <?php if (!$can_edit_assignment): ?>
                                <input type="hidden" name="gov_service" value="<?php echo old('gov_service', $gov_val); ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- IX. C4 & Refs -->
                <div class="tab-pane fade" id="c4" role="tabpanel">
                    <h4 class="mt-3 mb-3">Part IX: C4 - Questions</h4>
                    
                    <!-- Q34 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="fw-bold">34. Are you related by consanguinity or affinity to the appointing or recommending authority...</p>
                            <div class="mb-2">
                                <label>a. within the third degree?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q34A" value="1" <?php echo ($emp['Q34A'] == 1) ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q34A" value="0" <?php echo ($emp['Q34A'] == 0) ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label>b. within the fourth degree?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q34B" value="1" <?php echo ($emp['Q34B'] == 1) ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q34B" value="0" <?php echo ($emp['Q34B'] == 0) ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q34_details" class="form-control mt-2" placeholder="If YES, give details" value="<?php echo htmlspecialchars($emp['Q34_details']); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Q35 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="mb-2">
                                <p class="fw-bold">35. a. Have you ever been found guilty of any administrative offense?</p>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q35a" value="1" <?php echo ($emp['Q35a'] == 1) ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q35a" value="0" <?php echo ($emp['Q35a'] == 0) ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <p class="fw-bold">b. Have you been criminally charged before any court?</p>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q35b" value="1" <?php echo ($emp['Q35b'] == 1) ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q35b" value="0" <?php echo ($emp['Q35b'] == 0) ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q35_details" class="form-control mt-2" placeholder="If YES, give details" value="<?php echo htmlspecialchars($emp['Q35_details']); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Q36 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="fw-bold">36. Have you ever been convicted of any crime or violation of any law...?</p>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q36" value="Yes" <?php echo ($emp['Q36'] == 'Yes') ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q36" value="No" <?php echo ($emp['Q36'] == 'No') ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                </div>
                            </div>
                            <input type="text" name="Q36_details" class="form-control mt-2" placeholder="If YES, give details" value="<?php echo htmlspecialchars($emp['Q36_details']); ?>">
                        </div>
                    </div>

                    <!-- Q37 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="fw-bold">37. Have you ever been separated from the service...?</p>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q37" value="1" <?php echo ($emp['Q37'] == 1) ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q37" value="0" <?php echo ($emp['Q37'] == 0) ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                </div>
                            </div>
                            <input type="text" name="Q37_details" class="form-control mt-2" placeholder="If YES, give details" value="<?php echo htmlspecialchars($emp['Q37_details']); ?>">
                        </div>
                    </div>

                    <!-- Q38 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="mb-2">
                                <p class="fw-bold">38. a. Have you ever been a candidate in a national or local election...?</p>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q38a" value="1" <?php echo ($emp['Q38a'] == 1) ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q38a" value="0" <?php echo ($emp['Q38a'] == 0) ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <p class="fw-bold">b. Have you resigned from the government service...?</p>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q38b" value="1" <?php echo ($emp['Q38b'] == 1) ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q38b" value="0" <?php echo ($emp['Q38b'] == 0) ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q38_details" class="form-control mt-2" placeholder="If YES, give details" value="<?php echo htmlspecialchars($emp['Q38_details']); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Q39 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="fw-bold">39. Have you acquired the status of an immigrant...?</p>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q39a" value="1" <?php echo ($emp['Q39a'] == 1) ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q39a" value="0" <?php echo ($emp['Q39a'] == 0) ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                </div>
                            </div>
                            <input type="text" name="Q39_details" class="form-control mt-2" placeholder="If YES, give details" value="<?php echo htmlspecialchars($emp['Q39_details']); ?>">
                        </div>
                    </div>

                    <!-- Q40 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="fw-bold">40. Pursuant to: (a) Indigenous People's Act...</p>
                            
                            <div class="mb-2">
                                <label>a. Are you a member of any indigenous group?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40a" value="1" <?php echo ($emp['Q40a'] == 1) ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40a" value="0" <?php echo ($emp['Q40a'] == 0) ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q40a_details" class="form-control mt-2" placeholder="If YES, please specify" value="<?php echo htmlspecialchars($emp['Q40a_details']); ?>">
                            </div>

                            <div class="mb-2">
                                <label>b. Are you a person with disability?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40b" value="1" <?php echo ($emp['Q40b'] == 1) ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40b" value="0" <?php echo ($emp['Q40b'] == 0) ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q40b_details" class="form-control mt-2" placeholder="If YES, please specify ID No" value="<?php echo htmlspecialchars($emp['Q40b_details']); ?>">
                            </div>

                            <div class="mb-2">
                                <label>c. Are you a solo parent?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40c" value="1" <?php echo ($emp['Q40c'] == 1) ? 'checked' : ''; ?>> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40c" value="0" <?php echo ($emp['Q40c'] == 0) ? 'checked' : ''; ?>> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q40c_details" class="form-control mt-2" placeholder="If YES, please specify ID No" value="<?php echo htmlspecialchars($emp['Q40c_details']); ?>">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- References -->
                    <h4 class="mb-3">41. References</h4>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered" id="referencesTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Tel. No.</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($refs as $ref): ?>
                                <tr>
                                    <td><input type="text" name="ref_name[]" class="form-control" value="<?php echo htmlspecialchars($ref['name']); ?>" required></td>
                                    <td><input type="text" name="ref_address[]" class="form-control" value="<?php echo htmlspecialchars($ref['address']); ?>" required></td>
                                    <td><input type="text" name="ref_tel[]" class="form-control" value="<?php echo htmlspecialchars($ref['contact_no']); ?>" required></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($refs)): ?>
                                <tr>
                                    <td><input type="text" name="ref_name[]" class="form-control" required></td>
                                    <td><input type="text" name="ref_address[]" class="form-control" required></td>
                                    <td><input type="text" name="ref_tel[]" class="form-control" required></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($can_add_data): ?>
                        <button type="button" class="btn btn-primary btn-sm" id="addRefRow">Add Row</button>
                        <?php endif; ?>
                    </div>

                    <!-- Government ID -->
                    <h4 class="mb-3">Government Issued ID</h4>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered" id="govIdTable">
                            <thead>
                                <tr>
                                    <th>ID Type</th>
                                    <th>ID No.</th>
                                    <th>Date Issued</th>
                                    <th>Place Issued</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gov_ids as $gid): ?>
                                <tr>
                                    <td><input type="text" name="gov_id_type[]" class="form-control" value="<?php echo htmlspecialchars($gid['id_type']); ?>" required></td>
                                    <td><input type="text" name="gov_id_no[]" class="form-control" value="<?php echo htmlspecialchars($gid['id_number']); ?>" required></td>
                                    <td><input type="date" name="gov_date_issued[]" class="form-control" value="<?php echo htmlspecialchars($gid['date_of_issuance']); ?>"></td>
                                    <td><input type="text" name="gov_place_issued[]" class="form-control" value="<?php echo htmlspecialchars($gid['place_of_issuance']); ?>" required></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($gov_ids)): ?>
                                <tr>
                                    <td><input type="text" name="gov_id_type[]" class="form-control" required></td>
                                    <td><input type="text" name="gov_id_no[]" class="form-control" required></td>
                                    <td><input type="date" name="gov_date_issued[]" class="form-control"></td>
                                    <td><input type="text" name="gov_place_issued[]" class="form-control" required></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($can_add_data): ?>
                        <button type="button" class="btn btn-primary btn-sm" id="addGovIdRow">Add Row</button>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success btn-lg">Update Employee Record</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper to add rows
    function addRow(tableId, templateRow) {
        const table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
        const newRow = table.rows[0].cloneNode(true);
        
        // Clear inputs
        const inputs = newRow.querySelectorAll('input, select');
        inputs.forEach(input => input.value = '');
        
        table.appendChild(newRow);
        
        // Re-attach event listeners for remove buttons
        attachRemoveListeners();
    }

    function attachRemoveListeners() {
        const buttons = document.querySelectorAll('.remove-row');
        buttons.forEach(btn => {
            btn.onclick = function() {
                const row = this.closest('tr');
                const tbody = row.closest('tbody');
                if (tbody.rows.length > 1) {
                    row.remove();
                } else {
                    // Clear inputs if it's the last row
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => input.value = '');
                }
            };
        });
    }

    // Attach listeners
    document.getElementById('addFamilyRow').addEventListener('click', () => addRow('familyTable'));
    document.getElementById('addEducRow').addEventListener('click', () => addRow('educationTable'));
    document.getElementById('addCseRow').addEventListener('click', () => addRow('eligibilityTable'));
    document.getElementById('addWorkRow').addEventListener('click', () => addRow('workTable'));
    document.getElementById('addVolRow').addEventListener('click', () => addRow('voluntaryTable'));
    document.getElementById('addLdRow').addEventListener('click', () => addRow('trainingTable'));
    document.getElementById('addOtherRow').addEventListener('click', () => addRow('otherTable'));
    document.getElementById('addRefRow').addEventListener('click', () => addRow('referencesTable'));
    document.getElementById('addGovIdRow').addEventListener('click', () => addRow('govIdTable'));

    attachRemoveListeners();

    // Address Checkbox
    document.getElementById('sameAsResidential').addEventListener('change', function() {
        if(this.checked) {
            document.querySelector('[name="perm_spec_address"]').value = document.querySelector('[name="res_spec_address"]').value;
            document.querySelector('[name="perm_street_address"]').value = document.querySelector('[name="res_street_address"]').value;
            document.querySelector('[name="perm_vill_address"]').value = document.querySelector('[name="res_vill_address"]').value;
            document.querySelector('[name="perm_barangay_address"]').value = document.querySelector('[name="res_barangay_address"]').value;
            document.querySelector('[name="perm_city"]').value = document.querySelector('[name="res_city"]').value;
            document.querySelector('[name="perm_province"]').value = document.querySelector('[name="res_province"]').value;
            document.querySelector('[name="perm_zipcode"]').value = document.querySelector('[name="res_zipcode"]').value;
        }
    });

    // Form Validation for Hidden Tabs
    const form = document.getElementById('editEmployeeForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('invalid', function() {
            // Find the tab-pane this input belongs to
            const tabPane = input.closest('.tab-pane');
            if (tabPane) {
                const tabId = tabPane.getAttribute('id');
                // Find the button that targets this tab
                const tabButton = document.querySelector(`button[data-bs-target="#${tabId}"]`);
                if (tabButton) {
                    // Activate the tab
                    const tab = new bootstrap.Tab(tabButton);
                    tab.show();
                }
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
