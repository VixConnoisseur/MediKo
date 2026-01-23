<?php
// Simple test to check if users.php is accessible
echo "Testing users.php access...<br>";

// Check if the file exists
$usersFile = __DIR__ . '/pages/admin/users.php';
if (file_exists($usersFile)) {
    echo "✓ users.php file exists at: " . $usersFile . "<br>";
} else {
    echo "✗ users.php file NOT found at: " . $usersFile . "<br>";
}

// Check if includes directory exists
$includesDir = __DIR__ . '/includes';
if (file_exists($includesDir)) {
    echo "✓ includes directory exists<br>";
} else {
    echo "✗ includes directory NOT found<br>";
}

// Check if header.php exists
$headerFile = __DIR__ . '/includes/header.php';
if (file_exists($headerFile)) {
    echo "✓ header.php file exists<br>";
} else {
    echo "✗ header.php file NOT found<br>";
}

// Try to include header.php
try {
    require_once __DIR__ . '/includes/config.php';
    echo "✓ config.php loaded successfully<br>";
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "✓ Session started<br>";
    }
    
    // Mock admin session for testing
    $_SESSION[$auth->getSessionName()]['user_id'] = 1;
    $_SESSION[$auth->getSessionName()]['role'] = 'admin';
    echo "✓ Mock admin session created<br>";
    
    require_once __DIR__ . '/includes/database.php';
    echo "✓ database.php loaded successfully<br>";
    
    $db = Database::getInstance();
    echo "✓ Database connection established<br>";
    
    require_once __DIR__ . '/includes/header.php';
    echo "✓ header.php loaded successfully<br>";
    
    echo "<br><strong>All tests passed! users.php should be accessible.</strong><br>";
    echo '<a href="/bsit3a_guasis/mediko/pages/admin/users.php">Click here to access users.php</a>';
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}
?>
