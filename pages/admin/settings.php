<?php
require_once __DIR__ . '/../../includes/header.php';

$db = Database::getInstance();

// Get system settings
$settingsQuery = $db->query("SELECT * FROM system_settings");
$settings = [];
foreach ($settingsQuery->fetchAll(PDO::FETCH_ASSOC) as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

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
                        <li class="nav-item"><a class="nav-link active" href="settings.php"><i class="nav-icon fas fa-cog"></i><p>Settings</p></a></li>
                        <li class="nav-item"><a class="nav-link" href="logs.php"><i class="nav-icon fas fa-clipboard-list"></i><p>System Logs</p></a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
            <div class="page-header">
                <h1 class="page-title">System Settings</h1>
                <p class="page-description">Configure system-wide settings.</p>
            </div>

            <form action="system_settings_update.php" method="POST" enctype="multipart/form-data">
                <div class="card mb-4">
                    <div class="card-header"><h5>General Settings</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="site_name" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="site_email" class="form-label">Administrator Email</label>
                                <input type="email" class="form-control" id="site_email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select" id="timezone" name="timezone">
                                    <?php $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL); foreach ($timezones as $tz): ?>
                                        <option value="<?php echo $tz; ?>" <?php echo (isset($settings['timezone']) && $settings['timezone'] == $tz) ? 'selected' : ''; ?>><?php echo $tz; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="language" class="form-label">Language</label>
                                <select class="form-select" id="language" name="language">
                                    <option value="en" <?php echo (isset($settings['language']) && $settings['language'] == 'en') ? 'selected' : ''; ?>>English</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_format" class="form-label">Date Format</label>
                                <input type="text" class="form-control" id="date_format" name="date_format" value="<?php echo htmlspecialchars($settings['date_format'] ?? 'Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="time_format" class="form-label">Time Format</label>
                                <input type="text" class="form-control" id="time_format" name="time_format" value="<?php echo htmlspecialchars($settings['time_format'] ?? 'H:i:s'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5>Branding</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="site_logo" class="form-label">Site Logo</label>
                                <input type="file" class="form-control" id="site_logo" name="site_logo">
                                <?php if (isset($settings['site_logo']) && !empty($settings['site_logo'])): ?>
                                    <img src="/bsit3a_guasis/mediko/<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="Site Logo" class="img-thumbnail mt-2" style="max-height: 50px;">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="favicon" class="form-label">Favicon</label>
                                <input type="file" class="form-control" id="favicon" name="favicon">
                                <?php if (isset($settings['favicon']) && !empty($settings['favicon'])): ?>
                                    <img src="/bsit3a_guasis/mediko/<?php echo htmlspecialchars($settings['favicon']); ?>" alt="Favicon" class="img-thumbnail mt-2" style="max-height: 32px;">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5>SMTP Settings</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="smtp_host" class="form-label">SMTP Host</label>
                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="smtp_port" class="form-label">SMTP Port</label>
                                <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="smtp_user" class="form-label">SMTP Username</label>
                                <input type="text" class="form-control" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="smtp_pass" class="form-label">SMTP Password</label>
                                <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="smtp_encryption" class="form-label">Encryption</label>
                                <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                    <option value="tls" <?php echo (isset($settings['smtp_encryption']) && $settings['smtp_encryption'] == 'tls') ? 'selected' : ''; ?>>TLS</option>
                                    <option value="ssl" <?php echo (isset($settings['smtp_encryption']) && $settings['smtp_encryption'] == 'ssl') ? 'selected' : ''; ?>>SSL</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save All Settings</button>
            </form>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
