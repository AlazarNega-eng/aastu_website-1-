<?php
session_start();

// --- DIRECT DEBUG OUTPUT - REMOVE LATER ---
echo "--- DEBUG: process_student_reg.php SCRIPT REACHED --- <br>";
echo "Request Method: " . $_SERVER["REQUEST_METHOD"] . "<br>";
echo "POST Data Received:<br><pre>";
print_r($_POST);
echo "</pre>";
// --- END DIRECT DEBUG ---

require_once 'db_connect.php';

// ... rest of the script ...
require_once 'db_connect.php';

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to submit registration.";
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// --- Check connection ---
if ($conn === false || !($conn instanceof mysqli)) {
    error_log("Process Student Reg Error: Database connection failed.");
    $_SESSION['error'] = "Critical database connection error.";
    // Redirect? Where? Maybe back to dashboard if possible?
    header("Location: dashboard.php"); // Or login.php if session might be invalid
    exit;
}


// --- Check if form submitted ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    error_log("Processing student registration form for user ID: " . $user_id); // Log start

    // --- Data Sanitization & Retrieval ---
    // (Keep the same field list and sanitization as before)
     $fields = [
        'first_name', 'last_name', 'birth_month', 'birth_day', 'birth_year',
        'gender', 'ethnicity', 'email_address', 'phone_number', 'grade_level', 'semester',
        'res_street_address', 'res_street_address_2', 'res_city', 'res_state_province', 'res_postal_code',
        'res_home_area_code', 'res_home_phone_number',
        'parent_street_address', 'parent_street_address_2', 'parent_city', 'parent_state_province', 'parent_postal_code'
    ];
    $data = [];
    foreach ($fields as $field) {
        $value = isset($_POST[$field]) ? trim($_POST[$field]) : null;
        // Store NULL explicitly if empty or only whitespace after trimming
        $data[$field] = ($value !== null && $value !== '') ? htmlspecialchars($value) : null;
    }
    $data['parent_diff_address'] = isset($_POST['parent_diff_address_check']) ? 1 : 0;


    // --- Server-Side Validation ---
    $errors = [];
    // (Keep the same validation checks as before)
    if (empty($data['first_name'])) $errors[] = "First Name is required.";
    if (empty($data['last_name'])) $errors[] = "Last Name is required.";
    if ($data['birth_month'] === null || !filter_var($data['birth_month'], FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>12]])) $errors[] = "Valid Birth Month (1-12) is required.";
    if ($data['birth_day'] === null || !filter_var($data['birth_day'], FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>31]])) $errors[] = "Valid Birth Day (1-31) is required.";
    if ($data['birth_year'] === null || !filter_var($data['birth_year'], FILTER_VALIDATE_INT, ["options" => ["min_range"=>1950, "max_range"=>date('Y')-10]])) $errors[] = "Valid Birth Year (e.g., 1990-" . (date('Y')-10) . ") is required.";
    if (empty($data['gender'])) $errors[] = "Gender is required.";
    if (empty($data['email_address']) || !filter_var($data['email_address'], FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email Address is required.";
    if (empty($data['phone_number'])) $errors[] = "Phone Number is required.";
    if (empty($data['grade_level'])) $errors[] = "Grade/Year is required.";
    if (empty($data['semester'])) $errors[] = "Semester is required.";
    if (empty($data['res_street_address'])) $errors[] = "Street Address is required.";
    if (empty($data['res_city'])) $errors[] = "City is required.";
    if (empty($data['res_state_province'])) $errors[] = "State/Province is required.";
    if (empty($data['res_postal_code'])) $errors[] = "Postal/Zip Code is required.";

    // Validate parent address ONLY if the checkbox was checked
    if ($data['parent_diff_address'] == 1) {
        if (empty($data['parent_street_address'])) $errors[] = "Parent Street Address is required when different.";
        if (empty($data['parent_city'])) $errors[] = "Parent City is required when different.";
        if (empty($data['parent_state_province'])) $errors[] = "Parent State/Province is required when different.";
        if (empty($data['parent_postal_code'])) $errors[] = "Parent Postal Code is required when different.";
    } else {
        // If checkbox not checked, ensure parent fields are NULL for DB
        $data['parent_street_address'] = null;
        $data['parent_street_address_2'] = null;
        $data['parent_city'] = null;
        $data['parent_state_province'] = null;
        $data['parent_postal_code'] = null;
    }

    // --- If Validation Errors Found ---
    if (!empty($errors)) {
        error_log("Validation errors for user ID $user_id: " . implode(", ", $errors)); // Log validation errors
        $_SESSION['error'] = "Please fix the following errors:<br>" . implode("<br>", $errors);
        $_SESSION['form_data'] = $_POST; // Save submitted data to repopulate form
        $conn->close();
        header("Location: student_registration.php");
        exit;
    }

    // --- Process Data if No Validation Errors ---
    error_log("Validation passed for user ID: " . $user_id); // Log validation success

    // Check if user already has a profile (for INSERT vs UPDATE)
    $profile_exists = false;
    $stmt_check = $conn->prepare("SELECT id FROM student_profiles WHERE user_id = ?");
    if ($stmt_check) {
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $profile_exists = true;
        }
        $stmt_check->close();
        error_log("Profile exists check for user ID $user_id result: " . ($profile_exists ? 'TRUE' : 'FALSE')); // Log check result
    } else {
         // Critical error, stop processing
         error_log("Database error preparing profile check for user ID $user_id: " . $conn->error);
         $_SESSION['error'] = "Database error checking existing profile.";
         $conn->close();
         header("Location: student_registration.php");
         exit;
    }


    // --- Prepare SQL and Bind Parameters (INSERT or UPDATE) ---
    $stmt = null; // Initialize statement variable
    $sql_error = false; // Flag for SQL errors

    if ($profile_exists) {
        // --- UPDATE Existing Profile ---
        error_log("Preparing UPDATE statement for user ID: " . $user_id);
        $sql = "UPDATE student_profiles SET
                    first_name = ?, last_name = ?, birth_month = ?, birth_day = ?, birth_year = ?,
                    gender = ?, ethnicity = ?, email_address = ?, phone_number = ?, grade_level = ?, semester = ?,
                    res_street_address = ?, res_street_address_2 = ?, res_city = ?, res_state_province = ?, res_postal_code = ?,
                    res_home_area_code = ?, res_home_phone_number = ?, parent_diff_address = ?,
                    parent_street_address = ?, parent_street_address_2 = ?, parent_city = ?, parent_state_province = ?, parent_postal_code = ?,
                    registration_submitted_at = NOW() /* Optionally update submission time? */
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
             // Type string: ssiisssssssssssssissssssi (25 types for 25 variables)
             if (!$stmt->bind_param("ssiiissssssssssssissssssi",
                $data['first_name'], $data['last_name'], $data['birth_month'], $data['birth_day'], $data['birth_year'],
                $data['gender'], $data['ethnicity'], $data['email_address'], $data['phone_number'], $data['grade_level'], $data['semester'],
                $data['res_street_address'], $data['res_street_address_2'], $data['res_city'], $data['res_state_province'], $data['res_postal_code'],
                $data['res_home_area_code'], $data['res_home_phone_number'], $data['parent_diff_address'],
                $data['parent_street_address'], $data['parent_street_address_2'], $data['parent_city'], $data['parent_state_province'], $data['parent_postal_code'],
                $user_id // WHERE condition
            )) {
                 error_log("UPDATE bind_param failed for user ID $user_id: " . $stmt->error);
                 $_SESSION['error'] = "Error preparing data for update.";
                 $sql_error = true;
            };
        } else {
            error_log("UPDATE prepare failed for user ID $user_id: " . $conn->error);
            $_SESSION['error'] = "Database error preparing update.";
            $sql_error = true;
        }

    } else {
        // --- INSERT New Profile ---
        error_log("Preparing INSERT statement for user ID: " . $user_id);
        // Ensure all columns listed match the number of placeholders and bind variables
         $sql = "INSERT INTO student_profiles (user_id, first_name, last_name, birth_month, birth_day, birth_year,
                    gender, ethnicity, email_address, phone_number, grade_level, semester,
                    res_street_address, res_street_address_2, res_city, res_state_province, res_postal_code,
                    res_home_area_code, res_home_phone_number, parent_diff_address,
                    parent_street_address, parent_street_address_2, parent_city, parent_state_province, parent_postal_code,
                    registration_submitted_at, dept_approval_status) /* Added default pending status */
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')"; // 26 placeholders
         $stmt = $conn->prepare($sql);
          if ($stmt) {
             // Type string: issiiissssssssssssissssss (25 types for 25 variables)
             if (!$stmt->bind_param("issiiissssssssssssissssss",
                $user_id, // 1st variable
                $data['first_name'], $data['last_name'], $data['birth_month'], $data['birth_day'], $data['birth_year'],
                $data['gender'], $data['ethnicity'], $data['email_address'], $data['phone_number'], $data['grade_level'], $data['semester'],
                $data['res_street_address'], $data['res_street_address_2'], $data['res_city'], $data['res_state_province'], $data['res_postal_code'],
                $data['res_home_area_code'], $data['res_home_phone_number'], $data['parent_diff_address'],
                $data['parent_street_address'], $data['parent_street_address_2'], $data['parent_city'], $data['parent_state_province'], $data['parent_postal_code']
                // Note: registration_submitted_at and dept_approval_status are handled directly in SQL
            )) {
                 error_log("INSERT bind_param failed for user ID $user_id: " . $stmt->error);
                 $_SESSION['error'] = "Error preparing data for submission.";
                 $sql_error = true;
            };
        } else {
             error_log("INSERT prepare failed for user ID $user_id: " . $conn->error);
             $_SESSION['error'] = "Database error preparing submission.";
             $sql_error = true;
        }
    }

     // --- Execute the prepared statement (INSERT or UPDATE) ---
     $execution_success = false;
     if (!$sql_error && $stmt) {
        error_log("Executing " . ($profile_exists ? "UPDATE" : "INSERT") . " for user ID: " . $user_id);
        if ($stmt->execute()) {
            error_log("Execution successful for user ID: " . $user_id);
            $execution_success = true;
            $_SESSION['success'] = "Registration information submitted successfully!";
        } else {
            error_log("Execution failed for user ID $user_id: (" . $stmt->errno . ") " . $stmt->error);
            $_SESSION['error'] = "Failed to submit registration data. Please check your inputs or contact support. Error: " . $stmt->errno; // Provide code maybe
            $sql_error = true; // Mark as SQL error even if prepare/bind worked
        }
        $stmt->close(); // Close statement after execution attempt
     }

    // --- Redirect based on success or failure ---
    $conn->close(); // Close connection before redirecting

    if ($execution_success) {
        // Clear any potential lingering error/form data from previous attempts
        unset($_SESSION['error']);
        unset($_SESSION['form_data']);
        header("Location: dashboard.php"); // Redirect to dashboard on success
        exit;
    } else {
        // SQL error occurred OR prepare/bind failed before execution
        $_SESSION['form_data'] = $_POST; // Save submitted data to repopulate form
        header("Location: student_registration.php"); // Redirect back to registration page
        exit;
    }

} else {
    // Not a POST request, redirect to form
    error_log("Non-POST request to process_student_reg.php");
    if (isset($conn) && $conn instanceof mysqli) $conn->close(); // Close connection if open
    header("Location: student_registration.php");
    exit;
}
?>