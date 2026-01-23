<?php
require_once __DIR__ . '/../../includes/header.php';

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
                        <li class="nav-item"><a class="nav-link active" href="backup.php"><i class="nav-icon fas fa-database"></i><p>Backup</p></a></li>
                        <li class="nav-item"><a class="nav-link" href="settings.php"><i class="nav-icon fas fa-cog"></i><p>Settings</p></a></li>
                        <li class="nav-item"><a class="nav-link" href="logs.php"><i class="nav-icon fas fa-clipboard-list"></i><p>System Logs</p></a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
            <div class="page-header">
                <h1 class="page-title">Backup Management</h1>
                <p class="page-description">Manage and restore database backups.</p>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Database Backups</h5>
                    <button class="btn btn-primary" onclick="createBackup()">Create New Backup</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Backup File</th>
                                    <th>Size</th>
                                    <th>Date Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($backups) > 0): ?>
                                    <?php foreach ($backups as $backup): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($backup['name']); ?></td>
                                            <td><?php echo round($backup['size'] / 1024, 2); ?> KB</td>
                                            <td><?php echo $backup['date']; ?></td>
                                            <td class="text-end">
                                                <a href="download_backup.php?file=<?php echo urlencode($backup['name']); ?>" class="btn btn-sm btn-outline-primary">Download</a>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteBackup('<?php echo $backup['name']; ?>')">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4">No backups found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function createBackup() {
    fetch('ajax_system_actions.php', { 
        method: 'POST', 
        body: new URLSearchParams('action=create_backup') 
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if(data.success) location.reload();
    });
}

function deleteBackup(filename) {
    if(confirm('Are you sure you want to delete this backup?')) {
        fetch('delete_backup.php', { 
            method: 'POST', 
            body: new URLSearchParams('file=' + filename) 
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if(data.success) location.reload();
        });
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
