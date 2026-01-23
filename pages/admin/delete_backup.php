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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileName = $_POST['file'] ?? '';
    $fileName = basename($fileName);
    $backupDir = __DIR__ . '/../../backups';
    $filePath = $backupDir . '/' . $fileName;

    // Security check
    if (strpos(realpath($filePath), realpath($backupDir)) !== 0) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'message' => 'Invalid file path.']);
        exit();
    }

    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            // log_audit_action('Backup Deleted', 'admin', $_SESSION['user']['id'], ['file' => $fileName]);
            echo json_encode(['success' => true, 'message' => 'Backup file ' . htmlspecialchars($fileName) . ' deleted successfully.']);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'message' => 'Failed to delete backup file.']);
        }
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'File not found.']);
    }
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}
?>
