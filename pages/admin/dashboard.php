<?php
require_once __DIR__ . '/../../includes/header.php';
// Get database connection
$db = Database::getInstance();
// Total users
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$result = $stmt->fetch();
$totalUsers = $result['total'];

$stmt = $db->query("SELECT COUNT(*) as active FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND role = 'user'");
$result = $stmt->fetch();
$activeUsers = $result['active'];
// Total medications
$stmt = $db->query("SELECT COUNT(*) as total FROM medications");
$result = $stmt->fetch();
$totalMedications = $result['total'];
// Total appointments
$stmt = $db->query("SELECT COUNT(*) as total FROM appointments");
$result = $stmt->fetch();
$totalAppointments = $result['total'];
// Recent users
$stmt = $db->query("SELECT id, full_name, email, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
$recentUsers = $stmt->fetchAll();
// Get database version
$versionStmt = $db->query("SELECT VERSION() as version");
$dbVersion = 'MySQL ' . $versionStmt->fetch()['version'];
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
            <!-- Enhanced Page Header -->
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-description">Welcome back, <span class="fw-medium text-primary"><?php echo htmlspecialchars($currentUser['full_name'] ?? 'Admin'); ?></span>. Here's a summary of your system.</p>
            </div>

            <!-- Stats Grid -->
            <div class="row g-4 mb-5">
                <!-- Total Users -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card">
                        <div class="card-body">
                            <div class="icon-wrapper">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($totalUsers); ?></h3>
                            <p class="stats-label">Total Users</p>
                            <span class="stats-change positive"><i class="fas fa-arrow-up"></i> <?php echo number_format($activeUsers); ?> active</span>
                        </div>
                    </div>
                </div>

                <!-- Medications -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card">
                        <div class="card-body">
                            <div class="icon-wrapper">
                                <i class="fas fa-pills"></i>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($totalMedications); ?></h3>
                            <p class="stats-label">Medications</p>
                             <span class="stats-change"><i class="fas fa-database"></i> In system</span>
                        </div>
                    </div>
                </div>

                <!-- Appointments -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card">
                        <div class="card-body">
                            <div class="icon-wrapper">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($totalAppointments); ?></h3>
                            <p class="stats-label">Appointments</p>
                            <span class="stats-change"><i class="fas fa-clock"></i> 5 today</span>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card">
                        <div class="card-body">
                            <div class="icon-wrapper">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="stats-value">100%</h3>
                            <p class="stats-label">System Uptime</p>
                            <span class="stats-change positive"><i class="fas fa-shield-alt"></i> Secure</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="card">
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
                                        <a href="/pages/admin/user_view.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-icon btn-outline-primary" title="View User">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentUsers)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">No recent users found.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
});
</script>
