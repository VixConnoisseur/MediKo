<?php
/**
 * Session Management
 * 
 * Handles session configuration, security, and management.
 */

// Ensure session is only started once
if (session_status() === PHP_SESSION_NONE) {
    // Set session name
    $sessionName = 'mediko_session';
    
    // Set session cookie parameters for enhanced security
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $httponly = true; // Prevent JavaScript access to session cookie
    
    // Set session cookie parameters
    $lifetime = 0; // Until the browser is closed
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => 'Lax' // CSRF protection
    ]);
    
    // Set session name
    session_name($sessionName);
    
    // Start the session
    session_start();
    
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } else {
        $interval = 60 * 30; // 30 minutes
        if (time() - $_SESSION['last_regeneration'] > $interval) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// Session hijacking prevention
if (isset($_SESSION['user_agent'])) {
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        // Possible session hijacking attempt
        session_unset();
        session_destroy();
        header('Location: ' . baseUrl('/auth/login'));
        exit();
    }
} else {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
}

// Session fixation prevention
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Session timeout (30 minutes of inactivity)
$timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    // Last request was more than 30 minutes ago
    session_unset();
    session_destroy();
    
    // Redirect to login page with a session expired message
    if (!headers_sent()) {
        header('Location: ' . baseUrl('/auth/login?expired=1'));
        exit();
    }
}
$_SESSION['last_activity'] = time(); // Update last activity time

/**
 * Set a session variable
 */
function session_set($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Get a session variable
 */
function session_get($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

/**
 * Check if a session variable exists
 */
function session_has($key) {
    return isset($_SESSION[$key]);
}

/**
 * Remove a session variable
 */
function session_remove($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
        return true;
    }
    return false;
}

/**
 * Destroy the current session
 */
function session_destroy_all() {
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
}

/**
 * Set a flash message
 */
function set_flash($key, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][$key] = $message;
}

/**
 * Get and remove a flash message
 */
function get_flash($key) {
    if (isset($_SESSION['flash_messages'][$key])) {
        $message = $_SESSION['flash_messages'][$key];
        unset($_SESSION['flash_messages'][$key]);
        return $message;
    }
    return null;
}

/**
 * Check if a flash message exists
 */
function has_flash($key) {
    return !empty($_SESSION['flash_messages'][$key]);
}

/**
 * Get all flash messages
 */
function get_all_flash() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Set old form input data
 */
function set_old_input($data) {
    $_SESSION['old_input'] = $data;
}

/**
 * Get old form input data
 */
function old_input($key, $default = '') {
    return $_SESSION['old_input'][$key] ?? $default;
}

/**
 * Clear old form input data
 */
function clear_old_input() {
    unset($_SESSION['old_input']);
}

// Initialize old input from POST data if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    set_old_input($_POST);
}

// CSRF Protection
if (!function_exists('csrf_token')) {
    /**
     * Generate a CSRF token
     */
    function csrf_token() {
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field
     */
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('verify_csrf_token')) {
    /**
     * Verify the CSRF token
     */
    function verify_csrf_token($token = null) {
        if (empty($token)) {
            $token = $_POST['_token'] ?? $_GET['_token'] ?? null;
        }
        
        if (empty($token) || empty($_SESSION['_token']) || !hash_equals($_SESSION['_token'], $token)) {
            if (is_ajax()) {
                http_response_code(419); // CSRF token mismatch
                exit('CSRF token validation failed');
            }
            
            // Log CSRF token validation failure
            error_log('CSRF token validation failed. Expected: ' . ($_SESSION['_token'] ?? 'none') . ', Got: ' . $token);
            
            // For non-AJAX requests, redirect back with error
            set_flash('error', 'Security token has expired. Please try again.');
            
            // If this was a POST request, redirect back to the form
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/');
                exit();
            }
            
            return false;
        }
        
        return true;
    }
}

// Check for CSRF token on POST, PUT, PATCH, DELETE requests
$httpMethod = $_SERVER['REQUEST_METHOD'];
if (in_array($httpMethod, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
    // Skip CSRF check for these paths (e.g., webhook endpoints)
    $excludedPaths = ['/api/webhooks/'];
    $shouldCheckCsrf = true;
    
    foreach ($excludedPaths as $path) {
        if (strpos($_SERVER['REQUEST_URI'], $path) === 0) {
            $shouldCheckCsrf = false;
            break;
        }
    }
    
    if ($shouldCheckCsrf && !verify_csrf_token()) {
        // The verify_csrf_token function will handle the response
        exit();
    }
}

// Rate limiting (basic implementation)
if (!function_exists('rate_limit')) {
    /**
     * Rate limit requests
     * 
     * @param string $key Unique identifier for the rate limit
     * @param int $maxAttempts Maximum number of attempts allowed
     * @param int $decayMinutes Time in minutes until the rate limit resets
     * @return bool True if the request is allowed, false if rate limited
     */
    function rate_limit($key, $maxAttempts = 60, $decayMinutes = 1) {
        $key = 'rate_limit:' . $key;
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'expires_at' => time() + ($decayMinutes * 60)
            ];
        }
        
        // Reset if expired
        if (time() > $_SESSION[$key]['expires_at']) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'expires_at' => time() + ($decayMinutes * 60)
            ];
        }
        
        // Increment attempts
        $_SESSION[$key]['attempts']++;
        
        // Check if rate limited
        if ($_SESSION[$key]['attempts'] > $maxAttempts) {
            http_response_code(429); // Too Many Requests
            header('Retry-After: ' . ($_SESSION[$key]['expires_at'] - time()));
            return false;
        }
        
        return true;
    }
}
