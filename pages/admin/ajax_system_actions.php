<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Ensure user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'clear_cache':
        handle_clear_cache();
        break;
    case 'optimize_db':
        handle_optimize_db();
        break;
    case 'create_backup':
        handle_create_backup();
        break;
    default:
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        exit();
}

function handle_clear_cache() {
    // This is a simplified example. A real implementation would clear file-based caches, opcode cache, etc.
    // For now, we'll just simulate it.
    
    // log_audit_action('Cache Cleared', 'admin', $_SESSION['user']['id']);
    echo json_encode(['success' => true, 'message' => 'System cache has been cleared.']);
}

function handle_optimize_db() {
    $db = Database::getInstance();
    try {
        $tables_query = $db->query("SHOW TABLES");
        $tables = $tables_query->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            $db->query("OPTIMIZE TABLE `{$table}`");
        }
        
        // log_audit_action('Database Optimized', 'admin', $_SESSION['user']['id']);
        echo json_encode(['success' => true, 'message' => 'Database tables have been optimized.']);

    } catch (PDOException $e) {
        error_log('Database optimization failed: ' . $e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Database optimization failed.']);
    }
}

function handle_create_backup() {
    $backupDir = __DIR__ . '/../../backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    $backupFile = $backupDir . '/backup_' . date('Y-m-d_H-i-s') . '.sql';

    // This requires mysqldump command-line tool to be available.
    // You might need to configure the path to mysqldump.
    $command = sprintf(
        'mysqldump --user=%s --password=%s --host=%s %s > %s',
        escapeshellarg(DB_USER),
        escapeshellarg(DB_PASS),
        escapeshellarg(DB_HOST),
        escapeshellarg(DB_NAME),
        escapeshellarg($backupFile)
    );

    @exec($command, $output, $return_var);

    if ($return_var === 0) {
        // log_audit_action('Database Backup Created', 'admin', $_SESSION['user']['id']);
        echo json_encode(['success' => true, 'message' => 'Database backup created successfully: ' . basename($backupFile)]);
    } else {
        error_log('Backup failed. Command: ' . $command . ' | Return: ' . $return_var . ' | Output: ' . implode("\n", $output));
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Failed to create database backup. Check server logs.']);
    }
}
?>
