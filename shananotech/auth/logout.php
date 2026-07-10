<?php
// Authentication Page Setup
// Shared Dependencies
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/connection.php';

clear_remember_login($conn);
clear_login_session();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    unset($_COOKIE[session_name()]);
}

session_destroy();

// Redirect Handling
header('Location: ../index.php');
exit;
