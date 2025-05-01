<?php
// Database Configuration
define('DB_SERVER', 'localhost');      // Or 127.0.0.1
define('DB_USERNAME', 'root');         // Default XAMPP username
define('DB_PASSWORD', '3205');             // Default XAMPP password (usually empty)
define('DB_NAME', 'aastu_db');         // The database name you created

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    // Stop script execution and display error
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Optional: Set character set to utf8mb4 (recommended)
mysqli_set_charset($conn, "utf8mb4");

// No need to return anything, the $conn variable will be available
// in scripts that include this file.
?>