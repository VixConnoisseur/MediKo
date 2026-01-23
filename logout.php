<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/auth.php';

$db = Database::getInstance();
$auth = new Auth($db);

// Logout the user
$auth->logout();

// Redirect to landing page
header('Location: /bsit3a_guasis/mediko/');
exit();
