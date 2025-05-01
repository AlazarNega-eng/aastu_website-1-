<?php
session_start();
require_once 'db_connect.php';

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to submit registration.";
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// --- Check if form submitted ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Data Sanitization & Retrieval ---
    $fields = [
        'first_name', 'last_name', 'birth_month', 'birth_day', 'birth_year',
        'gender', 'ethnicity', 'email_address', 'phone_number', 'grade_level', 'semester',
        'res_street_address', 'res_street_address_2', 'res_city', 'res_state_province', 'res_postal_code',
        'res_home_area_code', 'res_home_phone_number',
        'parent_street_address', 'parent_street_address_2', 'parent_city', 'parent_state_province', 'parent_postal_code'
    ];
    $data = [];
    foreach ($fields as $field) {
        // Use filter_input for better security/validation where possible
        // Trim and basic sanitize other fields
        $value = isset($_POST[$field]) ? trim($_POST[$field]) : null;
        $data[$field] = $value !== '' ? htmlspecialchars($value) : null; // Store NULL if empty after trim
    }

    // Handle checkbox separately (value is '1' if checked, not present otherwise)
    $data['parent_diff_address'] = isset($_POST['parent_diff_address_check']) ? 1 : 0;

    // --- Server-Side Validation (Add more as needed) ---
    $errors = [];
    if (empty($data['first_name'])) $errors[] = "First Name is required.";
    if (empty($data['last_name'])) $errors[] = "Last Name is required.";
    if (empty($data['birth_month']) || !filter_var($data['birth_month'], FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>12]])) $errors[] = "Invalid Birth Month.";
    if (empty($data['birth_day']) || !filter_var($data['birth_day'], FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>31]])) $errors[] = "Invalid Birth Day.";
    if (empty($data['birth_year']) || !filter_var($data['birth_year'], FILTER_VALIDATE_INT, ["options" => ["min_range"=>1950, "max_range"=>date('Y')-10]])) $errors[] = "Invalid Birth Year.";
    if (empty($data['gender'])) $errors[] = "Gender is required.";
    if (empty($data['email_address']) || !filter_var($data['email_address'], FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email Address is required.";
    if (empty($data['phone_number'])) $errors[] = "Phone Number is required."; // Add format validation later if needed
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
        // If checkbox not checked, clear parent fields to avoid saving potentially old data
        $data['parent_street_address'] = null;
        $data['parent_street_address_2'] = null;
        $data['parent_city'] = null;
        $data['parent_state_province'] = null;
        $data['parent_postal_code'] = null;
    }

    // --- Process Data if No Errors ---
    if (empty($errors)) {
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
        } else {
             $errors[] = "Database error checking existing profile.";
        }

        if (empty($errors)) { // Re-check errors after DB check
             if ($profile_exists) {
                // --- UPDATE Existing Profile ---
                $sql = "UPDATE student_profiles SET
                            first_name = ?, last_name = ?, birth_month = ?, birth_day = ?, birth_year = ?,
                            gender = ?, ethnicity = ?, email_address = ?, phone_number = ?, grade_level = ?, semester = ?,
                            res_street_address = ?, res_street_address_2 = ?, res_city = ?, res_state_province = ?, res_postal_code = ?,
                            res_home_area_code = ?, res_home_phone_number = ?, parent_diff_address = ?,
                            parent_street_address = ?, parent_street_address_2 = ?, parent_city = ?, parent_state_province = ?, parent_postal_code = ?,
                            registration_submitted_at = NOW()
                        WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                     $stmt->bind_param("ssiiissssssssssssissssssi",
                        $data['first_name'], $data['last_name'], $data['birth_month'], $data['birth_day'], $data['birth_year'],
                        $data['gender'], $data['ethnicity'], $data['email_address'], $data['phone_number'], $data['grade_level'], $data['semester'],
                        $data['res_street_address'], $data['res_street_address_2'], $data['res_city'], $data['res_state_province'], $data['res_postal_code'],
                        $data['res_home_area_code'], $data['res_home_phone_number'], $data['parent_diff_address'],
                        $data['parent_street_address'], $data['parent_street_address_2'], $data['parent_city'], $data['parent_state_province'], $data['parent_postal_code'],
                        $user_id
                    );
                } else {
                    $errors[] = "Database error preparing update.";
                }

            } else {
                // --- INSERT New Profile ---
                 $sql = "INSERT INTO student_profiles (user_id, first_name, last_name, birth_month, birth_day, birth_year,
                            gender, ethnicity, email_address, phone_number, grade_level, semester,
                            res_street_address, res_street_address_2, res_city, res_state_province, res_postal_code,
                            res_home_area_code, res_home_phone_number, parent_diff_address,
                            parent_street_address, parent_street_address_2, parent_city, parent_state_province, parent_postal_code,
                            registration_submitted_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                 $stmt = $conn->prepare($sql);
                  if ($stmt) {
                     $stmt->bind_param("issiiissssssssssssissssss",
                        $user_id, // First parameter
                        $data['first_name'], $data['last_name'], $data['birth_month'], $data['birth_day'], $data['birth_year'],
                        $data['gender'], $data['ethnicity'], $data['email_address'], $data['phone_number'], $data['grade_level'], $data['semester'],
                        $data['res_street_address'], $data['res_street_address_2'], $data['res_city'], $data['res_state_province'], $data['res_postal_code'],
                        $data['res_home_area_code'], $data['res_home_phone_number'], $data['parent_diff_address'],
                        $data['parent_street_address'], $data['parent_street_address_2'], $data['parent_city'], $data['parent_state_province'], $data['parent_postal_code']
                    );
                } else {
                     $errors[] = "Database error preparing insert.";
                }
            }

             // Execute the prepared statement (INSERT or UPDATE)
            if (empty($errors) && $stmt && $stmt->execute()) {
                $_SESSION['success'] = "Registration information submitted successfully!";
                $stmt->close();
                $conn->close();
                header("Location: dashboard.php"); // Redirect to dashboard on success
                exit;
            } else {
                 $errors[] = "Failed to submit registration data. " . ($stmt ? $stmt->error : $conn->error);
                if($stmt) $stmt->close();
            }
        } // End re-check errors
    } // End initial error check

    // --- Handle Errors: Redirect back to form ---
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        $_SESSION['form_data'] = $_POST; // Save submitted data to repopulate form
        $conn->close();
        header("Location: student_registration.php");
        exit;
    }

} else {
    // Not a POST request, redirect to form
    header("Location: student_registration.php");
    exit;
}
?>