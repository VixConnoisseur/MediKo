<?php
/**
 * Helper Functions
 * 
 * Contains various utility and helper functions used throughout the application.
 */

/**
 * Escape output to prevent XSS attacks
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a different page
 */
function redirect($url, $statusCode = 303) {
    if (!headers_sent()) {
        header('Location: ' . $url, true, $statusCode);
    } else {
        echo "<script>window.location.href='{$url}';</script>";
    }
    exit();
}

/**
 * Get the current URL
 */
function currentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Get the base URL of the application
 */
function baseUrl($path = '') {
    $base = rtrim(APP_URL, '/');
    $path = ltrim($path, '/');
    return $base . ($path ? '/' . $path : '');
}

/**
 * Get the asset URL
 */
function asset($path) {
    return baseUrl('assets/' . ltrim($path, '/'));
}

/**
 * Get the current date and time in MySQL format
 */
function now() {
    return date('Y-m-d H:i:s');
}

/**
 * Format a date
 */
function format_date($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) {
        return '';
    }
    $date = is_numeric($date) ? $date : strtotime($date);
    return date($format, $date);
}

/**
 * Format a time difference in a human-readable format
 */
function time_ago($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate weeks from days
    $weeks = floor($diff->d / 7);
    $days = $diff->d % 7;

    $parts = [];
    
    // Add years
    if ($diff->y > 0) {
        $parts[] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
    }
    
    // Add months
    if ($diff->m > 0) {
        $parts[] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
    }
    
    // Add weeks if any
    if ($weeks > 0) {
        $parts[] = $weeks . ' week' . ($weeks > 1 ? 's' : '');
    }
    
    // Add remaining days
    if ($days > 0) {
        $parts[] = $days . ' day' . ($days > 1 ? 's' : '');
    }
    
    // If we don't have any parts yet, check for hours, minutes, seconds
    if (empty($parts)) {
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        }
        
        if ($diff->i > 0) {
            $parts[] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }
        
        if (empty($parts) && $diff->s > 0) {
            $parts[] = $diff->s . ' second' . ($diff->s != 1 ? 's' : '');
        }
    }

    if (!$full) {
        $parts = array_slice($parts, 0, 1);
    }
    return $parts ? implode(', ', $parts) . ' ago' : 'just now';
}

/**
 * Get the client's IP address
 */
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Generate a random string
 */
function random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Validate an email address
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate a date
 */
function is_valid_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Format a number as currency
 */
function format_currency($amount, $currency = 'USD') {
    return number_format($amount, 2) . ' ' . $currency;
}

/**
 * Truncate a string to a specified length
 */
function truncate($string, $length = 100, $append = '...') {
    if (mb_strlen($string) <= $length) {
        return $string;
    }
    return rtrim(mb_substr($string, 0, $length, 'UTF-8')) . $append;
}

/**
 * Generate a URL-friendly slug from a string
 */
function slugify($text) {
    // Replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim
    $text = trim($text, '-');
    // Remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    // Lowercase
    $text = strtolower($text);
    return $text ?: 'n-a';
}

/**
 * Get the first error message from an array of errors
 */
function first_error($errors) {
    if (!is_array($errors) || empty($errors)) {
        return '';
    }
    return reset($errors);
}

/**
 * Get a configuration value
 */
function config($key, $default = null) {
    static $config = null;
    
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }
    
    return $config[$key] ?? $default;
}

/**
 * Generate a secure password hash
 */
function password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password against a hash
 */
function password_verify_hash($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if a string is JSON
 */
function is_json($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Convert a string to camelCase
 */
function camel_case($string) {
    return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string))));
}

/**
 * Convert a string to snake_case
 */
function snake_case($string) {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
}

/**
 * Include a view file with data
 */
function view($path, $data = []) {
    extract($data);
    $viewFile = __DIR__ . '/../views/' . ltrim($path, '/') . '.php';
    
    if (!file_exists($viewFile)) {
        throw new Exception("View [{$path}] not found.");
    }
    
    ob_start();
    include $viewFile;
    return ob_get_clean();
}

/**
 * Send a JSON response
 */
function json_response($data = null, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

/**
 * Check if the request is an AJAX request
 */
function is_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get the current request method
 */
function request_method() {
    return $_SERVER['REQUEST_METHOD'];
}

/**
 * Check if the request method matches
 */
function is_method($method) {
    return strtoupper($method) === $_SERVER['REQUEST_METHOD'];
}

/**
 * Get the current URL path
 */
function current_path() {
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
}

/**
 * Check if the current URL matches a pattern
 */
function is_url($path) {
    $current = trim(current_path(), '/');
    $path = trim($path, '/');
    
    if ($path === $current) {
        return true;
    }
    
    // Handle wildcard patterns
    $pattern = preg_quote($path, '/');
    $pattern = str_replace('\*', '.*', $pattern);
    
    return (bool) preg_match("/^{$pattern}$/i", $current);
}

/**
 * Add active class to the current navigation item
 */
function active($path, $class = 'active') {
    return is_url($path) ? $class : '';
}

/**
 * Generate a URL for a route
 */
function route($name, $params = []) {
    // This is a simple implementation. In a real app, you would use a router.
    $routes = [
        'home' => '/',
        'login' => '/auth/login',
        'register' => '/auth/register',
        'logout' => '/auth/logout',
        'dashboard' => '/user/dashboard',
        'admin.dashboard' => '/admin/dashboard',
        // Add more routes as needed
    ];
    
    if (!isset($routes[$name])) {
        throw new Exception("Route [{$name}] not found.");
    }
    
    $url = $routes[$name];
    
    // Replace route parameters
    foreach ($params as $key => $value) {
        $url = str_replace("{{$key}}", $value, $url);
    }
    
    return baseUrl(ltrim($url, '/'));
}

/**
 * Check if the current user is authenticated
 */
function auth_check() {
    global $auth;
    return $auth->isLoggedIn();
}

/**
 * Check if the current user is a guest
 */
function guest() {
    return !auth_check();
}

/**
 * Get the authenticated user
 */
function auth_user() {
    global $auth;
    return $auth->getCurrentUser();
}

/**
 * Check if the current user has a specific role
 */
function has_role($role) {
    global $rbac;
    return $rbac->hasRole($role);
}

/**
 * Check if the current user has a specific permission
 */
function can($permission) {
    global $rbac;
    return $rbac->hasPermission($permission);
}

/**
 * Require a specific permission
 */
function authorize($permission, $redirect = '/') {
    global $rbac;
    return $rbac->requirePermission($permission, $redirect);
}

// Register the autoloader for classes in the MediKo namespace
spl_autoload_register(function ($class) {
    $prefix = 'MediKo\\';
    $base_dir = __DIR__ . '/';
    
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
