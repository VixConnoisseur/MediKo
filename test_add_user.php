<?php
// Simple test for the add user functionality
require_once __DIR__ . '/includes/config.php';

echo "<h1>Add User Functionality Test</h1>";

// Test 1: Check if handler file exists
if (file_exists(__DIR__ . '/pages/admin/add_user_handler.php')) {
    echo "<p>✅ Add user handler file exists</p>";
} else {
    echo "<p>❌ Add user handler file missing</p>";
}

// Test 2: Check if users page has the modal
$usersPage = file_get_contents(__DIR__ . '/pages/admin/users.php');
if (strpos($usersPage, 'addUserModal') !== false) {
    echo "<p>✅ Add user modal found in users.php</p>";
} else {
    echo "<p>❌ Add user modal missing in users.php</p>";
}

// Test 3: Check if showAddUserModal function is updated
if (strpos($usersPage, 'bootstrap.Modal(document.getElementById(\'addUserModal\'))') !== false) {
    echo "<p>✅ showAddUserModal function properly updated</p>";
} else {
    echo "<p>❌ showAddUserModal function not updated</p>";
}

// Test 4: Check database connection
try {
    $db = Database::getInstance();
    echo "<p>✅ Database connection successful</p>";
    
    // Test 5: Check if required tables exist
    $tables = ['users', 'user_profiles', 'audit_logs'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Table '$table' exists</p>";
        } else {
            echo "<p>❌ Table '$table' missing</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test 6: Check if Auth class is working
try {
    $auth = new Auth(Database::getInstance());
    echo "<p>✅ Auth class instantiated successfully</p>";
} catch (Exception $e) {
    echo "<p>❌ Auth class error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Test completed!</strong></p>";
echo "<p><a href='/bsit3a_guasis/mediko/pages/admin/users.php'>Go to Users Page</a></p>";
?>
