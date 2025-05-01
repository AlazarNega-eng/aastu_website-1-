<?php
$passwordToHash = '12345678';
$hashedPassword = password_hash($passwordToHash, PASSWORD_BCRYPT); // Or PASSWORD_DEFAULT

if ($hashedPassword === false) {
    echo "Error hashing password.";
} else {
    echo "Password: " . htmlspecialchars($passwordToHash) . "<br>";
    echo "Hashed Password: " . htmlspecialchars($hashedPassword);
}
?>