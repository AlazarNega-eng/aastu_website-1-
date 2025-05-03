<?php
session_start();
require_once 'db_connect.php';

// --- Authentication & Role Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'dept_head') {
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

// --- Check if form submitted ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['profile_id']) && isset($_POST['action'])) {

    $profile_id = filter_input(INPUT_POST, 'profile_id', FILTER_VALIDATE_INT);
    $action = $_POST['action']; // 'approve' or 'reject'
    $new_status = '';

    if ($action === 'approve') {
        $new_status = 'approved';
    } elseif ($action === 'reject') {
        $new_status = 'rejected'; // If you implement reject button
    } else {
        $_SESSION['error'] = "Invalid action specified.";
        header("Location: dept_view_students.php");
        exit;
    }

    if ($profile_id === false || $profile_id <= 0) {
         $_SESSION['error'] = "Invalid student profile ID.";
         header("Location: dept_view_students.php");
         exit;
    }

    // --- Update Database ---
    $sql = "UPDATE student_profiles SET dept_approval_status = ? WHERE id = ? AND dept_approval_status = 'pending'"; // Only update if pending
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $new_status, $profile_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                 $_SESSION['success'] = "Student registration status updated to '" . htmlspecialchars($new_status) . "'.";
                 // Optional: Notify Registrar or Student here
            } else {
                 $_SESSION['error'] = "Could not update status. Student might already be processed or ID is invalid.";
            }
        } else {
             $_SESSION['error'] = "Database error executing update: " . $stmt->error;
             error_log("Approval Error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Database error preparing update: " . $conn->error;
        error_log("Approval Prepare Error: " . $conn->error);
    }

    $conn->close();
    header("Location: dept_view_students.php"); // Redirect back to the list
    exit;

} else {
    // Not a POST request or missing data
    $_SESSION['error'] = "Invalid request.";
    header("Location: dept_view_students.php");
    exit;
}
?>