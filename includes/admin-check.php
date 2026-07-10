<?php
// Admin Access Check
// Shared Dependencies
require_once __DIR__ . '/auth-check.php';

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    $_SESSION['auth_error'] = 'Admin access is required for that page.';
    header('Location: ../participant/events.php');
    exit;
}
