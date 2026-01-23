<?php
// Application settings
define('APP_NAME', 'MediKo');
define('APP_URL', 'http://localhost/bsit3a_guasis/mediko');
define('APP_DEBUG', true);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'mediko');
define('DB_USER', 'root');
define('DB_PASS', '');

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Manila');

// Application paths
define('APP_ROOT', dirname(__DIR__));
define('INCLUDES_PATH', __DIR__);
define('UPLOADS_PATH', APP_ROOT . '/uploads');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOADS_PATH) && is_writable(dirname(UPLOADS_PATH))) {
    mkdir(UPLOADS_PATH, 0755, true);
}

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    // Session settings must be set before session_start()
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.cookie_samesite', 'Lax');
    
    // Start the session
    session_start();
}

// Include required files
require_once 'Database.php';
require_once 'auth.php';
require_once 'functions.php';