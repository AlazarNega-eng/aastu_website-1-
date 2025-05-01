<?php
session_start(); // Access the existing session

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page or home page
header("Location: login.php"); // Or index.php
exit;
?>