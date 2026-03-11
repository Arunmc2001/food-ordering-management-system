<?php
// This script helps generate hashed passwords for new delivery persons

$password = "your_password_here"; // Replace with the desired password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

echo "Original Password: " . $password . "\n";
echo "Hashed Password: " . $hashed_password . "\n";
?> 