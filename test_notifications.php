<?php
// Test file for the fully functional notification bell
require_once __DIR__ . '/includes/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fully Functional Notifications - MediKo</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' rel='stylesheet'>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f8fafc; 
            margin: 0; 
            padding: 2rem; 
        }
        .demo-container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .demo-header { 
            text-align: center; 
            margin-bottom: 2rem; 
            color: #333; 
        }
        .navbar-preview { 
            background: linear-gradient(135deg, #0d6efd, #0b5ed7); 
            padding: 1rem; 
            border-radius: 8px; 
            margin: 2rem 0; 
        }
        .navbar-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 100%;
        }
        .navbar-brand {
            color: white;
            text-decoration: none;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .nav-item {
            list-style: none;
        }
        .notification-btn {
            position: relative;
            background: none;
            border: none;
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            transition: background-color 0.2s ease;
            cursor: pointer;
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
        .notification-dropdown {
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            border: 1px solid #e2e8f0;
            animation: slideInDown 0.3s ease;
            min-width: 320px;
            max-width: 400px;
        }
        .notification-header {
            background: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .notification-item {
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .notification-item.unread {
            background: #eff6ff;
            border-left: 3px solid #2563eb;
        }
        .notification-item.read {
            opacity: 0.8;
        }
        .notification-item:hover {
            background: #f3f4f6;
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
            background: #16a34a;
        }
        .icon-wrapper-primary {
            background: #2563eb;
        }
        .icon-wrapper-info {
            background: #0284c7;
        }
        .icon-wrapper-secondary {
            background: #6b7280;
        }
        .notification-footer {
            background: white;
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
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        .feature-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #2563eb;
        }
        .demo-btn { 
            background: linear-gradient(135deg, #2563eb, #1d4ed8); 
            color: white; 
            border: none; 
            padding: 0.75rem 2rem; 
            border-radius: 50px; 
            font-weight: 600; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block; 
            transition: all 0.3s ease; 
            margin: 0.5rem; 
        } 
        .demo-btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4); 
        }
    </style>
</head>
<body>
    <div class='demo-container'>
        <div class='demo-header'>
            <h1><i class='fas fa-bell me-2'></i>Fully Functional Notification Bell</h1>
            <p class='lead'>Interactive dropdown with real notifications and management features</p>
        </div>

        <!-- Header Preview -->
        <div class='navbar-preview'>
            <div class='navbar-container'>
                <a class='navbar-brand' href='#'>
                    <i class='fas fa-hospital me-2'></i>
                    MediKo
                </a>
                
                <ul class='navbar-nav'>
                    <!-- Time Widget -->
                    <li class='nav-item'>
                        <div class='time-widget' style='color: white; display: flex; align-items: center; font-size: 0.875rem; font-weight: 500;'>
                            <i class='far fa-clock me-2'></i>
                            <span>Sat, Jan 24, 9:55 AM</span>
                        </div>
                    </li>
                    
                    <!-- Notification Bell -->
                    <li class='nav-item dropdown'>
                        <button class='notification-btn' href='#' id='notificationDropdown' role='button' data-bs-toggle='dropdown'>
                            <i class='far fa-bell'></i>
                            <span class='notification-badge' id='notificationCount'>3</span>
                        </button>
                        <ul class='dropdown-menu dropdown-menu-end notification-dropdown'>
                            <li class='notification-header'>
                                <div class='d-flex justify-content-between align-items-center p-3 border-bottom'>
                                    <h6 class='mb-0 fw-bold'>Notifications</h6>
                                    <button class='btn btn-sm btn-link text-muted p-0' onclick='markAllAsRead()'>
                                        <small>Mark all as read</small>
                                    </button>
                                </div>
                            </li>
                            <li class='notification-list' style='max-height: 300px; overflow-y: auto;'>
                                <!-- Recent User Registration -->
                                <div class='notification-item unread' data-id='1'>
                                    <div class='d-flex p-3 border-bottom hover-bg'>
                                        <div class='notification-icon me-3'>
                                            <div class='icon-wrapper icon-wrapper-success'>
                                                <i class='fas fa-user-plus'></i>
                                            </div>
                                        </div>
                                        <div class='flex-grow-1'>
                                            <div class='d-flex justify-content-between align-items-start'>
                                                <div>
                                                    <div class='fw-medium'>New User Registration</div>
                                                    <div class='text-muted small'>John Doe just registered on the platform</div>
                                                </div>
                                                <button class='btn btn-sm btn-link text-muted p-0' onclick='markAsRead(1)'>
                                                    <i class='fas fa-times'></i>
                                                </button>
                                            </div>
                                            <div class='text-muted small mt-1'>
                                                <i class='far fa-clock me-1'></i>2 minutes ago
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- New Appointment -->
                                <div class='notification-item unread' data-id='2'>
                                    <div class='d-flex p-3 border-bottom hover-bg'>
                                        <div class='notification-icon me-3'>
                                            <div class='icon-wrapper icon-wrapper-primary'>
                                                <i class='fas fa-calendar-check'></i>
                                            </div>
                                        </div>
                                        <div class='flex-grow-1'>
                                            <div class='d-flex justify-content-between align-items-start'>
                                                <div>
                                                    <div class='fw-medium'>New Appointment Scheduled</div>
                                                    <div class='text-muted small'>Dr. Smith has a new appointment tomorrow</div>
                                                </div>
                                                <button class='btn btn-sm btn-link text-muted p-0' onclick='markAsRead(2)'>
                                                    <i class='fas fa-times'></i>
                                                </button>
                                            </div>
                                            <div class='text-muted small mt-1'>
                                                <i class='far fa-clock me-1'></i>15 minutes ago
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- System Update -->
                                <div class='notification-item unread' data-id='3'>
                                    <div class='d-flex p-3 border-bottom hover-bg'>
                                        <div class='notification-icon me-3'>
                                            <div class='icon-wrapper icon-wrapper-info'>
                                                <i class='fas fa-info-circle'></i>
                                            </div>
                                        </div>
                                        <div class='flex-grow-1'>
                                            <div class='d-flex justify-content-between align-items-start'>
                                                <div>
                                                    <div class='fw-medium'>System Update Available</div>
                                                    <div class='text-muted small'>Version 2.1.0 is ready to install</div>
                                                </div>
                                                <button class='btn btn-sm btn-link text-muted p-0' onclick='markAsRead(3)'>
                                                    <i class='fas fa-times'></i>
                                                </button>
                                            </div>
                                            <div class='text-muted small mt-1'>
                                                <i class='far fa-clock me-1'></i>1 hour ago
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Older Notification -->
                                <div class='notification-item read' data-id='4'>
                                    <div class='d-flex p-3 hover-bg'>
                                        <div class='notification-icon me-3'>
                                            <div class='icon-wrapper icon-wrapper-secondary'>
                                                <i class='fas fa-database'></i>
                                            </div>
                                        </div>
                                        <div class='flex-grow-1'>
                                            <div class='d-flex justify-content-between align-items-start'>
                                                <div>
                                                    <div class='fw-medium'>Backup Completed</div>
                                                    <div class='text-muted small'>System backup was completed successfully</div>
                                                </div>
                                                <button class='btn btn-sm btn-link text-muted p-0' onclick='removeNotification(4)'>
                                                    <i class='fas fa-times'></i>
                                                </button>
                                            </div>
                                            <div class='text-muted small mt-1'>
                                                <i class='far fa-clock me-1'></i>Yesterday at 10:30 PM
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class='notification-footer'>
                                <div class='p-2 text-center border-top'>
                                    <a href='#' class='btn btn-sm btn-link text-primary'>
                                        View all notifications
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class='feature-grid'>
            <div class='feature-card'>
                <h3><i class='fas fa-bell me-2'></i>Interactive Dropdown</h3>
                <p>Click the notification bell to open a rich dropdown with detailed notifications</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-eye me-2'></i>Read/Unread States</h3>
                <p>Visual distinction between read and unread notifications with color coding</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-check me-2'></i>Mark as Read</h3>
                <p>Individual notifications can be marked as read with the X button</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-check-double me-2'></i>Mark All as Read</h3>
                <p>Bulk action to mark all unread notifications as read at once</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-trash me-2'></i>Remove Notifications</h3>
                <p>Individual notifications can be removed from the list</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-calculator me-2'></i>Dynamic Badge</h3>
                <p>Notification badge updates automatically based on unread count</p>
            </div>
        </div>

        <div style='text-align: center; margin-top: 3rem;'>
            <h2>✨ Fully Functional Notifications!</h2>
            <p style='color: #666; margin-bottom: 2rem;'>Interactive notification system with complete management features</p>
            <a href='/bsit3a_guasis/mediko/pages/admin/dashboard.php' class='demo-btn'>
                <i class='fas fa-eye me-2'></i>View Dashboard
            </a>
            <a href='javascript:history.back()' class='demo-btn'>
                <i class='fas fa-arrow-left me-2'></i>Go Back
            </a>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
    <script>
        // Notification management functions
        function markAsRead(notificationId) {
            const notificationItem = document.querySelector(`.notification-item[data-id=\"${notificationId}\"]`);
            if (notificationItem) {
                notificationItem.classList.remove('unread');
                notificationItem.classList.add('read');
                updateNotificationCount();
                showNotification('Notification', 'Marked as read', 'success');
            }
        }
        
        function removeNotification(notificationId) {
            const notificationItem = document.querySelector(`.notification-item[data-id=\"${notificationId}\"]`);
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
        
        // Simple notification system
        function showNotification(title, message, type = 'info') {
            const existingNotifications = document.querySelectorAll('.notification-toast');
            existingNotifications.forEach(n => n.remove());
            
            const notification = document.createElement('div');
            notification.className = 'notification-toast';
            notification.innerHTML = `
                <div class='notification-content'>
                    <strong>${title}</strong>
                    <div>${message}</div>
                </div>
            `;
            
            const colors = {
                'info': '#2563eb',
                'success': '#16a34a',
                'warning': '#ea580c',
                'danger': '#dc2626'
            };
            
            notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: white; border-left: 4px solid ' + (colors[type] || colors.info) + '; padding: 1rem; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); z-index: 9999; animation: slideInRight 0.3s ease; min-width: 250px; font-family: inherit;';
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // Initialize notification count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateNotificationCount();
        });
    </script>
</body>
</html>";
?>
