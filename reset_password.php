<?php
$newPassword = 'admin123'; // Change this to your desired password
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
echo "Hashed password: " . $hashedPassword;