<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json');

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/Security.php';

// Response array
$response = [
    'success' => false,
    'message' => 'An error occurred',
    'debug' => []
];

try {
    // Check if this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get form data
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
    $csrfToken = $_POST['_token'] ?? '';

    // Debug log
    error_log("Login attempt - Email: " . $email . ", Remember: " . ($remember ? 'Yes' : 'No'));

    // Validate input
    if (!$email) {
        throw new Exception('Please enter a valid email address');
    }
    if (empty($password)) {
        throw new Exception('Please enter your password');
    }

    // Initialize database, security, and auth
    $db = Database::getInstance();
    $security = new Security($db);
    $auth = new Auth($db);

    // Debug log before login attempt
    error_log("Attempting login for email: " . $email);

    // Log the login attempt (initially marked as failed until successful)
    $auth->logLoginAttempt(0, false); // User ID 0 means not yet authenticated

    // Attempt login with CSRF protection
    if ($auth->login($email, $password, $remember, $csrfToken)) {
        // Login successful - get the user data
        $user = $auth->getCurrentUser();
        if ($user) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Log the successful login attempt with the actual user ID
            $auth->logLoginAttempt($user['id'], true);

            // Set secure session variables
            $_SESSION[$auth->getSessionName()] = [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'logged_in' => true,
                'last_activity' => time()
            ];

            // Determine redirect URL based on role
            $redirectUrl = $user['role'] === 'admin'
                ? '/bsit3a_guasis/mediko/pages/admin/dashboard.php'
                : '/bsit3a_guasis/mediko/dashboard.php';

            $response = [
                'success' => true,
                'message' => 'Login successful',
                'redirect' => $redirectUrl,
                '_token' => $security->generateCsrfToken() // Generate new CSRF token for next request
            ];

            error_log("Login successful for user: " . $email . ", role: " . $user['role']);
        } else {
            error_log("Login failed: Unable to retrieve user data after login for email: " . $email);
            throw new Exception('Unable to retrieve user data after login. Please contact support.');
        }
    } else {
        // Get the last error from Auth class if available
        $error = $auth->getError() ?: 'Invalid email or password';
        error_log("Login failed: " . $error . " for email: " . $email);

        // Record failed login attempt in security system
        $security->recordLoginAttempt($email, false);

        // Log the failed login attempt with user ID if available
        $currentUser = $auth->getCurrentUser();
        if ($currentUser && $currentUser['email'] === $email) {
            $auth->logLoginAttempt($currentUser['id'], false);
        }

        throw new Exception($error);
    }
} catch (Exception $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => APP_DEBUG ? $e->getTraceAsString() : null
        ]
    ];
    
    error_log("Login handler error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    if (APP_DEBUG) {
        error_log("Stack trace: " . $e->getTraceAsString());
        $response['debug']['post_data'] = $_POST;
    }
}

// Clear output buffer and send JSON response
ob_end_clean();
echo json_encode($response, JSON_PRETTY_PRINT);
exit;