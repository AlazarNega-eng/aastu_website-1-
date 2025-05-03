<?php
$passwordToHash = '00000000'; // The password for Dawit
$hashedPassword = password_hash($passwordToHash, PASSWORD_BCRYPT);

if ($hashedPassword === false) {
    echo "Error hashing password.";
} else {
    echo "Password: " . htmlspecialchars($passwordToHash) . "<br>";
    echo "Hashed Password: " . htmlspecialchars($hashedPassword);
    // Copy the hashed password string below
}
?>