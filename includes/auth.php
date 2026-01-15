<?php
/**
 * Authentication Class
 * 
 * Handles user authentication, registration, and session management.
 */
class Auth {
    private $db;
    private $sessionName = 'mediko_auth';
    private $rememberMeExpiry = 2592000; // 30 days in seconds

    public function __construct($db) {
        $this->db = $db;
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Auto-login from remember me cookie if needed
        if (!$this->isLoggedIn() && isset($_COOKIE['remember_token'])) {
            $this->loginWithRememberToken($_COOKIE['remember_token']);
        }
    }

    /**
     * Register a new user
     */
    public function register($userData) {
        // Validate input
        $required = ['email', 'password', 'full_name'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                throw new Exception("The {$field} field is required.");
            }
        }
        
        // Validate email
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address.");
        }
        
        // Check if email already exists
        $existingUser = $this->getUserByEmail($userData['email']);
        if ($existingUser) {
            throw new Exception("Email already registered.");
        }
        
        // Hash password
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Prepare user data
        $user = [
            'email' => $userData['email'],
            'password' => $hashedPassword,
            'full_name' => $userData['full_name'],
            'role' => 'user', // Default role
            'is_active' => 1, // Auto-activate for now
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => null,
            'verification_token' => bin2hex(random_bytes(32)),
            'email_verified_at' => date('Y-m-d H:i:s') // Auto-verify for now
        ];
        
        // Add optional fields if provided
        $optionalFields = ['phone', 'date_of_birth', 'gender'];
        foreach ($optionalFields as $field) {
            if (isset($userData[$field])) {
                $user[$field] = $userData[$field];
            }
        }
        
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Insert user
            $userId = $this->db->insert('users', $user);
            
            // Create user profile
            $profileData = [
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('user_profiles', $profileData);
            
            // Commit transaction
            $this->db->commit();
            
            // Send verification email (in production)
            // $this->sendVerificationEmail($user['email'], $user['verification_token']);
            
            return $userId;
        } catch (Exception $e) {
            // Rollback on error
            $this->db->rollback();
            throw new Exception("Registration failed: " . $e->getMessage());
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password, $rememberMe = false) {
        // Validate input
        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required.");
        }
        
        // Get user by email
        $user = $this->getUserByEmail($email);
        
        // Check if user exists and is active
        if (!$user || !$user['is_active']) {
            throw new Exception("Invalid credentials or account not active.");
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            // Log failed login attempt
            $this->logLoginAttempt($user['id'], false);
            throw new Exception("Invalid credentials.");
        }
        
        // Check if account is locked due to too many failed attempts
        if ($this->isAccountLocked($user['id'])) {
            throw new Exception("Account locked due to too many failed login attempts. Please try again later.");
        }
        
        // Update last login
        $this->updateLastLogin($user['id']);
        
        // Set session
        $this->setUserSession($user);
        
        // Set remember me cookie if requested
        if ($rememberMe) {
            $this->setRememberMeCookie($user['id']);
        }
        
        // Log successful login
        $this->logLoginAttempt($user['id'], true);
        
        return true;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        // Delete remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            $this->deleteRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        return true;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION[$this->sessionName]['user_id']);
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $userId = $_SESSION[$this->sessionName]['user_id'];
        return $this->getUserById($userId);
    }
    
    /**
     * Check if current user has a specific role
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Check if current user is admin
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email) {
        $user = $this->getUserByEmail($email);
        if (!$user) {
            // Don't reveal if email exists or not
            return true;
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Save token to database
        $this->db->update('users', 
            ['reset_token' => $token, 'reset_expires' => $expires],
            'id = :id',
            ['id' => $user['id']]
        );
        
        // Send email with reset link (in production)
        $resetLink = APP_URL . '/auth/reset-password?token=' . $token;
        // $this->sendEmail($email, 'Password Reset', "Click here to reset your password: $resetLink");
        
        return true;
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword) {
        // Find user with valid token
        $user = $this->db->query(
            "SELECT * FROM users WHERE reset_token = :token AND reset_expires > NOW() LIMIT 1",
            ['token' => $token]
        )->fetch();
        
        if (!$user) {
            throw new Exception("Invalid or expired token.");
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->update('users', 
            [
                'password' => $hashedPassword,
                'reset_token' => null,
                'reset_expires' => null
            ],
            'id = :id',
            ['id' => $user['id']]
        );
        
        return true;
    }
    
    /**
     * Verify email with token
     */
    public function verifyEmail($token) {
        $user = $this->db->query(
            "SELECT * FROM users WHERE verification_token = :token LIMIT 1",
            ['token' => $token]
        )->fetch();
        
        if (!$user) {
            throw new Exception("Invalid verification token.");
        }
        
        // Mark email as verified
        $this->db->update('users', 
            [
                'email_verified_at' => date('Y-m-d H:i:s'),
                'verification_token' => null
            ],
            'id = :id',
            ['id' => $user['id']]
        );
        
        return true;
    }
    
    // ===== PRIVATE METHODS =====
    
    private function getUserByEmail($email) {
        return $this->db->query(
            "SELECT * FROM users WHERE email = :email LIMIT 1",
            ['email' => $email]
        )->fetch();
    }
    
    private function getUserById($id) {
        return $this->db->query(
            "SELECT * FROM users WHERE id = :id LIMIT 1",
            ['id' => $id]
        )->fetch();
    }
    
    private function setUserSession($user) {
        $_SESSION[$this->sessionName] = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'logged_in' => true,
            'last_activity' => time()
        ];
    }
    
    private function setRememberMeCookie($userId) {
        // Generate a random token
        $token = bin2hex(random_bytes(32));
        $expires = time() + $this->rememberMeExpiry;
        
        // Save token to database
        $this->db->insert('remember_tokens', [
            'user_id' => $userId,
            'token' => password_hash($token, PASSWORD_DEFAULT),
            'expires_at' => date('Y-m-d H:i:s', $expires),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Set cookie
        setcookie('remember_token', $token, $expires, '/', '', false, true);
    }
    
    private function loginWithRememberToken($token) {
        // Find valid token
        $tokenRecord = $this->db->query(
            "SELECT * FROM remember_tokens WHERE token = :token AND expires_at > NOW() LIMIT 1",
            ['token' => $token]
        )->fetch();
        
        if ($tokenRecord) {
            $user = $this->getUserById($tokenRecord['user_id']);
            if ($user && $user['is_active']) {
                $this->setUserSession($user);
                
                // Update token expiration
                $newExpiry = time() + $this->rememberMeExpiry;
                $this->db->update('remember_tokens', 
                    ['expires_at' => date('Y-m-d H:i:s', $newExpiry)],
                    'id = :id',
                    ['id' => $tokenRecord['id']]
                );
                
                setcookie('remember_token', $token, $newExpiry, '/', '', false, true);
                return true;
            }
        }
        
        // If we get here, the token was invalid
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        return false;
    }
    
    private function deleteRememberToken($token) {
        $this->db->query(
            "DELETE FROM remember_tokens WHERE token = :token",
            ['token' => $token]
        );
    }
    
    private function updateLastLogin($userId) {
        $this->db->update('users', 
            ['last_login' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $userId]
        );
    }
    
    private function logLoginAttempt($userId, $success) {
        $this->db->insert('login_attempts', [
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'success' => $success ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function isAccountLocked($userId) {
        // Check for too many failed attempts in the last 15 minutes
        $result = $this->db->query(
            "SELECT COUNT(*) as attempts 
             FROM login_attempts 
             WHERE user_id = :user_id 
             AND success = 0 
             AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            ['user_id' => $userId]
        )->fetch();
        
        return $result['attempts'] >= 5; // Lock after 5 failed attempts
    }
}
