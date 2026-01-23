<?php
/**
 * Admin Setup Script
 * 
 * This script helps you create the first admin account.
 * It will self-destruct after successful setup.
 */

// Prevent direct access to this file
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('Access denied.');
}

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';

// Initialize database connection
$db = Database::getInstance();

// Check if admin already exists
$stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$result = $stmt->fetch();

if ($result['count'] > 0) {
    die("An admin account already exists. Please use the login page to access the admin dashboard.");
}

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $fullName = trim($_POST['full_name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($fullName)) {
        $errors[] = 'Full name is required';
    }
    
    if (!$email) {
        $errors[] = 'Valid email is required';
    }
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email already exists';
    }
    
    // If no errors, create admin account
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $currentTime = date('Y-m-d H:i:s');
        
        try {
            $db->beginTransaction();
            
            // Insert admin user
            $stmt = $db->prepare("
                INSERT INTO users (email, password, full_name, role, is_active, email_verified_at, created_at, updated_at)
                VALUES (?, ?, ?, 'admin', 1, ?, ?, ?)
            ");
            
            $success = $stmt->execute([
                $email,
                $hashedPassword,
                $fullName,
                $currentTime,
                $currentTime,
                $currentTime
            ]);
            
            if ($success) {
                $userId = $db->lastInsertId();
                
                // Create user profile
                $stmt = $db->prepare("
                    INSERT INTO user_profiles (user_id, created_at, updated_at)
                    VALUES (?, ?, ?)
                ");
                
                $stmt->execute([$userId, $currentTime, $currentTime]);
                
                $db->commit();
                
                // Display success message
                $successMessage = "Admin account created successfully!\n";
                $successMessage .= "Email: " . htmlspecialchars($email) . "\n";
                $successMessage .= "Name: " . htmlspecialchars($fullName) . "\n\n";
                $successMessage .= "Please log in using the admin dashboard.";
                
                // Self-destruct the file
                if (unlink(__FILE__)) {
                    $successMessage .= "\n\nThis setup script has been deleted for security reasons.";
                }
                
                die("<pre>" . $successMessage . "</pre>");
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Error creating admin account: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin Account - MediKo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .setup-container {
            max-width: 500px;
            margin: 5% auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo i {
            font-size: 3rem;
            color: #0d6efd;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <div class="logo">
                <i class="fas fa-user-shield"></i>
                <h2 class="mt-2">Admin Setup</h2>
                <p class="text-muted">Create the first administrator account</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h5>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? 'Admin User'); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? 'admin@example.com'); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="At least 8 characters" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" placeholder="Retype your password" required>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus me-2"></i> Create Admin Account
                    </button>
                </div>
            </form>
            
            <div class="mt-4 text-center text-muted">
                <small>
                    <i class="fas fa-info-circle me-1"></i> 
                    This script will self-destruct after successful setup.
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side password match validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirmPassword.focus();
            } else if (password.value.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                password.focus();
            }
        });
    </script>
</body>
</html>
