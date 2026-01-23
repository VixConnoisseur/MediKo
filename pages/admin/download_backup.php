<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    die('Unauthorized access.');
}

if (isset($_GET['file'])) {
    $fileName = basename($_GET['file']);
    $backupDir = __DIR__ . '/../../backups';
    $filePath = $backupDir . '/' . $fileName;

    // Security check: ensure the file is within the backup directory
    if (strpos(realpath($filePath), realpath($backupDir)) !== 0) {
        header('HTTP/1.1 400 Bad Request');
        die('Invalid file path.');
    }

    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        header('HTTP/1.1 404 Not Found');
        die('File not found.');
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    die('No file specified.');
}
?>
