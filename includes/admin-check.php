<?php
// Shared Include Setup
require_once __DIR__ . '/auth-check.php';

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    $_SESSION['auth_error'] = 'Admin access is required for that page.';
// Redirect Handling
    header('Location: ../participant/events.php');
    exit;
}



