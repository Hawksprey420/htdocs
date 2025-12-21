<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';
require_once '../classes/Logger.php';

Auth::requireLogin();
$user = Auth::user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Collect Input Data
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'] ?? '';
    $last_name = $_POST['last_name'];
    $name_extension = $_POST['name_extension'] ?? 'N/A';
    $birthdate = $_POST['birthdate'];
    $birth_city = $_POST['birth_city'];
    $birth_province = $_POST['birth_province'];
    $birth_country = $_POST['birth_country'];
    $sex = $_POST['sex'];
    $civil_status = $_POST['civil_status'];
    $height = $_POST['height_in_meter'];
    $weight = $_POST['weight_in_kg'];
    $blood_type = $_POST['blood_type'];
    $citizenship = $_POST['citizenship'];
    $mobile_no = $_POST['mobile_no'];
    $email = $_POST['email'];
    $gsis = $_POST['gsis_no'] ?? 'N/A';
    $sss = $_POST['sss_no'] ?? 'N/A';
    $philhealth = $_POST['philhealthno'] ?? 'N/A';
    $tin = $_POST['tin'];
    $employee_no = $_POST['employee_no'];

    // Address
    $res_brgy = $_POST['res_barangay_address'];
    $res_city = $_POST['res_city'];
    $res_prov = $_POST['res_province'];
    $res_zip = $_POST['res_zipcode'];
    
    $perm_brgy = $_POST['perm_barangay_address'];
    $perm_city = $_POST['perm_city'];
    $perm_prov = $_POST['perm_province'];
    $perm_zip = $_POST['perm_zipcode'];

    // Assignment & Position
    $dept_id = $_POST['department_id'];
    $pos_id = $_POST['job_position_id'];
    $salary = $_POST['monthly_salary'];
    $pay_grade = $_POST['pay_grade'];
    $contract_type = $_POST['contract_type_id'];
    $start_date = $_POST['appointment_start_date'];
    $end_date = $_POST['appointment_end_date'];
    $institution_id = $_POST['institution_id'];
    $gov_service = $_POST['gov_service'];

    // Defaults for missing columns
    $contactno = $mobile_no; // Use mobile as contact
    $res_spec = 'N/A';
    $res_street = 'N/A';
    $res_vill = 'N/A';
    $res_muni = $res_city; // Assume city/muni same for now
    
    $perm_spec = 'N/A';
    $perm_street = 'N/A';
    $perm_vill = 'N/A';
    $perm_muni = $perm_city;

    $telephone = 'N/A';
    
    // Q columns defaults
    $q34a = 0; $q34b = 0; $q34_det = 'N/A';
    $q35a = 0; $q35b = 0; $q35_det = 'N/A';
    $q36 = '0'; $q36_det = 'N/A';
    $q37 = 0; $q37_det = 'N/A';
    $q38a = 0; $q38b = 0; $q38_det = 'N/A';
    $q39a = 0; $q39b = 0; $q39_det = 'N/A';
    $q40a = 0; $q40a_det = 'N/A';
    $q40b = 0; $q40b_det = 'N/A';
    $q40c = 0; $q40c_det = 'N/A';

    $conn->begin_transaction();

    try {
        // Insert into employees
        $sql_emp = "INSERT INTO employees (
            first_name, middle_name, last_name, name_extension, 
            birthdate, birth_city, birth_province, birth_country, 
            sex, civil_status, height_in_meter, weight_in_kg, 
            contactno, blood_type, gsis_no, sss_no, philhealthno, tin, employee_no, citizenship,
            res_spec_address, res_street_address, res_vill_address, res_barangay_address, res_city, res_municipality, res_province, res_zipcode,
            perm_spec_address, perm_street_address, perm_vill_address, perm_barangay_address, perm_city, perm_municipality, perm_province, perm_zipcode,
            telephone, mobile_no, email,
            Q34A, Q34B, Q34_details, Q35a, Q35b, Q35_details, Q36, Q36_details, Q37, Q37_details,
            Q38a, Q38b, Q38_details, Q39a, Q39b, Q39_details, Q40a, Q40a_details, Q40b, Q40b_details, Q40c, Q40c_details
        ) VALUES (
            ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";

        $stmt = $conn->prepare($sql_emp);
        $stmt->bind_param(
            "ssssssssssddssssssissssssssssssssssssssiiisiisisisiisiisiisiis",
            $first_name, $middle_name, $last_name, $name_extension,
            $birthdate, $birth_city, $birth_province, $birth_country,
            $sex, $civil_status, $height, $weight,
            $contactno, $blood_type, $gsis, $sss, $philhealth, $tin, $employee_no, $citizenship,
            $res_spec, $res_street, $res_vill, $res_brgy, $res_city, $res_muni, $res_prov, $res_zip,
            $perm_spec, $perm_street, $perm_vill, $perm_brgy, $perm_city, $perm_muni, $perm_prov, $perm_zip,
            $telephone, $mobile_no, $email,
            $q34a, $q34b, $q34_det, $q35a, $q35b, $q35_det, $q36, $q36_det, $q37, $q37_det,
            $q38a, $q38b, $q38_det, $q39a, $q39b, $q39_det, $q40a, $q40a_det, $q40b, $q40b_det, $q40c, $q40c_det
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error inserting employee: " . $stmt->error);
        }
        $emp_id = $conn->insert_id;
        $stmt->close();

        // Insert into employees_unitassignments
        $sql_dept = "INSERT INTO employees_unitassignments (employees_idemployees, departments_iddepartments, transfer_date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_dept);
        $stmt->bind_param("iis", $emp_id, $dept_id, $start_date);
        if (!$stmt->execute()) {
             throw new Exception("Error inserting unit assignment: " . $stmt->error);
        }
        $stmt->close();

        // Insert into service_records
        $sql_srv = "INSERT INTO service_records (
            employees_idemployees, job_positions_idjob_positions, 
            appointment_start_date, appointment_end_date, 
            institutions_idinstitutions, monthly_salary, pay_grade, 
            contract_types_idcontract_types, gov_service
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_srv);
        $stmt->bind_param(
            "iissidsii",
            $emp_id, $pos_id,
            $start_date, $end_date,
            $institution_id, $salary, $pay_grade,
            $contract_type, $gov_service
        );
        if (!$stmt->execute()) {
            throw new Exception("Error inserting service record: " . $stmt->error);
        }
        $stmt->close();

        $conn->commit();
        Logger::log($user['id'], 'Add Employee', "Added employee ID: $emp_id ($first_name $last_name)");
        
        header("Location: ../views/employee-list.php?msg=added");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>
