<?php
require_once '../config/auth.php';
require_once '../classes/conn.php';

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

Auth::requireLogin();
$user = Auth::user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_POST['emp_id'];
    // $current_srv_id removed as it does not exist

    // RBAC Check
    if (Auth::hasRole(3)) {
        if ($user['employee_id'] != $emp_id) {
            $_SESSION['error'] = "Access Denied: You can only edit your own record.";
            header("Location: ../views/employee-data-edit.php?id=$emp_id");
            exit();
        }
    } elseif (!Auth::hasRole(1) && !Auth::hasRole(2)) {
        $_SESSION['error'] = "Access Denied: You do not have permission to edit records.";
        header("Location: ../views/employee-list.php");
        exit();
    }
    
    // --- Personal Info ---
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
    $telephone = $_POST['telephone'];
    
    // --- Address ---
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

    // --- Assignment & Position (System Info) ---
    $can_edit_assignment = (Auth::hasRole(1) || Auth::hasRole(2));
    
    if ($can_edit_assignment) {
        $dept_id = $_POST['department_id'];
        $job_pos_id = $_POST['job_position_id'];
        $monthly_salary = $_POST['monthly_salary'];
        $pay_grade = $_POST['pay_grade'];
        $contract_type_id = $_POST['contract_type_id'];
        $start_date = $_POST['appointment_start_date'];
        $end_date = !empty($_POST['appointment_end_date']) ? $_POST['appointment_end_date'] : NULL;
        $institution_id = $_POST['institution_id'];
        $gov_service = $_POST['gov_service'];
    }

    // --- C4 Questions ---
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

    $conn->begin_transaction();

    try {
        // 1. Update Employees Table
        $sql_emp = "UPDATE employees SET 
            first_name=?, middle_name=?, last_name=?, name_extension=?, 
            birthdate=?, birth_city=?, birth_province=?, birth_country=?, 
            sex=?, civil_status=?, height_in_meter=?, weight_in_kg=?, 
            blood_type=?, citizenship=?, mobile_no=?, email=?, 
            gsis_no=?, sss_no=?, philhealthno=?, tin=?, employee_no=?, telephone=?,
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
        $stmt_emp->bind_param("ssssssssssddsssssssssissssssssssssssiisiisssisiisiisisisisi", 
            $first_name, $middle_name, $last_name, $name_extension,
            $birthdate, $birth_city, $birth_province, $birth_country,
            $sex, $civil_status, $height, $weight,
            $blood_type, $citizenship, $mobile_no, $email,
            $gsis_no, $sss_no, $philhealthno, $tin, $employee_no, $telephone,
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

        // 2. Update Assignment (If allowed)
        if ($can_edit_assignment) {
            // 2.1 Unit Assignment
            $original_dept_id = $_POST['original_department_id'];
            if ($dept_id != $original_dept_id) {
                // Insert new assignment history
                $sql_assign = "INSERT INTO employees_unitassignments (employees_idemployees, departments_iddepartments, transfer_date) VALUES (?, ?, NOW())";
                $stmt_assign = $conn->prepare($sql_assign);
                $stmt_assign->bind_param("ii", $emp_id, $dept_id);
                $stmt_assign->execute();
                $stmt_assign->close();
            }

            // 2.2 Service Record (Current)
            $original_job_pos_id = isset($_POST['original_job_position_id']) ? $_POST['original_job_position_id'] : null;
            
            if (!empty($original_job_pos_id)) {
                // If changing job position, we need to handle PK change
                if ($job_pos_id != $original_job_pos_id) {
                    // Check if target exists
                    $check = $conn->query("SELECT 1 FROM service_records WHERE employees_idemployees = $emp_id AND job_positions_idjob_positions = $job_pos_id");
                    if ($check->num_rows > 0) {
                        // Target exists. Delete old source, update target.
                        $conn->query("DELETE FROM service_records WHERE employees_idemployees = $emp_id AND job_positions_idjob_positions = $original_job_pos_id");
                        
                        $sql_srv = "UPDATE service_records SET 
                            appointment_start_date=?, appointment_end_date=?, 
                            institutions_idinstitutions=?, monthly_salary=?, pay_grade=?, 
                            contract_types_idcontract_types=?, gov_service=?
                            WHERE employees_idemployees=? AND job_positions_idjob_positions=?";
                        $stmt_srv = $conn->prepare($sql_srv);
                        $stmt_srv->bind_param("ssidsiiii", 
                            $start_date, $end_date, 
                            $institution_id, $monthly_salary, $pay_grade, 
                            $contract_type_id, $gov_service, $emp_id, $job_pos_id
                        );
                        $stmt_srv->execute();
                        $stmt_srv->close();
                    } else {
                        // Target does not exist. Update PK.
                        $sql_srv = "UPDATE service_records SET 
                            job_positions_idjob_positions=?, appointment_start_date=?, appointment_end_date=?, 
                            institutions_idinstitutions=?, monthly_salary=?, pay_grade=?, 
                            contract_types_idcontract_types=?, gov_service=?
                            WHERE employees_idemployees=? AND job_positions_idjob_positions=?";
                        $stmt_srv = $conn->prepare($sql_srv);
                        $stmt_srv->bind_param("issidsiii", 
                            $job_pos_id, $start_date, $end_date, 
                            $institution_id, $monthly_salary, $pay_grade, 
                            $contract_type_id, $gov_service, $emp_id, $original_job_pos_id
                        );
                        $stmt_srv->execute();
                        $stmt_srv->close();
                    }
                } else {
                    // Same job position, just update fields
                    $sql_srv = "UPDATE service_records SET 
                        appointment_start_date=?, appointment_end_date=?, 
                        institutions_idinstitutions=?, monthly_salary=?, pay_grade=?, 
                        contract_types_idcontract_types=?, gov_service=?
                        WHERE employees_idemployees=? AND job_positions_idjob_positions=?";
                    $stmt_srv = $conn->prepare($sql_srv);
                    $stmt_srv->bind_param("ssidsiiii", 
                        $start_date, $end_date, 
                        $institution_id, $monthly_salary, $pay_grade, 
                        $contract_type_id, $gov_service, $emp_id, $job_pos_id
                    );
                    $stmt_srv->execute();
                    $stmt_srv->close();
                }
            } else {
                // Insert if no current record exists
                $sql_srv = "INSERT INTO service_records (employees_idemployees, job_positions_idjob_positions, appointment_start_date, appointment_end_date, institutions_idinstitutions, monthly_salary, pay_grade, contract_types_idcontract_types, gov_service) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_srv = $conn->prepare($sql_srv);
                $stmt_srv->bind_param("iissidsii", $emp_id, $job_pos_id, $start_date, $end_date, $institution_id, $monthly_salary, $pay_grade, $contract_type_id, $gov_service);
                $stmt_srv->execute();
                $stmt_srv->close();
            }
        }

        // 3. Family Background
        $conn->query("DELETE FROM employees_relatives WHERE employees_idemployees = $emp_id");
        if (isset($_POST['family_name'])) {
            $sql_fam = "INSERT INTO relatives (first_name, middle_name, last_name, name_extension, Occupation, Emp_business, business_address, telephone, birthdate) VALUES (?, ?, ?, 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '1900-01-01')";
            $stmt_fam = $conn->prepare($sql_fam);
            $sql_link = "INSERT INTO employees_relatives (employees_idemployees, Relatives_idrelatives, relationship) VALUES (?, ?, ?)";
            $stmt_link = $conn->prepare($sql_link);

            foreach ($_POST['family_name'] as $k => $fullname) {
                if (empty($fullname)) continue;
                $parts = explode(',', $fullname);
                $lname = trim($parts[0] ?? '');
                $fname = trim($parts[1] ?? '');
                $mname = trim($parts[2] ?? '');
                
                $stmt_fam->bind_param("sss", $fname, $mname, $lname);
                $stmt_fam->execute();
                $rel_id = $conn->insert_id;
                
                $rel = $_POST['family_relationship'][$k];
                $stmt_link->bind_param("iis", $emp_id, $rel_id, $rel);
                $stmt_link->execute();
            }
            $stmt_fam->close();
            $stmt_link->close();
        }

        // 4. Education
        $conn->query("DELETE FROM employees_education WHERE employees_idemployees = $emp_id");
        if (isset($_POST['educ_school'])) {
            $sql_inst = "INSERT INTO institutions (institution_name) VALUES (?)";
            $stmt_inst = $conn->prepare($sql_inst);
            $sql_educ = "INSERT INTO employees_education (employees_idemployees, institutions_idinstitutions, education_level, Education_degree, start_period, end_period, units_earned, year_graduated, acad_honors, scholarships) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_educ = $conn->prepare($sql_educ);

            foreach ($_POST['educ_school'] as $k => $school) {
                if (empty($school)) continue;
                
                $check = $conn->query("SELECT idinstitutions FROM institutions WHERE institution_name = '$school'");
                if ($check->num_rows > 0) {
                    $inst_id = $check->fetch_assoc()['idinstitutions'];
                } else {
                    $stmt_inst->bind_param("s", $school);
                    $stmt_inst->execute();
                    $inst_id = $conn->insert_id;
                }

                $level = $_POST['educ_level'][$k];
                $degree = $_POST['educ_degree'][$k];
                $from = $_POST['educ_start'][$k];
                $to = $_POST['educ_end'][$k];
                $units = $_POST['educ_units'][$k];
                $grad = $_POST['educ_grad'][$k];
                $honors = $_POST['educ_honors'][$k];
                $scholarship = 'N/A'; // Default value as it's not in the form

                $stmt_educ->bind_param("iissssssss", $emp_id, $inst_id, $level, $degree, $from, $to, $units, $grad, $honors, $scholarship);
                $stmt_educ->execute();
            }
            $stmt_inst->close();
            $stmt_educ->close();
        }

        // 5. Eligibility
        $conn->query("DELETE FROM employees_prof_eligibility WHERE employees_idemployees = $emp_id");
        if (isset($_POST['cse_name'])) {
            $sql_exam = "INSERT INTO professional_exams (Exam_description) VALUES (?)";
            $stmt_exam = $conn->prepare($sql_exam);
            $sql_elig = "INSERT INTO employees_prof_eligibility (employees_idemployees, professional_exams_idprofessional_exams, rating, exam_date, exam_place, license_no, license_validity) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_elig = $conn->prepare($sql_elig);

            foreach ($_POST['cse_name'] as $k => $name) {
                if (empty($name)) continue;

                $check = $conn->query("SELECT idprofessional_exams FROM professional_exams WHERE Exam_description = '$name'");
                if ($check->num_rows > 0) {
                    $exam_id = $check->fetch_assoc()['idprofessional_exams'];
                } else {
                    $stmt_exam->bind_param("s", $name);
                    $stmt_exam->execute();
                    $exam_id = $conn->insert_id;
                }

                $rating = $_POST['cse_rating'][$k];
                $date = !empty($_POST['cse_date'][$k]) ? $_POST['cse_date'][$k] : NULL;
                $place = $_POST['cse_place'][$k];
                $lic = $_POST['cse_license'][$k];
                $val = !empty($_POST['cse_validity'][$k]) ? $_POST['cse_validity'][$k] : NULL;

                $stmt_elig->bind_param("iidssss", $emp_id, $exam_id, $rating, $date, $place, $lic, $val);
                $stmt_elig->execute();
            }
            $stmt_exam->close();
            $stmt_elig->close();
        }

        // 6. Work Experience (Exclude Current)
        $current_job_id_to_keep = null;
        if (isset($job_pos_id)) {
             $current_job_id_to_keep = $job_pos_id;
        } elseif (isset($_POST['original_job_position_id'])) {
             $current_job_id_to_keep = $_POST['original_job_position_id'];
        }

        if (!empty($current_job_id_to_keep)) {
            $conn->query("DELETE FROM service_records WHERE employees_idemployees = $emp_id AND job_positions_idjob_positions != $current_job_id_to_keep");
        } else {
            $conn->query("DELETE FROM service_records WHERE employees_idemployees = $emp_id");
        }

        if (isset($_POST['work_position'])) {
            $sql_inst = "INSERT INTO institutions (institution_name) VALUES (?)";
            $stmt_inst = $conn->prepare($sql_inst);
            $sql_job = "INSERT INTO job_positions (job_category, Job_description) VALUES (?, ?)";
            $stmt_job = $conn->prepare($sql_job);
            $sql_work = "INSERT INTO service_records (employees_idemployees, job_positions_idjob_positions, institutions_idinstitutions, appointment_start_date, appointment_end_date, monthly_salary, pay_grade, gov_service, contract_types_idcontract_types) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
            $stmt_work = $conn->prepare($sql_work);

            foreach ($_POST['work_position'] as $k => $pos) {
                if (empty($pos)) continue;

                $agency = $_POST['work_agency'][$k];
                $check_inst = $conn->query("SELECT idinstitutions FROM institutions WHERE institution_name = '$agency'");
                if ($check_inst->num_rows > 0) {
                    $inst_id = $check_inst->fetch_assoc()['idinstitutions'];
                } else {
                    $stmt_inst->bind_param("s", $agency);
                    $stmt_inst->execute();
                    $inst_id = $conn->insert_id;
                }

                $check_job = $conn->query("SELECT idjob_positions FROM job_positions WHERE job_category = '$pos'");
                if ($check_job->num_rows > 0) {
                    $job_id = $check_job->fetch_assoc()['idjob_positions'];
                } else {
                    $stmt_job->bind_param("ss", $pos, $pos);
                    $stmt_job->execute();
                    $job_id = $conn->insert_id;
                }

                $from = $_POST['work_from'][$k];
                $to = !empty($_POST['work_to'][$k]) ? $_POST['work_to'][$k] : NULL;
                $salary = $_POST['work_salary'][$k];
                $grade = $_POST['work_grade'][$k];
                $gov = $_POST['work_gov'][$k];

                $stmt_work->bind_param("iiissdsi", $emp_id, $job_id, $inst_id, $from, $to, $salary, $grade, $gov);
                $stmt_work->execute();
            }
            $stmt_inst->close();
            $stmt_job->close();
            $stmt_work->close();
        }

        // 7. Voluntary
        $conn->query("DELETE FROM employees_ext_involvements WHERE employees_idemployees = $emp_id");
        if (isset($_POST['vol_org'])) {
            $sql_inst = "INSERT INTO institutions (institution_name) VALUES (?)";
            $stmt_inst = $conn->prepare($sql_inst);
            $sql_vol = "INSERT INTO employees_ext_involvements (employees_idemployees, institutions_idinstitutions, start_date, end_date, no_hours, work_nature, involvement_type) VALUES (?, ?, ?, ?, ?, ?, 'Voluntary Work')";
            $stmt_vol = $conn->prepare($sql_vol);

            foreach ($_POST['vol_org'] as $k => $org) {
                if (empty($org)) continue;

                $check = $conn->query("SELECT idinstitutions FROM institutions WHERE institution_name = '$org'");
                if ($check->num_rows > 0) {
                    $inst_id = $check->fetch_assoc()['idinstitutions'];
                } else {
                    $stmt_inst->bind_param("s", $org);
                    $stmt_inst->execute();
                    $inst_id = $conn->insert_id;
                }

                $from = $_POST['vol_from'][$k];
                $to = $_POST['vol_to'][$k];
                $hours = $_POST['vol_hours'][$k];
                $pos = $_POST['vol_position'][$k];

                $stmt_vol->bind_param("iissis", $emp_id, $inst_id, $from, $to, $hours, $pos);
                $stmt_vol->execute();
            }
            $stmt_inst->close();
            $stmt_vol->close();
        }

        // 8. Training
        $conn->query("DELETE FROM employees_has_trainings WHERE employees_idemployees = $emp_id");
        if (isset($_POST['ld_title'])) {
            $sql_inst = "INSERT INTO institutions (idinstitutions, institution_name) VALUES (?, ?)";
            $stmt_inst = $conn->prepare($sql_inst);
            $sql_train = "INSERT INTO trainings (idtrainings, training_title, start_date, end_date, training_type, institutions_idinstitutions, training_venue) VALUES (?, ?, ?, ?, ?, ?, 'N/A')";
            $stmt_train = $conn->prepare($sql_train);
            $sql_link = "INSERT INTO employees_has_trainings (employees_idemployees, trainings_idtrainings, participation_type) VALUES (?, ?, 'Participant')";
            $stmt_link = $conn->prepare($sql_link);

            foreach ($_POST['ld_title'] as $k => $title) {
                if (empty($title)) continue;

                $sponsor = $_POST['ld_sponsor'][$k];
                $check = $conn->query("SELECT idinstitutions FROM institutions WHERE institution_name = '$sponsor'");
                if ($check->num_rows > 0) {
                    $inst_id = $check->fetch_assoc()['idinstitutions'];
                } else {
                    $new_inst_id = generateId($conn, 'institutions', 'idinstitutions');
                    $stmt_inst->bind_param("is", $new_inst_id, $sponsor);
                    $stmt_inst->execute();
                    $inst_id = $new_inst_id;
                }

                $from = $_POST['ld_from'][$k];
                $to = $_POST['ld_to'][$k];
                $type = $_POST['ld_type'][$k];

                $new_train_id = generateId($conn, 'trainings', 'idtrainings');
                $stmt_train->bind_param("issssi", $new_train_id, $title, $from, $to, $type, $inst_id);
                $stmt_train->execute();
                $train_id = $new_train_id;

                $stmt_link->bind_param("ii", $emp_id, $train_id);
                $stmt_link->execute();
            }
            $stmt_inst->close();
            $stmt_train->close();
            $stmt_link->close();
        }

        // 9. Other Info
        $conn->query("DELETE FROM skills_has_employees WHERE employees_idemployees = $emp_id");
        if (isset($_POST['other_name'])) {
            $sql_skill = "INSERT INTO skills (idskills, skill_description, skill_type) VALUES (?, ?, ?)";
            $stmt_skill = $conn->prepare($sql_skill);
            $sql_link = "INSERT INTO skills_has_employees (skills_idskills, employees_idemployees, skill_level) VALUES (?, ?, 'N/A')";
            $stmt_link = $conn->prepare($sql_link);

            foreach ($_POST['other_name'] as $k => $name) {
                if (empty($name)) continue;
                
                $type = $_POST['other_type'][$k];
                
                $check = $conn->query("SELECT idskills FROM skills WHERE skill_description = '$name' AND skill_type = '$type'");
                if ($check->num_rows > 0) {
                    $skill_id = $check->fetch_assoc()['idskills'];
                } else {
                    $new_skill_id = generateId($conn, 'skills', 'idskills');
                    $stmt_skill->bind_param("iss", $new_skill_id, $name, $type);
                    $stmt_skill->execute();
                    $skill_id = $new_skill_id;
                }

                $stmt_link->bind_param("ii", $skill_id, $emp_id);
                $stmt_link->execute();
            }
            $stmt_skill->close();
            $stmt_link->close();
        }

        // 10. Gov IDs & Refs
        $conn->query("DELETE FROM government_ids WHERE employee_id = $emp_id");
        if (isset($_POST['gov_id_type'])) {
            $sql_gov = "INSERT INTO government_ids (employee_id, id_type, id_number, date_of_issuance, place_of_issuance) VALUES (?, ?, ?, ?, ?)";
            $stmt_gov = $conn->prepare($sql_gov);
            foreach ($_POST['gov_id_type'] as $k => $type) {
                if (empty($type)) continue;
                $no = $_POST['gov_id_no'][$k];
                $date = !empty($_POST['gov_date_issued'][$k]) ? $_POST['gov_date_issued'][$k] : NULL;
                $place = $_POST['gov_place_issued'][$k];
                $stmt_gov->bind_param("issss", $emp_id, $type, $no, $date, $place);
                $stmt_gov->execute();
            }
            $stmt_gov->close();
        }

        $conn->query("DELETE FROM character_references WHERE employee_id = $emp_id");
        if (isset($_POST['ref_name'])) {
            $sql_ref = "INSERT INTO character_references (employee_id, name, address, contact_no) VALUES (?, ?, ?, ?)";
            $stmt_ref = $conn->prepare($sql_ref);
            foreach ($_POST['ref_name'] as $k => $name) {
                if (empty($name)) continue;
                $addr = $_POST['ref_address'][$k];
                $tel = $_POST['ref_tel'][$k];
                $stmt_ref->bind_param("isss", $emp_id, $name, $addr, $tel);
                $stmt_ref->execute();
            }
            $stmt_ref->close();
        }

        $conn->commit();
        unset($_SESSION['form_data']);
        unset($_SESSION['error']);
        header("Location: ../views/employee-list.php?msg=updated");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Update failed: " . $e->getMessage();
        header("Location: ../views/employee-data-edit.php?id=$emp_id");
        exit();
    }
}
?>
