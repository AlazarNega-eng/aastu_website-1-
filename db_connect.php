<?php
// ---- VERY IMPORTANT: Ensure NO blank lines or spaces before this line ----

// --- Database Configuration ---
// Use constants for better security and clarity
define('DB_SERVER', 'localhost');      // Or '127.0.0.1'
define('DB_USERNAME', 'root');         // Default XAMPP username - CHANGE FOR PRODUCTION
define('DB_PASSWORD', '3205');             // Default XAMPP password (usually empty) - CHANGE FOR PRODUCTION
define('DB_NAME', 'aastu_db');         // Your database name

// --- Error Reporting for Connection (Crucial for Debugging) ---
// Report mysqli connection errors explicitly
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// --- Attempt to connect to MySQL database ---
// Use try...catch block to handle potential connection errors gracefully
try {
    // The connection object is assigned to $conn
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Optional: Set character set immediately after successful connection
    mysqli_set_charset($conn, "utf8mb4");

    // Optional: Log successful connection only during debugging if needed
    // error_log("Database connection successful to " . DB_NAME);

} catch (mysqli_sql_exception $e) {
    // Connection failed. Log the detailed error for the administrator.
    error_log("Database Connection Error: " . $e->getMessage() . " (Error Code: " . $e->getCode() . ")");

    // Set $conn to false so other scripts know the connection failed.
    $conn = false;

    // --- IMPORTANT: Do NOT output anything here! ---
    // Avoid die() or echo(). Let the script that includes this file decide how to handle the failure.
    // For example, the calling script can check `if ($conn === false)`
    // die("ERROR: Could not connect. Please contact support."); // <-- Avoid this
}

// Reset mysqli error reporting to default (optional, depends on overall error handling strategy)
// mysqli_report(MYSQLI_REPORT_OFF);

// The $conn variable (holding the mysqli connection object OR false)
// is now available to any script that includes/requires this file.

// ---- VERY IMPORTANT: Ensure NO blank lines or spaces after this line ----
// NO closing  tag needed as this file contains only PHP code.?>