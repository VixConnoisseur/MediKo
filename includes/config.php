<?php
/**
 * Main Configuration File
 * 
 * This file contains all the configuration settings for the application.
 */

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Application settings
define('APP_NAME', $_ENV['APP_NAME'] ?? 'MediKo');
define('APP_URL', rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/'));
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', 'mediko'); // Database name is fixed as 'mediko'
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Email configuration
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'smtp.sendgrid.net');
define('MAIL_PORT', $_ENV['MAIL_PORT'] ?? 587);
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? 'tls');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@mediko.com');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? APP_NAME);

// SMS configuration
define('TWILIO_SID', $_ENV['TWILIO_SID'] ?? '');
define('TWILIO_AUTH_TOKEN', $_ENV['TWILIO_AUTH_TOKEN'] ?? '');
define('TWILIO_PHONE_NUMBER', $_ENV['TWILIO_PHONE_NUMBER'] ?? '');

// Session configuration - must be set before session_start()
if (session_status() === PHP_SESSION_NONE) {
    // Only set these if session hasn't been started yet
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.gc_maxlifetime', '86400'); // 24 hours
    
    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax' // or 'Strict' or 'None'
    ]);
}

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('UTC');

// Application paths
define('APP_ROOT', dirname(__DIR__));
define('INCLUDES_PATH', __DIR__);
define('UPLOADS_PATH', APP_ROOT . '/uploads');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOADS_PATH)) {
    mkdir(UPLOADS_PATH, 0755, true);
}

// Set include path
set_include_path(
    get_include_path() . PATH_SEPARATOR . 
    INCLUDES_PATH . PATH_SEPARATOR .
    dirname(INCLUDES_PATH) . '/lib'
);

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'MediKo\\';
    $base_dir = INCLUDES_PATH . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Set error and exception handlers
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error = [
        'type' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ];
    
    // Log the error
    error_log(json_encode($error));
    
    if (APP_DEBUG) {
        echo "<pre>Error: " . print_r($error, true) . "</pre>";
    } else {
        // In production, show a generic error message
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        include INCLUDES_PATH . '/error_pages/500.php';
    }
    
    return true;
});

set_exception_handler(function($exception) {
    $error = [
        'type' => get_class($exception),
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ];
    
    // Log the exception
    error_log(json_encode($error));
    
    if (APP_DEBUG) {
        echo "<pre>Exception: " . print_r($error, true) . "</pre>";
    } else {
        // In production, show a generic error message
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        include INCLUDES_PATH . '/error_pages/500.php';
    }
});

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include helper functions
require_once 'functions.php';
require_once 'database.php';
require_once 'auth.php';
require_once 'roles.php';

// Initialize database connection
$db = new Database();
$db->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Initialize authentication
$auth = new Auth($db);
