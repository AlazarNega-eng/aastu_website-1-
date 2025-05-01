<?php
session_start(); // Start the session
require_once 'db_connect.php'; // Include database connection

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Function to sanitize input data (can reuse from register_process or define here)
    function sanitize_input($data, $conn) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = mysqli_real_escape_string($conn, $data);
        return $data;
    }

    $username_or_email = sanitize_input($_POST['username_or_email'], $conn);
    $password = $_POST['password']; // Get raw password for verification

    $error_msg = "Invalid username/email or password."; // Generic error message

    if (empty($username_or_email) || empty($password)) {
        $_SESSION['error'] = "Please enter both username/email and password.";
        header("Location: login.php");
        exit();
    }

    // Prepare SQL statement to fetch user by username OR email
    $sql = "SELECT id, username, password FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $username_or_email, $username_or_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) == 1) {
            // Fetch user data
            $user = mysqli_fetch_assoc($result);

            // Verify the password against the stored hash
            if (password_verify($password, $user['password'])) {
                // Password is correct, start the session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                // Regenerate session ID for security
                session_regenerate_id(true);

                mysqli_stmt_close($stmt); // Close statement
                mysqli_close($conn); // Close connection

                // Redirect to a logged-in page (e.g., index or dashboard)
                header("Location: index.php"); // Change to dashboard.php if you have one
                exit();
            } else {
                // Password is not valid
                $_SESSION['error'] = $error_msg;
            }
        } else {
            // No user found with that username/email
            $_SESSION['error'] = $error_msg;
        }
        mysqli_stmt_close($stmt); // Close statement
    } else {
        // SQL Prepare failed
        $_SESSION['error'] = "Database error. Please try again later.";
         // Log detailed error: error_log("Prepare statement failed: " . mysqli_error($conn));
    }

    mysqli_close($conn); // Close connection
    header("Location: login.php"); // Redirect back to login on failure
    exit();

} else {
    // Not a POST request
    header("Location: login.php");
    exit();
}
?>