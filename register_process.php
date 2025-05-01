<?php
session_start(); // Start the session at the very beginning
require_once 'db_connect.php'; // Include database connection

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Function to sanitize input data
    function sanitize_input($data, $conn) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = mysqli_real_escape_string($conn, $data); // Escape for SQL
        return $data;
    }

    // Sanitize and validate inputs
    $username = sanitize_input($_POST['username'], $conn);
    $email = sanitize_input($_POST['email'], $conn);
    $password = $_POST['password']; // Get raw password for comparison/hashing
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Basic Validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        $errors[] = "Username must be 3-20 characters and contain only letters, numbers, and underscores.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) { // Example: Minimum length
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // If no basic validation errors, check database
    if (empty($errors)) {
        // Check if username or email already exists using prepared statements
        $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);

        if ($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check); // Store result to check num_rows

            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $errors[] = "Username or Email already taken.";
            }
            mysqli_stmt_close($stmt_check); // Close statement
        } else {
            $errors[] = "Database error checking user existence.";
            // Log detailed error: error_log("Prepare statement failed: " . mysqli_error($conn));
        }
    }

    // If still no errors after database check, proceed with registration
    if (empty($errors)) {
        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Or PASSWORD_DEFAULT

        if ($hashed_password === false) {
             $errors[] = "Could not hash the password.";
             // Log this error server-side
        } else {
             // Insert the new user using prepared statements
            $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);

            if ($stmt_insert) {
                mysqli_stmt_bind_param($stmt_insert, "sss", $username, $email, $hashed_password);

                if (mysqli_stmt_execute($stmt_insert)) {
                    // Registration successful
                    $_SESSION['success'] = "Registration successful! Please login.";
                    mysqli_stmt_close($stmt_insert); // Close statement
                    mysqli_close($conn); // Close connection
                    header("Location: login.php");
                    exit(); // Important: Stop script execution after redirect
                } else {
                    $errors[] = "Registration failed. Please try again later.";
                    // Log detailed error: error_log("Execute statement failed: " . mysqli_stmt_error($stmt_insert));
                }
                 mysqli_stmt_close($stmt_insert); // Close statement even if execute failed
            } else {
                 $errors[] = "Database error preparing registration.";
                 // Log detailed error: error_log("Prepare statement failed: " . mysqli_error($conn));
            }
        }

    }

    // If there were any errors (validation or database)
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors); // Combine errors
        mysqli_close($conn); // Close connection
        header("Location: register.php"); // Redirect back to registration page
        exit(); // Stop script execution
    }

} else {
    // If not a POST request, redirect to registration page
    header("Location: register.php");
    exit();
}
?>