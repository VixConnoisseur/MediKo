<?php
require_once __DIR__ . '/../../includes/header.php';

// Get database connection
$db = Database::getInstance();

// Get system statistics
$totalUsersQuery = $db->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $totalUsersQuery->fetch()['total'];

$activeUsersQuery = $db->query("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
$activeUsers = $activeUsersQuery->fetch()['total'];

$totalMedicationsQuery = $db->query("SELECT COUNT(*) as total FROM medications");
$totalMedications = $totalMedicationsQuery->fetch()['total'];

$totalAppointmentsQuery = $db->query("SELECT COUNT(*) as total FROM appointments");
$totalAppointments = $totalAppointmentsQuery->fetch()['total'];

$systemLogsQuery = $db->query("SELECT COUNT(*) as total FROM audit_logs");
$systemLogs = $systemLogsQuery->fetch()['total'];

// Database size
$dbSizeQuery = $db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = DATABASE()");
$dbSize = $dbSizeQuery->fetch()['size_mb'];

// Server info
$serverInfo = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'server_time' => date('Y-m-d H:i:s'),
    'uptime' => 'N/A',
    'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
    'disk_free' => round(disk_free_space('/') / 1024 / 1024 / 1024, 2) . ' GB',
    'disk_total' => round(disk_total_space('/') / 1024 / 1024 / 1024, 2) . ' GB'
];

// Get recent system logs
$recentLogsQuery = $db->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 10");
$recentLogs = $recentLogsQuery->fetchAll(PDO::FETCH_ASSOC);

// Get system settings
$settingsQuery = $db->query("SELECT * FROM system_settings ORDER BY setting_key");
$settings = [];
foreach ($settingsQuery->fetchAll(PDO::FETCH_ASSOC) as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Get backup information
$backupDir = __DIR__ . '/../../backups';
$backups = [];
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backupDir . '/' . $file),
                'date' => date('Y-m-d H:i:s', filemtime($backupDir . '/' . $file))
            ];
        }
    }
}
usort($backups, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
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
                        <div class="mb-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center border border-2 mx-auto sidebar-profile-avatar">
                                <span class="fw-bold">
                                    <?php echo strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)); ?>
                                </span>
                            </div>
                        </div>
                        <div class="fw-medium text-truncate w-100 px-2 sidebar-profile-name">
                            <?php echo htmlspecialchars($currentUser['full_name'] ?? 'Admin'); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="flex-grow-1 px-3 py-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <!-- Main Navigation -->
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/dashboard.php">
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
                            <a class="nav-link active" href="/bsit3a_guasis/mediko/pages/admin/system_management.php">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>System Management</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/reports_analytics.php">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>Reports & Analytics</p>
                            </a>
                        </li>
                        
                        <!-- Analytics Section -->
                        <li class="nav-header mt-4 mb-2">
                            <h6 class="text-xs text-muted text-uppercase font-weight-bold">Analytics</h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/medication_analytics.php">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>Medication Analytics</p>
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
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">System Management</h1>
                <p class="page-description">Monitor and manage system performance, settings, and maintenance</p>
            </div>

            <!-- System Overview Cards -->
            <div class="row g-4 mb-5">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card stats-card-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="icon-wrapper icon-wrapper-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stats-trend positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span class="trend-text">+<?php echo $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0; ?>%</span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($totalUsers); ?></h3>
                            <p class="stats-label">Total Users</p>
                            <div class="stats-footer">
                                <div class="stats-progress">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: <?php echo $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0; ?>%"></div>
                                    </div>
                                </div>
                                <span class="stats-info"><?php echo $activeUsers; ?> active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card stats-card-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="icon-wrapper icon-wrapper-info">
                                    <i class="fas fa-pills"></i>
                                </div>
                                <div class="stats-trend positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span class="trend-text">+<?php echo $totalMedications; ?></span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($totalMedications); ?></h3>
                            <p class="stats-label">Total Medications</p>
                            <div class="stats-footer">
                                <div class="stats-progress">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-info" style="width: 75%"></div>
                                    </div>
                                </div>
                                <span class="stats-info">In database</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card stats-card-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="icon-wrapper icon-wrapper-warning">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stats-trend positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span class="trend-text">+<?php echo $totalAppointments; ?></span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($totalAppointments); ?></h3>
                            <p class="stats-label">Total Appointments</p>
                            <div class="stats-footer">
                                <div class="stats-progress">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-warning" style="width: 60%"></div>
                                    </div>
                                </div>
                                <span class="stats-info">Scheduled</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card stats-card-secondary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="icon-wrapper icon-wrapper-secondary">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="stats-trend positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span class="trend-text">+<?php echo $systemLogs; ?></span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($systemLogs); ?></h3>
                            <p class="stats-label">System Logs</p>
                            <div class="stats-footer">
                                <div class="stats-progress">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-secondary" style="width: 40%"></div>
                                    </div>
                                </div>
                                <span class="stats-info">Activities</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-clipboard-list fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">System Logs</h5>
                            <p class="card-text">View detailed system and user activity logs.</p>
                            <a href="logs.php" class="btn btn-primary">View Logs</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-database fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Backup Management</h5>
                            <p class="card-text">Create, download, and manage database backups.</p>
                            <a href="backup.php" class="btn btn-success">Manage Backups</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-cog fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">System Settings</h5>
                            <p class="card-text">Configure system-wide application settings.</p>
                            <a href="settings.php" class="btn btn-warning">Configure Settings</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Enhanced CSS for System Management Page -->
<style>
/* Enhanced Stats Cards */
.stats-card-primary {
    border-left: 4px solid var(--primary);
}

.stats-card-info {
    border-left: 4px solid var(--info);
}

.stats-card-warning {
    border-left: 4px solid var(--warning);
}

.stats-card-secondary {
    border-left: 4px solid #6c757d;
}

.stats-trend {
    display: flex;
    align-items: center;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
}

.stats-trend.positive {
    background-color: rgba(22, 163, 74, 0.1);
    color: var(--success);
}

.stats-trend.negative {
    background-color: rgba(220, 38, 38, 0.1);
    color: var(--danger);
}

.stats-footer {
    margin-top: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stats-progress {
    flex: 1;
    margin-right: 1rem;
}

.stats-info {
    font-size: 0.75rem;
    color: var(--text-muted);
    font-weight: 500;
}

/* Enhanced Cards */
.card-enhanced {
    border: none;
    box-shadow: 0 0.15rem 0.75rem rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.card-enhanced:hover {
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.card-header-enhanced {
    background: linear-gradient(135deg, var(--bg-surface) 0%, rgba(255, 255, 255, 0.95) 100%);
    border-bottom: 1px solid var(--border);
    padding: 1.5rem;
}

/* Info Items */
.info-item {
    margin-bottom: 1rem;
}

.info-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 0.875rem;
    color: var(--text-primary);
    font-weight: 500;
}

/* Enhanced Table */
.table-enhanced {
    border-collapse: separate;
    border-spacing: 0;
    overflow: hidden;
}

.table-thead {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
}

.table-th {
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    padding: 1rem;
    border: none;
}

.table-row-hover {
    transition: all 0.2s ease;
}

.table-row-hover:hover {
    background-color: rgba(37, 99, 235, 0.05);
    transform: scale(1.01);
}

.table-row-hover td {
    border-top: 1px solid var(--border);
}

/* Badge Enhancements */
.badge-enhanced {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.5rem 0.75rem;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s ease;
}

.badge-enhanced:hover {
    transform: scale(1.05);
}

/* Empty State */
.empty-state {
    padding: 3rem 2rem;
    text-align: center;
}

.empty-state-icon {
    font-size: 4rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
    opacity: 0.5;
}

.empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.empty-state-description {
    color: var(--text-muted);
    margin-bottom: 2rem;
    font-size: 0.875rem;
}

.empty-state-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .card-header-enhanced {
        padding: 1rem;
    }
    
    .table-th {
        padding: 0.75rem;
        font-size: 0.625rem;
    }
    
    .info-item {
        margin-bottom: 0.75rem;
    }
    
    .empty-state {
        padding: 2rem 1rem;
    }
    
    .empty-state-icon {
        font-size: 3rem;
    }
    
    .empty-state-title {
        font-size: 1.125rem;
    }
    
    .empty-state-actions {
        flex-direction: column;
        align-items: center;
    }
}
</style>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
