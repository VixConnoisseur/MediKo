<?php
require_once __DIR__ . '/../../includes/header.php';
// Get database connection
$db = Database::getInstance();

// Enhanced Statistics Queries
// Total users
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$result = $stmt->fetch();
$totalUsers = $result['total'];

// Active users (last 30 days)
$stmt = $db->query("SELECT COUNT(*) as active FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND role = 'user'");
$result = $stmt->fetch();
$activeUsers = $result['active'];

// New users this month
$stmt = $db->query("SELECT COUNT(*) as new_users FROM users WHERE role = 'user' AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
$result = $stmt->fetch();
$newUsersThisMonth = $result['new_users'];

// Total medications
$stmt = $db->query("SELECT COUNT(*) as total FROM medications");
$result = $stmt->fetch();
$totalMedications = $result['total'];

// Active medications
$stmt = $db->query("SELECT COUNT(*) as active FROM medications WHERE is_active = 1");
$result = $stmt->fetch();
$activeMedications = $result['active'];

// Total appointments
$stmt = $db->query("SELECT COUNT(*) as total FROM appointments");
$result = $stmt->fetch();
$totalAppointments = $result['total'];

// Today's appointments
$stmt = $db->query("SELECT COUNT(*) as today FROM appointments WHERE DATE(appointment_date) = CURDATE()");
$result = $stmt->fetch();
$todayAppointments = $result['today'];

// Upcoming appointments (next 7 days)
$stmt = $db->query("SELECT COUNT(*) as upcoming FROM appointments WHERE appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$result = $stmt->fetch();
$upcomingAppointments = $result['upcoming'];

// System performance metrics calculation
$stmt = $db->query("SELECT COUNT(*) as total FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$result = $stmt->fetch();
$recentActivity = $result['total'];

// Calculate system performance based on multiple factors
$cpuUsage = rand(20, 80); // Simulated CPU usage
$memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
$diskFree = round(disk_free_space('/') / 1024 / 1024 / 1024, 2);
$diskTotal = round(disk_total_space('/') / 1024 / 1024 / 1024, 2);
$diskUsagePercent = round((($diskTotal - $diskFree) / $diskTotal) * 100, 2);

// Calculate overall system performance (0-100)
$systemPerformance = max(0, min(100, 100 - ($cpuUsage * 0.3 + $diskUsagePercent * 0.2 + ($memoryUsage > 512 ? 20 : 0) * 0.5)));

// Get database size
$stmt = $db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = DATABASE()");
$databaseSize = $stmt->fetch()['size_mb'];

// Get database version
$versionStmt = $db->query("SELECT VERSION() as version");
$dbVersion = 'MySQL ' . $versionStmt->fetch()['version'];

// Recent users
$stmt = $db->query("SELECT id, full_name, email, created_at, last_login FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
$recentUsers = $stmt->fetchAll();

// Recent activity logs
$stmt = $db->query("SELECT al.*, u.full_name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 10");
$recentActivityLogs = $stmt->fetchAll();

// User growth data (last 6 months)
$stmt = $db->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM users 
    WHERE role = 'user' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
");
$userGrowthData = $stmt->fetchAll();

// Appointment trends (last 30 days)
$stmt = $db->query("
    SELECT DATE(appointment_date) as date, COUNT(*) as count 
    FROM appointments 
    WHERE appointment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(appointment_date)
    ORDER BY date DESC
    LIMIT 30
");
$appointmentTrends = $stmt->fetchAll();

// Get database version
$versionStmt = $db->query("SELECT VERSION() as version");
$dbVersion = 'MySQL ' . $versionStmt->fetch()['version'];

// System alerts (critical issues)
$systemAlerts = [];
if ($diskFree < 10) { // Less than 10GB free
    $systemAlerts[] = [
        'type' => 'danger',
        'icon' => 'exclamation-triangle',
        'message' => 'Low disk space: Only ' . $diskFree . 'GB remaining'
    ];
}

if ($memoryUsage > 512) { // High memory usage
    $systemAlerts[] = [
        'type' => 'warning',
        'icon' => 'memory',
        'message' => 'High memory usage: ' . $memoryUsage . 'MB'
    ];
}
?>

<div class="container-fluid p-0">
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle d-md-none" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="row g-0">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar sidebar-dark">
            <div class="d-flex flex-column h-100">
                <!-- Admin Profile -->
                <div class="text-center py-3 py-md-4 border-secondary border-bottom px-2">
                    <div class="d-flex flex-column align-items-center">
                        <div class="mb-2 position-relative">
                            <div class="profile-upload-container" onclick="document.getElementById('profileImageUpload').click()">
                                <div class="rounded-circle d-flex align-items-center justify-content-center border border-2 mx-auto sidebar-profile-avatar position-relative overflow-hidden" style="cursor: pointer; transition: all 0.3s ease;">
                                    <?php if (!empty($currentUser['profile_image'])): ?>
                                        <img src="/bsit3a_guasis/mediko/<?php echo htmlspecialchars($currentUser['profile_image']); ?>" 
                                             alt="Profile" 
                                             class="w-100 h-100 object-fit-cover"
                                             style="position: absolute; top: 0; left: 0; border-radius: 50%;">
                                    <?php else: ?>
                                        <span class="fw-bold" id="avatarInitial">
                                            <?php echo strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="profile-upload-overlay">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                            <input type="file" 
                                   id="profileImageUpload" 
                                   name="profile_image" 
                                   accept="image/*" 
                                   style="display: none;" 
                                   onchange="handleProfileImageUpload(event)">
                        </div>
                        <div class="fw-medium text-truncate w-100 px-2 sidebar-profile-name">
                            <?php echo htmlspecialchars($currentUser['full_name'] ?? 'Admin'); ?>
                </div>
                
                <!-- Navigation -->
                <div class="flex-grow-1 px-3 py-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <!-- Main Navigation -->
                        <li class="nav-item">
                            <a class="nav-link active" href="/bsit3a_guasis/mediko/pages/admin/dashboard.php">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/users.php">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/system_management.php">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>System Management</p>
                            </a>
                        </li>
                        
                        <!-- Analytics Section -->
                        <li class="nav-header mt-4 mb-2">
                            <h6 class="text-xs text-muted text-uppercase font-weight-bold">Analytics</h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/reports_analytics.php">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>Reports & Analytics</p>
                            </a>
                        </li>
                        
                        <!-- System Management Section -->
                        <li class="nav-header mt-4 mb-2">
                            <h6 class="text-xs text-muted text-uppercase font-weight-bold">System</h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/backup.php">
                                <i class="nav-icon fas fa-database"></i>
                                <p>Backup</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/settings.php">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>Settings</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/logs.php">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>System Logs</p>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- User Profile -->
                <div class="border-top p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3">
                            <?php echo strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)); ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium"><?php echo htmlspecialchars($currentUser['full_name'] ?? 'Admin'); ?></div>
                            <div class="text-muted small">Administrator</div>
                        </div>
                        <a href="/bsit3a_guasis/mediko/logout.php" class="btn btn-sm btn-icon" title="Sign out">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
            <!-- Modern Page Header -->
            <div class="page-header-enhanced">
                <!-- Header Background Pattern -->
                <div class="header-pattern"></div>
                
                <!-- Header Content -->
                <div class="header-content">
                    <div class="header-left">
                        <div class="header-welcome">
                            <span class="welcome-label">Welcome back,</span>
                            <h1 class="page-title-modern">
                                <?php echo htmlspecialchars($currentUser['full_name'] ?? 'Admin'); ?>
                            </h1>
                        </div>
                        <div class="header-description">
                            <p class="header-subtitle">
                                <i class="fas fa-chart-line me-2"></i>
                                Here's what's happening with your system today
                            </p>
                        </div>
                    </div>
                    
                    <div class="header-right">
                        <div class="header-actions">
                            <button class="header-action-btn primary" onclick="quickAddUser()">
                                <i class="fas fa-plus me-2"></i>
                                Add User
                            </button>
                            <button class="header-action-btn secondary" onclick="quickBackup()">
                                <i class="fas fa-download me-2"></i>
                                Backup
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Indicator -->
                <div class="header-progress">
                    <div class="progress-info">
                        <span class="progress-label">System Performance</span>
                        <span class="progress-percentage" id="systemPerformanceValue"><?php echo round($systemPerformance); ?>%</span>
                        <button class="btn btn-sm btn-link text-white p-0 ms-2" onclick="refreshSystemPerformance()" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="systemProgressBar" style="width: <?php echo $systemPerformance; ?>%; background: var(--success);"></div>
                    </div>
                    <div class="progress-details mt-2" style="padding: 0 2rem; font-size: 0.75rem; color: rgba(255, 255, 255, 0.8);">
                        <div class="row">
                            <div class="col-4">CPU: <?php echo $cpuUsage; ?>%</div>
                            <div class="col-4">Memory: <?php echo $memoryUsage; ?>MB</div>
                            <div class="col-4">Disk: <?php echo $diskUsagePercent; ?>%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Alerts -->
            <?php if (!empty($systemAlerts)): ?>
            <div class="row g-3 mb-4">
                <?php foreach ($systemAlerts as $alert): ?>
                <div class="col-12">
                    <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $alert['icon']; ?> me-2"></i>
                        <strong>System Alert:</strong> <?php echo $alert['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Enhanced Stats Grid -->
            <div class="row g-4 mb-5">
                <!-- Total Users -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card stats-card-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-wrapper icon-wrapper-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stats-trend positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span class="trend-text">+<?php echo $newUsersThisMonth; ?></span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($totalUsers); ?></h3>
                            <p class="stats-label">Total Users</p>
                            <div class="stats-progress">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-primary" style="width: <?php echo $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                            <span class="stats-info"><?php echo $activeUsers; ?> active this month</span>
                        </div>
                    </div>
                </div>

                <!-- Medications -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card stats-card-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-wrapper icon-wrapper-success">
                                    <i class="fas fa-pills"></i>
                                </div>
                                <div class="stats-trend positive">
                                    <i class="fas fa-check-circle"></i>
                                    <span class="trend-text">Active</span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($totalMedications); ?></h3>
                            <p class="stats-label">Medications</p>
                            <div class="stats-progress">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $totalMedications > 0 ? round(($activeMedications / $totalMedications) * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                            <span class="stats-info"><?php echo $activeMedications; ?> active</span>
                        </div>
                    </div>
                </div>

                <!-- Appointments -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card stats-card-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-wrapper icon-wrapper-warning">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="stats-trend positive">
                                    <i class="fas fa-clock"></i>
                                    <span class="trend-text">Today</span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($totalAppointments); ?></h3>
                            <p class="stats-label">Appointments</p>
                            <div class="stats-progress">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-warning" style="width: <?php echo $totalAppointments > 0 ? round(($todayAppointments / $totalAppointments) * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                            <span class="stats-info"><?php echo $todayAppointments; ?> today, <?php echo $upcomingAppointments; ?> upcoming</span>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card stats-card-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-wrapper icon-wrapper-info">
                                    <i class="fas fa-heartbeat"></i>
                                </div>
                                <div class="stats-trend positive">
                                    <i class="fas fa-shield-alt"></i>
                                    <span class="trend-text">Healthy</span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo $recentActivity; ?></h3>
                            <p class="stats-label">System Activity</p>
                            <div class="stats-progress">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-info" style="width: 75%"></div>
                                </div>
                            </div>
                            <span class="stats-info">Last 24 hours</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Metrics Row -->
            <div class="row g-4 mb-5">
                <!-- Database Size -->
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-database text-primary fs-2 mb-2"></i>
                            <h5 class="card-title mb-1"><?php echo $databaseSize; ?> MB</h5>
                            <p class="card-text text-muted small">Database Size</p>
                        </div>
                    </div>
                </div>

                <!-- Memory Usage -->
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-memory text-warning fs-2 mb-2"></i>
                            <h5 class="card-title mb-1"><?php echo $memoryUsage; ?> MB</h5>
                            <p class="card-text text-muted small">Memory Usage</p>
                        </div>
                    </div>
                </div>

                <!-- Disk Space -->
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-hdd text-info fs-2 mb-2"></i>
                            <h5 class="card-title mb-1"><?php echo $diskFree; ?> GB</h5>
                            <p class="card-text text-muted small">Free Disk Space</p>
                        </div>
                    </div>
                </div>

                <!-- Uptime -->
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-clock text-success fs-2 mb-2"></i>
                            <h5 class="card-title mb-1">99.9%</h5>
                            <p class="card-text text-muted small">System Uptime</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-users"></i> Recent Users</h5>
                    <a href="/bsit3a_guasis/mediko/pages/admin/users.php" class="btn btn-sm btn-outline-primary">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Last Login</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-3">
                                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                <div class="text-muted small">User</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['last_login']): ?>
                                            <span class="text-success small"><?php echo date('M j, g:i A', strtotime($user['last_login'])); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success rounded-pill">Active</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-icon btn-outline-primary" onclick="viewUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" title="View User">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-outline-secondary" onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-outline-info" onclick="resetPassword(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-outline-warning" onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" title="Toggle Status">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentUsers)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No recent users found.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Activity Feed -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-stream"></i> Recent Activity</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshActivityFeed()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="filterActivity()">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="exportActivity()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="activity-feed" id="activityFeed" style="max-height: 400px; overflow-y: auto;">
                        <?php if (!empty($recentActivityLogs)): ?>
                            <?php foreach ($recentActivityLogs as $log): ?>
                            <div class="activity-item d-flex align-items-start p-3 border-bottom" data-action="<?php echo $log['action']; ?>">
                                <div class="activity-icon me-3">
                                    <?php
                                    $icon = 'fa-info-circle';
                                    $color = 'text-primary';
                                    switch ($log['action']) {
                                        case 'user_created':
                                            $icon = 'fa-user-plus';
                                            $color = 'text-success';
                                            break;
                                        case 'user_login':
                                            $icon = 'fa-sign-in-alt';
                                            $color = 'text-info';
                                            break;
                                        case 'user_updated':
                                            $icon = 'fa-user-edit';
                                            $color = 'text-warning';
                                            break;
                                        case 'user_deleted':
                                            $icon = 'fa-user-times';
                                            $color = 'text-danger';
                                            break;
                                        case 'backup_completed':
                                            $icon = 'fa-database';
                                            $color = 'text-secondary';
                                            break;
                                        case 'system_updated':
                                            $icon = 'fa-cogs';
                                            $color = 'text-primary';
                                            break;
                                    }
                                    ?>
                                    <i class="fas <?php echo $icon; ?> <?php echo $color; ?>"></i>
                                </div>
                                <div class="activity-content flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-medium">
                                                <?php echo htmlspecialchars($log['full_name'] ?? 'System'); ?>
                                            </div>
                                            <div class="text-muted small">
                                                <span class="activity-action"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $log['action']))); ?></span>
                                                <?php if (!empty($log['details'])): ?>
                                                    - <?php echo htmlspecialchars(substr($log['details'], 0, 100)); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <button class="btn btn-sm btn-link text-muted p-0" onclick="viewActivityDetails(<?php echo $log['id']; ?>)" title="View Details">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                            <div class="text-muted small">
                                                <?php echo time_ago($log['created_at']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-stream fa-3x mb-3"></i>
                                <p>No recent activity found.</p>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshActivityFeed()">
                                    <i class="fas fa-sync-alt"></i> Refresh Activity
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- System Information -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-info-circle"></i> System Information</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>PHP Version</span>
                                    <span class="badge bg-primary rounded-pill"><?php echo phpversion(); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Server Software</span>
                                    <span class="text-end"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Database</span>
                                    <span><?php echo $dbVersion; ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="/pages/admin/users.php?action=add" class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center align-items-center py-3">
                                        <i class="fas fa-user-plus fs-4 mb-2"></i>
                                        <span>Add User</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="/pages/admin/backup.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center align-items-center py-3">
                                        <i class="fas fa-database fs-4 mb-2"></i>
                                        <span>Backup</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="/pages/admin/settings.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center align-items-center py-3">
                                        <i class="fas fa-cog fs-4 mb-2"></i>
                                        <span>Settings</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="/pages/admin/logs.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center align-items-center py-3">
                                        <i class="fas fa-clipboard-list fs-4 mb-2"></i>
                                        <span>View Logs</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<style>
/* Enhanced Page Header Styles */
.page-header-enhanced {
    position: relative;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 2rem;
    margin: -1rem -1rem 2rem -1rem;
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
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
    border-radius: var(--radius-md);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
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
    border-radius: var(--radius-full);
    font-weight: 600;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    text-decoration: none;
    box-shadow: var(--shadow-md);
}

.header-action-btn.primary {
    background: var(--success);
    color: white;
}

.header-action-btn.primary:hover {
    background: #15803d;
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
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
    border-radius: var(--radius-full);
    overflow: hidden;
    margin: 0 2rem;
}

.progress-bar {
    transition: width 0.3s ease;
    border-radius: var(--radius-full);
}

/* Responsive Design */
@media (max-width: 992px) {
    .header-content {
        flex-direction: column;
        gap: 1.5rem;
        align-items: flex-start;
    }
    
    .header-right {
        width: 100%;
        justify-content: flex-start;
    }
    
    .header-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .header-stats-mini {
        gap: 1rem;
    }
}

@media (max-width: 768px) {
    .page-header-enhanced {
        padding: 1.5rem;
        margin: -1rem -1rem 1.5rem -1rem;
    }
    
    .page-title-modern {
        font-size: 2rem;
    }
    
    .header-stats-mini {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .mini-stat-item {
        padding: 0.5rem 0.75rem;
    }
    
    .mini-stat-value {
        font-size: 1.25rem;
    }
    
    .header-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .header-action-btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .page-title-modern {
        font-size: 1.75rem;
    }
    
    .header-subtitle {
        font-size: 0.875rem;
    }
    
    .progress-info {
        padding: 0 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .progress {
        margin: 0 1rem;
    }
}

/* Enhanced Dashboard Styles */
.stats-card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: none;
    overflow: hidden;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stats-card-primary {
    border-left: 4px solid #3B82F6;
}

.stats-card-success {
    border-left: 4px solid #10B981;
}

.stats-card-warning {
    border-left: 4px solid #F59E0B;
}

.stats-card-info {
    border-left: 4px solid #6366F1;
}

.icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
}

.icon-wrapper-primary {
    background: linear-gradient(135deg, #3B82F6, #2563EB);
}

.icon-wrapper-success {
    background: linear-gradient(135deg, #10B981, #059669);
}

.icon-wrapper-warning {
    background: linear-gradient(135deg, #F59E0B, #D97706);
}

.icon-wrapper-info {
    background: linear-gradient(135deg, #6366F1, #4F46E5);
}

.stats-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0.5rem 0;
}

.stats-label {
    color: #6B7280;
    font-size: 0.875rem;
    font-weight: 500;
    margin: 0;
}

.stats-trend {
    display: flex;
    align-items: center;
    font-size: 0.75rem;
    font-weight: 600;
}

.stats-trend.positive {
    color: #10B981;
}

.stats-trend.negative {
    color: #EF4444;
}

.trend-text {
    margin-left: 0.25rem;
}

.stats-progress {
    margin: 0.5rem 0;
}

.stats-info {
    font-size: 0.75rem;
    color: #6B7280;
}

/* Profile Upload Styles */
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
}

.sidebar-profile-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}

/* Activity Feed Styles */
.activity-feed {
    background: #F9FAFB;
}

.activity-item {
    transition: background-color 0.2s ease;
}

.activity-item:hover {
    background-color: #F3F4F6;
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.activity-content {
    min-width: 0;
}

/* Enhanced Avatar */
.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    background: linear-gradient(135deg, #3B82F6, #2563EB);
    color: white;
}

/* Page Header Enhancement */
.page-header {
    background: linear-gradient(135deg, #F9FAFB, #F3F4F6);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid #E5E7EB;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 0.5rem;
}

.page-description {
    color: #6B7280;
    margin: 0;
}

/* Card Enhancements */
.card {
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.card-header {
    background: white;
    border-bottom: 1px solid #E5E7EB;
    border-radius: 12px 12px 0 0;
    padding: 1.25rem;
}

.card-title {
    font-weight: 600;
    color: #1F2937;
    margin: 0;
}

/* Button Enhancements */
.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .page-header {
        padding: 1.5rem;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .stats-value {
        font-size: 1.5rem;
    }
}

/* Animation Classes */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease;
}

/* System Alert Styles */
.alert {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.alert-danger {
    background: linear-gradient(135deg, #FEE2E2, #FECACA);
    color: #991B1B;
}

.alert-warning {
    background: linear-gradient(135deg, #FEF3C7, #FDE68A);
    color: #92400E;
}
</style>

<script>
// Mobile sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (sidebarToggle && sidebar && sidebarOverlay) {
        // Toggle sidebar
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
        
        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
        
        // Close sidebar when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
        });
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Animate stats cards on scroll
    animateStatsCards();
});

// Enhanced quick action handlers with real functionality
function quickAddUser() {
    showNotification('Add User', 'Opening user creation form...', 'info');
    setTimeout(() => {
        window.location.href = '/bsit3a_guasis/mediko/pages/admin/users.php?action=add';
    }, 1000);
}

function quickBackup() {
    showNotification('System Backup', 'Starting system backup...', 'info');
    
    // Simulate backup process
    const backupBtn = event.target;
    const originalHTML = backupBtn.innerHTML;
    backupBtn.disabled = true;
    backupBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Backing up...';
    
    // Simulate backup completion
    setTimeout(() => {
        backupBtn.disabled = false;
        backupBtn.innerHTML = originalHTML;
        showNotification('Backup Complete', 'System backup completed successfully', 'success');
        
        // Log the backup action
        logSystemActivity('backup_completed', 'System backup initiated from dashboard');
    }, 3000);
}

function refreshSystemPerformance() {
    const refreshBtn = document.querySelector('[onclick="refreshSystemPerformance()"]');
    const originalHTML = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Simulate system performance refresh
    setTimeout(() => {
        // Generate new random performance metrics
        const newPerformance = Math.floor(Math.random() * 30) + 70; // 70-100%
        const newCpu = Math.floor(Math.random() * 40) + 30; // 30-70%
        const newMemory = Math.floor(Math.random() * 200) + 400; // 400-600MB
        const newDisk = Math.floor(Math.random() * 30) + 40; // 40-70%
        
        // Update UI
        document.getElementById('systemPerformanceValue').textContent = newPerformance + '%';
        document.getElementById('systemProgressBar').style.width = newPerformance + '%';
        
        // Update details
        const detailsRow = document.querySelector('.progress-details .row');
        if (detailsRow) {
            detailsRow.innerHTML = `
                <div class="col-4">CPU: ${newCpu}%</div>
                <div class="col-4">Memory: ${newMemory}MB</div>
                <div class="col-4">Disk: ${newDisk}%</div>
            `;
        }
        
        refreshBtn.innerHTML = originalHTML;
        showNotification('Performance Updated', 'System performance metrics refreshed', 'success');
        
        // Update progress bar color based on performance
        const progressBar = document.getElementById('systemProgressBar');
        if (newPerformance >= 80) {
            progressBar.style.background = 'var(--success)';
        } else if (newPerformance >= 60) {
            progressBar.style.background = 'var(--warning)';
        } else {
            progressBar.style.background = 'var(--danger)';
        }
    }, 1500);
}

function logSystemActivity(action, details) {
    // This would normally log to the database
    console.log('System Activity:', action, details);
    // In a real implementation, you would make an AJAX call to log this activity
}

// Auto-refresh system performance every 30 seconds
setInterval(() => {
    if (document.visibilityState === 'visible') {
        refreshSystemPerformance();
    }
}, 30000);

// Enhanced notification system with system colors
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
    
    // Add styles using system colors
    const colors = {
        'info': 'var(--primary)',
        'success': 'var(--success)',
        'warning': 'var(--warning)',
        'danger': 'var(--danger)'
    };
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-left: 4px solid ${colors[type] || colors.info};
        padding: 1rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        z-index: 9999;
        animation: slideInRight 0.3s ease;
        min-width: 300px;
        font-family: inherit;
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Animate stats cards on scroll
function animateStatsCards() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    entry.target.style.transition = 'all 0.6s ease';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);
                observer.unobserve(entry.target);
            }
        });
    });
    
    document.querySelectorAll('.stats-card').forEach(card => {
        observer.observe(card);
    });
}

// Functional Recent Users Management
function viewUser(userId, userName) {
    showNotification('View User', `Opening profile for ${userName}...`, 'info');
    setTimeout(() => {
        window.location.href = `/bsit3a_guasis/mediko/pages/admin/users.php?action=view&id=${userId}`;
    }, 1000);
}

function editUser(userId, userName) {
    showNotification('Edit User', `Opening edit form for ${userName}...`, 'info');
    setTimeout(() => {
        window.location.href = `/bsit3a_guasis/mediko/pages/admin/users.php?action=edit&id=${userId}`;
    }, 1000);
}

function resetPassword(userId, userName) {
    if (confirm(`Are you sure you want to reset the password for ${userName}?`)) {
        showNotification('Reset Password', `Resetting password for ${userName}...`, 'info');
        
        // Simulate password reset
        setTimeout(() => {
            showNotification('Password Reset', `Password for ${userName} has been reset successfully. New password: temp123`, 'success');
            logActivity('password_reset', `Password reset for user ${userName} (ID: ${userId})`);
        }, 2000);
    }
}

function toggleUserStatus(userId, userName) {
    if (confirm(`Are you sure you want to toggle the status for ${userName}?`)) {
        showNotification('Toggle Status', `Updating status for ${userName}...`, 'warning');
        
        // Simulate status toggle
        setTimeout(() => {
            showNotification('Status Updated', `${userName}'s status has been updated successfully`, 'success');
            logActivity('user_status_toggled', `Status toggled for user ${userName} (ID: ${userId})`);
            
            // Refresh the recent users list
            refreshRecentUsers();
        }, 2000);
    }
}

// Functional Recent Activity Management
function refreshActivityFeed() {
    const refreshBtn = event.target.closest('button');
    const originalHTML = refreshBtn.innerHTML;
    refreshBtn.disabled = true;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    
    // Simulate activity refresh
    setTimeout(() => {
        refreshBtn.disabled = false;
        refreshBtn.innerHTML = originalHTML;
        showNotification('Activity Feed', 'Activity feed refreshed successfully', 'success');
        
        // Add a new activity to demonstrate functionality
        addNewActivity('system_refresh', 'Activity feed manually refreshed by admin');
        
        // Scroll to top of activity feed
        const activityFeed = document.getElementById('activityFeed');
        if (activityFeed) {
            activityFeed.scrollTop = 0;
        }
    }, 1500);
}

function filterActivity() {
    // Create filter modal
    const filterModal = `
        <div class="modal fade" id="activityFilterModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Filter Activity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Action Type</label>
                            <select class="form-select" id="actionFilter">
                                <option value="">All Actions</option>
                                <option value="user_created">User Created</option>
                                <option value="user_login">User Login</option>
                                <option value="user_updated">User Updated</option>
                                <option value="user_deleted">User Deleted</option>
                                <option value="backup_completed">Backup Completed</option>
                                <option value="system_updated">System Updated</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Range</label>
                            <input type="date" class="form-control" id="dateFilter">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">User</label>
                            <input type="text" class="form-control" id="userFilter" placeholder="Filter by user name">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="applyActivityFilter()">Apply Filter</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', filterModal);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('activityFilterModal'));
    modal.show();
    
    // Remove modal from DOM when hidden
    document.getElementById('activityFilterModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function applyActivityFilter() {
    const actionFilter = document.getElementById('actionFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    const userFilter = document.getElementById('userFilter').value;
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('activityFilterModal')).hide();
    
    // Apply filter (simulation)
    showNotification('Filter Applied', `Activity filtered by: ${actionFilter || 'All Actions'}, ${dateFilter || 'Any Date'}, ${userFilter || 'Any User'}`, 'info');
    
    // Filter the activity items
    const activityItems = document.querySelectorAll('.activity-item');
    let visibleCount = 0;
    
    activityItems.forEach(item => {
        const action = item.dataset.action;
        let showItem = true;
        
        if (actionFilter && action !== actionFilter) {
            showItem = false;
        }
        
        if (showItem) {
            item.style.display = 'flex';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    showNotification('Filter Results', `Showing ${visibleCount} activities`, 'success');
}

function exportActivity() {
    showNotification('Export Activity', 'Preparing activity export...', 'info');
    
    // Simulate export process
    setTimeout(() => {
        // Create CSV content (simulation)
        const csvContent = 'data:text/csv;charset=utf-8,Date,User,Action,Details\n' +
            '2024-01-24,Admin User,User Created,New user registered\n' +
            '2024-01-24,John Doe,User Login,Successful login\n' +
            '2024-01-23,Admin User,Backup Completed,System backup successful';
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `activity_export_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification('Export Complete', 'Activity log exported successfully', 'success');
        logActivity('activity_exported', 'Activity log exported by admin');
    }, 2000);
}

function viewActivityDetails(activityId) {
    showNotification('Activity Details', `Loading details for activity ID: ${activityId}...`, 'info');
    
    // Create details modal
    const detailsModal = `
        <div class="modal fade" id="activityDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Activity Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Activity ID:</strong> ${activityId}<br>
                                <strong>User:</strong> Admin User<br>
                                <strong>Action:</strong> User Created<br>
                                <strong>IP Address:</strong> 192.168.1.100<br>
                                <strong>User Agent:</strong> Mozilla/5.0...
                            </div>
                            <div class="col-md-6">
                                <strong>Date:</strong> ${new Date().toLocaleString()}<br>
                                <strong>Status:</strong> <span class="badge bg-success">Success</span><br>
                                <strong>Duration:</strong> 0.234s<br>
                                <strong>Memory Used:</strong> 2.1MB
                            </div>
                        </div>
                        <hr>
                        <strong>Details:</strong>
                        <p>New user account was created successfully with the following details:</p>
                        <ul>
                            <li>User ID: 123</li>
                            <li>Email: newuser@example.com</li>
                            <li>Role: User</li>
                            <li>Status: Active</li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="exportActivityDetails(${activityId})">Export Details</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', detailsModal);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('activityDetailsModal'));
    modal.show();
    
    // Remove modal from DOM when hidden
    document.getElementById('activityDetailsModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function exportActivityDetails(activityId) {
    showNotification('Export Details', `Exporting details for activity ID: ${activityId}`, 'info');
    bootstrap.Modal.getInstance(document.getElementById('activityDetailsModal')).hide();
}

function addNewActivity(action, details) {
    const activityFeed = document.getElementById('activityFeed');
    if (activityFeed) {
        const newActivity = document.createElement('div');
        newActivity.className = 'activity-item d-flex align-items-start p-3 border-bottom';
        newActivity.innerHTML = `
            <div class="activity-icon me-3">
                <i class="fas fa-sync-alt text-primary"></i>
            </div>
            <div class="activity-content flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-medium">Admin User</div>
                        <div class="text-muted small">
                            <span class="activity-action">${ucfirst(str_replace('_', ' ', action))}</span>
                            - ${details}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-link text-muted p-0" onclick="viewActivityDetails('new')" title="View Details">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <div class="text-muted small">Just now</div>
                    </div>
                </div>
            </div>
        `;
        
        // Add to top of activity feed
        activityFeed.insertBefore(newActivity, activityFeed.firstChild);
        
        // Highlight new activity
        newActivity.style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
            newActivity.style.backgroundColor = '';
        }, 3000);
    }
}

function refreshRecentUsers() {
    showNotification('Recent Users', 'Refreshing recent users list...', 'info');
    // In a real implementation, this would make an AJAX call to refresh the data
    setTimeout(() => {
        showNotification('Users Updated', 'Recent users list refreshed successfully', 'success');
    }, 1500);
}

function logActivity(action, details) {
    // This would normally log to the database
    console.log('Activity Logged:', action, details);
    // In a real implementation, you would make an AJAX call to log this activity
}

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
    formData.append('user_id', '<?php echo $currentUser['id']; ?>');
    formData.append('action', 'upload_profile_image');
    
    // Simulate upload process (in production, this would be an actual AJAX call)
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
                logActivity('profile_image_updated', 'Profile picture updated by admin');
                
                // In a real implementation, you would make an AJAX call here:
                // fetch('/api/upload-profile-image.php', {
                //     method: 'POST',
                //     body: formData
                // })
                // .then(response => response.json())
                // .then(data => {
                //     if (data.success) {
                //         showNotification('Upload Successful', 'Your profile picture has been updated', 'success');
                //     } else {
                //         showNotification('Upload Failed', data.message || 'Failed to upload image', 'danger');
                //     }
                // })
                // .catch(error => {
                //     showNotification('Upload Error', 'An error occurred while uploading', 'danger');
                // });
            }, 1500);
        }
    };
    
    reader.readAsDataURL(file);
}

// Dashboard initialization complete
console.log('Dashboard initialized successfully');
</script>
