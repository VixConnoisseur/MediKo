<?php
// Test file for the new header widgets
require_once __DIR__ . '/includes/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Header Widgets Test - MediKo</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' rel='stylesheet'>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f8f9fa; 
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
        /* Header Widgets Styles */
        .time-widget {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            font-weight: 500;
            color: white;
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
            border-left: 4px solid #0d6efd;
        }
        .demo-btn { 
            background: linear-gradient(135deg, #0d6efd, #0b5ed7); 
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
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.4); 
        }
    </style>
</head>
<body>
    <div class='demo-container'>
        <div class='demo-header'>
            <h1><i class='fas fa-palette me-2'></i>Header Widgets Implementation</h1>
            <p class='lead'>Modern widgets integrated into the main header.php file</p>
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
                        <div class='time-widget'>
                            <i class='far fa-clock me-2'></i>
                            <span id='demo-time'>Loading...</span>
                        </div>
                    </li>
                    
                    <!-- Notification Bell -->
                    <li class='nav-item'>
                        <button class='notification-btn' onclick='showDemoNotification()'>
                            <i class='far fa-bell'></i>
                            <span class='notification-badge'>3</span>
                        </button>
                    </li>
                    
                    <!-- User Profile Widget -->
                    <li class='nav-item'>
                        <div class='user-widget'>
                            <div class='user-avatar'>
                                A
                            </div>
                            <div class='user-info'>
                                <div class='user-name'>Admin User</div>
                                <div class='user-role'>Administrator</div>
                            </div>
                            <i class='fas fa-chevron-down user-menu-btn'></i>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class='feature-grid'>
            <div class='feature-card'>
                <h3><i class='fas fa-clock me-2'></i>Time Widget</h3>
                <p>Real-time clock display integrated into the main navbar with formatted date and time.</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-bell me-2'></i>Notification Bell</h3>
                <p>Interactive notification button with red badge counter showing unread notifications.</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-user me-2'></i>User Profile Widget</h3>
                <p>User avatar with name, role, and dropdown menu for profile actions.</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-paint-brush me-2'></i>Modern Design</h3>
                <p>Clean, modern design with glassmorphism effects and smooth transitions.</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-mobile-alt me-2'></i>Responsive Layout</h3>
                <p>Fully responsive design that adapts to mobile, tablet, and desktop screens.</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-code me-2'></i>Clean Implementation</h3>
                <p>Widgets integrated into header.php without background color changes.</p>
            </div>
        </div>

        <div style='text-align: center; margin-top: 3rem;'>
            <h2>✨ Header Widgets Successfully Integrated!</h2>
            <p style='color: #666; margin-bottom: 2rem;'>Modern widgets now available in the main header across all pages</p>
            <a href='/bsit3a_guasis/mediko/pages/admin/dashboard.php' class='demo-btn'>
                <i class='fas fa-eye me-2'></i>View Dashboard
            </a>
            <a href='javascript:history.back()' class='demo-btn'>
                <i class='fas fa-arrow-left me-2'></i>Go Back
            </a>
        </div>
    </div>

    <script>
        // Update time widget
        function updateDemoTime() {
            const now = new Date();
            const options = {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            };
            const timeElement = document.getElementById('demo-time');
            if (timeElement) {
                timeElement.textContent = now.toLocaleString('en-US', options);
            }
        }
        
        // Demo notification
        function showDemoNotification() {
            alert('Notifications: You have 3 new notifications');
        }
        
        // Initialize time widget
        updateDemoTime();
        setInterval(updateDemoTime, 60000);
    </script>
</body>
</html>";
?>
