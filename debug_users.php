<?php
// Simple debug script to check users in database
require_once __DIR__ . '/includes/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize database
$db = Database::getInstance();

echo "<h1>Database Debug - Users Table</h1>";

// Check if users table exists
try {
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->rowCount() > 0;
    
    echo "<h2>Users Table Status: " . ($tableExists ? "EXISTS" : "NOT FOUND") . "</h2>";
    
    if ($tableExists) {
        // Get total count of users
        $stmt = $db->query("SELECT COUNT(*) as total FROM users");
        $totalUsers = $stmt->fetch()['total'];
        echo "<h3>Total Users in Database: " . $totalUsers . "</h3>";
        
        // Get all users (limit 10 for display)
        $stmt = $db->query("SELECT id, full_name, email, role, is_active, created_at, last_login FROM users LIMIT 10");
        $users = $stmt->fetchAll();
        
        echo "<h3>Sample Users (First 10):</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Created</th><th>Last Login</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "<td>" . ($user['last_login'] ?: 'Never') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check user_profiles table
        $stmt = $db->query("SHOW TABLES LIKE 'user_profiles'");
        $profilesExists = $stmt->rowCount() > 0;
        echo "<h3>User Profiles Table Status: " . ($profilesExists ? "EXISTS" : "NOT FOUND") . "</h3>";
        
        if ($profilesExists) {
            $stmt = $db->query("SELECT COUNT(*) as total FROM user_profiles");
            $totalProfiles = $stmt->fetch()['total'];
            echo "<h4>Total User Profiles: " . $totalProfiles . "</h4>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>Error: " . $e->getMessage() . "</h2>";
}

// Test the exact query from users.php
echo "<h2>Testing Query from users.php:</h2>";
try {
    $query = "SELECT u.*, up.date_of_birth, u.phone as profile_phone 
              FROM users u 
              LEFT JOIN user_profiles up ON u.id = up.user_id 
              WHERE u.role = 'user'
              ORDER BY u.created_at DESC
              LIMIT 10";
    
    $stmt = $db->query($query);
    $users = $stmt->fetchAll();
    
    echo "<h3>Query Results: " . count($users) . " users found</h3>";
    
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>DOB</th><th>Role</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . ($user['profile_phone'] ?: 'N/A') . "</td>";
            echo "<td>" . ($user['date_of_birth'] ?: 'N/A') . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<h2>Query Error: " . $e->getMessage() . "</h2>";
}

echo "<p><a href='pages/admin/users.php'>Go back to Users Page</a></p>";
?>
