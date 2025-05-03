<?php
session_start();
require_once 'db_connect.php'; // Establishes $conn

// --- Authentication & Role Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'dept_head') {
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

// --- Check connection first ---
if ($conn === false || !($conn instanceof mysqli)) {
    error_log("Process Grades Error: Database connection failed or invalid in db_connect.php.");
    $_SESSION['error'] = "Critical database connection error.";
    // Redirect back to main dashboard maybe?
    header("Location: dept_dashboard.php");
    exit;
}


// --- Check if form submitted and essential data arrays exist ---
if ($_SERVER["REQUEST_METHOD"] == "POST"
    && isset($_POST['student_user_id']) // Essential hidden field
    && isset($_POST['course_code']) && is_array($_POST['course_code'])
    && isset($_POST['course_title']) && is_array($_POST['course_title'])
    && isset($_POST['semester']) && is_array($_POST['semester'])
    && isset($_POST['academic_year']) && is_array($_POST['academic_year'])
    && isset($_POST['credit_hours']) && is_array($_POST['credit_hours'])
    && isset($_POST['grade_letter']) && is_array($_POST['grade_letter'])
) {

    // --- Validate Submitted Student ID ---
    $student_user_id = filter_input(INPUT_POST, 'student_user_id', FILTER_VALIDATE_INT);
    if (!$student_user_id) {
        $_SESSION['error'] = "Invalid student ID submitted with grades.";
        $conn->close(); // Close connection before redirect
        header("Location: dept_manage_grades.php"); // Go back to list
        exit;
    }

    // Get arrays
    $course_codes = $_POST['course_code'];
    $course_titles = $_POST['course_title'];
    $semesters = $_POST['semester'];
    $academic_years = $_POST['academic_year'];
    $credit_hours_list = $_POST['credit_hours'];
    $grade_letters = $_POST['grade_letter'];

    // Basic Check: Ensure all arrays have the same count
    $count = count($course_codes);
    if ($count === 0 || count($course_titles) != $count || count($semesters) != $count || count($academic_years) != $count || count($credit_hours_list) != $count || count($grade_letters) != $count) {
         $_SESSION['error'] = "Form data mismatch or no grade rows submitted. Please try again.";
         $conn->close();
         header("Location: dept_enter_grades.php?user_id=" . $student_user_id); // Back to entry page
         exit;
    }

    // Define Grade Points Mapping (Adjust as per AASTU's scale)
    $grade_point_map = [
        'A+' => 4.00, 'A' => 4.00, 'A-' => 3.75,
        'B+' => 3.50, 'B' => 3.00, 'B-' => 2.75,
        'C+' => 2.50, 'C' => 2.00, 'C-' => 1.75,
        'D' => 1.00,
        'F' => 0.00,
        'P' => null, // Pass grades might not have points affecting GPA
        'NG' => null, // No Grade
        '' => null    // Handle empty selection explicitly
    ];

    $errors = [];
    $row_errors = []; // To store specific row errors
    $success_count = 0;
    $skipped_count = 0;
    $processed_rows = 0;

    // --- Prepare Statements (Prepare ONCE outside the loop) ---
    $conn->begin_transaction(); // Start transaction for atomicity
    $insert_failed = false;
    $prepare_failed = false;

    // Prepare statement for checking duplicates
    $sql_check = "SELECT id FROM student_grades WHERE user_id = ? AND course_code = ? AND semester = ? AND academic_year = ?";
    $stmt_check = $conn->prepare($sql_check);
    if (!$stmt_check) {
        $prepare_failed = true;
        $errors[] = "Database error preparing duplicate check: " . $conn->error;
        error_log("Grade Check Prepare Error: ".$conn->error);
    }

    // Prepare statement for inserting grades
    $sql_insert = "INSERT INTO student_grades (user_id, course_code, course_title, semester, academic_year, credit_hours, grade_letter, grade_points)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
     if (!$stmt_insert) {
        $prepare_failed = true;
        $errors[] = "Database error preparing insert: " . $conn->error;
        error_log("Grade Insert Prepare Error: ".$conn->error);
    }

    // --- Loop through submitted rows ONLY if prepares succeeded ---
    if (!$prepare_failed) {
        for ($i = 0; $i < $count; $i++) {
            $processed_rows++;
            $row_num = $i + 1; // For user-friendly error messages

            // Sanitize and Validate individual row data
            $course_code = isset($course_codes[$i]) ? trim(strtoupper(htmlspecialchars($course_codes[$i]))) : '';
            $course_title = isset($course_titles[$i]) ? trim(htmlspecialchars($course_titles[$i])) : '';
            $semester = isset($semesters[$i]) ? trim(htmlspecialchars($semesters[$i])) : '';
            $academic_year = isset($academic_years[$i]) ? trim(htmlspecialchars($academic_years[$i])) : '';
            $credit_hours_input = $credit_hours_list[$i] ?? null;
            $grade_letter = isset($grade_letters[$i]) ? trim(strtoupper(htmlspecialchars($grade_letters[$i]))) : '';

            // --- Row Level Validation ---
            $current_row_errors = [];
            if (empty($course_code)) { $current_row_errors[] = "Course Code required."; }
            if (empty($course_title)) { $current_row_errors[] = "Course Title required."; }
            if (empty($semester)) { $current_row_errors[] = "Semester required."; }
            if (empty($academic_year)) { $current_row_errors[] = "Academic Year required."; } // Add format check?

            $credit_hours = filter_var($credit_hours_input, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]); // Allow 0 credits?
            if ($credit_hours === false) { $current_row_errors[] = "Invalid Credit Hours value."; }

            // Validate grade letter against map keys
            if (!array_key_exists($grade_letter, $grade_point_map)) {
                 $current_row_errors[] = "Invalid Grade Letter selected.";
            }

            // If validation for this row failed, add errors and skip to next row
            if (!empty($current_row_errors)) {
                $row_errors[$row_num] = $current_row_errors;
                $skipped_count++; // Count validation skips
                continue; // Go to the next iteration of the loop
            }

            // Calculate grade points (if validation passed)
            $grade_points = $grade_point_map[$grade_letter]; // Use map, will be null if P, NG, ''

            // --- Check for Duplicates (if validation passed) ---
            $is_duplicate = false;
            $stmt_check->bind_param("isss", $student_user_id, $course_code, $semester, $academic_year);
            if($stmt_check->execute()){
                $result_check = $stmt_check->get_result();
                if ($result_check->num_rows > 0) {
                    $is_duplicate = true;
                    $skipped_count++;
                    $row_errors[$row_num] = ["Grade for '$course_code' in '$semester' ($academic_year) already exists. Skipped."];
                }
            } else {
                 $errors[] = "Row $row_num: Database error checking duplicates: " . $stmt_check->error;
                 $insert_failed = true; // Treat check error as critical
                 break; // Stop processing
            }


             // --- Insert if Valid and Not Duplicate ---
            if (!$is_duplicate) {
                // Type string: "issssdsd" (8 placeholders)
                if (!$stmt_insert->bind_param("issssdsd",
                    $student_user_id,
                    $course_code,
                    $course_title,
                    $semester,
                    $academic_year,
                    $credit_hours,
                    $grade_letter,
                    $grade_points // Bind the calculated points (can be null)
                )) {
                    $errors[] = "Row $row_num: Failed to bind parameters for '$course_code'. Error: " . $stmt_insert->error;
                    $insert_failed = true;
                    break;
                }

                if (!$stmt_insert->execute()) {
                    // Check for specific duplicate key error just in case check failed somehow
                    if ($stmt_insert->errno == 1062) { // 1062 is MySQL duplicate entry error code
                         $skipped_count++;
                         $row_errors[$row_num] = ["Grade for '$course_code' in '$semester' ($academic_year) already exists (detected on insert). Skipped."];
                    } else {
                        $errors[] = "Row $row_num: Failed to insert grade for '$course_code'. Error (" . $stmt_insert->errno . "): " . $stmt_insert->error;
                        $insert_failed = true;
                        break; // Stop processing on first critical insert error
                    }
                } else {
                    $success_count++;
                }
            } // end !is_duplicate check

        } // End loop
    } // End !$prepare_failed check

    // --- Close Prepared Statements ---
    if ($stmt_check instanceof mysqli_stmt) $stmt_check->close();
    if ($stmt_insert instanceof mysqli_stmt) $stmt_insert->close();

    // --- Commit or Rollback Transaction ---
    if ($insert_failed || $prepare_failed) {
        $conn->rollback();
        // Add specific row errors to the main error message
        $error_message = "Grade submission failed due to critical errors.<br>" . implode("<br>", $errors);
         if (!empty($row_errors)) {
             $error_message .= "<br><br>Specific row issues:<br>";
             foreach ($row_errors as $num => $errs) {
                 $error_message .= "Row $num: " . implode(", ", $errs) . "<br>";
             }
         }
        $_SESSION['error'] = $error_message;

    } else {
        $conn->commit();
        $success_message = "$success_count grade(s) successfully recorded.";
        if ($skipped_count > 0) {
             $success_message .= " $skipped_count row(s) were skipped due to validation errors or duplicates.";
        }
         $_SESSION['success'] = $success_message;
        // Optionally add row-specific non-critical errors (like duplicates) to success message
         if (!empty($row_errors)) {
             $_SESSION['error'] = "Note: Some rows had issues:<br>"; // Use error session for non-critical row issues
             foreach ($row_errors as $num => $errs) {
                 $_SESSION['error'] .= "Row $num: " . implode(", ", $errs) . "<br>";
             }
         }
    }

    $conn->close();
    // Redirect back to the entry page for that student
    header("Location: dept_enter_grades.php?user_id=" . $student_user_id);
    exit;

} else {
    // Not a POST request or missing essential array data
    $_SESSION['error'] = "Invalid request method or missing required grade data arrays.";
    // Close connection if it was opened
    if (isset($conn) && $conn instanceof mysqli) {
       $conn->close();
    }
    // Redirect back to the main grade management page if no specific student ID is known
    header("Location: dept_manage_grades.php");
    exit;
}
?>