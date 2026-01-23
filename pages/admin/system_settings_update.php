<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $uploadDir = __DIR__ . '/../../uploads/branding/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $settings = [
        'site_name' => $_POST['site_name'] ?? 'MediKo',
        'site_email' => $_POST['site_email'] ?? 'admin@mediko.com',
        'timezone' => $_POST['timezone'] ?? 'UTC',
        'language' => $_POST['language'] ?? 'en',
        'date_format' => $_POST['date_format'] ?? 'Y-m-d',
        'time_format' => $_POST['time_format'] ?? 'H:i:s',
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '587',
        'smtp_user' => $_POST['smtp_user'] ?? '',
        'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
    ];

    // Handle SMTP password separately
    if (!empty($_POST['smtp_pass'])) {
        $settings['smtp_pass'] = $_POST['smtp_pass']; // In a real app, this should be encrypted
    }

    // Handle file uploads
    $allowed_images = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
    $allowed_favicon = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png'];

    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
        if (in_array($_FILES['site_logo']['type'], $allowed_images)) {
            $logo_filename = 'logo.' . pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['site_logo']['tmp_name'], $uploadDir . $logo_filename);
            $settings['site_logo'] = 'uploads/branding/' . $logo_filename;
        }
    }

    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] == 0) {
        if (in_array($_FILES['favicon']['type'], $allowed_favicon)) {
            $favicon_filename = 'favicon.' . pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['favicon']['tmp_name'], $uploadDir . $favicon_filename);
            $settings['favicon'] = 'uploads/branding/' . $favicon_filename;
        }
    }

    try {
        $db->getPdo()->beginTransaction();
        foreach ($settings as $key => $value) {
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value");
            $stmt->bindValue(':key', $key);
            $stmt->bindValue(':value', $value);
            $stmt->execute();
        }
        $db->getPdo()->commit();

        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'System settings updated successfully.'];
        header('Location: settings.php');
        exit();

    } catch (PDOException $e) {
        $db->getPdo()->rollBack();
        error_log('System settings update failed: ' . $e->getMessage());
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to update settings. Please check the logs.'];
        header('Location: settings.php');
        exit();
    }
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}
?>
