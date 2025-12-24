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
        <h2 class="mb-4">Add New Employee (CSC Form 212)</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="../operations/add_employee_op.php" method="POST" id="addEmployeeForm">
            
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
                    <button class="nav-link" id="c4-tab" data-bs-toggle="tab" data-bs-target="#c4" type="button" role="tab">IX. C4 Questions</button>
                </li>
            </ul>

            <div class="tab-content p-3 border border-top-0 bg-white" id="employeeTabsContent">
                
                <!-- I. Personal Information -->
                <div class="tab-pane fade show active" id="personal" role="tabpanel">
                    <h4 class="mt-3 mb-3">Personal Information</h4>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo old('first_name'); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" value="<?php echo old('middle_name'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo old('last_name'); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Name Extension (Jr, Sr)</label>
                            <input type="text" name="name_extension" class="form-control" value="<?php echo old('name_extension'); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
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
                            <label class="form-label">Sex <span class="text-danger">*</span></label>
                            <select name="sex" class="form-select" required>
                                <option value="Male" <?php echo (old('sex') == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (old('sex') == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Civil Status <span class="text-danger">*</span></label>
                            <select name="civil_status" class="form-select" required>
                                <option value="Single" <?php echo (old('civil_status') == 'Single') ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo (old('civil_status') == 'Married') ? 'selected' : ''; ?>>Married</option>
                                <option value="Widowed" <?php echo (old('civil_status') == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                                <option value="Separated" <?php echo (old('civil_status') == 'Separated') ? 'selected' : ''; ?>>Separated</option>
                                <option value="Divorced" <?php echo (old('civil_status') == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Height (m)</label>
                            <input type="number" step="0.01" name="height_in_meter" class="form-control" value="<?php echo old('height_in_meter'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" step="0.01" name="weight_in_kg" class="form-control" value="<?php echo old('weight_in_kg'); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Blood Type</label>
                            <input type="text" name="blood_type" class="form-control" value="<?php echo old('blood_type'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">GSIS ID No.</label>
                            <input type="text" name="gsis_no" class="form-control" value="<?php echo old('gsis_no'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PAG-IBIG ID No.</label>
                            <input type="text" name="pagibig_no" class="form-control" value="<?php echo old('pagibig_no'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PhilHealth No.</label>
                            <input type="text" name="philhealthno" class="form-control" value="<?php echo old('philhealthno'); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">SSS No.</label>
                            <input type="text" name="sss_no" class="form-control" value="<?php echo old('sss_no'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">TIN No.</label>
                            <input type="text" name="tin" class="form-control" value="<?php echo old('tin'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Employee No.</label>
                            <input type="text" name="employee_no" class="form-control" value="<?php echo old('employee_no'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Citizenship</label>
                            <input type="text" name="citizenship" class="form-control" value="<?php echo old('citizenship', 'Filipino'); ?>">
                        </div>
                    </div>

                    <h5 class="mt-4">Residential Address</h5>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">House/Block/Lot No.</label>
                            <input type="text" name="res_spec_address" class="form-control" value="<?php echo old('res_spec_address'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Street</label>
                            <input type="text" name="res_street_address" class="form-control" value="<?php echo old('res_street_address'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subdivision/Village</label>
                            <input type="text" name="res_vill_address" class="form-control" value="<?php echo old('res_vill_address'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Barangay</label>
                            <input type="text" name="res_barangay_address" class="form-control" value="<?php echo old('res_barangay_address'); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">City/Municipality</label>
                            <input type="text" name="res_city" class="form-control" value="<?php echo old('res_city'); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Province</label>
                            <input type="text" name="res_province" class="form-control" value="<?php echo old('res_province'); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Zip Code</label>
                            <input type="text" name="res_zipcode" class="form-control" value="<?php echo old('res_zipcode'); ?>" required>
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
                            <input type="text" name="perm_spec_address" class="form-control" value="<?php echo old('perm_spec_address'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Street</label>
                            <input type="text" name="perm_street_address" class="form-control" value="<?php echo old('perm_street_address'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subdivision/Village</label>
                            <input type="text" name="perm_vill_address" class="form-control" value="<?php echo old('perm_vill_address'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Barangay</label>
                            <input type="text" name="perm_barangay_address" class="form-control" value="<?php echo old('perm_barangay_address'); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">City/Municipality</label>
                            <input type="text" name="perm_city" class="form-control" value="<?php echo old('perm_city'); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Province</label>
                            <input type="text" name="perm_province" class="form-control" value="<?php echo old('perm_province'); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Zip Code</label>
                            <input type="text" name="perm_zipcode" class="form-control" value="<?php echo old('perm_zipcode'); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Telephone No.</label>
                            <input type="text" name="telephone" class="form-control" value="<?php echo old('telephone'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mobile No. <span class="text-danger">*</span></label>
                            <input type="text" name="mobile_no" class="form-control" value="<?php echo old('mobile_no'); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?php echo old('email'); ?>" required>
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
                                    <th>Relationship</th>
                                    <th>Name (Last, First, Middle)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select name="family_relationship[]" class="form-select">
                                            <option value="Spouse">Spouse</option>
                                            <option value="Father">Father</option>
                                            <option value="Mother">Mother</option>
                                            <option value="Child">Child</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="family_name[]" class="form-control" placeholder="Last Name, First Name, Middle Name"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary btn-sm" id="addFamilyRow">Add Row</button>
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
                                    <th>Name of School</th>
                                    <th>Degree/Course</th>
                                    <th>Period From</th>
                                    <th>Period To</th>
                                    <th>Units Earned</th>
                                    <th>Year Graduated</th>
                                    <th>Honors</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select name="educ_level[]" class="form-select">
                                            <option value="Elementary">Elementary</option>
                                            <option value="Secondary">Secondary</option>
                                            <option value="Vocational">Vocational</option>
                                            <option value="College">College</option>
                                            <option value="Graduate Studies">Graduate Studies</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="educ_school[]" class="form-control"></td>
                                    <td><input type="text" name="educ_degree[]" class="form-control"></td>
                                    <td><input type="date" name="educ_start[]" class="form-control"></td>
                                    <td><input type="date" name="educ_end[]" class="form-control"></td>
                                    <td><input type="text" name="educ_units[]" class="form-control"></td>
                                    <td><input type="text" name="educ_grad[]" class="form-control"></td>
                                    <td><input type="text" name="educ_honors[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary btn-sm" id="addEducRow">Add Row</button>
                    </div>
                </div>

                <!-- IV. Civil Service Eligibility -->
                <div class="tab-pane fade" id="eligibility" role="tabpanel">
                    <h4 class="mt-3 mb-3">Civil Service Eligibility</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="eligibilityTable">
                            <thead>
                                <tr>
                                    <th>Eligibility/Exam Name</th>
                                    <th>Rating</th>
                                    <th>Date of Exam</th>
                                    <th>Place of Exam</th>
                                    <th>License No.</th>
                                    <th>Validity Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="cse_name[]" class="form-control"></td>
                                    <td><input type="text" name="cse_rating[]" class="form-control"></td>
                                    <td><input type="date" name="cse_date[]" class="form-control"></td>
                                    <td><input type="text" name="cse_place[]" class="form-control"></td>
                                    <td><input type="text" name="cse_license[]" class="form-control"></td>
                                    <td><input type="date" name="cse_validity[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary btn-sm" id="addCseRow">Add Row</button>
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
                                    <th>Position Title</th>
                                    <th>Agency/Company</th>
                                    <th>Monthly Salary</th>
                                    <th>Pay Grade</th>
                                    <th>Status of Appointment</th>
                                    <th>Gov't Service (Y/N)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="date" name="work_from[]" class="form-control"></td>
                                    <td><input type="date" name="work_to[]" class="form-control"></td>
                                    <td><input type="text" name="work_position[]" class="form-control"></td>
                                    <td><input type="text" name="work_agency[]" class="form-control"></td>
                                    <td><input type="number" name="work_salary[]" class="form-control"></td>
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
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary btn-sm" id="addWorkRow">Add Row</button>
                    </div>
                </div>

                <!-- VI. Voluntary Work -->
                <div class="tab-pane fade" id="voluntary" role="tabpanel">
                    <h4 class="mt-3 mb-3">Voluntary Work</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="voluntaryTable">
                            <thead>
                                <tr>
                                    <th>Organization Name</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>No. of Hours</th>
                                    <th>Position/Nature of Work</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="vol_org[]" class="form-control"></td>
                                    <td><input type="date" name="vol_from[]" class="form-control"></td>
                                    <td><input type="date" name="vol_to[]" class="form-control"></td>
                                    <td><input type="number" name="vol_hours[]" class="form-control"></td>
                                    <td><input type="text" name="vol_position[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary btn-sm" id="addVolRow">Add Row</button>
                    </div>
                </div>

                <!-- VII. Learning & Development -->
                <div class="tab-pane fade" id="training" role="tabpanel">
                    <h4 class="mt-3 mb-3">Learning & Development (L&D)</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="trainingTable">
                            <thead>
                                <tr>
                                    <th>Title of Learning & Dev.</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>No. of Hours</th>
                                    <th>Type of LD</th>
                                    <th>Conducted By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="ld_title[]" class="form-control"></td>
                                    <td><input type="date" name="ld_from[]" class="form-control"></td>
                                    <td><input type="date" name="ld_to[]" class="form-control"></td>
                                    <td><input type="number" name="ld_hours[]" class="form-control"></td>
                                    <td><input type="text" name="ld_type[]" class="form-control"></td>
                                    <td><input type="text" name="ld_sponsor[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary btn-sm" id="addLdRow">Add Row</button>
                    </div>
                </div>

                <!-- VIII. Other Information -->
                <div class="tab-pane fade" id="other" role="tabpanel">
                    <h4 class="mt-3 mb-3">Other Information</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="otherTable">
                            <thead>
                                <tr>
                                    <th>Skill / Hobby / Recognition</th>
                                    <th>Type (Skill, Hobby, Recognition)</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="other_name[]" class="form-control"></td>
                                    <td>
                                        <select name="other_type[]" class="form-select">
                                            <option value="Skill">Skill</option>
                                            <option value="Hobby">Hobby</option>
                                            <option value="Recognition">Recognition</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="other_desc[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary btn-sm" id="addOtherRow">Add Row</button>
                    </div>

                    <hr class="my-4">
                    
                    <h4 class="mb-3">Current Assignment (System Info)</h4>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php while($dept = $dept_result->fetch_assoc()): ?>
                                    <option value="<?php echo $dept['iddepartments']; ?>" <?php echo (old('department_id') == $dept['iddepartments']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['dept_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Job Position <span class="text-danger">*</span></label>
                            <select name="job_position_id" class="form-select" required>
                                <option value="">Select Position</option>
                                <?php while($pos = $pos_result->fetch_assoc()): ?>
                                    <option value="<?php echo $pos['idjob_positions']; ?>" <?php echo (old('job_position_id') == $pos['idjob_positions']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pos['job_category']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contract Type <span class="text-danger">*</span></label>
                            <select name="contract_type_id" class="form-select" required>
                                <option value="">Select Contract Type</option>
                                <?php while($con = $contract_result->fetch_assoc()): ?>
                                    <option value="<?php echo $con['idcontract_types']; ?>" <?php echo (old('contract_type_id') == $con['idcontract_types']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($con['contract_classification']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Appointment Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="appointment_start_date" class="form-control" value="<?php echo old('appointment_start_date'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Appointment End Date</label>
                            <input type="date" name="appointment_end_date" class="form-control" value="<?php echo old('appointment_end_date'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Monthly Salary</label>
                            <input type="number" name="monthly_salary" class="form-control" value="<?php echo old('monthly_salary'); ?>">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('c4-tab').click()">Next: C4 Questions</button>
                    </div>
                </div>

                <!-- IX. C4 - Questions -->
                <div class="tab-pane fade" id="c4" role="tabpanel">
                    <h4 class="mt-3 mb-3">Part IX: C4 - Questions</h4>
                    
                    <!-- Q34 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="fw-bold">34. Are you related by consanguinity or affinity to the appointing or recommending authority, or to the chief of bureau or office or to the person who has immediate supervision over you in the Office, Bureau or Department where you will be appointed,</p>
                            
                            <div class="mb-2">
                                <label>a. within the third degree?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q34A" value="1"> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q34A" value="0" checked> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <label>b. within the fourth degree (for Local Government Unit - Career Employees)?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q34B" value="1"> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q34B" value="0" checked> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q34_details" class="form-control mt-2" placeholder="If YES, give details">
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
                                        <input class="form-check-input" type="radio" name="Q35a" value="1"> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q35a" value="0" checked> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <p class="fw-bold">b. Have you been criminally charged before any court?</p>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q35b" value="1"> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q35b" value="0" checked> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q35_details" class="form-control mt-2" placeholder="If YES, give details (Date Filed, Status of Case/s)">
                            </div>
                        </div>
                    </div>

                    <!-- Q36 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="fw-bold">36. Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?</p>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q36" value="Yes"> <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q36" value="No" checked> <label class="form-check-label">No</label>
                                </div>
                            </div>
                            <input type="text" name="Q36_details" class="form-control mt-2" placeholder="If YES, give details">
                        </div>
                    </div>

                    <!-- Q37 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="fw-bold">37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished contract or phased out (abolition) in the public or private sector?</p>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q37" value="1"> <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q37" value="0" checked> <label class="form-check-label">No</label>
                                </div>
                            </div>
                            <input type="text" name="Q37_details" class="form-control mt-2" placeholder="If YES, give details">
                        </div>
                    </div>

                    <!-- Q38 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="mb-2">
                                <p class="fw-bold">38. a. Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?</p>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q38a" value="1"> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q38a" value="0" checked> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <p class="fw-bold">b. Have you resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national or local candidate?</p>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q38b" value="1"> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q38b" value="0" checked> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q38_details" class="form-control mt-2" placeholder="If YES, give details">
                            </div>
                        </div>
                    </div>

                    <!-- Q39 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="fw-bold">39. Have you acquired the status of an immigrant or permanent resident of another country?</p>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q39a" value="1"> <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="Q39a" value="0" checked> <label class="form-check-label">No</label>
                                </div>
                            </div>
                            <input type="text" name="Q39_details" class="form-control mt-2" placeholder="If YES, give details (Country)">
                        </div>
                    </div>

                    <!-- Q40 -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="fw-bold">40. Pursuant to: (a) Indigenous People's Act (RA 8371); (b) Magna Carta for Disabled Persons (RA 7277, as amended); and (c) Expanded Solo Parents Welfare Act (RA 11861), please answer the following items:</p>
                            
                            <div class="mb-2">
                                <label>a. Are you a member of any indigenous group?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40a" value="1"> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40a" value="0" checked> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q40a_details" class="form-control mt-2" placeholder="If YES, please specify">
                            </div>

                            <div class="mb-2">
                                <label>b. Are you a person with disability?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40b" value="1"> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40b" value="0" checked> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q40b_details" class="form-control mt-2" placeholder="If YES, please specify ID No">
                            </div>

                            <div class="mb-2">
                                <label>c. Are you a solo parent?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40c" value="1"> <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="Q40c" value="0" checked> <label class="form-check-label">No</label>
                                    </div>
                                </div>
                                <input type="text" name="Q40c_details" class="form-control mt-2" placeholder="If YES, please specify ID No">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- References -->
                    <h4 class="mb-3">41. References (Person not related by consanguinity or affinity to applicant /appointee)</h4>
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
                                <tr>
                                    <td><input type="text" name="ref_name[]" class="form-control"></td>
                                    <td><input type="text" name="ref_address[]" class="form-control"></td>
                                    <td><input type="text" name="ref_tel[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary btn-sm" id="addRefRow">Add Row</button>
                    </div>

                    <!-- Government ID -->
                    <h4 class="mb-3">Government Issued ID (i.e.Passport, GSIS, SSS, PRC, Driver's License, etc.)</h4>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Government Issued ID</label>
                            <input type="text" name="gov_id_type" class="form-control" placeholder="e.g. Passport, Driver's License">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ID/License/Passport No.</label>
                            <input type="text" name="gov_id_no" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date of Issuance</label>
                            <input type="date" name="gov_id_date" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Place of Issuance</label>
                            <input type="text" name="gov_id_place" class="form-control">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success btn-lg">Save Employee Record</button>
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
    const form = document.getElementById('addEmployeeForm');
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
