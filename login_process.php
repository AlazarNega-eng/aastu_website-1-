<?php
// ---- VERY IMPORTANT: Ensure NO blank lines, spaces, or characters before this line ----
ini_set('display_errors', 1); // TEMPORARY: Force error display
ini_set('display_startup_errors', 1); // TEMPORARY: Force startup errors
error_reporting(E_ALL);      // TEMPORARY: Report all errors

session_start(); // Start the session FIRST

require_once 'db_connect.php'; // Include DB connection

// --- Check if the DB connection itself failed in the include ---
if ($conn === false || !($conn instanceof mysqli)) {
    error_log("Login Process Error: Database connection failed or invalid in db_connect.php.");
    // Set a session error *before* trying to redirect
    $_SESSION['error'] = "Critical database connection error. Please try again later or contact support.";
    // Attempt redirect (might fail if headers already sent by an error above, but try)
    header("Location: login.php");
    exit; // Stop script immediately
}

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Function to sanitize input data
    function sanitize_input($data, $conn_obj) { // Renamed to avoid conflict
         // Check connection passed to function
        if (!$conn_obj || !($conn_obj instanceof mysqli)) {
             error_log("Invalid connection object passed to sanitize_input in login_process.");
             // Return empty string or null to indicate failure within the function
             return ''; // Or handle error more robustly if needed
        }
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        // Use mysqli_real_escape_string ONLY if connection is valid
        $data = mysqli_real_escape_string($conn_obj, $data);
        return $data;
    }

    // --- Get and Sanitize Inputs ---
    $username_or_email = isset($_POST['username_or_email']) ? sanitize_input($_POST['username_or_email'], $conn) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // Get raw password for verification

    $generic_error_msg = "Invalid username/email or password."; // Use a consistent generic message

    // --- Basic Input Validation ---
    if (empty($username_or_email) || empty($password)) {
        $_SESSION['error'] = "Please enter both username/email and password.";
        error_log("Login attempt failed: Missing username/email or password.");
        // Close connection before redirecting on validation failure
        mysqli_close($conn);
        header("Location: login.php");
        exit(); // Stop script
    }

    error_log("Login attempt for: " . $username_or_email); // Log the attempt

    // --- Prepare SQL statement ---
    $sql = "SELECT id, username, password, role FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        error_log("SQL statement prepared successfully."); // Debug log

        mysqli_stmt_bind_param($stmt, "ss", $username_or_email, $username_or_email);

        if(mysqli_stmt_execute($stmt)) {
            error_log("SQL statement executed successfully."); // Debug log
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) == 1) {
                // User found
                $user = mysqli_fetch_assoc($result);
                error_log("User found: " . $user['username'] . " (ID: " . $user['id'] . ")"); // Debug log

                // --- Verify the password ---
                 // Ensure both arguments are valid strings before calling password_verify
                if (isset($user['password']) && is_string($user['password']) && $user['password'] !== '' && is_string($password) && $password !== '') {
                    if (password_verify($password, $user['password'])) {
                        // Password is correct
                        error_log("Password VERIFIED for user ID: " . $user['id']);

                        // --- Set Session Variables ---
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role']; // Store role

                        // Regenerate session ID (Security best practice)
                        session_regenerate_id(true);
                        error_log("Session regenerated and variables set. User ID: " . $_SESSION['user_id'] . ", Role: " . $_SESSION['role']);

                        // Close statement and connection BEFORE redirect
                        mysqli_stmt_close($stmt);
                        mysqli_close($conn);

                        // --- Redirect Based on Role ---
                        $redirect_url = 'dashboard.php'; // Default (student)
                        if ($_SESSION['role'] == 'dept_head') {
                            $redirect_url = 'dept_dashboard.php';
                        } elseif ($_SESSION['role'] == 'registrar') {
                            $redirect_url = 'registrar_dashboard.php'; // Future use
                        }
                        error_log("Redirecting to: " . $redirect_url);
                        header("Location: " . $redirect_url);
                        exit(); // CRITICAL: Stop immediately after header

                    } else {
                        // Password verification failed
                        error_log("Password verification FAILED for: " . $username_or_email);
                        $_SESSION['error'] = $generic_error_msg;
                    }
                } else {
                    // Problem with fetched password hash or submitted password
                     error_log("Password verification SKIPPED due to invalid fetched hash or submitted password for: " . $username_or_email);
                     $_SESSION['error'] = "Login error. Please try again."; // More generic error
                }

            } else {
                // User not found
                error_log("User NOT found matching: " . $username_or_email);
                $_SESSION['error'] = $generic_error_msg;
            }
            // No need to close result explicitly with get_result

        } else {
             // Execute failed
             error_log("SQL statement execute failed: " . mysqli_stmt_error($stmt));
             $_SESSION['error'] = "Login failed due to a database error.";
        }
        // Close statement if it was prepared successfully
        mysqli_stmt_close($stmt);

    } else {
        // SQL Prepare failed
        error_log("SQL prepare failed: " . mysqli_error($conn));
        $_SESSION['error'] = "Database error during login preparation.";
    }

    // Close connection if it's still open (e.g., after errors)
    if ($conn instanceof mysqli) {
        mysqli_close($conn);
    }

    // Redirect back to login on any failure AFTER the initial validation
    error_log("Redirecting back to login.php due to login failure.");
    header("Location: login.php");
    exit(); // Stop script

} else {
    // Not a POST request
    error_log("Non-POST request to login_process.php");
     // Close connection if it was opened
    if (isset($conn) && $conn instanceof mysqli) {
      mysqli_close($conn);
    }
    header("Location: login.php");
    exit();
}
// ---- VERY IMPORTANT: Ensure NO blank lines or spaces after this point ----
// NO closing ?> needed