<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_POST['emp_id'];
    
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
    $res_barangay = $_POST['res_barangay_address'];
    $res_city = $_POST['res_city'];
    $res_province = $_POST['res_province'];
    $res_zip = $_POST['res_zipcode'];
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
            res_barangay_address=?, res_city=?, res_province=?, res_zipcode=?,
            perm_barangay_address=?, perm_city=?, perm_province=?, perm_zipcode=?
            WHERE idemployees=?";
        
        $stmt_emp = $conn->prepare($sql_emp);
        $stmt_emp->bind_param("ssssssssssddssssssssissssssssi", 
            $first_name, $middle_name, $last_name, $name_extension,
            $birthdate, $birth_city, $birth_province, $birth_country,
            $sex, $civil_status, $height, $weight,
            $blood_type, $citizenship, $mobile_no, $email,
            $gsis_no, $sss_no, $philhealthno, $tin, $employee_no,
            $res_barangay, $res_city, $res_province, $res_zip,
            $perm_barangay, $perm_city, $perm_province, $perm_zip,
            $emp_id
        );
        
        if (!$stmt_emp->execute()) {
            throw new Exception("Error updating employee: " . $stmt_emp->error);
        }
        $stmt_emp->close();

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
                 $stmt_srv->bind_param("ssidssiiii", 
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
                 $stmt_srv->bind_param("issidssiii", 
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
            $stmt_srv->bind_param("iissidssi", $emp_id, $job_pos_id, $start_date, $end_date, $institution_id, $monthly_salary, $pay_grade, $contract_type_id, $gov_service);
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
             $stmt_srv->bind_param("ssidssiii", 
                $start_date, $end_date, $institution_id, $monthly_salary, $pay_grade, $contract_type_id, $gov_service,
                $emp_id, $job_pos_id
             );
             if (!$stmt_srv->execute()) {
                throw new Exception("Error updating service record: " . $stmt_srv->error);
             }
             $stmt_srv->close();
        }

        $conn->commit();
        header("Location: ../views/employee-list.php?msg=updated");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Transaction failed: " . $e->getMessage());
    }
}
?>
