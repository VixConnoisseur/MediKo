<?php
// This script clears all login attempts
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();

try {
    $db->query('TRUNCATE TABLE login_attempts', []);
    echo "Login attempts cleared successfully.\n";
    
    // Also clear any rate limiting in the session
    session_start();
    unset($_SESSION['login_attempts']);
    echo "Session login attempts cleared.\n";
    
} catch (Exception $e) {
    echo "Error clearing login attempts: " . $e->getMessage() . "\n";
}

// Provide a link back to the login page
echo '<p><a href="login.php">Return to login page</a></p>';
