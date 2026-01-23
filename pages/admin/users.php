<?php
require_once __DIR__ . '/../../includes/header.php';

// Get database connection
$db = Database::getInstance();

// Get user statistics - get all users
$totalUsersQuery = $db->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $totalUsersQuery->fetch()['total'];

$activeUsersQuery = $db->query("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
$activeUsers = $activeUsersQuery->fetch()['total'];

$newUsersThisMonthQuery = $db->query("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$newUsersThisMonth = $newUsersThisMonthQuery->fetch()['total'];

$verifiedUsersQuery = $db->query("SELECT COUNT(*) as total FROM users WHERE email_verified_at IS NOT NULL");
$verifiedUsers = $verifiedUsersQuery->fetch()['total'];

// Pagination and filtering
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';
$query = "SELECT u.*, up.date_of_birth, u.phone as profile_phone 
          FROM users u 
          LEFT JOIN user_profiles up ON u.id = up.user_id 
          WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM users u WHERE 1=1";
$params = [];
$countParams = [];
if (!empty($search)) {
    $query .= " AND (u.full_name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
    $countQuery .= " AND (u.full_name LIKE :search OR u.email LIKE :search)";
    $searchTerm = "%$search%";
    $params[':search'] = $searchTerm;
    $countParams[':search'] = $searchTerm;
}

// Apply status filter
if ($status === 'active') {
    $query .= " AND u.is_active = 1";
    $countQuery .= " AND u.is_active = 1";
} elseif ($status === 'inactive') {
    $query .= " AND u.is_active = 0";
    $countQuery .= " AND u.is_active = 0";
}

// Add sorting
$validSortColumns = ['full_name', 'email', 'created_at', 'last_login'];
$sort = in_array($sort, $validSortColumns) ? $sort : 'created_at';
$query .= " ORDER BY $sort $order";

// Add pagination
$query .= " LIMIT :limit OFFSET :offset";

// Get total count
$stmt = $db->prepare($countQuery);
foreach ($countParams as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalUsers / $perPage);

// Get users
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                            <a class="nav-link active" href="/bsit3a_guasis/mediko/pages/admin/users.php">
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
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/medications.php">
                                <i class="nav-icon fas fa-pills"></i>
                                <p>Medications</p>
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
                <h1 class="page-title">User Management</h1>
                <p class="page-description">Manage user accounts, permissions, and access control</p>
            </div>

            <!-- Enhanced User Statistics Cards -->
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
                                    <span class="trend-text">+12%</span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($totalUsers); ?></h3>
                            <p class="stats-label">Total Users</p>
                            <div class="stats-footer">
                                <div class="stats-progress">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: 85%"></div>
                                    </div>
                                </div>
                                <span class="stats-info">85% of capacity</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card stats-card-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="icon-wrapper icon-wrapper-success">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="stats-trend positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span class="trend-text">+8%</span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($activeUsers); ?></h3>
                            <p class="stats-label">Active Users</p>
                            <div class="stats-footer">
                                <div class="stats-progress">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo round(($activeUsers / $totalUsers) * 100); ?>%"></div>
                                    </div>
                                </div>
                                <span class="stats-info"><?php echo round(($activeUsers / $totalUsers) * 100); ?>% active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card stats-card-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="icon-wrapper icon-wrapper-warning">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="stats-trend positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span class="trend-text">+23%</span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($newUsersThisMonth); ?></h3>
                            <p class="stats-label">New This Month</p>
                            <div class="stats-footer">
                                <div class="stats-progress">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-warning" style="width: 65%"></div>
                                    </div>
                                </div>
                                <span class="stats-info">Above average</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stats-card stats-card-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="icon-wrapper icon-wrapper-info">
                                    <i class="fas fa-envelope-check"></i>
                                </div>
                                <div class="stats-trend positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span class="trend-text">+5%</span>
                                </div>
                            </div>
                            <h3 class="stats-value"><?php echo number_format($verifiedUsers); ?></h3>
                            <p class="stats-label">Verified Emails</p>
                            <div class="stats-footer">
                                <div class="stats-progress">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-info" style="width: <?php echo round(($verifiedUsers / $totalUsers) * 100); ?>%"></div>
                                    </div>
                                </div>
                                <span class="stats-info"><?php echo round(($verifiedUsers / $totalUsers) * 100); ?>% verified</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Filters and Actions -->
            <div class="card card-enhanced mb-4">
                <div class="card-header card-header-enhanced">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="fas fa-filter me-2"></i>Filters & Actions
                            </h5>
                            <p class="card-subtitle text-muted mb-0">Search and filter users</p>
                        </div>
                        <div class="card-actions">
                            <button type="button" class="btn btn-primary btn-sm" onclick="showAddUserModal()">
                                <i class="fas fa-plus me-1"></i> Add New User
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" action="" class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Search</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" name="search" placeholder="Search by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
                                <?php if (!empty($search)): ?>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearSearch()">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-lg">
                                <option value="">All Statuses</option>
                                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>
                                    <i class="fas fa-check-circle me-1"></i> Active
                                </option>
                                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>
                                    <i class="fas fa-times-circle me-1"></i> Inactive
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Actions</label>
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                                <a href="?" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Export</label>
                            <div class="dropdown">
                                <button class="btn btn-outline-success dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="?export=csv"><i class="fas fa-file-csv me-2"></i>Export as CSV</a></li>
                                    <li><a class="dropdown-item" href="?export=excel"><i class="fas fa-file-excel me-2"></i>Export as Excel</a></li>
                                    <li><a class="dropdown-item" href="?export=pdf"><i class="fas fa-file-pdf me-2"></i>Export as PDF</a></li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enhanced Users Table -->
            <div class="card card-enhanced">
                <div class="card-header card-header-enhanced">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>Users List
                            </h5>
                            <p class="card-subtitle text-muted mb-0">Manage all user accounts</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="badge bg-primary rounded-pill me-3">
                                <i class="fas fa-database me-1"></i>
                                <span id="userCount"><?php echo count($users); ?></span> users
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-sort me-1"></i> Sort: <?php echo ucwords(str_replace('_', ' ', $sort)); ?>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'full_name', 'order' => $order])); ?>">
                                        <i class="fas fa-user me-2"></i> Name
                                    </a></li>
                                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'email', 'order' => $order])); ?>">
                                        <i class="fas fa-envelope me-2"></i> Email
                                    </a></li>
                                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'created_at', 'order' => $order])); ?>">
                                        <i class="fas fa-calendar me-2"></i> Date Joined
                                    </a></li>
                                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'last_login', 'order' => $order])); ?>">
                                        <i class="fas fa-clock me-2"></i> Last Login
                                    </a></li>
                                </ul>
                            </div>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['order' => $order === 'ASC' ? 'DESC' : 'ASC'])); ?>" class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="fas fa-sort-<?php echo strtolower($order); ?>"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-enhanced mb-0">
                            <thead class="table-thead">
                                <tr>
                                    <th class="table-th">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user me-2"></i>
                                            User
                                        </div>
                                    </th>
                                    <th class="table-th">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-envelope me-2"></i>
                                            Contact
                                        </div>
                                    </th>
                                    <th class="table-th">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Status
                                        </div>
                                    </th>
                                    <th class="table-th">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar me-2"></i>
                                            Joined
                                        </div>
                                    </th>
                                    <th class="table-th">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock me-2"></i>
                                            Last Login
                                        </div>
                                    </th>
                                    <th class="table-th">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-chart-line me-2"></i>
                                            Activity
                                        </div>
                                    </th>
                                    <th class="table-th text-end">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <i class="fas fa-cog me-2"></i>
                                            Actions
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr class="table-row-hover">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar avatar-enhanced">
                                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium text-primary"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                        <div class="text-muted small">ID: #<?php echo str_pad($user['id'], 5, '0', STR_PAD_LEFT); ?></div>
                                                        <div class="text-muted small">Role: <?php echo ucfirst($user['role']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <div class="contact-item">
                                                        <i class="fas fa-envelope text-muted me-2"></i>
                                                        <span class="text-truncate"><?php echo htmlspecialchars($user['email']); ?></span>
                                                    </div>
                                                    <?php if (!empty($user['profile_phone'])): ?>
                                                        <div class="contact-item">
                                                            <i class="fas fa-phone text-muted me-2"></i>
                                                            <span><?php echo htmlspecialchars($user['profile_phone']); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="status-badges">
                                                    <?php if ($user['is_active']): ?>
                                                        <span class="badge badge-success badge-enhanced">
                                                            <i class="fas fa-check-circle me-1"></i>Active
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary badge-enhanced">
                                                            <i class="fas fa-times-circle me-1"></i>Inactive
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($user['email_verified_at']): ?>
                                                        <span class="badge badge-info badge-enhanced">
                                                            <i class="fas fa-check-circle me-1"></i>Verified
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning badge-enhanced">
                                                            <i class="fas fa-exclamation-circle me-1"></i>Not Verified
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="date-info">
                                                    <div class="date-value">
                                                        <i class="fas fa-calendar-alt text-muted me-1"></i>
                                                        <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                                    </div>
                                                    <div class="date-ago text-muted">
                                                        <?php 
                                                            $daysAgo = floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24));
                                                            echo $daysAgo . ' days ago';
                                                        ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($user['last_login']): ?>
                                                    <div class="date-info">
                                                        <div class="date-value">
                                                            <i class="fas fa-clock text-muted me-1"></i>
                                                            <?php echo date('M j, Y g:i A', strtotime($user['last_login'])); ?>
                                                        </div>
                                                        <div class="date-ago text-muted">
                                                            <?php 
                                                                $daysAgo = floor((time() - strtotime($user['last_login'])) / (60 * 60 * 24));
                                                                echo $daysAgo . ' days ago';
                                                            ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="date-info">
                                                        <div class="date-value text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            Never
                                                        </div>
                                                        <div class="date-ago text-muted">
                                                            No activity yet
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="activity-info">
                                                    <?php 
                                                    // Calculate activity level based on last login
                                                    $activityLevel = 'low';
                                                    $activityColor = 'secondary';
                                                    $activityIcon = 'fa-minus-circle';
                                                    
                                                    if ($user['last_login']) {
                                                        $daysSinceLogin = floor((time() - strtotime($user['last_login'])) / (60 * 60 * 24));
                                                        if ($daysSinceLogin <= 7) {
                                                            $activityLevel = 'high';
                                                            $activityColor = 'success';
                                                            $activityIcon = 'fa-arrow-up';
                                                        } elseif ($daysSinceLogin <= 30) {
                                                            $activityLevel = 'medium';
                                                            $activityColor = 'warning';
                                                            $activityIcon = 'fa-minus';
                                                        }
                                                    }
                                                    ?>
                                                    <div class="activity-badge">
                                                        <span class="badge badge-<?php echo $activityColor; ?> badge-enhanced">
                                                            <i class="fas <?php echo $activityIcon; ?> me-1"></i>
                                                            <?php echo ucfirst($activityLevel); ?>
                                                        </span>
                                                    </div>
                                                    <div class="activity-details text-muted small">
                                                        <?php 
                                                        if ($user['last_login']) {
                                                            echo 'Active ' . $daysSinceLogin . ' days ago';
                                                        } else {
                                                            echo 'Never logged in';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-primary" onclick="viewUser(<?php echo $user['id']; ?>)" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" onclick="editUser(<?php echo $user['id']; ?>)" title="Edit User">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-warning" onclick="resetPassword(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" title="Reset Password">
                                                        <i class="fas fa-key"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" title="Delete User">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state">
                                                <div class="empty-state-icon">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                                <div class="empty-state-title">No users found</div>
                                                <div class="empty-state-description">
                                                    Try adjusting your search or filters to find users
                                                </div>
                                                <div class="empty-state-actions">
                                                    <a href="?" class="btn btn-primary">
                                                        <i class="fas fa-redo me-1"></i> Reset Filters
                                                    </a>
                                                    <button type="button" class="btn btn-success" onclick="showAddUserModal()">
                                                        <i class="fas fa-plus me-1"></i> Add New User
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing <?php echo (($page - 1) * $perPage) + 1; ?> to 
                        <?php echo min($page * $perPage, $totalUsers); ?> of 
                        <?php echo number_format($totalUsers); ?> users
                    </div>
                    
                    <nav aria-label="User pagination">
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" aria-label="Previous">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php 
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++): 
                            ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" aria-label="Next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Enhanced CSS for Users Page -->
<style>
/* Enhanced Stats Cards */
.stats-card-primary {
    border-left: 4px solid var(--primary);
}

.stats-card-success {
    border-left: 4px solid var(--success);
}

.stats-card-warning {
    border-left: 4px solid var(--warning);
}

.stats-card-info {
    border-left: 4px var(--info);
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

.card-actions {
    display: flex;
    gap: 0.5rem;
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

/* Enhanced Avatar */
.avatar-enhanced {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    font-weight: 600;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    transition: all 0.3s ease;
}

.avatar-enhanced:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
}

/* Contact Info */
.contact-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.contact-item {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
}

.contact-item i {
    width: 16px;
    text-align: center;
}

/* Status Badges */
.status-badges {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

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

/* Date Info */
.date-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.date-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
}

.date-ago {
    font-size: 0.75rem;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.25rem;
    justify-content: flex-end;
}

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.btn-icon:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

/* Activity Info */
.activity-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.activity-badge {
    display: flex;
    align-items: center;
}

.activity-details {
    font-size: 0.75rem;
    line-height: 1.3;
}

/* Enhanced Progress Bars */
.progress {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
}

/* Enhanced Badge Colors */
.badge-success {
    background-color: var(--success);
}

.badge-warning {
    background-color: var(--warning);
}

.badge-info {
    background-color: var(--info);
}

.badge-secondary {
    background-color: #6c757d;
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
    
    .contact-info {
        font-size: 0.75rem;
    }
    
    .date-info {
        font-size: 0.75rem;
    }
    
    .activity-info {
        font-size: 0.75rem;
    }
    
    .action-buttons {
        flex-wrap: wrap;
        justify-content: center;
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

<!-- Enhanced JavaScript -->
<script>
// Mobile sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (sidebarToggle && sidebar && sidebarOverlay) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
        });
    }
});

// Enhanced user actions
function viewUser(userId) {
    window.location.href = `user_view.php?id=${userId}`;
}

function editUser(userId) {
    window.location.href = `user_edit.php?id=${userId}`;
}

function showAddUserModal() {
    // Create a simple alert for now - in production, this would open a modal
    alert('Add User functionality would open a modal here');
}

function clearSearch() {
    window.location.href = '?';
}

// Delete user confirmation
function confirmDelete(userId, userName) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteUserId').value = userId;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}

// Reset password
function resetPassword(userId, userName) {
    document.getElementById('resetPasswordUserName').textContent = userName;
    document.getElementById('resetPasswordUserId').value = userId;
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}

// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('newPassword');
    const passwordToggle = document.getElementById('passwordToggle');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordToggle.classList.remove('fa-eye');
        passwordToggle.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordToggle.classList.remove('fa-eye-slash');
        passwordToggle.classList.add('fa-eye');
    }
}

// Form validation
document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match');
        return;
    }
    
    if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(newPassword)) {
        e.preventDefault();
        alert('Password must contain uppercase, lowercase, and numbers');
        return;
    }
});

// Add loading states
function showLoadingState() {
    const tableBody = document.querySelector('.table tbody');
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <div class="ms-3">Loading users...</div>
                    </div>
                </td>
            </tr>
        `;
    }
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

// Initialize animations
animateStatsCards();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
