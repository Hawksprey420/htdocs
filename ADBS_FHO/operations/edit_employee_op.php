<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';

Auth::requireLogin();
$user = Auth::user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_POST['emp_id'];

    // RBAC Check
    // Admin (1) and HR (2) can edit anyone.
    // Employee (3) can only edit themselves.
    if (Auth::hasRole(3)) {
        if ($user['employee_id'] != $emp_id) {
            $_SESSION['error'] = "Access Denied: You can only edit your own record.";
            header("Location: ../views/employee-data-edit.php?id=$emp_id");
            exit();
        }
    } elseif (!Auth::hasRole(1) && !Auth::hasRole(2)) {
        // If not 1, 2, or 3 (or if role is missing)
        $_SESSION['error'] = "Access Denied: You do not have permission to edit records.";
        header("Location: ../views/employee-list.php");
        exit();
    }
    
    // Personal Info
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $name_extension = $_POST['name_extension'];
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
    $gsis_no = $_POST['gsis_no'];
    $sss_no = $_POST['sss_no'];
    $philhealthno = $_POST['philhealthno'];
    $tin = $_POST['tin'];
    $employee_no = $_POST['employee_no'];
    
    // Address
    $res_spec = $_POST['res_spec_address'];
    $res_street = $_POST['res_street_address'];
    $res_vill = $_POST['res_vill_address'];
    $res_barangay = $_POST['res_barangay_address'];
    $res_city = $_POST['res_city'];
    $res_province = $_POST['res_province'];
    $res_zip = $_POST['res_zipcode'];
    
    $perm_spec = $_POST['perm_spec_address'];
    $perm_street = $_POST['perm_street_address'];
    $perm_vill = $_POST['perm_vill_address'];
    $perm_barangay = $_POST['perm_barangay_address'];
    $perm_city = $_POST['perm_city'];
    $perm_province = $_POST['perm_province'];
    $perm_zip = $_POST['perm_zipcode'];

    // Assignment & Position
    $dept_id = $_POST['department_id'];
    $job_pos_id = $_POST['job_position_id'];
    $monthly_salary = $_POST['monthly_salary'];
    $pay_grade = $_POST['pay_grade'];
    $contract_type_id = $_POST['contract_type_id'];
    $start_date = $_POST['appointment_start_date'];
    $end_date = $_POST['appointment_end_date'];
    $institution_id = $_POST['institution_id'];
    $gov_service = $_POST['gov_service'];

    // Q34-Q40
    $q34a = isset($_POST['Q34A']) ? $_POST['Q34A'] : 0;
    $q34b = isset($_POST['Q34B']) ? $_POST['Q34B'] : 0;
    $q34_det = isset($_POST['Q34_details']) ? $_POST['Q34_details'] : '';
    
    $q35a = isset($_POST['Q35a']) ? $_POST['Q35a'] : 0;
    $q35b = isset($_POST['Q35b']) ? $_POST['Q35b'] : 0;
    $q35_det = isset($_POST['Q35_details']) ? $_POST['Q35_details'] : '';
    
    $q36 = isset($_POST['Q36']) ? $_POST['Q36'] : 'No';
    $q36_det = isset($_POST['Q36_details']) ? $_POST['Q36_details'] : '';
    
    $q37 = isset($_POST['Q37']) ? $_POST['Q37'] : 0;
    $q37_det = isset($_POST['Q37_details']) ? $_POST['Q37_details'] : '';
    
    $q38a = isset($_POST['Q38a']) ? $_POST['Q38a'] : 0;
    $q38b = isset($_POST['Q38b']) ? $_POST['Q38b'] : 0;
    $q38_det = isset($_POST['Q38_details']) ? $_POST['Q38_details'] : '';
    
    $q39a = isset($_POST['Q39a']) ? $_POST['Q39a'] : 0;
    $q39b = 0; 
    $q39_det = isset($_POST['Q39_details']) ? $_POST['Q39_details'] : '';
    
    $q40a = isset($_POST['Q40a']) ? $_POST['Q40a'] : 0;
    $q40a_det = isset($_POST['Q40a_details']) ? $_POST['Q40a_details'] : '';
    $q40b = isset($_POST['Q40b']) ? $_POST['Q40b'] : 0;
    $q40b_det = isset($_POST['Q40b_details']) ? $_POST['Q40b_details'] : '';
    $q40c = isset($_POST['Q40c']) ? $_POST['Q40c'] : 0;
    $q40c_det = isset($_POST['Q40c_details']) ? $_POST['Q40c_details'] : '';

    // Validation
    $required_fields = [
        'First Name' => $first_name,
        'Last Name' => $last_name,
        'Birthdate' => $birthdate,
        'Sex' => $sex,
        'Civil Status' => $civil_status,
        'Mobile No' => $mobile_no,
        'Email' => $email,
        'Department' => $dept_id,
        'Job Position' => $job_pos_id,
        'Contract Type' => $contract_type_id,
        'Start Date' => $start_date
    ];

    foreach ($required_fields as $field => $value) {
        if (empty($value)) {
            $_SESSION['error'] = "$field is required.";
            $_SESSION['form_data'] = $_POST;
            header("Location: ../views/employee-data-edit.php?id=$emp_id");
            exit();
        }
    }


    $original_job_pos_id = $_POST['original_job_position_id'];
    $original_dept_id = $_POST['original_department_id'];

    $conn->begin_transaction();

    try {
        // 1. Update Employees Table
        $sql_emp = "UPDATE employees SET 
            first_name=?, middle_name=?, last_name=?, name_extension=?, 
            birthdate=?, birth_city=?, birth_province=?, birth_country=?, 
            sex=?, civil_status=?, height_in_meter=?, weight_in_kg=?, 
            blood_type=?, citizenship=?, mobile_no=?, email=?, 
            gsis_no=?, sss_no=?, philhealthno=?, tin=?, employee_no=?,
            res_spec_address=?, res_street_address=?, res_vill_address=?, res_barangay_address=?, res_city=?, res_province=?, res_zipcode=?,
            perm_spec_address=?, perm_street_address=?, perm_vill_address=?, perm_barangay_address=?, perm_city=?, perm_province=?, perm_zipcode=?,
            Q34A=?, Q34B=?, Q34_details=?,
            Q35a=?, Q35b=?, Q35_details=?,
            Q36=?, Q36_details=?,
            Q37=?, Q37_details=?,
            Q38a=?, Q38b=?, Q38_details=?,
            Q39a=?, Q39b=?, Q39_details=?,
            Q40a=?, Q40a_details=?,
            Q40b=?, Q40b_details=?,
            Q40c=?, Q40c_details=?
            WHERE idemployees=?";
        
        $stmt_emp = $conn->prepare($sql_emp);
        $stmt_emp->bind_param("ssssssssssddssssssssissssssssssssssiisiisssisiisiisisisisi", 
            $first_name, $middle_name, $last_name, $name_extension,
            $birthdate, $birth_city, $birth_province, $birth_country,
            $sex, $civil_status, $height, $weight,
            $blood_type, $citizenship, $mobile_no, $email,
            $gsis_no, $sss_no, $philhealthno, $tin, $employee_no,
            $res_spec, $res_street, $res_vill, $res_barangay, $res_city, $res_province, $res_zip,
            $perm_spec, $perm_street, $perm_vill, $perm_barangay, $perm_city, $perm_province, $perm_zip,
            $q34a, $q34b, $q34_det,
            $q35a, $q35b, $q35_det,
            $q36, $q36_det,
            $q37, $q37_det,
            $q38a, $q38b, $q38_det,
            $q39a, $q39b, $q39_det,
            $q40a, $q40a_det,
            $q40b, $q40b_det,
            $q40c, $q40c_det,
            $emp_id
        );
        
        if (!$stmt_emp->execute()) {
            throw new Exception("Error updating employee: " . $stmt_emp->error);
        }
        $stmt_emp->close();

        // 1.1 Update Government IDs
        // Delete existing
        $del_gov = "DELETE FROM government_ids WHERE employee_id = ?";
        $stmt_del_gov = $conn->prepare($del_gov);
        $stmt_del_gov->bind_param("i", $emp_id);
        $stmt_del_gov->execute();
        $stmt_del_gov->close();

        // Insert new
        if (isset($_POST['gov_id_type']) && is_array($_POST['gov_id_type'])) {
            $sql_gov = "INSERT INTO government_ids (employee_id, id_type, id_number, date_of_issuance, place_of_issuance) VALUES (?, ?, ?, ?, ?)";
            $stmt_gov = $conn->prepare($sql_gov);
            
            foreach ($_POST['gov_id_type'] as $key => $type) {
                if (!empty($type)) {
                    $no = $_POST['gov_id_no'][$key];
                    $date = !empty($_POST['gov_date_issued'][$key]) ? $_POST['gov_date_issued'][$key] : NULL;
                    $place = $_POST['gov_place_issued'][$key];
                    
                    $stmt_gov->bind_param("issss", $emp_id, $type, $no, $date, $place);
                    $stmt_gov->execute();
                }
            }
            $stmt_gov->close();
        }

        // 1.2 Update Character References
        // Delete existing
        $del_ref = "DELETE FROM character_references WHERE employee_id = ?";
        $stmt_del_ref = $conn->prepare($del_ref);
        $stmt_del_ref->bind_param("i", $emp_id);
        $stmt_del_ref->execute();
        $stmt_del_ref->close();

        // Insert new
        if (isset($_POST['ref_name']) && is_array($_POST['ref_name'])) {
            $sql_ref = "INSERT INTO character_references (employee_id, name, address, contact_no) VALUES (?, ?, ?, ?)";
            $stmt_ref = $conn->prepare($sql_ref);
            
            foreach ($_POST['ref_name'] as $key => $name) {
                if (!empty($name)) {
                    $address = $_POST['ref_address'][$key];
                    $tel = $_POST['ref_tel_no'][$key];
                    
                    $stmt_ref->bind_param("isss", $emp_id, $name, $address, $tel);
                    $stmt_ref->execute();
                }
            }
            $stmt_ref->close();
        }

        // 2. Update Unit Assignment
        if ($dept_id != $original_dept_id) {
            if (!empty($original_dept_id)) {
                // Update existing assignment
                // Check if target exists
                $check_sql = "SELECT 1 FROM employees_unitassignments WHERE employees_idemployees = ? AND departments_iddepartments = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ii", $emp_id, $dept_id);
                $check_stmt->execute();
                if ($check_stmt->get_result()->num_rows > 0) {
                    // Target exists. We cannot update the PK to this.
                    // We should probably just update the transfer_date of the target?
                    // And delete the old one?
                    // For now, let's just throw an error or handle it gracefully.
                    // Let's DELETE the old one and UPDATE the new one's date.
                    $del_sql = "DELETE FROM employees_unitassignments WHERE employees_idemployees = ? AND departments_iddepartments = ?";
                    $del_stmt = $conn->prepare($del_sql);
                    $del_stmt->bind_param("ii", $emp_id, $original_dept_id);
                    $del_stmt->execute();
                    $del_stmt->close();

                    $upd_sql = "UPDATE employees_unitassignments SET transfer_date = NOW() WHERE employees_idemployees = ? AND departments_iddepartments = ?";
                    $upd_stmt = $conn->prepare($upd_sql);
                    $upd_stmt->bind_param("ii", $emp_id, $dept_id);
                    $upd_stmt->execute();
                    $upd_stmt->close();
                } else {
                    // Target does not exist. Safe to update PK.
                    $sql_assign = "UPDATE employees_unitassignments SET departments_iddepartments = ?, transfer_date = NOW() WHERE employees_idemployees = ? AND departments_iddepartments = ?";
                    $stmt_assign = $conn->prepare($sql_assign);
                    $stmt_assign->bind_param("iii", $dept_id, $emp_id, $original_dept_id);
                    if (!$stmt_assign->execute()) {
                        throw new Exception("Error updating assignment: " . $stmt_assign->error);
                    }
                    $stmt_assign->close();
                }
                $check_stmt->close();
            } else {
                // Insert new assignment
                $sql_assign = "INSERT INTO employees_unitassignments (employees_idemployees, departments_iddepartments, transfer_date) VALUES (?, ?, NOW())";
                $stmt_assign = $conn->prepare($sql_assign);
                $stmt_assign->bind_param("ii", $emp_id, $dept_id);
                if (!$stmt_assign->execute()) {
                    throw new Exception("Error inserting assignment: " . $stmt_assign->error);
                }
                $stmt_assign->close();
            }
        }

        // 3. Update Service Record
        // Always update fields. If PK changes, handle it.
        if ($job_pos_id != $original_job_pos_id && !empty($original_job_pos_id)) {
             // Check if target exists
             $check_sql = "SELECT 1 FROM service_records WHERE employees_idemployees = ? AND job_positions_idjob_positions = ?";
             $check_stmt = $conn->prepare($check_sql);
             $check_stmt->bind_param("ii", $emp_id, $job_pos_id);
             $check_stmt->execute();
             if ($check_stmt->get_result()->num_rows > 0) {
                 // Target exists. Delete old, Update new.
                 $del_sql = "DELETE FROM service_records WHERE employees_idemployees = ? AND job_positions_idjob_positions = ?";
                 $del_stmt = $conn->prepare($del_sql);
                 $del_stmt->bind_param("ii", $emp_id, $original_job_pos_id);
                 $del_stmt->execute();
                 $del_stmt->close();

                 $upd_sql = "UPDATE service_records SET 
                    appointment_start_date=?, appointment_end_date=?, 
                    institutions_idinstitutions=?, monthly_salary=?, 
                    pay_grade=?, contract_types_idcontract_types=?, gov_service=?
                    WHERE employees_idemployees=? AND job_positions_idjob_positions=?";
                 $stmt_srv = $conn->prepare($upd_sql);
                 $stmt_srv->bind_param("ssidsiiii", 
                    $start_date, $end_date, $institution_id, $monthly_salary, $pay_grade, $contract_type_id, $gov_service,
                    $emp_id, $job_pos_id
                 );
                 $stmt_srv->execute();
                 $stmt_srv->close();
             } else {
                 // Target does not exist. Update PK and fields.
                 $sql_srv = "UPDATE service_records SET 
                    job_positions_idjob_positions=?,
                    appointment_start_date=?, appointment_end_date=?, 
                    institutions_idinstitutions=?, monthly_salary=?, 
                    pay_grade=?, contract_types_idcontract_types=?, gov_service=?
                    WHERE employees_idemployees=? AND job_positions_idjob_positions=?";
                 $stmt_srv = $conn->prepare($sql_srv);
                 $stmt_srv->bind_param("issidsiiii", 
                    $job_pos_id,
                    $start_date, $end_date, $institution_id, $monthly_salary, $pay_grade, $contract_type_id, $gov_service,
                    $emp_id, $original_job_pos_id
                 );
                 if (!$stmt_srv->execute()) {
                    throw new Exception("Error updating service record: " . $stmt_srv->error);
                 }
                 $stmt_srv->close();
             }
             $check_stmt->close();
        } elseif (empty($original_job_pos_id)) {
            // Insert new
            $sql_srv = "INSERT INTO service_records (employees_idemployees, job_positions_idjob_positions, appointment_start_date, appointment_end_date, institutions_idinstitutions, monthly_salary, pay_grade, contract_types_idcontract_types, gov_service) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_srv = $conn->prepare($sql_srv);
            $stmt_srv->bind_param("iissidsii", $emp_id, $job_pos_id, $start_date, $end_date, $institution_id, $monthly_salary, $pay_grade, $contract_type_id, $gov_service);
            if (!$stmt_srv->execute()) {
                throw new Exception("Error inserting service record: " . $stmt_srv->error);
            }
            $stmt_srv->close();
        } else {
            // PK didn't change. Just update fields.
            $sql_srv = "UPDATE service_records SET 
                appointment_start_date=?, appointment_end_date=?, 
                institutions_idinstitutions=?, monthly_salary=?, 
                pay_grade=?, contract_types_idcontract_types=?, gov_service=?
                WHERE employees_idemployees=? AND job_positions_idjob_positions=?";
             $stmt_srv = $conn->prepare($sql_srv);
             $stmt_srv->bind_param("ssidsiiii", 
                $start_date, $end_date, $institution_id, $monthly_salary, $pay_grade, $contract_type_id, $gov_service,
                $emp_id, $job_pos_id
             );
             if (!$stmt_srv->execute()) {
                throw new Exception("Error updating service record: " . $stmt_srv->error);
             }
             $stmt_srv->close();
        }

        // 4. Update Government IDs
        // Delete existing
        $del_gov = "DELETE FROM government_ids WHERE employee_id = ?";
        $stmt_del_gov = $conn->prepare($del_gov);
        $stmt_del_gov->bind_param("i", $emp_id);
        $stmt_del_gov->execute();
        $stmt_del_gov->close();

        // Insert new
        if (isset($_POST['gov_id_type'])) {
            $sql_gov = "INSERT INTO government_ids (employee_id, id_type, id_number, date_of_issuance, place_of_issuance) VALUES (?, ?, ?, ?, ?)";
            $stmt_gov = $conn->prepare($sql_gov);
            
            foreach ($_POST['gov_id_type'] as $index => $type) {
                if (empty($type)) continue;
                $no = $_POST['gov_id_no'][$index];
                $date = !empty($_POST['gov_date_issued'][$index]) ? $_POST['gov_date_issued'][$index] : NULL;
                $place = $_POST['gov_place_issued'][$index];
                
                $stmt_gov->bind_param("issss", $emp_id, $type, $no, $date, $place);
                $stmt_gov->execute();
            }
            $stmt_gov->close();
        }

        // 5. Update Character References
        // Delete existing
        $del_ref = "DELETE FROM character_references WHERE employee_id = ?";
        $stmt_del_ref = $conn->prepare($del_ref);
        $stmt_del_ref->bind_param("i", $emp_id);
        $stmt_del_ref->execute();
        $stmt_del_ref->close();

        // Insert new
        if (isset($_POST['ref_name'])) {
            $sql_ref = "INSERT INTO character_references (employee_id, name, address, contact_no) VALUES (?, ?, ?, ?)";
            $stmt_ref = $conn->prepare($sql_ref);
            
            foreach ($_POST['ref_name'] as $index => $name) {
                if (empty($name)) continue;
                $address = $_POST['ref_address'][$index];
                $tel = $_POST['ref_tel'][$index];
                
                $stmt_ref->bind_param("isss", $emp_id, $name, $address, $tel);
                $stmt_ref->execute();
            }
            $stmt_ref->close();
        }

        $conn->commit();
        
        // Clear form data on success
        unset($_SESSION['form_data']);
        unset($_SESSION['error']);

        header("Location: ../views/employee-list.php?msg=updated");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Transaction failed: " . $e->getMessage();
        $_SESSION['form_data'] = $_POST;
        header("Location: ../views/employee-data-edit.php?id=$emp_id");
        exit();
    }
}
?>
