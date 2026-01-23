<?php
// Include configuration first (this will handle session start)
require_once __DIR__ . '/config.php';

// Initialize database and auth
$db = Database::getInstance();
$auth = new Auth($db);

// Load system settings
$settingsQuery = $db->query("SELECT * FROM system_settings");
$settings = [];
foreach ($settingsQuery->fetchAll(PDO::FETCH_ASSOC) as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Apply settings
date_default_timezone_set($settings['timezone'] ?? 'UTC');

// Maintenance mode check
if (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == 1 && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    // Allow admins to bypass maintenance mode
    if (!isset($_SESSION[$auth->getSessionName()]['user_id']) || $_SESSION[$auth->getSessionName()]['role'] !== 'admin') {
        include __DIR__ . '/maintenance.php';
        exit();
    }
}

// Check if user is logged in
if (!isset($_SESSION[$auth->getSessionName()]['user_id'])) {
    // Only redirect if we're not already on the login page
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page != 'login.php') {
        header('Location: /bsit3a_guasis/mediko/login.php');
        exit();
    }
    return; // Stop further execution if we're redirecting
}

// Get current user data
$currentUser = $auth->getCurrentUser();

// Check if user is admin (only if we have a user)
if ($currentUser && $currentUser['role'] !== 'admin') {
    // Only redirect if we're not already on an error page
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page != 'unauthorized.php') {
        header('Location: /bsit3a_guasis/mediko/unauthorized.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo htmlspecialchars($settings['site_name'] ?? APP_NAME); ?></title>
    <?php if (isset($settings['favicon']) && !empty($settings['favicon'])): ?>
        <link rel="icon" href="/bsit3a_guasis/mediko/<?php echo htmlspecialchars($settings['favicon']); ?>" type="image/png">
    <?php endif; ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/bsit3a_guasis/mediko/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/bsit3a_guasis/mediko/pages/admin/dashboard.php">
                <?php if (isset($settings['site_logo']) && !empty($settings['site_logo'])): ?>
                    <img src="/bsit3a_guasis/mediko/<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="Site Logo" style="max-height: 30px;" class="me-2">
                <?php else: ?>
                    <i class="fas fa-hospital me-2"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($settings['site_name'] ?? APP_NAME); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($currentUser['full_name'] ?? 'Admin'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/bsit3a_guasis/mediko/pages/admin/profile.php"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/bsit3a_guasis/mediko/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">