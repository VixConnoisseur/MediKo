<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';

// Database connection
try {
    $db = Database::getInstance();
    
    // Drop the existing table if it exists
    $db->query("DROP TABLE IF EXISTS login_attempts");
    
    // Create the table with the correct schema
    $db->query("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        ip VARCHAR(45) NOT NULL,
        time INT NOT NULL,
        user_agent TEXT,
        INDEX idx_ip_time (ip, time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    echo "Successfully recreated login_attempts table with the correct schema.\n";
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}

// Test the table
$testEmail = 'test@example.com';
try {
    $db->insert('login_attempts', [
        'email' => $testEmail,
        'ip' => '127.0.0.1',
        'time' => time(),
        'user_agent' => 'Test Script'
    ]);
    
    $result = $db->query("SELECT * FROM login_attempts WHERE email = ?", [$testEmail])->fetch();
    if ($result) {
        echo "Test record inserted and retrieved successfully.\n";
        echo "Table structure is correct.\n";
        
        // Clean up test data
        $db->query("DELETE FROM login_attempts WHERE email = ?", [$testEmail]);
    } else {
        echo "Warning: Test record not found after insertion.\n";
    }
} catch (Exception $e) {
    die("Error testing login_attempts table: " . $e->getMessage() . "\n");
}

echo "\nPlease delete this file after use for security reasons.";
?>
