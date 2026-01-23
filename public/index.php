<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Basic routing
$page = $_GET['page'] ?? 'home';

// Include the appropriate controller
$controllerFile = __DIR__ . '/../app/controllers/' . $page . '.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
} else {
    // 404 page
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . '/../app/views/404.php';
    exit;
}