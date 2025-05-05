<?php
session_start();
require_once 'db_connect.php'; // Establishes $conn
require_once 'helpers.php';    // Include helpers (contains getValue)

// --- Initial Connection Check ---
if ($conn === false || !($conn instanceof mysqli)) {
    error_log("Process Cost Sharing Error: Database connection failed from db_connect.php.");
    $_SESSION['error'] = "Critical database connection error.";
    header("Location: dashboard.php"); // Redirect dashboard or login
    exit;
}

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to submit this form.";
    $conn->close(); // Close connection before redirect
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];


// --- Check if form submitted ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id_hidden'])) {

    // Verify submitted user ID matches session user ID
    $submitted_user_id = filter_input(INPUT_POST, 'user_id_hidden', FILTER_VALIDATE_INT);
     if (!$submitted_user_id || $submitted_user_id !== $user_id) {
          $_SESSION['error'] = "User mismatch error. Please re-login.";
          $conn->close(); // Close connection before redirect
          header("Location: login.php");
          exit;
     }
    // Use the verified $user_id from the session from now on

    // --- Data Sanitization & Retrieval ---
    // Define ALL fields from the new form that you expect in $_POST
    $fields_text = [
        'full_name', 'identity_no', 'nationality', 'birth_date_str',
        'place_of_birth', // Added this back based on form/DB
        'birth_place_region', 'birth_place_zone', 'birth_place_wereda', 'birth_place_town',
        'birth_place_kebele', 'birth_place_house_no', 'birth_place_po_box', 'birth_place_phone_no',
        'mother_adopter_full_name', 'mother_adopter_region', 'mother_adopter_zone', 'mother_adopter_wereda', 'mother_adopter_city_town',
        'prep_school_name', 'prep_date_completed', 'university_name', 'faculty_school', 'year_of_entrance',
        'department', 'study_year_circle', 'date_of_withdraw', 'withdraw_semester',
        'transfer_source_institute', 'transfer_college_faculty', 'transfer_department', 'date_of_transfer', 'transfer_semester',
        'prev_stay_cost_words', 'advance_payment_date', 'advance_payment_discount', 'advance_payment_receipt_no',
        'beneficiary_signature_date', 'clearance_reason'
    ];
     $fields_numeric = [ 'prev_stay_cost_figures', 'estimated_cost_tuition', 'estimated_cost_food', 'estimated_cost_boarding', 'estimated_cost_total'];
     $fields_radio = ['sex', 'payment_option'];
     $fields_checkbox = ['demand_service_kind', 'demand_service_cash']; // These submit arrays
     $fields_bool_check = ['withdrawn_previously_check', 'transferred_from_other_check']; // These submit '1' if checked

    $data = []; // Array to hold sanitized data for DB

    // Sanitize text fields
    foreach ($fields_text as $field) { $value = isset($_POST[$field]) ? trim($_POST[$field]) : null; $data[$field] = ($value !== null && $value !== '') ? htmlspecialchars($value) : null; }
    // Sanitize numeric fields
     foreach ($fields_numeric as $field) { $value = isset($_POST[$field]) ? trim($_POST[$field]) : null; $data[$field] = ($value !== null && $value !== '' && is_numeric(str_replace(',', '', $value))) ? filter_var(str_replace(',', '', $value), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null; }
     // Sanitize radio buttons
    foreach ($fields_radio as $field) { $value = isset($_POST[$field]) ? trim($_POST[$field]) : null; $data[$field] = ($value !== null && $value !== '') ? htmlspecialchars($value) : null; }
    // Sanitize checkbox groups (store as comma-separated string)
    foreach ($fields_checkbox as $field) { if (isset($_POST[$field]) && is_array($_POST[$field])) { $sanitized_values = array_map('htmlspecialchars', array_map('trim', $_POST[$field])); $data[$field] = implode(',', $sanitized_values); } else { $data[$field] = null; } }
    // Process boolean checkboxes into 0 or 1
    $data['withdrawn_previously'] = isset($_POST['withdrawn_previously_check']) ? 1 : 0;
    $data['transferred_from_other'] = isset($_POST['transferred_from_other_check']) ? 1 : 0;

     // Clear conditional fields if their checkbox is not checked
     if ($data['withdrawn_previously'] == 0) { $data['date_of_withdraw'] = null; $data['withdraw_semester'] = null; }
     if ($data['transferred_from_other'] == 0) { $data['transfer_source_institute'] = null; $data['transfer_college_faculty'] = null; $data['transfer_department'] = null; $data['date_of_transfer'] = null; $data['transfer_semester'] = null; }


    // --- File Upload Handling ---
    $upload_dir = "uploads/cost_sharing_photos/";
    $photo_filename = null; // Final filename to save in DB
    $upload_error = null;
    $old_photo_filename = null; // To potentially delete later

    // Fetch existing filename first (Needs DB connection)
    $stmt_fetch_old_photo = $conn->prepare("SELECT photo_filename FROM cost_sharing_forms WHERE user_id = ?");
    if ($stmt_fetch_old_photo) { /* ... (Fetch logic as before) ... */
        $stmt_fetch_old_photo->bind_param("i", $user_id); $stmt_fetch_old_photo->execute(); $result_old_photo = $stmt_fetch_old_photo->get_result();
        if ($result_old_photo->num_rows > 0) { $old_photo_data = $result_old_photo->fetch_assoc(); $old_photo_filename = $old_photo_data['photo_filename']; }
        $stmt_fetch_old_photo->close(); $photo_filename = $old_photo_filename;
    } else { error_log("Error preparing fetch for old photo filename: " . $conn->error); }

    // Process new upload if provided
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == UPLOAD_ERR_OK) { /* ... (File upload logic as before) ... */
        $tmp_name = $_FILES["profile_photo"]["tmp_name"]; $original_name = basename($_FILES["profile_photo"]["name"]); $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION)); $allowed_types = ['jpg', 'jpeg', 'png'];
        if (in_array($file_extension, $allowed_types)) { $new_photo_filename = "user_" . $user_id . "_costshare_" . time() . "." . $file_extension; $destination = $upload_dir . $new_photo_filename; if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0755, true); }
            if (move_uploaded_file($tmp_name, $destination)) { $photo_filename = $new_photo_filename; } else { $upload_error = "Failed to move uploaded photo."; $photo_filename = $old_photo_filename; error_log($upload_error." Dest: ".$destination); }
        } else { $upload_error = "Invalid photo file type."; $photo_filename = $old_photo_filename; }
    } elseif (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] != UPLOAD_ERR_NO_FILE) { $upload_error = "Photo upload error code: " . $_FILES['profile_photo']['error']; $photo_filename = $old_photo_filename; error_log($upload_error); }
    $data['photo_filename'] = $photo_filename;


    // --- Server-Side Validation ---
    $errors = [];
    if ($upload_error) $errors[] = $upload_error;
    // Add REQUIRED field checks based on the PDF
    if (empty($data['full_name'])) $errors[] = "Full Name is required.";
    if (empty($data['identity_no'])) $errors[] = "Identity Number is required.";
    if (empty($data['sex'])) $errors[] = "Sex is required.";
    if (empty($data['nationality'])) $errors[] = "Nationality is required.";
    if (empty($data['birth_date_str'])) $errors[] = "Date of Birth is required.";
    if (empty($data['mother_adopter_full_name'])) $errors[] = "Mother/Adopter Full Name is required.";
    if (empty($data['prep_school_name'])) $errors[] = "Preparatory School Name is required.";
    if (empty($data['prep_date_completed'])) $errors[] = "Preparatory Completion Date is required.";
    if (empty($data['faculty_school'])) $errors[] = "Faculty/School is required.";
    if (empty($data['year_of_entrance'])) $errors[] = "Year of Entrance is required.";
    if (empty($data['department'])) $errors[] = "Department is required.";
    if (empty($data['study_year_circle'])) $errors[] = "Study Year (I-VI) is required.";
    if (empty($data['payment_option'])) $errors[] = "Payment/Service Option (Section 12) must be selected.";
    if (empty($data['beneficiary_signature_date'])) $errors[] = "Signature Date is required.";
    // Add validation for conditional fields if needed


    // --- Process Data if No Validation Errors ---
    if (empty($errors)) {
        error_log("Validation passed for Cost Sharing form, User ID: " . $user_id);
        $db_operation_failed = false; // Flag for DB errors

        // Check if record exists
        $existing_record = false;
        $stmt_check = $conn->prepare("SELECT id FROM cost_sharing_forms WHERE user_id = ?");
        if($stmt_check){
            $stmt_check->bind_param("i", $user_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) $existing_record = true;
            $stmt_check->close();
             error_log("Existing record check complete. Found: " . ($existing_record ? 'Yes' : 'No'));
        } else {
             $errors[] = "DB Error checking existing record: " . $conn->error;
             error_log("Cost Share Check Prepare Error: ".$conn->error);
             $db_operation_failed = true; // Treat check failure as critical
        }

        // Proceed only if check didn't cause critical errors
        if(empty($errors) && !$db_operation_failed) {
            $sql = "";
            $types = "";
            $params = [];
            $action_type = $existing_record ? "UPDATE" : "INSERT"; // Determine action

            // --- Define columns, types, and params CONSISTENTLY ---
            // Array mapping DB column name => [type_char, data_key_in_$data]
            $db_map = [
                // Page 1
                'full_name' => ['s', 'full_name'], 'identity_no' => ['s', 'identity_no'], 'sex' => ['s', 'sex'], 'nationality' => ['s', 'nationality'], 'birth_date_str' => ['s', 'birth_date_str'],
                'place_of_birth' => ['s', 'place_of_birth'], 'birth_place_region' => ['s', 'birth_place_region'], 'birth_place_zone' => ['s', 'birth_place_zone'], 'birth_place_wereda' => ['s', 'birth_place_wereda'], 'birth_place_town' => ['s', 'birth_place_town'],
                'birth_place_kebele' => ['s', 'birth_place_kebele'], 'birth_place_house_no' => ['s', 'birth_place_house_no'], 'birth_place_po_box' => ['s', 'birth_place_po_box'], 'birth_place_phone_no' => ['s', 'birth_place_phone_no'],
                'mother_adopter_full_name' => ['s', 'mother_adopter_full_name'], 'mother_adopter_region' => ['s', 'mother_adopter_region'], 'mother_adopter_zone' => ['s', 'mother_adopter_zone'], 'mother_adopter_wereda' => ['s', 'mother_adopter_wereda'], 'mother_adopter_city_town' => ['s', 'mother_adopter_city_town'],
                'photo_filename' => ['s', 'photo_filename'],
                // Page 2
                'prep_school_name' => ['s', 'prep_school_name'], 'prep_date_completed' => ['s', 'prep_date_completed'], 'university_name' => ['s', 'university_name'], 'faculty_school' => ['s', 'faculty_school'], 'year_of_entrance' => ['s', 'year_of_entrance'],
                'department' => ['s', 'department'], 'study_year_circle' => ['s', 'study_year_circle'], 'withdrawn_previously' => ['i', 'withdrawn_previously'], 'date_of_withdraw' => ['s', 'date_of_withdraw'], 'withdraw_semester' => ['s', 'withdraw_semester'],
                'transferred_from_other' => ['i', 'transferred_from_other'], 'transfer_source_institute' => ['s', 'transfer_source_institute'],
                // Page 3
                'transfer_college_faculty' => ['s', 'transfer_college_faculty'], 'transfer_department' => ['s', 'transfer_department'], 'date_of_transfer' => ['s', 'date_of_transfer'], 'transfer_semester' => ['s', 'transfer_semester'],
                'prev_stay_cost_figures' => ['d', 'prev_stay_cost_figures'], 'prev_stay_cost_words' => ['s', 'prev_stay_cost_words'], 'demand_service_kind' => ['s', 'demand_service_kind'], 'demand_service_cash' => ['s', 'demand_service_cash'],
                'estimated_cost_tuition' => ['d', 'estimated_cost_tuition'], 'estimated_cost_food' => ['d', 'estimated_cost_food'], 'estimated_cost_boarding' => ['d', 'estimated_cost_boarding'], 'estimated_cost_total' => ['d', 'estimated_cost_total'],
                'advance_payment_date' => ['s', 'advance_payment_date'], 'advance_payment_discount' => ['s', 'advance_payment_discount'], 'advance_payment_receipt_no' => ['s', 'advance_payment_receipt_no'],
                // Page 4
                'payment_option' => ['s', 'payment_option'], 'beneficiary_signature_date' => ['s', 'beneficiary_signature_date'], 'clearance_reason' => ['s', 'clearance_reason']
            ]; // Total 51 columns defined here

            if ($existing_record) {
                // --- Build UPDATE ---
                $set_clauses = [];
                $types = "";
                $params = [];
                foreach ($db_map as $col => $info) {
                    $set_clauses[] = "`" . $col . "` = ?";
                    $types .= $info[0]; // Append type character
                    $params[] = $data[$info[1]]; // Append data value
                }
                $sql = "UPDATE cost_sharing_forms SET " . implode(', ', $set_clauses) . " WHERE user_id = ?";
                $types .= "i"; // Add type for user_id in WHERE clause
                $params[] = $user_id; // Add user_id value for WHERE clause

            } else {
                // --- Build INSERT ---
                $columns = [];
                $placeholders = [];
                $types = "i"; // Start with user_id type
                $params = [$user_id]; // Start with user_id value
                foreach ($db_map as $col => $info) {
                    $columns[] = "`" . $col . "`";
                    $placeholders[] = "?";
                    $types .= $info[0]; // Append type character
                    $params[] = $data[$info[1]]; // Append data value
                }
                $sql = "INSERT INTO cost_sharing_forms (`user_id`, " . implode(', ', $columns) . ") VALUES (?," . implode(', ', $placeholders) . ")";
            }

             error_log("Preparing {$action_type} SQL: " . $sql);
             error_log("Param Types ({$action_type}): " . $types . " (Count: " . strlen($types) . ")");
             error_log("Param Count ({$action_type}): " . count($params));


            $stmt = $conn->prepare($sql);
            if ($stmt) {
                // Check param count matches types length *before* binding
                if (count($params) == strlen($types)) {
                    if ($stmt->bind_param($types, ...$params)) { // Use array unpacking
                         error_log("Executing {$action_type} for Cost Sharing, User ID: " . $user_id);
                        if ($stmt->execute()) {
                            // SUCCESS PATH
                            $_SESSION['success'] = "Cost Sharing Form " . ($existing_record ? "updated" : "submitted") . " successfully!";
                            // Delete old photo ONLY if UPDATE was successful AND a new photo was uploaded
                             if ($action_type === 'UPDATE' && $old_photo_filename && isset($data['photo_filename']) && $old_photo_filename !== $data['photo_filename'] && file_exists($upload_dir . $old_photo_filename)) {
                                 if (!@unlink($upload_dir . $old_photo_filename)) {
                                     error_log("Could not delete old photo: " . $upload_dir . $old_photo_filename);
                                     // Non-critical error, maybe log or notify admin
                                 } else {
                                      error_log("Deleted old photo: " . $upload_dir . $old_photo_filename);
                                 }
                             }
                        } else { $errors[] = "Database execute error ({$action_type}): (" . $stmt->errno . ") " . $stmt->error; $db_operation_failed = true; error_log($errors[count($errors)-1]); }
                    } else { $errors[] = "Database bind param error ({$action_type}): " . $stmt->error; $db_operation_failed = true; error_log($errors[count($errors)-1]); }
                } else {
                    // This error means the logic building the params/types is wrong
                    $errors[] = "Internal Error: Parameter count mismatch ({$action_type}): Types length ".strlen($types).", Params count ".count($params).". Please report this.";
                    error_log($errors[count($errors)-1]); // Log this critical internal error
                    $db_operation_failed = true;
                }
                if($stmt) $stmt->close(); // Close statement
            } else {
                $errors[] = "Database prepare error ({$action_type}): " . $conn->error;
                $db_operation_failed = true;
                error_log($errors[count($errors)-1]);
            }
        } // end re-check after db check
    } // End initial validation check

    // --- Commit/Rollback and Redirect ---
    if (!$db_operation_failed && empty($errors)) {
        // Only commit if no errors occurred during DB operations
        // $conn->commit(); // Uncomment if you use transactions explicitly
        $redirect_page = "dashboard.php"; // Success -> Dashboard
         // Keep $_SESSION['success'] set from above
    } else {
        // If validation errors OR db operation errors occurred
        // $conn->rollback(); // Uncomment if you use transactions explicitly
        $_SESSION['error'] = "Failed to submit form. Please fix errors:<br>" . implode("<br>", $errors);
        $_SESSION['form_data'] = $_POST; // Save data to repopulate
        $redirect_page = "cost_sharing.php"; // Failure -> Back to form
    }

    // Ensure connection is closed before redirecting
    if ($conn instanceof mysqli) $conn->close();
    header("Location: " . $redirect_page);
    exit;

} else {
    // Not a POST request or missing user_id_hidden
    $_SESSION['error'] = "Invalid request.";
    // Close connection if it was opened
    if (isset($conn) && $conn instanceof mysqli) {
       $conn->close();
    }
    header("Location: cost_sharing.php"); // Redirect back
    exit;
}
?> // Optional closing tag