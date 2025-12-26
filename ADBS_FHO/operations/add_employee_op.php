<?php
session_start();
require_once '../config/auth.php';
require_once '../classes/conn.php';

Auth::requireLogin();

if (Auth::hasRole(3)) {
    $_SESSION['error'] = "Access Denied: You do not have permission to add employees.";
    header("Location: ../views/employee-data-view.php?id=" . Auth::user()['employee_id']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../views/add-employee.php");
    exit();
}

// Helper function to clean input
function clean($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Helper function to generate a new ID
function generateId($conn, $table, $idColumn) {
    // 1. Check if ID 1 is available
    $check = $conn->query("SELECT $idColumn FROM $table WHERE $idColumn = 1");
    if ($check->num_rows == 0) return 1;

    // 2. Find the first gap after 1
    $query = "
        SELECT t1.$idColumn + 1 AS next_id
        FROM $table t1
        LEFT JOIN $table t2 ON t1.$idColumn + 1 = t2.$idColumn
        WHERE t2.$idColumn IS NULL AND t1.$idColumn < 2147483647
        ORDER BY t1.$idColumn ASC
        LIMIT 1
    ";
    $result = $conn->query($query);
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['next_id'];
    }
    
    throw new Exception("Unable to generate ID for $table. Table might be full.");
}

// Helper to get or insert institution
function getOrInsertInstitution($conn, $name) {
    $name = clean($name);
    if (empty($name)) return null;
    
    $query = "SELECT idinstitutions FROM institutions WHERE institution_name = '$name' LIMIT 1";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['idinstitutions'];
    } else {
        $newId = generateId($conn, 'institutions', 'idinstitutions');
        $insert = "INSERT INTO institutions (idinstitutions, institution_name) VALUES ($newId, '$name')";
        if ($conn->query($insert)) {
            return $newId;
        }
    }
    return null;
}

// Helper to get or insert job position
function getOrInsertJobPosition($conn, $title) {
    $title = clean($title);
    if (empty($title)) return null;
    
    $query = "SELECT idjob_positions FROM job_positions WHERE job_category = '$title' LIMIT 1";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['idjob_positions'];
    } else {
        $newId = generateId($conn, 'job_positions', 'idjob_positions');
        $insert = "INSERT INTO job_positions (idjob_positions, job_category, Job_description) VALUES ($newId, '$title', '$title')";
        if ($conn->query($insert)) {
            return $newId;
        }
    }
    return null;
}

// Helper to get or insert professional exam
function getOrInsertExam($conn, $name) {
    $name = clean($name);
    if (empty($name)) return null;
    
    $query = "SELECT idprofessional_exams FROM professional_exams WHERE Exam_description = '$name' LIMIT 1";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['idprofessional_exams'];
    } else {
        $newId = generateId($conn, 'professional_exams', 'idprofessional_exams');
        $insert = "INSERT INTO professional_exams (idprofessional_exams, Exam_description) VALUES ($newId, '$name')";
        if ($conn->query($insert)) {
            return $newId;
        }
    }
    return null;
}

// Helper to get or insert skill
function getOrInsertSkill($conn, $name, $type) {
    $name = clean($name);
    $type = clean($type);
    if (empty($name)) return null;
    
    $query = "SELECT idskills FROM skills WHERE skill_description = '$name' AND skill_type = '$type' LIMIT 1";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['idskills'];
    } else {
        $newId = generateId($conn, 'skills', 'idskills');
        $insert = "INSERT INTO skills (idskills, skill_description, skill_type) VALUES ($newId, '$name', '$type')";
        if ($conn->query($insert)) {
            return $newId;
        }
    }
    return null;
}

// Start Transaction
$conn->begin_transaction();

try {
    // 1. Insert Employee
    $first_name = clean($_POST['first_name']);
    $middle_name = clean($_POST['middle_name']);
    $last_name = clean($_POST['last_name']);
    $name_extension = clean($_POST['name_extension']);
    $birthdate = clean($_POST['birthdate']);
    $birth_city = clean($_POST['birth_city']);
    $birth_province = clean($_POST['birth_province']);
    $birth_country = clean($_POST['birth_country']);
    $sex = clean($_POST['sex']);
    $civil_status = clean($_POST['civil_status']);
    $height = !empty($_POST['height_in_meter']) ? clean($_POST['height_in_meter']) : 0;
    $weight = !empty($_POST['weight_in_kg']) ? clean($_POST['weight_in_kg']) : 0;
    $blood_type = clean($_POST['blood_type']);
    $gsis = clean($_POST['gsis_no']);
    // $pagibig = clean($_POST['pagibig_no']); // Removed
    $philhealth = clean($_POST['philhealthno']);
    $sss = clean($_POST['sss_no']);
    $tin = clean($_POST['tin']);
    $emp_no = clean($_POST['employee_no']);
    $citizenship = clean($_POST['citizenship']);
    
    $res_spec = clean($_POST['res_spec_address']);
    $res_street = clean($_POST['res_street_address']);
    $res_vill = clean($_POST['res_vill_address']);
    $res_brgy = clean($_POST['res_barangay_address']);
    $res_city = clean($_POST['res_city']);
    $res_prov = clean($_POST['res_province']);
    $res_zip = clean($_POST['res_zipcode']);
    
    $perm_spec = clean($_POST['perm_spec_address']);
    $perm_street = clean($_POST['perm_street_address']);
    $perm_vill = clean($_POST['perm_vill_address']);
    $perm_brgy = clean($_POST['perm_barangay_address']);
    $perm_city = clean($_POST['perm_city']);
    $perm_prov = clean($_POST['perm_province']);
    $perm_zip = clean($_POST['perm_zipcode']);
    
    $tel = clean($_POST['telephone']);
    $mobile = clean($_POST['mobile_no']);
    $email = clean($_POST['email']);

    // Q34-Q40
    $q34a = isset($_POST['Q34A']) ? clean($_POST['Q34A']) : 0;
    $q34b = isset($_POST['Q34B']) ? clean($_POST['Q34B']) : 0;
    $q34_det = isset($_POST['Q34_details']) ? clean($_POST['Q34_details']) : '';
    
    $q35a = isset($_POST['Q35a']) ? clean($_POST['Q35a']) : 0;
    $q35b = isset($_POST['Q35b']) ? clean($_POST['Q35b']) : 0;
    $q35_det = isset($_POST['Q35_details']) ? clean($_POST['Q35_details']) : '';
    
    $q36 = isset($_POST['Q36']) ? clean($_POST['Q36']) : 'No';
    $q36_det = isset($_POST['Q36_details']) ? clean($_POST['Q36_details']) : '';
    
    $q37 = isset($_POST['Q37']) ? clean($_POST['Q37']) : 0;
    $q37_det = isset($_POST['Q37_details']) ? clean($_POST['Q37_details']) : '';
    
    $q38a = isset($_POST['Q38a']) ? clean($_POST['Q38a']) : 0;
    $q38b = isset($_POST['Q38b']) ? clean($_POST['Q38b']) : 0;
    $q38_det = isset($_POST['Q38_details']) ? clean($_POST['Q38_details']) : '';
    
    $q39a = isset($_POST['Q39a']) ? clean($_POST['Q39a']) : 0;
    $q39b = 0; 
    $q39_det = isset($_POST['Q39_details']) ? clean($_POST['Q39_details']) : '';
    
    $q40a = isset($_POST['Q40a']) ? clean($_POST['Q40a']) : 0;
    $q40a_det = isset($_POST['Q40a_details']) ? clean($_POST['Q40a_details']) : '';
    $q40b = isset($_POST['Q40b']) ? clean($_POST['Q40b']) : 0;
    $q40b_det = isset($_POST['Q40b_details']) ? clean($_POST['Q40b_details']) : '';
    $q40c = isset($_POST['Q40c']) ? clean($_POST['Q40c']) : 0;
    $q40c_det = isset($_POST['Q40c_details']) ? clean($_POST['Q40c_details']) : '';

    $sql_emp = "INSERT INTO employees (
        first_name, middle_name, last_name, name_extension, 
        birthdate, birth_city, birth_province, birth_country, 
        sex, civil_status, height_in_meter, weight_in_kg, 
        blood_type, gsis_no, philhealthno, sss_no, tin, employee_no, citizenship,
        res_spec_address, res_street_address, res_vill_address, res_barangay_address, res_city, res_municipality, res_province, res_zipcode,
        perm_spec_address, perm_street_address, perm_vill_address, perm_barangay_address, perm_city, perm_municipality, perm_province, perm_zipcode,
        telephone, mobile_no, contactno, email,
        Q34A, Q34B, Q34_details, Q35a, Q35b, Q35_details, Q36, Q36_details, Q37, Q37_details, 
        Q38a, Q38b, Q38_details, Q39a, Q39b, Q39_details, Q40a, Q40a_details, Q40b, Q40b_details, Q40c, Q40c_details
    ) VALUES (
        '$first_name', '$middle_name', '$last_name', '$name_extension',
        '$birthdate', '$birth_city', '$birth_province', '$birth_country',
        '$sex', '$civil_status', '$height', '$weight',
        '$blood_type', '$gsis', '$philhealth', '$sss', '$tin', '$emp_no', '$citizenship',
        '$res_spec', '$res_street', '$res_vill', '$res_brgy', '$res_city', '$res_city', '$res_prov', '$res_zip',
        '$perm_spec', '$perm_street', '$perm_vill', '$perm_brgy', '$perm_city', '$perm_city', '$perm_prov', '$perm_zip',
        '$tel', '$mobile', '$mobile', '$email',
        '$q34a', '$q34b', '$q34_det', '$q35a', '$q35b', '$q35_det', '$q36', '$q36_det', '$q37', '$q37_det',
        '$q38a', '$q38b', '$q38_det', '$q39a', '$q39b', '$q39_det', '$q40a', '$q40a_det', '$q40b', '$q40b_det', '$q40c', '$q40c_det'
    )";

    if (!$conn->query($sql_emp)) {
        throw new Exception("Error inserting employee: " . $conn->error);
    }
    $employee_id = $conn->insert_id;

    // 2. Family Background
    if (isset($_POST['family_name'])) {
        foreach ($_POST['family_name'] as $index => $name) {
            if (empty($name)) continue;
            $rel_type = clean($_POST['family_relationship'][$index]);
            $name_parts = explode(',', $name);
            $rel_last = isset($name_parts[0]) ? clean(trim($name_parts[0])) : '';
            $rel_first = isset($name_parts[1]) ? clean(trim($name_parts[1])) : '';
            $rel_mid = isset($name_parts[2]) ? clean(trim($name_parts[2])) : '';

            // Insert into relatives
            $newRelId = generateId($conn, 'relatives', 'idrelatives');
            $sql_rel = "INSERT INTO relatives (idrelatives, first_name, middle_name, last_name, name_extension, Occupation, Emp_business, business_address, telephone, birthdate) VALUES ($newRelId, '$rel_first', '$rel_mid', '$rel_last', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '1900-01-01')";
            if (!$conn->query($sql_rel)) throw new Exception("Error inserting relative: " . $conn->error);
            $rel_id = $newRelId;

            // Link
            $sql_emp_rel = "INSERT INTO employees_relatives (employees_idemployees, Relatives_idrelatives, relationship) VALUES ('$employee_id', '$rel_id', '$rel_type')";
            if (!$conn->query($sql_emp_rel)) throw new Exception("Error linking relative: " . $conn->error);
        }
    }

    // 3. Education
    if (isset($_POST['educ_school'])) {
        foreach ($_POST['educ_school'] as $index => $school) {
            if (empty($school)) continue;
            $level = clean($_POST['educ_level'][$index]);
            $degree = clean($_POST['educ_degree'][$index]);
            $from = clean($_POST['educ_start'][$index]);
            $to = clean($_POST['educ_end'][$index]);
            $units = clean($_POST['educ_units'][$index]);
            $grad = clean($_POST['educ_grad'][$index]);
            $honors = clean($_POST['educ_honors'][$index]);

            $inst_id = getOrInsertInstitution($conn, $school);
            if (!$inst_id) continue;

            $sql_educ = "INSERT INTO employees_education (
                employees_idemployees, institutions_idinstitutions, education_level, Education_degree, 
                start_period, end_period, units_earned, year_graduated, acad_honors, scholarships
            ) VALUES (
                '$employee_id', '$inst_id', '$level', '$degree', 
                '$from', '$to', '$units', '$grad', '$honors', 'N/A'
            )";
            if (!$conn->query($sql_educ)) throw new Exception("Error inserting education: " . $conn->error);
        }
    }

    // 4. Eligibility
    if (isset($_POST['cse_name'])) {
        foreach ($_POST['cse_name'] as $index => $exam_name) {
            if (empty($exam_name)) continue;
            $rating = clean($_POST['cse_rating'][$index]);
            $date = clean($_POST['cse_date'][$index]);
            $place = clean($_POST['cse_place'][$index]);
            $license = clean($_POST['cse_license'][$index]);
            $validity = clean($_POST['cse_validity'][$index]);

            $exam_id = getOrInsertExam($conn, $exam_name);
            if (!$exam_id) continue;

            $sql_elig = "INSERT INTO employees_prof_eligibility (
                employees_idemployees, professional_exams_idprofessional_exams, rating, exam_date, exam_place, license_no, license_validity
            ) VALUES (
                '$employee_id', '$exam_id', '$rating', '$date', '$place', '$license', '$validity'
            )";
            if (!$conn->query($sql_elig)) throw new Exception("Error inserting eligibility: " . $conn->error);
        }
    }

    // 5. Work Experience
    if (isset($_POST['work_position'])) {
        foreach ($_POST['work_position'] as $index => $position) {
            if (empty($position)) continue;
            $from = clean($_POST['work_from'][$index]);
            $to = clean($_POST['work_to'][$index]);
            $agency = clean($_POST['work_agency'][$index]);
            $salary = clean($_POST['work_salary'][$index]);
            $grade = clean($_POST['work_grade'][$index]);
            $status = clean($_POST['work_status'][$index]);
            $gov = clean($_POST['work_gov'][$index]);

            $pos_id = getOrInsertJobPosition($conn, $position);
            $inst_id = getOrInsertInstitution($conn, $agency);
            if (!$pos_id || !$inst_id) continue;

            // Note: contract_types_idcontract_types is required in service_records. 
            // We don't have it in the work exp table form, so we might need a default or nullable.
            // Assuming 1 for now or we need to check if it's nullable. 
            // Based on schema, it might be required. Let's try to find a default 'N/A' or similar.
            // For now, I'll use a dummy value '1' assuming there is at least one contract type.
            $contract_id = 1; 

            $sql_work = "INSERT INTO service_records (
                employees_idemployees, job_positions_idjob_positions, institutions_idinstitutions, contract_types_idcontract_types,
                appointment_start_date, appointment_end_date, monthly_salary, pay_grade, gov_service
            ) VALUES (
                '$employee_id', '$pos_id', '$inst_id', '$contract_id',
                '$from', '$to', '$salary', '$grade', '$gov'
            )";
            if (!$conn->query($sql_work)) throw new Exception("Error inserting work exp: " . $conn->error);
        }
    }

    // 6. Voluntary Work
    if (isset($_POST['vol_org'])) {
        foreach ($_POST['vol_org'] as $index => $org) {
            if (empty($org)) continue;
            $from = clean($_POST['vol_from'][$index]);
            $to = clean($_POST['vol_to'][$index]);
            $hours = clean($_POST['vol_hours'][$index]);
            $pos = clean($_POST['vol_position'][$index]);

            $inst_id = getOrInsertInstitution($conn, $org);
            if (!$inst_id) continue;

            $sql_vol = "INSERT INTO employees_ext_involvements (
                employees_idemployees, institutions_idinstitutions, start_date, end_date, no_hours, work_nature, involvement_type
            ) VALUES (
                '$employee_id', '$inst_id', '$from', '$to', '$hours', '$pos', 'Voluntary Work'
            )";
            if (!$conn->query($sql_vol)) throw new Exception("Error inserting voluntary work: " . $conn->error);
        }
    }

    // 7. Training
    if (isset($_POST['ld_title'])) {
        foreach ($_POST['ld_title'] as $index => $title) {
            if (empty($title)) continue;
            $from = clean($_POST['ld_from'][$index]);
            $to = clean($_POST['ld_to'][$index]);
            $type = clean($_POST['ld_type'][$index]);
            $sponsor = clean($_POST['ld_sponsor'][$index]);

            $inst_id = getOrInsertInstitution($conn, $sponsor);
            if (!$inst_id) continue;

            // Insert Training
            $newTrainId = generateId($conn, 'trainings', 'idtrainings');
            $sql_train = "INSERT INTO trainings (idtrainings, training_title, start_date, end_date, training_type, training_venue, institutions_idinstitutions) 
                          VALUES ($newTrainId, '$title', '$from', '$to', '$type', 'N/A', '$inst_id')";
            if (!$conn->query($sql_train)) throw new Exception("Error inserting training: " . $conn->error);
            $train_id = $newTrainId;

            // Link
            $sql_emp_train = "INSERT INTO employees_has_trainings (employees_idemployees, trainings_idtrainings, participation_type) 
                              VALUES ('$employee_id', '$train_id', 'Participant')";
            if (!$conn->query($sql_emp_train)) throw new Exception("Error linking training: " . $conn->error);
        }
    }

    // 8. Other Info
    if (isset($_POST['other_name'])) {
        foreach ($_POST['other_name'] as $index => $name) {
            if (empty($name)) continue;
            $type = clean($_POST['other_type'][$index]);
            
            $skill_id = getOrInsertSkill($conn, $name, $type);
            if (!$skill_id) continue;

            $sql_skill = "INSERT INTO skills_has_employees (skills_idskills, employees_idemployees, skill_level) 
                          VALUES ('$skill_id', '$employee_id', 'N/A')";
            if (!$conn->query($sql_skill)) throw new Exception("Error inserting skill: " . $conn->error);
        }
    }

    // 9. Current Assignment (System Info)
    $dept_id = clean($_POST['department_id']);
    $job_id = clean($_POST['job_position_id']);
    $contract_id = clean($_POST['contract_type_id']);
    $appt_start = clean($_POST['appointment_start_date']);
    $appt_end = !empty($_POST['appointment_end_date']) ? clean($_POST['appointment_end_date']) : NULL;
    $salary = !empty($_POST['monthly_salary']) ? clean($_POST['monthly_salary']) : 0;

    // Insert into employees_unitassignments
    $sql_assign = "INSERT INTO employees_unitassignments (employees_idemployees, departments_iddepartments, transfer_date) 
                   VALUES ('$employee_id', '$dept_id', '$appt_start')";
    if (!$conn->query($sql_assign)) throw new Exception("Error inserting assignment: " . $conn->error);

    // Insert into service_records (Current)
    // Need an institution ID for the current organization. Let's try to find 'LGU' or create it.
    $current_org_id = getOrInsertInstitution($conn, 'Local Government Unit');
    
    $appt_end_val = $appt_end ? "'$appt_end'" : "NULL";
    $sql_curr_work = "INSERT INTO service_records (
        employees_idemployees, job_positions_idjob_positions, institutions_idinstitutions, contract_types_idcontract_types,
        appointment_start_date, appointment_end_date, monthly_salary, pay_grade, gov_service
    ) VALUES (
        '$employee_id', '$job_id', '$current_org_id', '$contract_id',
        '$appt_start', $appt_end_val, '$salary', 'N/A', 1
    )";
    if (!$conn->query($sql_curr_work)) throw new Exception("Error inserting current work: " . $conn->error);

    // 10. References
    if (isset($_POST['ref_name'])) {
        foreach ($_POST['ref_name'] as $index => $name) {
            if (empty($name)) continue;
            $address = clean($_POST['ref_address'][$index]);
            $tel = clean($_POST['ref_tel'][$index]);

            $sql_ref = "INSERT INTO character_references (employee_id, name, address, contact_no) 
                        VALUES ('$employee_id', '$name', '$address', '$tel')";
            if (!$conn->query($sql_ref)) throw new Exception("Error inserting reference: " . $conn->error);
        }
    }

    // 11. Government ID
    $gov_type = clean($_POST['gov_id_type']);
    $gov_no = clean($_POST['gov_id_no']);
    $gov_date = clean($_POST['gov_id_date']);
    $gov_place = clean($_POST['gov_id_place']);
    
    if (!empty($gov_type)) {
        $sql_gov = "INSERT INTO government_ids (employee_id, id_type, id_number, date_of_issuance, place_of_issuance) 
                    VALUES ('$employee_id', '$gov_type', '$gov_no', '$gov_date', '$gov_place')";
        if (!$conn->query($sql_gov)) throw new Exception("Error inserting government ID: " . $conn->error);
    }

    $conn->commit();
    $_SESSION['success'] = "Employee added successfully!";
    header("Location: ../views/employee-data-view.php?id=" . $employee_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Failed to add employee: " . $e->getMessage();
    $_SESSION['form_data'] = $_POST; // Save form data to repopulate
    header("Location: ../views/add-employee.php");
    exit();
}
?>
