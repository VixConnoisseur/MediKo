<?php
/**
 * Security Class
 * Handles security-related functionality like hashing, CSRF, rate limiting, etc.
 */
class Security {
    private $db;
    private $maxLoginAttempts = 5;
    private $lockoutTime = 900; // 15 minutes in seconds
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Hash a password with the latest algorithm
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }
    
    /**
     * Check if password needs rehashing
     */
    public function needsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_DEFAULT, ['cost' => 12]);
    }
    
    /**
     * Check login attempts and implement rate limiting
     */
    public function checkLoginAttempts($email) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $time = time() - $this->lockoutTime;
        
        try {
            $stmt = $this->db->query(
                'SELECT COUNT(*) as count FROM login_attempts WHERE ip = ? AND time > ?',
                [$ip, $time]
            );
            
            $result = $stmt->fetch();
            
            if ($result && $result['count'] >= $this->maxLoginAttempts) {
                throw new Exception('Too many login attempts. Please try again later.');
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Database error in checkLoginAttempts: " . $e->getMessage());
            // Don't block login on database error, just log it
            return true;
        }
    }
    
    /**
     * Record a failed login attempt
     */
    public function recordLoginAttempt($email, $success = false) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $time = time();
        
        try {
            if ($success) {
                // Clear failed attempts on successful login
                $this->db->query('DELETE FROM login_attempts WHERE ip = ?', [$ip]);
            } else {
                // Record failed attempt
                $this->db->query(
                    'INSERT INTO login_attempts (email, ip, time, user_agent) VALUES (?, ?, ?, ?)',
                    [
                        $email,
                        $ip,
                        $time,
                        $_SERVER['HTTP_USER_AGENT'] ?? ''
                    ]
                );
            }
        } catch (PDOException $e) {
            error_log("Database error in recordLoginAttempt: " . $e->getMessage());
            // Continue execution even if logging fails
        }
    }
    
    /**
     * Generate and store CSRF token
     */
    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception('Invalid CSRF token');
        }
        return true;
    }
}

// Create database table for login attempts if it doesn't exist
try {
    $db = Database::getInstance();
    $db->query("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        ip VARCHAR(45) NOT NULL,
        time INT NOT NULL,
        user_agent TEXT,
        INDEX idx_ip_time (ip, time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {
    error_log("Error creating login_attempts table: " . $e->getMessage());
}
