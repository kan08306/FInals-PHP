<?php
// Shared Include Setup
require_once __DIR__ . '/auth-check.php';

if (($_SESSION['user_role'] ?? '') !== 'participant') {
    if (($_SESSION['user_role'] ?? '') === 'admin') {
// Redirect Handling
        header('Location: ../admin/admin-dashboard.php');
        exit;
    }

    $_SESSION['auth_error'] = 'Participant access is required for that page.';
    header('Location: ../auth/signin.php');
    exit;
}



