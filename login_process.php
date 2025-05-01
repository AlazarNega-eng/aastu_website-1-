<?php
// ---- VERY IMPORTANT: Ensure NO blank lines or spaces before this line ----
session_start(); // Start the session
require_once 'db_connect.php'; // Ensure db_connect.php also has NO output/whitespace before <?php

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Function to sanitize input data
    function sanitize_input($data, $conn) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = mysqli_real_escape_string($conn, $data);
        return $data;
    }

    $username_or_email = sanitize_input($_POST['username_or_email'], $conn);
    $password = $_POST['password'];

    $error_msg = "Invalid username/email or password.";

    if (empty($username_or_email) || empty($password)) {
        $_SESSION['error'] = "Please enter both username/email and password.";
        error_log("Login attempt failed: Missing username/email or password."); // Debug
        header("Location: login.php");
        exit();
    }

    error_log("Login attempt for: " . $username_or_email); // Debug: Log attempt

    // Prepare SQL statement to fetch user by username OR email
    $sql = "SELECT id, username, password FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $username_or_email, $username_or_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) == 1) {
            // User found
            error_log("User found: " . $username_or_email); // Debug
            $user = mysqli_fetch_assoc($result);

            // Verify the password against the stored hash
            if (password_verify($password, $user['password'])) {
                // Password is correct
                error_log("Password VERIFIED for user ID: " . $user['id']); // Debug

                // Start the session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Regenerate session ID for security
                session_regenerate_id(true);

                error_log("Session set. User ID: " . $_SESSION['user_id'] . ", Username: " . $_SESSION['username']); // Debug

                mysqli_stmt_close($stmt);
                mysqli_close($conn);

                // ---- Redirect ----
                // Make sure nothing is output before this line!
                header("Location: dashboard.php");
                exit(); // CRITICAL: Stop script execution immediately

            } else {
                // Password is not valid
                error_log("Password verification FAILED for: " . $username_or_email); // Debug
                $_SESSION['error'] = $error_msg;
            }
        } else {
            // No user found with that username/email
            error_log("User NOT found: " . $username_or_email); // Debug
            $_SESSION['error'] = $error_msg;
        }
        mysqli_stmt_close($stmt);
    } else {
        // SQL Prepare failed
        error_log("SQL prepare failed: " . mysqli_error($conn)); // Debug
        $_SESSION['error'] = "Database error. Please try again later.";
    }

    mysqli_close($conn);
    error_log("Redirecting back to login.php due to failure."); // Debug
    header("Location: login.php"); // Redirect back to login on failure
    exit();

} else {
    // Not a POST request
    error_log("Non-POST request to login_process.php"); // Debug
    header("Location: login.php");
    exit();
}
// ---- VERY IMPORTANT: Ensure NO blank lines or spaces after this line ----
?>