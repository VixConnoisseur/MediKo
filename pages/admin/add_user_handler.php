<?php
// AJAX handler for adding new users
require_once __DIR__ . '/../../includes/config.php';

// Set JSON header
header('Content-Type: application/json');

// Response array
$response = [
    'success' => false,
    'message' => 'An error occurred',
    'errors' => []
];

try {
    // Check if this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Verify CSRF token
    if (!verify_csrf_token($_POST['_token'] ?? '')) {
        throw new Exception('Security token validation failed');
    }

    // Get form data
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';

    // Validate input
    if (empty($fullName)) {
        $response['errors']['full_name'] = 'Full name is required';
    }
    
    if (empty($email)) {
        $response['errors']['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = 'Invalid email address';
    }
    
    if (empty($password)) {
        $response['errors']['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $response['errors']['password'] = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) {
        $response['errors']['password'] = 'Password must contain uppercase, lowercase, and numbers';
    }
    
    if ($password !== $confirmPassword) {
        $response['errors']['confirm_password'] = 'Passwords do not match';
    }
    
    if (!in_array($role, ['user', 'admin'])) {
        $response['errors']['role'] = 'Invalid role selected';
    }
    
    if (!empty($dateOfBirth)) {
        $dob = DateTime::createFromFormat('Y-m-d', $dateOfBirth);
        if (!$dob || $dob->format('Y-m-d') !== $dateOfBirth) {
            $response['errors']['date_of_birth'] = 'Invalid date format';
        }
    }
    
    if (!in_array($gender, ['', 'male', 'female', 'other'])) {
        $response['errors']['gender'] = 'Invalid gender selected';
    }

    // If there are validation errors, return them
    if (!empty($response['errors'])) {
        echo json_encode($response);
        exit;
    }

    // Initialize database and auth
    $db = Database::getInstance();
    $auth = new Auth($db);

    // Check if email already exists
    $existingUserStmt = $db->query("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
    $existingUser = $existingUserStmt->fetch();
    if ($existingUser) {
        $response['errors']['email'] = 'Email address already registered';
        echo json_encode($response);
        exit;
    }

    // Prepare user data
    $userData = [
        'full_name' => $fullName,
        'email' => $email,
        'password' => $password,
        'phone' => $phone,
        'role' => $role,
        'is_active' => $isActive,
        'date_of_birth' => $dateOfBirth ?: null,
        'gender' => $gender ?: null
    ];

    // Register the user
    $userId = $auth->register($userData);

    if ($userId) {
        // Log the action
        $currentUser = $auth->getCurrentUser();
        $db->insert('audit_logs', [
            'user_id' => $currentUser['id'],
            'action' => 'user_created',
            'details' => json_encode([
                'created_user_id' => $userId,
                'created_user_email' => $email,
                'created_user_name' => $fullName,
                'role' => $role
            ]),
            'ip_address' => get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $response = [
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $userId
        ];
    } else {
        throw new Exception('Failed to create user');
    }

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
