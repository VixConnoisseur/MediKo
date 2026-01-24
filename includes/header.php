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
    <style>
        /* Header Widgets Styles */
        .time-widget {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .notification-btn {
            position: relative;
            background: none;
            border: none;
            padding: 0.5rem;
            border-radius: 50%;
            transition: background-color 0.2s ease;
        }
        
        .notification-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #EF4444;
            color: white;
            font-size: 0.625rem;
            font-weight: 600;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 16px;
            text-align: center;
            line-height: 1;
        }
        
        .user-widget {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            text-decoration: none;
        }
        
        .user-widget:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3B82F6, #1D4ED8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            color: white;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        
        .user-name {
            color: white;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .user-role {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.75rem;
        }
        
        .user-menu-btn {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.75rem;
            transition: color 0.2s ease;
        }
        
        .user-widget:hover .user-menu-btn {
            color: white;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .user-info {
                display: none;
            }
            
            .user-widget {
                padding: 0.5rem;
            }
            
            .time-widget span {
                display: none;
            }
            
            .time-widget {
                font-size: 1rem;
            }
        }
        
        /* Notification Dropdown Styles */
        .notification-dropdown {
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            animation: slideInDown 0.3s ease;
        }
        
        .notification-header {
            background: var(--bg-surface);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .notification-item {
            transition: var(--transition);
        }
        
        .notification-item.unread {
            background: var(--primary-light);
            border-left: 3px solid var(--primary);
        }
        
        .notification-item.read {
            opacity: 0.8;
        }
        
        .notification-item:hover {
            background: var(--gray-50);
        }
        
        .notification-icon .icon-wrapper {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            color: white;
        }
        
        .icon-wrapper-success {
            background: var(--success);
        }
        
        .icon-wrapper-primary {
            background: var(--primary);
        }
        
        .icon-wrapper-info {
            background: var(--info);
        }
        
        .icon-wrapper-secondary {
            background: var(--gray-500);
        }
        
        .notification-footer {
            background: var(--bg-surface);
            position: sticky;
            bottom: 0;
            z-index: 10;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
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
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- Time Widget -->
                    <li class="nav-item">
                        <div class="nav-link text-white time-widget">
                            <i class="far fa-clock me-2"></i>
                            <span id="current-time">Loading...</span>
                        </div>
                    </li>
                    
                    <!-- Notification Bell -->
                    <li class="nav-item dropdown">
                        <button class="btn nav-link text-white notification-btn" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="far fa-bell"></i>
                            <span class="notification-badge" id="notificationCount">3</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="min-width: 320px; max-width: 400px;">
                            <li class="notification-header">
                                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                    <h6 class="mb-0 fw-bold">Notifications</h6>
                                    <button class="btn btn-sm btn-link text-muted p-0" onclick="markAllAsRead()">
                                        <small>Mark all as read</small>
                                    </button>
                                </div>
                            </li>
                            <li class="notification-list" style="max-height: 300px; overflow-y: auto;">
                                <!-- Recent User Registration -->
                                <div class="notification-item unread" data-id="1">
                                    <div class="d-flex p-3 border-bottom hover-bg">
                                        <div class="notification-icon me-3">
                                            <div class="icon-wrapper icon-wrapper-success">
                                                <i class="fas fa-user-plus"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-medium">New User Registration</div>
                                                    <div class="text-muted small">John Doe just registered on the platform</div>
                                                </div>
                                                <button class="btn btn-sm btn-link text-muted p-0" onclick="markAsRead(1)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="text-muted small mt-1">
                                                <i class="far fa-clock me-1"></i>2 minutes ago
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- New Appointment -->
                                <div class="notification-item unread" data-id="2">
                                    <div class="d-flex p-3 border-bottom hover-bg">
                                        <div class="notification-icon me-3">
                                            <div class="icon-wrapper icon-wrapper-primary">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-medium">New Appointment Scheduled</div>
                                                    <div class="text-muted small">Dr. Smith has a new appointment tomorrow</div>
                                                </div>
                                                <button class="btn btn-sm btn-link text-muted p-0" onclick="markAsRead(2)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="text-muted small mt-1">
                                                <i class="far fa-clock me-1"></i>15 minutes ago
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- System Update -->
                                <div class="notification-item unread" data-id="3">
                                    <div class="d-flex p-3 border-bottom hover-bg">
                                        <div class="notification-icon me-3">
                                            <div class="icon-wrapper icon-wrapper-info">
                                                <i class="fas fa-info-circle"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-medium">System Update Available</div>
                                                    <div class="text-muted small">Version 2.1.0 is ready to install</div>
                                                </div>
                                                <button class="btn btn-sm btn-link text-muted p-0" onclick="markAsRead(3)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="text-muted small mt-1">
                                                <i class="far fa-clock me-1"></i>1 hour ago
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Older Notification -->
                                <div class="notification-item read" data-id="4">
                                    <div class="d-flex p-3 hover-bg">
                                        <div class="notification-icon me-3">
                                            <div class="icon-wrapper icon-wrapper-secondary">
                                                <i class="fas fa-database"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-medium">Backup Completed</div>
                                                    <div class="text-muted small">System backup was completed successfully</div>
                                                </div>
                                                <button class="btn btn-sm btn-link text-muted p-0" onclick="removeNotification(4)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="text-muted small mt-1">
                                                <i class="far fa-clock me-1"></i>Yesterday at 10:30 PM
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="notification-footer">
                                <div class="p-2 text-center border-top">
                                    <a href="/bsit3a_guasis/mediko/pages/admin/notifications.php" class="btn btn-sm btn-link text-primary">
                                        View all notifications
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- User Profile Widget -->
                    <li class="nav-item dropdown">
                        <div class="user-widget dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)); ?>
                            </div>
                            <div class="user-info">
                                <div class="user-name"><?php echo htmlspecialchars($currentUser['full_name'] ?? 'Admin'); ?></div>
                                <div class="user-role">Administrator</div>
                            </div>
                            <i class="fas fa-chevron-down user-menu-btn"></i>
                        </div>
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
            <script>
                // Update time widget
                function updateTimeWidget() {
                    const now = new Date();
                    const options = {
                        weekday: 'short',
                        month: 'short',
                        day: 'numeric',
                        hour: 'numeric',
                        minute: 'numeric',
                        hour12: true
                    };
                    const timeElement = document.getElementById('current-time');
                    if (timeElement) {
                        timeElement.textContent = now.toLocaleString('en-US', options);
                    }
                }
                
                // Notification management functions
                function markAsRead(notificationId) {
                    const notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
                    if (notificationItem) {
                        notificationItem.classList.remove('unread');
                        notificationItem.classList.add('read');
                        updateNotificationCount();
                        showNotification('Notification', 'Marked as read', 'success');
                    }
                }
                
                function removeNotification(notificationId) {
                    const notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
                    if (notificationItem) {
                        notificationItem.remove();
                        updateNotificationCount();
                        showNotification('Notification', 'Removed', 'info');
                    }
                }
                
                function markAllAsRead() {
                    const unreadNotifications = document.querySelectorAll('.notification-item.unread');
                    unreadNotifications.forEach(item => {
                        item.classList.remove('unread');
                        item.classList.add('read');
                    });
                    updateNotificationCount();
                    showNotification('Notifications', 'All marked as read', 'success');
                }
                
                function updateNotificationCount() {
                    const unreadCount = document.querySelectorAll('.notification-item.unread').length;
                    const badge = document.getElementById('notificationCount');
                    if (badge) {
                        if (unreadCount > 0) {
                            badge.textContent = unreadCount;
                            badge.style.display = 'block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                }
                
                // Initialize notification count on page load
                document.addEventListener('DOMContentLoaded', function() {
                    updateNotificationCount();
                });
                
                // Simple notification system
                function showNotification(title, message, type = 'info') {
                    // Remove existing notifications
                    const existingNotifications = document.querySelectorAll('.notification-toast');
                    existingNotifications.forEach(n => n.remove());
                    
                    // Create notification element
                    const notification = document.createElement('div');
                    notification.className = 'notification-toast';
                    notification.innerHTML = `
                        <div class="notification-content">
                            <strong>${title}</strong>
                            <div>${message}</div>
                        </div>
                    `;
                    
                    // Add styles
                    notification.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: white;
                        border-left: 4px solid ${type === 'info' ? '#3B82F6' : '#10B981'};
                        padding: 1rem;
                        border-radius: 8px;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                        z-index: 9999;
                        animation: slideInRight 0.3s ease;
                        min-width: 250px;
                    `;
                    
                    // Add to page
                    document.body.appendChild(notification);
                    
                    // Auto-remove after 3 seconds
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                }
                
                // Initialize time widget
                updateTimeWidget();
                setInterval(updateTimeWidget, 60000);
            </script>