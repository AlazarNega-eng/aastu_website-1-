<?php
session_start();
require_once 'db_connect.php'; // Establishes $conn
require_once 'helpers.php';    // Include helpers (contains getValue)

// --- Initial Connection Check ---
if ($conn === false || !($conn instanceof mysqli)) {
    error_log("Process Cost Sharing Error: Database connection failed.");
    $_SESSION['error'] = "Critical database connection error.";
    header("Location: dashboard.php");
    exit;
}

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to submit this form.";
    $conn->close();
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
          $conn->close();
          header("Location: login.php");
          exit;
     }

    // --- Data Sanitization & Retrieval ---
    $fields_text = [
        'full_name', 'identity_no', 'nationality', 'birth_date_str',
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
     $fields_checkbox = ['demand_service_kind', 'demand_service_cash'];
     $fields_bool_check = ['withdrawn_previously_check', 'transferred_from_other_check'];

    $data = [];
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
     if ($data['withdrawn_previously'] == 0) {
         $data['date_of_withdraw'] = null;
         $data['withdraw_semester'] = null;
     }
    if ($data['transferred_from_other'] == 0) {
         $data['transfer_source_institute'] = null;
         $data['transfer_college_faculty'] = null;
         $data['transfer_department'] = null;
         $data['date_of_transfer'] = null;
         $data['transfer_semester'] = null;
    }


    // --- File Upload Handling ---
    $upload_dir = "uploads/cost_sharing_photos/";
    $photo_filename = null; // Final filename to save in DB
    $upload_error = null;
    $old_photo_filename = null;

    // Fetch existing filename first
    $stmt_fetch_old_photo = $conn->prepare("SELECT photo_filename FROM cost_sharing_forms WHERE user_id = ?");
    if ($stmt_fetch_old_photo) {
        $stmt_fetch_old_photo->bind_param("i", $user_id);
        $stmt_fetch_old_photo->execute();
        $result_old_photo = $stmt_fetch_old_photo->get_result();
        if ($result_old_photo->num_rows > 0) {
            $old_photo_data = $result_old_photo->fetch_assoc();
            $old_photo_filename = $old_photo_data['photo_filename'];
        }
        $stmt_fetch_old_photo->close();
        $photo_filename = $old_photo_filename; // Default to old one
    } else {
        error_log("Error preparing fetch for old photo filename: " . $conn->error);
        // Decide if this is critical - perhaps not, just can't delete old one
    }

    // Process new upload if provided
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES["profile_photo"]["tmp_name"];
        $original_name = basename($_FILES["profile_photo"]["name"]);
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png'];

        if (in_array($file_extension, $allowed_types)) {
            $new_photo_filename = "user_" . $user_id . "_costshare_" . time() . "." . $file_extension;
            $destination = $upload_dir . $new_photo_filename;
            if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0755, true); } // Attempt to create dir

            if (move_uploaded_file($tmp_name, $destination)) {
                $photo_filename = $new_photo_filename; // Use the new filename
                // Don't delete the old file yet, wait until DB update is successful
            } else { $upload_error = "Failed to move uploaded photo to destination."; error_log($upload_error); $photo_filename = $old_photo_filename; }
        } else { $upload_error = "Invalid photo file type. Only JPG, JPEG, PNG allowed."; $photo_filename = $old_photo_filename; }
    } elseif (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_error = "Photo upload error code: " . $_FILES['profile_photo']['error']; $photo_filename = $old_photo_filename; error_log($upload_error);
    }
    $data['photo_filename'] = $photo_filename; // Store final name


    // --- Server-Side Validation ---
    $errors = [];
    if ($upload_error) $errors[] = $upload_error;
    // Add REQUIRED field checks based on the PDF (many fields seem required)
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
    if (empty($data['payment_option'])) $errors[] = "Payment/Service Option (Section 12) is required.";
    if (empty($data['beneficiary_signature_date'])) $errors[] = "Signature Date is required.";
    // Add more...

    // --- Process Data if No Validation Errors ---
    if (empty($errors)) {
        error_log("Validation passed for Cost Sharing form, User ID: " . $user_id);
        $db_operation_failed = false; // Flag for DB errors

        // Check if record exists (need connection open)
        $existing_record = false;
        $stmt_check = $conn->prepare("SELECT id FROM cost_sharing_forms WHERE user_id = ?");
        if($stmt_check){ /* ... check logic as before ... */
            $stmt_check->bind_param("i", $user_id); $stmt_check->execute(); $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) $existing_record = true; $stmt_check->close();
        } else { $errors[] = "DB Error checking existing record: " . $conn->error; $db_operation_failed = true; }

        if(empty($errors)) {
            $sql = ""; $types = ""; $params = [];
            $action_type = $existing_record ? "UPDATE" : "INSERT";

            if ($existing_record) {
                 // UPDATE Query and Parameters (Ensure order matches types and table structure)
                 // List all columns except id, user_id (which is in WHERE), submitted_at, updated_at
                 $sql = "UPDATE cost_sharing_forms SET full_name=?, identity_no=?, sex=?, nationality=?, birth_date_str=?, place_of_birth=?, birth_place_region=?, birth_place_zone=?, birth_place_wereda=?, birth_place_town=?, birth_place_kebele=?, birth_place_house_no=?, birth_place_po_box=?, birth_place_phone_no=?, mother_adopter_full_name=?, mother_adopter_region=?, mother_adopter_zone=?, mother_adopter_wereda=?, mother_adopter_city_town=?, photo_filename=?, prep_school_name=?, prep_date_completed=?, university_name=?, faculty_school=?, year_of_entrance=?, department=?, study_year_circle=?, withdrawn_previously=?, date_of_withdraw=?, withdraw_semester=?, transferred_from_other=?, transfer_source_institute=?, transfer_college_faculty=?, transfer_department=?, date_of_transfer=?, transfer_semester=?, prev_stay_cost_figures=?, prev_stay_cost_words=?, demand_service_kind=?, demand_service_cash=?, estimated_cost_tuition=?, estimated_cost_food=?, estimated_cost_boarding=?, estimated_cost_total=?, advance_payment_date=?, advance_payment_discount=?, advance_payment_receipt_no=?, payment_option=?, beneficiary_signature_date=?, clearance_reason=? WHERE user_id=?"; // 50 SET + 1 WHERE
                 $types = "ssssssssssssssssssssssssssisssissssssdssssddddssssssssi"; // Recalculate carefully! 50 types + 1 int
                 $params = [ $data['full_name'], $data['identity_no'], $data['sex'], $data['nationality'], $data['birth_date_str'], $data['place_of_birth'] ?? null, $data['birth_place_region'], $data['birth_place_zone'], $data['birth_place_wereda'], $data['birth_place_town'], $data['birth_place_kebele'], $data['birth_place_house_no'], $data['birth_place_po_box'], $data['birth_place_phone_no'], $data['mother_adopter_full_name'], $data['mother_adopter_region'], $data['mother_adopter_zone'], $data['mother_adopter_wereda'], $data['mother_adopter_city_town'], $data['photo_filename'], $data['prep_school_name'], $data['prep_date_completed'], $data['university_name'], $data['faculty_school'], $data['year_of_entrance'], $data['department'], $data['study_year_circle'], $data['withdrawn_previously'], $data['date_of_withdraw'], $data['withdraw_semester'], $data['transferred_from_other'], $data['transfer_source_institute'], $data['transfer_college_faculty'], $data['transfer_department'], $data['date_of_transfer'], $data['transfer_semester'], $data['prev_stay_cost_figures'], $data['prev_stay_cost_words'], $data['demand_service_kind'], $data['demand_service_cash'], $data['estimated_cost_tuition'], $data['estimated_cost_food'], $data['estimated_cost_boarding'], $data['estimated_cost_total'], $data['advance_payment_date'], $data['advance_payment_discount'], $data['advance_payment_receipt_no'], $data['payment_option'], $data['beneficiary_signature_date'], $data['clearance_reason'], $user_id ];
            } else {
                 // INSERT Query and Parameters (Ensure order matches types and table structure)
                  // List all columns except id, submitted_at, updated_at
                 $sql = "INSERT INTO cost_sharing_forms (user_id, full_name, identity_no, sex, nationality, birth_date_str, place_of_birth, birth_place_region, birth_place_zone, birth_place_wereda, birth_place_town, birth_place_kebele, birth_place_house_no, birth_place_po_box, birth_place_phone_no, mother_adopter_full_name, mother_adopter_region, mother_adopter_zone, mother_adopter_wereda, mother_adopter_city_town, photo_filename, prep_school_name, prep_date_completed, university_name, faculty_school, year_of_entrance, department, study_year_circle, withdrawn_previously, date_of_withdraw, withdraw_semester, transferred_from_other, transfer_source_institute, transfer_college_faculty, transfer_department, date_of_transfer, transfer_semester, prev_stay_cost_figures, prev_stay_cost_words, demand_service_kind, demand_service_cash, estimated_cost_tuition, estimated_cost_food, estimated_cost_boarding, estimated_cost_total, advance_payment_date, advance_payment_discount, advance_payment_receipt_no, payment_option, beneficiary_signature_date, clearance_reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 51 placeholders
                 $types = "issssssssssssssssssssssssssisssissssssdssssddddssssssss"; // Recalculate carefully! 1 int + 50 others
                 $params = [ $user_id, $data['full_name'], $data['identity_no'], $data['sex'], $data['nationality'], $data['birth_date_str'], $data['place_of_birth'] ?? null, $data['birth_place_region'], $data['birth_place_zone'], $data['birth_place_wereda'], $data['birth_place_town'], $data['birth_place_kebele'], $data['birth_place_house_no'], $data['birth_place_po_box'], $data['birth_place_phone_no'], $data['mother_adopter_full_name'], $data['mother_adopter_region'], $data['mother_adopter_zone'], $data['mother_adopter_wereda'], $data['mother_adopter_city_town'], $data['photo_filename'], $data['prep_school_name'], $data['prep_date_completed'], $data['university_name'], $data['faculty_school'], $data['year_of_entrance'], $data['department'], $data['study_year_circle'], $data['withdrawn_previously'], $data['date_of_withdraw'], $data['withdraw_semester'], $data['transferred_from_other'], $data['transfer_source_institute'], $data['transfer_college_faculty'], $data['transfer_department'], $data['date_of_transfer'], $data['transfer_semester'], $data['prev_stay_cost_figures'], $data['prev_stay_cost_words'], $data['demand_service_kind'], $data['demand_service_cash'], $data['estimated_cost_tuition'], $data['estimated_cost_food'], $data['estimated_cost_boarding'], $data['estimated_cost_total'], $data['advance_payment_date'], $data['advance_payment_discount'], $data['advance_payment_receipt_no'], $data['payment_option'], $data['beneficiary_signature_date'], $data['clearance_reason'] ];
            }

            $stmt = $conn->prepare($sql);
            if ($stmt) {
                // Check param count matches types length
                if (count($params) == strlen($types)) {
                    if ($stmt->bind_param($types, ...$params)) {
                        if ($stmt->execute()) {
                             $_SESSION['success'] = "Cost Sharing Form " . ($existing_record ? "updated" : "submitted") . " successfully!";
                             // Delete old photo if UPDATE was successful AND a new photo was uploaded
                             if ($action_type === 'UPDATE' && $old_photo_filename && $old_photo_filename !== $data['photo_filename'] && file_exists($upload_dir . $old_photo_filename)) {
                                 @unlink($upload_dir . $old_photo_filename); // Attempt to delete old file
                             }
                        } else { $errors[] = "DB execute error ({$action_type}): " . $stmt->error; $db_operation_failed = true; }
                    } else { $errors[] = "DB bind param error ({$action_type}): " . $stmt->error; $db_operation_failed = true; }
                } else { $errors[] = "Parameter count mismatch ({$action_type}): Expected ".strlen($types).", got ".count($params); $db_operation_failed = true;}
                 $stmt->close();
            } else { $errors[] = "DB prepare error ({$action_type}): " . $conn->error; $db_operation_failed = true; }
        } // end re-check errors
    } // End initial validation check

    // --- Handle Errors: Redirect back to form ---
    if (!empty($errors)) {
        $_SESSION['error'] = "Failed to submit form. Please fix errors:<br>" . implode("<br>", $errors);
        $_SESSION['form_data'] = $_POST; // Save data to repopulate
        // Rollback transaction if started and failed
        // $conn->rollback(); // Need to manage transaction state carefully
    }

    if ($conn instanceof mysqli) $conn->close(); // Close connection before redirecting
    header("Location: cost_sharing.php"); // Redirect back to cost sharing form
    exit;

} else {
    // Not a POST request or missing user_id_hidden
    $_SESSION['error'] = "Invalid request.";
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
    header("Location: cost_sharing.php");
    exit;
}
?>