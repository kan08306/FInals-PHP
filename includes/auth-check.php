<?php
require_once __DIR__ . '/session.php';

$signin_redirect_path = $signin_redirect_path ?? '../auth/signin.php';

if (!is_user_logged_in()) {
    $_SESSION['auth_error'] = 'Please sign in first to continue.';
    header('Location: ' . $signin_redirect_path);
    exit;
}
