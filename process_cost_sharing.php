<?php
session_start();
require_once 'db_connect.php';

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to submit this form.";
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// --- Check if form submitted ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Data Sanitization & Retrieval ---
    $fields = [
        'full_name', 'grandfather_name', 'id_number', 'tin_number',
        'birth_year_ec', 'place_of_birth', 'faculty', 'department',
        'admission_year_ec', 'mobile_phone', 'prep_school_last_attended',
        'clearance_reason'
    ];
    $data = [];
    foreach ($fields as $field) {
        $value = isset($_POST[$field]) ? trim($_POST[$field]) : null;
        $data[$field] = $value !== '' ? htmlspecialchars($value) : null;
    }

    // --- Server-Side Validation (Basic Example) ---
    $errors = [];
    if (empty($data['full_name'])) $errors[] = "Full Name is required.";
    if (empty($data['grandfather_name'])) $errors[] = "Grandfather's Name is required.";
    if (empty($data['id_number'])) $errors[] = "ID Number is required.";
    if (empty($data['birth_year_ec'])) $errors[] = "Year of Birth (E.C) is required.";
    if (empty($data['place_of_birth'])) $errors[] = "Place of Birth is required.";
    if (empty($data['faculty'])) $errors[] = "Faculty is required.";
    if (empty($data['department'])) $errors[] = "Department is required.";
    if (empty($data['admission_year_ec'])) $errors[] = "Year of Admission (E.C) is required.";
    if (empty($data['mobile_phone'])) $errors[] = "Mobile Phone Number is required.";
    if (empty($data['prep_school_last_attended'])) $errors[] = "Preparatory School is required.";
    if (empty($data['clearance_reason'])) $errors[] = "Reason for Clearance is required.";
    // Add more specific validation (e.g., phone number format) if needed

    // --- Process Data if No Errors ---
    if (empty($errors)) {
         // For this form, usually insert a new record each time or based on specific rules
         // Let's assume INSERT for now. Add logic for UPDATE if needed.
         $sql = "INSERT INTO cost_sharing_forms (user_id, full_name, grandfather_name, id_number, tin_number,
                    birth_year_ec, place_of_birth, faculty, department, admission_year_ec,
                    mobile_phone, prep_school_last_attended, clearance_reason)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
         $stmt = $conn->prepare($sql);
         if ($stmt) {
            $stmt->bind_param("issssssssssss",
                $user_id,
                $data['full_name'], $data['grandfather_name'], $data['id_number'], $data['tin_number'],
                $data['birth_year_ec'], $data['place_of_birth'], $data['faculty'], $data['department'],
                $data['admission_year_ec'], $data['mobile_phone'], $data['prep_school_last_attended'],
                $data['clearance_reason']
            );

            if ($stmt->execute()) {
                $_SESSION['success'] = "Cost Sharing Form submitted successfully!";
                $stmt->close();
                $conn->close();
                header("Location: dashboard.php"); // Redirect to dashboard
                exit;
            } else {
                 $errors[] = "Failed to submit Cost Sharing Form. " . $stmt->error;
                 $stmt->close();
            }
         } else {
              $errors[] = "Database error preparing statement. " . $conn->error;
         }
    } // End empty errors check

    // --- Handle Errors: Redirect back to form ---
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        $_SESSION['form_data'] = $_POST; // Save data to repopulate
        $conn->close();
        header("Location: cost_sharing.php");
        exit;
    }

} else {
    // Not a POST request
    header("Location: cost_sharing.php");
    exit;
}
?>