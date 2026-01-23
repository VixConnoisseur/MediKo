<?php
require_once __DIR__ . '/../../includes/header.php';

$db = Database::getInstance();

// Fetch data for analytics
$date_range = $_GET['date_range'] ?? null;
$where_clause = '';
$params = [];

if ($date_range) {
    list($start_date, $end_date) = explode(' to ', $date_range);
    $where_clause = ' WHERE created_at BETWEEN :start_date AND :end_date';
    $params = [':start_date' => $start_date, ':end_date' => $end_date];
}

// User analytics
$user_growth_query = $db->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM users" . $where_clause . " GROUP BY month ORDER BY month ASC");
$user_growth_query->execute($params);
$user_growth_data = $user_growth_query->fetchAll(PDO::FETCH_ASSOC);

$user_roles_query = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$user_roles_data = $user_roles_query->fetchAll(PDO::FETCH_ASSOC);

// Medication analytics
$medication_usage_query = $db->prepare("SELECT m.name, COUNT(s.id) as schedule_count FROM medications m JOIN medication_schedules s ON m.id = s.medication_id" . str_replace('created_at', 's.created_at', $where_clause) . " GROUP BY m.id ORDER BY schedule_count DESC LIMIT 10");
$medication_usage_query->execute($params);
$medication_usage_data = $medication_usage_query->fetchAll(PDO::FETCH_ASSOC);

// Appointment analytics
$appointment_status_query = $db->prepare("SELECT status, COUNT(*) as count FROM appointments" . str_replace('created_at', 'appointment_date', $where_clause) . " GROUP BY status");
$appointment_status_query->execute($params);
$appointment_status_data = $appointment_status_query->fetchAll(PDO::FETCH_ASSOC);

// Active Users Trend (last 30 days)
$active_users_query = $db->prepare(
    "SELECT DATE(last_login) as login_date, COUNT(DISTINCT id) as user_count 
     FROM users 
     WHERE last_login >= :start_date 
     GROUP BY login_date 
     ORDER BY login_date ASC"
);
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
$active_users_query->execute([':start_date' => $thirty_days_ago]);
$active_users_data = $active_users_query->fetchAll(PDO::FETCH_ASSOC);

// Medication Adherence Rates
$adherence_query = $db->prepare(
    "SELECT 
        SUM(CASE WHEN r.status = 'taken' THEN 1 ELSE 0 END) as taken_doses, 
        COUNT(r.id) as total_doses 
     FROM reminders r"
);
$adherence_query->execute();
$adherence_data = $adherence_query->fetch(PDO::FETCH_ASSOC);

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
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/dashboard.php"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/users.php"><i class="nav-icon fas fa-users"></i><p>Users</p></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/system_management.php"><i class="nav-icon fas fa-cogs"></i><p>System Management</p></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/bsit3a_guasis/mediko/pages/admin/reports_analytics.php"><i class="nav-icon fas fa-chart-pie"></i><p>Reports & Analytics</p></a>
                        </li>
                        <li class="nav-header mt-4 mb-2"><h6 class="text-xs text-muted text-uppercase font-weight-bold">Analytics</h6></li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/medication_analytics.php"><i class="nav-icon fas fa-chart-line"></i><p>Medication Analytics</p></a>
                        </li>
                        <li class="nav-header mt-4 mb-2"><h6 class="text-xs text-muted text-uppercase font-weight-bold">System</h6></li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/backup.php"><i class="nav-icon fas fa-database"></i><p>Backup</p></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/settings.php"><i class="nav-icon fas fa-cog"></i><p>Settings</p></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/bsit3a_guasis/mediko/pages/admin/logs.php"><i class="nav-icon fas fa-clipboard-list"></i><p>System Logs</p></a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
            <div class="page-header">
                <h1 class="page-title">Reports & Analytics</h1>
                <p class="page-description">Visualize system data and generate reports.</p>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="report-type" class="form-label">Report Type</label>
                            <select id="report-type" name="report_type" class="form-select">
                                <option value="user_analytics" <?php echo ($_GET['report_type'] ?? 'user_analytics') === 'user_analytics' ? 'selected' : ''; ?>>User Analytics</option>
                                <option value="medication_analytics" <?php echo ($_GET['report_type'] ?? '') === 'medication_analytics' ? 'selected' : ''; ?>>Medication Analytics</option>
                                <option value="appointment_analytics" <?php echo ($_GET['report_type'] ?? '') === 'appointment_analytics' ? 'selected' : ''; ?>>Appointment Analytics</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date-range" class="form-label">Date Range</label>
                            <input type="text" id="date-range" name="date_range" class="form-control" placeholder="Select date range" value="<?php echo htmlspecialchars($date_range ?? ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Generate</button>
                        </div>
                        <div class="col-md-3 text-end">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="exportButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download me-2"></i>Export
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportButton">
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('csv')">Export as CSV</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel')">Export as Excel</a></li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- User Analytics -->
            <div id="user-analytics">
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">User Growth Over Time</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Monthly</button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Daily</a></li>
                                        <li><a class="dropdown-item" href="#">Weekly</a></li>
                                        <li><a class="dropdown-item active" href="#">Monthly</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body"><canvas id="userGrowthChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header"><h5 class="card-title mb-0">User Roles</h5></div>
                            <div class="card-body"><canvas id="userRolesChart"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Active Users Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="activeUsersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medication Analytics (Initially Hidden) -->
            <div id="medication-analytics" style="display: none;">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Top 10 Most Scheduled Medications</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="medicationUsageChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Medication Adherence Rates</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="adherenceRateChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Appointment Analytics (Initially Hidden) -->
            <div id="appointment-analytics" style="display: none;">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Appointment Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="appointmentStatusChart"></canvas>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flatpickr for date range
    flatpickr("#date-range", {
        mode: "range",
        dateFormat: "Y-m-d",
    });

    // Handle report type switching
    const reportTypeSelect = document.getElementById('report-type');
    const userAnalytics = document.getElementById('user-analytics');
    const medicationAnalytics = document.getElementById('medication-analytics');
    const appointmentAnalytics = document.getElementById('appointment-analytics');

    reportTypeSelect.addEventListener('change', function() {
        userAnalytics.style.display = 'none';
        medicationAnalytics.style.display = 'none';
        appointmentAnalytics.style.display = 'none';

        if (this.value === 'User Analytics') userAnalytics.style.display = 'block';
        if (this.value === 'Medication Analytics') medicationAnalytics.style.display = 'block';
        if (this.value === 'Appointment Analytics') appointmentAnalytics.style.display = 'block';
    });

    // Chart Data
    const userGrowthData = <?php echo json_encode($user_growth_data); ?>;
    const userRolesData = <?php echo json_encode($user_roles_data); ?>;
    const medicationUsageData = <?php echo json_encode($medication_usage_data); ?>;
    const appointmentStatusData = <?php echo json_encode($appointment_status_data); ?>;
    const activeUsersData = <?php echo json_encode($active_users_data); ?>;
    const adherenceData = <?php echo json_encode($adherence_data); ?>;

    // User Growth Chart
    new Chart(document.getElementById('userGrowthChart'), {
        type: 'line',
        data: {
            labels: userGrowthData.map(d => d.month),
            datasets: [{
                label: 'New Users',
                data: userGrowthData.map(d => d.count),
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                fill: true,
            }]
        }
    });

    // User Roles Chart
    new Chart(document.getElementById('userRolesChart'), {
        type: 'doughnut',
        data: {
            labels: userRolesData.map(d => d.role),
            datasets: [{
                label: 'User Roles',
                data: userRolesData.map(d => d.count),
                backgroundColor: ['rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)'],
            }]
        }
    });

    // Medication Usage Chart
    new Chart(document.getElementById('medicationUsageChart'), {
        type: 'bar',
        data: {
            labels: medicationUsageData.map(d => d.name),
            datasets: [{
                label: 'Number of Schedules',
                data: medicationUsageData.map(d => d.schedule_count),
                backgroundColor: 'rgba(245, 158, 11, 0.8)',
            }]
        },
        options: { indexAxis: 'y' }
    });

    // Appointment Status Chart
    new Chart(document.getElementById('appointmentStatusChart'), {
        type: 'pie',
        data: {
            labels: appointmentStatusData.map(d => d.status),
            datasets: [{
                label: 'Appointment Status',
                data: appointmentStatusData.map(d => d.count),
                backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(239, 68, 68, 0.8)', 'rgba(107, 114, 128, 0.8)'],
            }]
        }
    });

    // Active Users Trend Chart
    new Chart(document.getElementById('activeUsersChart'), {
        type: 'bar',
        data: {
            labels: activeUsersData.map(d => d.login_date),
            datasets: [{
                label: 'Daily Active Users',
                data: activeUsersData.map(d => d.user_count),
                backgroundColor: 'rgba(22, 163, 74, 0.5)',
                borderColor: 'rgba(22, 163, 74, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Medication Adherence Chart
    const takenDoses = adherenceData.taken_doses || 0;
    const totalDoses = adherenceData.total_doses || 0;
    const missedDoses = totalDoses - takenDoses;
    const adherenceRate = totalDoses > 0 ? (takenDoses / totalDoses) * 100 : 0;

    new Chart(document.getElementById('adherenceRateChart'), {
        type: 'doughnut',
        data: {
            labels: ['Taken', 'Missed'],
            datasets: [{
                label: 'Dose Status',
                data: [takenDoses, missedDoses],
                backgroundColor: ['rgba(22, 163, 74, 0.8)', 'rgba(239, 68, 68, 0.8)'],
                borderColor: ['rgba(22, 163, 74, 1)', 'rgba(239, 68, 68, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: `Overall Adherence Rate: ${adherenceRate.toFixed(2)}%`
                }
            }
        }
    });

    function exportReport(format) {
        const reportType = document.getElementById('report-type').value;
        const dateRange = document.getElementById('date-range').value;
        
        let reportQuery = '';
        switch(reportType) {
            case 'user_analytics':
                reportQuery = 'user_growth';
                break;
            case 'medication_analytics':
                reportQuery = 'medication_usage';
                break;
            // Add cases for other reports as needed
        }

        let url = `export.php?report_type=${reportQuery}&format=${format}`;
        if (dateRange) {
            url += `&date_range=${encodeURIComponent(dateRange)}`;
        }
        
        window.location.href = url;
    }

    // Make function global
    window.exportReport = exportReport;
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
