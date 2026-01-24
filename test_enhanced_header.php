<?php
// Test file for the enhanced page header
require_once __DIR__ . '/includes/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Enhanced Page Header - MediKo</title>
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
        .header-preview { 
            background: linear-gradient(135deg, #2563eb, #1d4ed8); 
            color: white; 
            padding: 2rem; 
            border-radius: 0 0 1rem 1rem; 
            margin: 2rem 0; 
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1); 
            overflow: hidden; 
            position: relative; 
        }
        .header-pattern { 
            position: absolute; 
            top: 0; 
            left: 0; 
            right: 0; 
            bottom: 0; 
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%), 
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%), 
                radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.03) 0%, transparent 50%); 
            opacity: 0.8; 
        }
        .header-content { 
            position: relative; 
            z-index: 10; 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            gap: 2rem; 
        } 
        .header-left { 
            flex: 1; 
        } 
        .header-welcome { 
            margin-bottom: 1rem; 
        } 
        .welcome-label { 
            display: block; 
            font-size: 0.875rem; 
            color: rgba(255, 255, 255, 0.9); 
            margin-bottom: 0.5rem; 
            font-weight: 500; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        } 
        .page-title-modern { 
            font-size: 2.5rem; 
            font-weight: 700; 
            margin: 0; 
            color: white; 
            line-height: 1.2; 
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
        } 
        .header-description { 
            margin-top: 1rem; 
        } 
        .header-subtitle { 
            font-size: 1rem; 
            color: rgba(255, 255, 255, 0.9); 
            margin: 0 0 1rem 0; 
            display: flex; 
            align-items: center; 
        } 
        .header-stats-mini { 
            display: flex; 
            gap: 2rem; 
            margin-top: 1rem; 
        } 
        .mini-stat-item { 
            text-align: center; 
            background: rgba(255, 255, 255, 0.1); 
            padding: 0.75rem 1rem; 
            border-radius: 0.75rem; 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.2); 
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1); 
        } 
        .mini-stat-item:hover { 
            background: rgba(255, 255, 255, 0.15); 
            transform: translateY(-2px); 
        } 
        .mini-stat-value { 
            display: block; 
            font-size: 1.5rem; 
            font-weight: 700; 
            color: white; 
            line-height: 1.2; 
        } 
        .mini-stat-label { 
            font-size: 0.75rem; 
            color: rgba(255, 255, 255, 0.8); 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            margin-top: 0.25rem; 
        } 
        .header-right { 
            display: flex; 
            align-items: flex-start; 
        } 
        .header-actions { 
            display: flex; 
            gap: 1rem; 
        } 
        .header-action-btn { 
            padding: 0.75rem 1.5rem; 
            border-radius: 50px; 
            font-weight: 600; 
            font-size: 0.875rem; 
            border: none; 
            cursor: pointer; 
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1); 
            display: flex; 
            align-items: center; 
            text-decoration: none; 
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); 
        } 
        .header-action-btn.primary { 
            background: #16a34a; 
            color: white; 
        } 
        .header-action-btn.primary:hover { 
            background: #15803d; 
            transform: translateY(-2px); 
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1); 
        } 
        .header-action-btn.secondary { 
            background: rgba(255, 255, 255, 0.2); 
            color: white; 
            border: 2px solid rgba(255, 255, 255, 0.3); 
        } 
        .header-action-btn.secondary:hover { 
            background: rgba(255, 255, 255, 0.3); 
            transform: translateY(-2px); 
        } 
        .header-progress { 
            background: rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(10px); 
            border-top: 1px solid rgba(255, 255, 255, 0.2); 
            padding: 1rem 0; 
            position: relative; 
            z-index: 10; 
            margin-top: 1rem; 
        } 
        .progress-info { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 0.5rem; 
            padding: 0 2rem; 
        } 
        .progress-label { 
            font-size: 0.875rem; 
            font-weight: 500; 
            color: rgba(255, 255, 255, 0.9); 
        } 
        .progress-percentage { 
            font-size: 0.875rem; 
            font-weight: 600; 
            color: white; 
        } 
        .progress { 
            height: 8px; 
            background: rgba(255, 255, 255, 0.2); 
            border-radius: 50px; 
            overflow: hidden; 
            margin: 0 2rem; 
        } 
        .progress-bar { 
            transition: width 0.3s ease; 
            border-radius: 50px; 
            background: #16a34a; 
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
            <h1><i class='fas fa-palette me-2'></i>Enhanced Page Header</h1>
            <p class='lead'>Modern and aesthetic design following system color scheme</p>
        </div>

        <!-- Header Preview -->
        <div class='header-preview'>
            <div class='header-pattern'></div>
            
            <div class='header-content'>
                <div class='header-left'>
                    <div class='header-welcome'>
                        <span class='welcome-label'>Welcome back,</span>
                        <h1 class='page-title-modern'>Admin User</h1>
                    </div>
                    <div class='header-description'>
                        <p class='header-subtitle'>
                            <i class='fas fa-chart-line me-2'></i>
                            Here's what's happening with your system today
                        </p>
                        <div class='header-stats-mini'>
                            <div class='mini-stat-item'>
                                <span class='mini-stat-value'>150</span>
                                <span class='mini-stat-label'>Total Users</span>
                            </div>
                            <div class='mini-stat-item'>
                                <span class='mini-stat-value'>25</span>
                                <span class='mini-stat-label'>Appointments</span>
                            </div>
                            <div class='mini-stat-item'>
                                <span class='mini-stat-value'>12</span>
                                <span class='mini-stat-label'>Activities</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class='header-right'>
                    <div class='header-actions'>
                        <button class='header-action-btn primary' onclick='showDemoNotification()'>
                            <i class='fas fa-plus me-2'></i>
                            Add User
                        </button>
                        <button class='header-action-btn secondary' onclick='showDemoNotification()'>
                            <i class='fas fa-download me-2'></i>
                            Backup
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Progress Indicator -->
            <div class='header-progress'>
                <div class='progress-info'>
                    <span class='progress-label'>System Performance</span>
                    <span class='progress-percentage'>95%</span>
                </div>
                <div class='progress'>
                    <div class='progress-bar progress-bar-striped progress-bar-animated' style='width: 95%; background: #16a34a;'></div>
                </div>
            </div>
        </div>

        <div class='feature-grid'>
            <div class='feature-card'>
                <h3><i class='fas fa-paint-brush me-2'></i>Modern Design</h3>
                <p>Beautiful gradient background with subtle pattern overlay and glassmorphism effects</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-chart-line me-2'></i>Mini Statistics</h3>
                <p>Quick overview of key metrics displayed in glassmorphic cards</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-bolt me-2'></i>Quick Actions</h3>
                <p>Interactive buttons for common tasks with hover effects and animations</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-tachometer-alt me-2'></i>Progress Bar</h3>
                <p>Animated progress indicator showing system performance status</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-mobile-alt me-2'></i>Responsive Design</h3>
                <p>Fully responsive layout that adapts to all screen sizes</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-palette me-2'></i>System Colors</h3>
                <p>Strictly follows the established color scheme of the system</p>
            </div>
        </div>

        <div style='text-align: center; margin-top: 3rem;'>
            <h2>✨ Enhanced Header Complete!</h2>
            <p style='color: #666; margin-bottom: 2rem;'>Modern, aesthetic header design that preserves the system UI</p>
            <a href='/bsit3a_guasis/mediko/pages/admin/dashboard.php' class='demo-btn'>
                <i class='fas fa-eye me-2'></i>View Dashboard
            </a>
            <a href='javascript:history.back()' class='demo-btn'>
                <i class='fas fa-arrow-left me-2'></i>Go Back
            </a>
        </div>
    </div>

    <script>
        // Demo notification
        function showDemoNotification() {
            alert('Enhanced Header: This is a demo of the new modern header design!');
        }
        
        // Add hover effects to mini stats
        document.querySelectorAll('.mini-stat-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Animate progress bar on load
        setTimeout(() => {
            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                progressBar.style.width = '0%';
                setTimeout(() => {
                    progressBar.style.width = '95%';
                }, 100);
            }
        }, 500);
    </script>
</body>
</html>";
?>
