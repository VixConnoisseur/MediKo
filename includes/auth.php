<?php
/**
 * Authentication Class
 * 
 * Handles user authentication, registration, and session management.
 */

// Include required classes
require_once __DIR__ . '/Security.php';

class Auth {
    private $db;
    private $security;
    private $sessionName = 'mediko_auth';
    private $rememberMeExpiry = 2592000; // 30 days in seconds
    private $lastError = '';

    public function __construct($db) {
        $this->db = $db;
        $this->security = new Security($db);
        
        // Don't start session here - it will be handled by config.php
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
        
        // Hash password using security class
        $hashedPassword = $this->security->hashPassword($userData['password']);
        
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
            
            
            return $userId;
        } catch (Exception $e) {
            // Rollback on error
            $this->db->rollback();
            throw new Exception("Registration failed: " . $e->getMessage());
        }
    }
    
    /**
     * Regenerate session ID and update session data
     */
    private function regenerateSession($user) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session data
        $this->setUserSession($user);
        
        // Update last activity time
        $_SESSION[$this->sessionName]['last_activity'] = time();
    }
    
    /**
     * Login user with security checks
     */
    public function login($email, $password, $rememberMe = false, $csrfToken = null) {
        try {
            // Validate CSRF token if provided
            if ($csrfToken !== null) {
                $this->security->verifyCsrfToken($csrfToken);
            }
            
            // Check login attempts
            $this->security->checkLoginAttempts($email);
            
            // Validate input
            if (empty($email) || empty($password)) {
                $this->security->recordLoginAttempt($email);
                throw new Exception('Email and password are required.');
            }
            
            // Get user by email
            $user = $this->getUserByEmail($email);
            
            // Check if user exists and is active
            if (!$user) {
                $this->security->recordLoginAttempt($email);
                throw new Exception('Invalid email or password.');
            }
            
            if (!$user['is_active']) {
                throw new Exception('Your account is inactive. Please contact support.');
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->security->recordLoginAttempt($email);
                throw new Exception('Invalid email or password.');
            }
            
            // Check if password needs rehashing
            if ($this->security->needsRehash($user['password'])) {
                $newHash = $this->security->hashPassword($password);
                $this->db->update('users', 
                    ['password' => $newHash],
                    'id = :id',
                    ['id' => $user['id']]
                );
            }
            
            // Regenerate session for security
            $this->regenerateSession($user);
            
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Handle remember me
            if ($rememberMe) {
                $this->setRememberMe($user['id']);
            }
            
            // Record successful login
            $this->security->recordLoginAttempt($email, true);
            
            // Log successful login
            $this->logLoginAttempt($user['id'], true);
            
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Login error: " . $this->lastError);
            return false;
        }
    }

    public function getError() {
        return $this->lastError;
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
    
    public function getCurrentUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION[$this->sessionName]['user_id'])) {
        return $this->getUserById($_SESSION[$this->sessionName]['user_id']);
    }
    
    return null;
}

/**
 * Get the session name
 */
public function getSessionName() {
    return $this->sessionName;
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
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1",
            [$token]
        );
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('Invalid or expired reset token.');
        }
        
        // Update password and clear reset token
        $this->db->query(
            "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?",
            [$this->security->hashPassword($newPassword), $user['id']]
        );
        
        return true;
    }
    
    /**
     * Verify email with token
     */
    public function verifyEmail($token) {
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE verification_token = ? LIMIT 1",
            [$token]
        );
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception("Invalid verification token.");
        }
        
        // Mark email as verified
        $this->db->query(
            "UPDATE users SET email_verified_at = ?, verification_token = NULL WHERE id = ?",
            [date('Y-m-d H:i:s'), $user['id']]
        );
        
        return true;
    }
    
    // ===== PRIVATE METHODS =====
    
    private function getUserByEmail($email) {
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE email = ? LIMIT 1",
            [$email]
        );
        return $stmt->fetch();
    }
    
    private function getUserById($id) {
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE id = ? LIMIT 1",
            [$id]
        );
        return $stmt->fetch();
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
    
    /**
     * Set remember me cookie and store token in database
     */
    private function setRememberMe($userId) {
        // Generate a random token
        $token = bin2hex(random_bytes(32));
        $expires = time() + $this->rememberMeExpiry;
        
        // Store hashed token in database
        $this->db->insert('remember_tokens', [
            'user_id' => $userId,
            'token' => password_hash($token, PASSWORD_DEFAULT),
            'expires_at' => date('Y-m-d H:i:s', $expires),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Set cookie
        setcookie(
            'remember_token',
            $token,
            [
                'expires' => $expires,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }
    
    /**
     * Set remember me cookie with token
     */
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
        $stmt = $this->db->query(
            "SELECT * FROM remember_tokens WHERE token = ? AND expires_at > NOW() LIMIT 1",
            [$token]
        );
        $tokenRecord = $stmt->fetch();
        
        if ($tokenRecord) {
            $user = $this->getUserById($tokenRecord['user_id']);
            if ($user && $user['is_active']) {
                $this->setUserSession($user);
                
                // Update token expiration
                $newExpiry = time() + $this->rememberMeExpiry;
                $this->db->query(
                    "UPDATE remember_tokens SET expires_at = ? WHERE id = ?",
                    [date('Y-m-d H:i:s', $newExpiry), $tokenRecord['id']]
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
            "DELETE FROM remember_tokens WHERE token = ?",
            [$token]
        );
    }
    
    private function updateLastLogin($userId) {
        // Update last login time
        $this->db->query(
            "UPDATE users SET last_login = ? WHERE id = ?",
            [date('Y-m-d H:i:s'), $userId]
        );
        
        // Reset login attempts for this user
        $this->db->query(
            "DELETE FROM login_attempts WHERE user_id = ?",
            [$userId]
        );
        
        return true;
    }
    
    /**
     * Log a login attempt
     * 
     * @param int $userId The user ID
     * @param bool $success Whether the login was successful
     * @return bool True on success, false on failure
     */
    public function logLoginAttempt($userId, $success) {
        try {
            return (bool)$this->db->insert('login_attempts', [
                'user_id' => $userId,
                'email' => $this->getUserById($userId)['email'] ?? 'unknown',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'time' => time(),
                'success' => $success ? 1 : 0
            ]);
        } catch (Exception $e) {
            error_log("Failed to log login attempt: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if account is locked due to too many failed login attempts
     * 
     * @param string $email User's email address
     * @return bool True if account is locked, false otherwise
     */
    private function isAccountLocked($email) {
        $time = time() - 900; // 15 minutes ago
        
        // Count failed attempts in the last 15 minutes
        $attempts = $this->db->select(
            "SELECT COUNT(*) as count FROM login_attempts 
             WHERE email = :email 
             AND success = 0 
             AND time > :time",
            ['email' => $email, 'time' => $time]
        );
        
        return $attempts && $attempts[0]['count'] >= 5; // Lock after 5 failed attempts
    }
}
