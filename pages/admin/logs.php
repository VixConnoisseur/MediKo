<?php
require_once __DIR__ . '/../../includes/header.php';

$db = Database::getInstance();

// Pagination and filtering
$perPage = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';

$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(user_id LIKE :search OR action LIKE :search OR table_name LIKE :search OR ip_address LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($action_filter)) {
    $where_clauses[] = "action = :action";
    $params[':action'] = $action_filter;
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM audit_logs" . $where_sql;
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$totalLogs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalLogs / $perPage);

// Get logs
$logsQuery = "SELECT * FROM audit_logs" . $where_sql . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($logsQuery);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar sidebar-dark">
             <div class="d-flex flex-column h-100">
                <!-- Admin Profile -->
                <div class="text-center py-3 py-md-4 border-secondary border-bottom px-2">
                    <div class="d-flex flex-column align-items-center">
                        <div class="mb-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center border border-2 mx-auto sidebar-profile-avatar">
                                <span class="fw-bold"><?php echo strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)); ?></span>
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
                        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
                        <li class="nav-item"><a class="nav-link" href="users.php"><i class="nav-icon fas fa-users"></i><p>Users</p></a></li>
                        <li class="nav-item"><a class="nav-link" href="system_management.php"><i class="nav-icon fas fa-cogs"></i><p>System Management</p></a></li>
                        <li class="nav-item"><a class="nav-link" href="reports_analytics.php"><i class="nav-icon fas fa-chart-pie"></i><p>Reports & Analytics</p></a></li>
                        <li class="nav-header mt-4 mb-2"><h6 class="text-xs text-muted text-uppercase font-weight-bold">System</h6></li>
                        <li class="nav-item"><a class="nav-link" href="backup.php"><i class="nav-icon fas fa-database"></i><p>Backup</p></a></li>
                        <li class="nav-item"><a class="nav-link" href="settings.php"><i class="nav-icon fas fa-cog"></i><p>Settings</p></a></li>
                        <li class="nav-item"><a class="nav-link active" href="logs.php"><i class="nav-icon fas fa-clipboard-list"></i><p>System Logs</p></a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
            <div class="page-header">
                <h1 class="page-title">System Logs</h1>
                <p class="page-description">Track all system and user activities.</p>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Search logs..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-4">
                            <select name="action" class="form-select">
                                <option value="">All Actions</option>
                                <option value="CREATE" <?php echo ($action_filter === 'CREATE') ? 'selected' : ''; ?>>CREATE</option>
                                <option value="UPDATE" <?php echo ($action_filter === 'UPDATE') ? 'selected' : ''; ?>>UPDATE</option>
                                <option value="DELETE" <?php echo ($action_filter === 'DELETE') ? 'selected' : ''; ?>>DELETE</option>
                                <option value="LOGIN" <?php echo ($action_filter === 'LOGIN') ? 'selected' : ''; ?>>LOGIN</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User ID</th>
                                    <th>Action</th>
                                    <th>Table</th>
                                    <th>Record ID</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($logs) > 0): ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                                            <td><?php echo htmlspecialchars($log['user_id'] ?? 'System'); ?></td>
                                            <td><span class="badge bg-<?php echo strtolower($log['action']); ?>"><?php echo htmlspecialchars($log['action']); ?></span></td>
                                            <td><?php echo htmlspecialchars($log['table_name']); ?></td>
                                            <td><?php echo htmlspecialchars($log['record_id']); ?></td>
                                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-4">No logs found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($totalPages > 1): ?>
                <div class="card-footer d-flex justify-content-center">
                    <nav>
                        <ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&action=<?php echo urlencode($action_filter); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<style>
.badge.bg-create { background-color: #198754; }
.badge.bg-update { background-color: #ffc107; color: #000; }
.badge.bg-delete { background-color: #dc3545; }
.badge.bg-login { background-color: #0dcaf0; }
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
