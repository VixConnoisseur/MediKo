<?php
// Test file for the uploadable profile picture functionality
require_once __DIR__ . '/includes/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Uploadable Profile Picture - MediKo</title>
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
            max-width: 800px; 
            margin: 0 auto; 
        }
        .demo-header { 
            text-align: center; 
            margin-bottom: 2rem; 
            color: #333; 
        }
        .sidebar-preview { 
            background: linear-gradient(135deg, #1e293b, #334155); 
            color: white; 
            padding: 2rem; 
            border-radius: 12px; 
            margin: 2rem 0; 
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1); 
        }
        .profile-upload-container {
            position: relative;
            display: inline-block;
        }
        .profile-upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }
        .profile-upload-container:hover .profile-upload-overlay {
            opacity: 1;
        }
        .profile-upload-overlay i {
            color: white;
            font-size: 1.2rem;
        }
        .sidebar-profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3B82F6, #2563EB);
            color: white;
            font-size: 2rem;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .sidebar-profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
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
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-left: 4px solid #2563eb;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideInRight 0.3s ease;
            min-width: 300px;
            font-family: inherit;
        }
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .validation-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }
        .validation-info h6 {
            color: #0369a1;
            margin-bottom: 0.5rem;
        }
        .validation-info ul {
            margin: 0;
            padding-left: 1.2rem;
            color: #0c4a6e;
        }
    </style>
</head>
<body>
    <div class='demo-container'>
        <div class='demo-header'>
            <h1><i class='fas fa-camera me-2'></i>Uploadable Profile Picture</h1>
            <p class='lead'>Interactive profile picture upload with preview and validation</p>
        </div>

        <!-- Sidebar Profile Preview -->
        <div class='sidebar-preview'>
            <div class='text-center py-3 py-md-4 border-secondary border-bottom px-2'>
                <div class='d-flex flex-column align-items-center'>
                    <div class='mb-2 position-relative'>
                        <div class='profile-upload-container' onclick='document.getElementById(\"profileImageUpload\").click()'>
                            <div class='rounded-circle d-flex align-items-center justify-content-center border border-2 mx-auto sidebar-profile-avatar position-relative overflow-hidden'>
                                <span class='fw-bold' id='avatarInitial'>A</span>
                            </div>
                            <div class='profile-upload-overlay'>
                                <i class='fas fa-camera'></i>
                            </div>
                        </div>
                        <input type='file' 
                               id='profileImageUpload' 
                               name='profile_image' 
                               accept='image/*' 
                               style='display: none;' 
                               onchange='handleProfileImageUpload(event)'>
                    </div>
                    <div class='fw-medium text-truncate w-100 px-2 sidebar-profile-name'>
                        Admin User
                    </div>
                    <small class='text-muted'>Click avatar to change photo</small>
                </div>
            </div>
        </div>

        <div class='validation-info'>
            <h6><i class='fas fa-info-circle me-2'></i>Upload Requirements</h6>
            <ul>
                <li>Supported formats: JPEG, PNG, GIF, WebP</li>
                <li>Maximum file size: 5MB</li>
                <li>Recommended dimensions: Square (1:1 ratio)</li>
                <li>Click on the avatar to upload a new picture</li>
            </ul>
        </div>

        <div class='feature-grid'>
            <div class='feature-card'>
                <h3><i class='fas fa-camera me-2'></i>Click to Upload</h3>
                <p>Simply click on the profile avatar to open the file selection dialog</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-eye me-2'></i>Instant Preview</h3>
                <p>See your new profile picture immediately after selection</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-shield-alt me-2'></i>File Validation</h3>
                <p>Automatic validation for file type and size with error handling</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-sync me-2'></i>Hover Effect</h3>
                <p>Camera icon appears on hover to indicate upload functionality</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-history me-2'></i>Activity Logging</h3>
                <p>All profile picture changes are logged for audit purposes</p>
            </div>
            
            <div class='feature-card'>
                <h3><i class='fas fa-mobile-alt me-2'></i>Responsive Design</h3>
                <p>Works seamlessly on desktop and mobile devices</p>
            </div>
        </div>

        <div style='text-align: center; margin-top: 3rem;'>
            <h2>✨ Profile Upload Ready!</h2>
            <p style='color: #666; margin-bottom: 2rem;'>Try clicking the avatar above to upload a profile picture</p>
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
        // Profile Image Upload Handler
        function handleProfileImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showNotification('Invalid File', 'Please select a valid image file (JPEG, PNG, GIF, or WebP)', 'danger');
                event.target.value = '';
                return;
            }
            
            // Validate file size (max 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                showNotification('File Too Large', 'Please select an image smaller than 5MB', 'danger');
                event.target.value = '';
                return;
            }
            
            // Show loading notification
            showNotification('Uploading', 'Uploading profile picture...', 'info');
            
            // Create FormData for file upload
            const formData = new FormData();
            formData.append('profile_image', file);
            formData.append('user_id', '1');
            formData.append('action', 'upload_profile_image');
            
            // Simulate upload process
            const reader = new FileReader();
            reader.onload = function(e) {
                // Update the avatar preview immediately
                const avatarContainer = document.querySelector('.sidebar-profile-avatar');
                const avatarInitial = document.getElementById('avatarInitial');
                
                if (avatarContainer && avatarInitial) {
                    // Create or update the image element
                    let imgElement = avatarContainer.querySelector('img');
                    if (!imgElement) {
                        imgElement = document.createElement('img');
                        imgElement.alt = 'Profile';
                        imgElement.className = 'w-100 h-100 object-fit-cover';
                        imgElement.style.cssText = 'position: absolute; top: 0; left: 0; border-radius: 50%;';
                        avatarContainer.appendChild(imgElement);
                    }
                    
                    imgElement.src = e.target.result;
                    avatarInitial.style.display = 'none';
                    
                    // Simulate server upload completion
                    setTimeout(() => {
                        showNotification('Upload Successful', 'Your profile picture has been updated successfully', 'success');
                        console.log('Activity Logged: profile_image_updated');
                    }, 1500);
                }
            };
            
            reader.readAsDataURL(file);
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
            
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border-left: 4px solid ${colors[type] || colors.info};
                padding: 1rem;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                z-index: 9999;
                animation: slideInRight 0.3s ease;
                min-width: 300px;
                font-family: inherit;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>";
?>
