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
$sql_srv = "SELECT sr.*, jp.job_category, i.institution_name, ct.contract_classification 
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
                    <div class="col-md-4"><strong>Contract Type:</strong> <?php echo $srv ? htmlspecialchars($srv['contract_classification']) : 'N/A'; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Appointment Date:</strong> <?php echo $srv ? $srv['appointment_start_date'] . ' to ' . $srv['appointment_end_date'] : 'N/A'; ?></div>
                    <div class="col-md-6"><strong>Institution:</strong> <?php echo $srv ? htmlspecialchars($srv['institution_name']) : 'N/A'; ?></div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Government IDs</h5>
            </div>
            <div class="card-body">
                <?php
                $sql_gov = "SELECT * FROM government_ids WHERE employee_id = ?";
                $stmt = $conn->prepare($sql_gov);
                $stmt->bind_param("i", $emp_id);
                $stmt->execute();
                $res_gov = $stmt->get_result();
                
                if ($res_gov->num_rows > 0): ?>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>ID Type</th>
                                <th>ID Number</th>
                                <th>Date of Issuance</th>
                                <th>Place of Issuance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $res_gov->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['id_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['date_of_issuance']); ?></td>
                                <td><?php echo htmlspecialchars($row['place_of_issuance']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No government IDs recorded.</p>
                <?php endif; $stmt->close(); ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">C4 - Questions (Q34-Q40)</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>34. Related to appointing authority?</strong><br>
                    3rd Degree: <?php echo $emp['Q34A'] ? 'Yes' : 'No'; ?><br>
                    4th Degree: <?php echo $emp['Q34B'] ? 'Yes' : 'No'; ?><br>
                    Details: <?php echo htmlspecialchars($emp['Q34_details']); ?>
                </div>
                <div class="mb-3">
                    <strong>35. Found guilty of offense?</strong><br>
                    Admin Offense: <?php echo $emp['Q35a'] ? 'Yes' : 'No'; ?><br>
                    Criminal Charge: <?php echo $emp['Q35b'] ? 'Yes' : 'No'; ?><br>
                    Details: <?php echo htmlspecialchars($emp['Q35_details']); ?>
                </div>
                <div class="mb-3">
                    <strong>36. Convicted of crime?</strong> <?php echo $emp['Q36']; ?><br>
                    Details: <?php echo htmlspecialchars($emp['Q36_details']); ?>
                </div>
                <div class="mb-3">
                    <strong>37. Separated from service?</strong> <?php echo $emp['Q37'] ? 'Yes' : 'No'; ?><br>
                    Details: <?php echo htmlspecialchars($emp['Q37_details']); ?>
                </div>
                <div class="mb-3">
                    <strong>38. Candidate in election?</strong><br>
                    Candidate: <?php echo $emp['Q38a'] ? 'Yes' : 'No'; ?><br>
                    Resigned for campaign: <?php echo $emp['Q38b'] ? 'Yes' : 'No'; ?><br>
                    Details: <?php echo htmlspecialchars($emp['Q38_details']); ?>
                </div>
                <div class="mb-3">
                    <strong>39. Immigrant status?</strong> <?php echo $emp['Q39a'] ? 'Yes' : 'No'; ?><br>
                    Details: <?php echo htmlspecialchars($emp['Q39_details']); ?>
                </div>
                <div class="mb-3">
                    <strong>40. Special Group Membership?</strong><br>
                    Indigenous: <?php echo $emp['Q40a'] ? 'Yes' : 'No'; ?> (<?php echo htmlspecialchars($emp['Q40a_details']); ?>)<br>
                    PWD: <?php echo $emp['Q40b'] ? 'Yes' : 'No'; ?> (<?php echo htmlspecialchars($emp['Q40b_details']); ?>)<br>
                    Solo Parent: <?php echo $emp['Q40c'] ? 'Yes' : 'No'; ?> (<?php echo htmlspecialchars($emp['Q40c_details']); ?>)
                </div>
            </div>
        </div>

        <div class="card mt-4 mb-5">
            <div class="card-header bg-light">
                <h5 class="mb-0">References</h5>
            </div>
            <div class="card-body">
                <?php
                $sql_ref = "SELECT * FROM character_references WHERE employee_id = ?";
                $stmt = $conn->prepare($sql_ref);
                $stmt->bind_param("i", $emp_id);
                $stmt->execute();
                $res_ref = $stmt->get_result();
                
                if ($res_ref->num_rows > 0): ?>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Contact No.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $res_ref->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No references recorded.</p>
                <?php endif; $stmt->close(); ?>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
