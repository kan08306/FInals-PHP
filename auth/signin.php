<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/connection.php';

if (is_user_logged_in()) {
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        header('Location: ../admin/admin-dashboard.php');
        exit;
    }

    header('Location: ../participant/events.php');
    exit;
}

$page_title = 'Shenanovents | Sign In';
$current_page = 'signin';
$asset_version = 'frontend-standards';
$base_path = '../';
$signin_errors = [];
$email = '';
$success_message = $_SESSION['auth_success'] ?? '';
$notice_message = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_success'], $_SESSION['auth_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $signin_errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signin_errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $signin_errors[] = 'Password is required.';
    }

    if (empty($signin_errors)) {
        $login_sql = 'SELECT user_id, first_name, last_name, email, password, role, status FROM users WHERE email = ? LIMIT 1';
        $login_stmt = mysqli_prepare($conn, $login_sql);

        if ($login_stmt) {
            mysqli_stmt_bind_param($login_stmt, 's', $email);
            mysqli_stmt_execute($login_stmt);
            mysqli_stmt_bind_result($login_stmt, $user_id, $first_name, $last_name, $user_email, $hashed_password, $role, $status);

            if (mysqli_stmt_fetch($login_stmt)) {
                $user = [
                    'user_id' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $user_email,
                    'password' => $hashed_password,
                    'role' => $role,
                    'status' => $status,
                ];

                if (strtolower($user['status']) !== 'active') {
                    $signin_errors[] = 'Your account is not active. Please contact the administrator.';
                } elseif (password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    set_login_session($user);
                    mysqli_stmt_close($login_stmt);

                    if (!empty($_POST['remember'])) {
                        create_remember_login($conn, (int) $user['user_id']);
                    } else {
                        clear_remember_login($conn);
                    }

                    if ($user['role'] === 'admin') {
                        header('Location: ../admin/admin-dashboard.php');
                        exit;
                    }

                    header('Location: ../participant/events.php');
                    exit;
                } else {
                    $signin_errors[] = 'Email or password is incorrect.';
                }
            } else {
                $signin_errors[] = 'Email or password is incorrect.';
            }

            mysqli_stmt_close($login_stmt);
        } else {
            $signin_errors[] = 'Unable to prepare the signin request.';
        }
    }
}

$safe_page_title = htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8');
$safe_asset_version = urlencode($asset_version);
$safe_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $safe_page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo $safe_asset_version; ?>">
</head>
<body class="auth-page">
    <main class="auth-main" aria-labelledby="signinTitle">
        <div class="auth-card">
            <h1 class="auth-card-title" id="signinTitle">Sign in Account</h1>
            <p class="auth-card-subtitle">Enter your personal information to access your account.</p>

            <?php if ($success_message !== ''): ?>
                <div class="auth-feedback auth-feedback-success" role="status">
                    <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($notice_message !== ''): ?>
                <div class="auth-feedback auth-feedback-error" role="alert">
                    <?php echo htmlspecialchars($notice_message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($signin_errors)): ?>
                <div class="auth-feedback auth-feedback-error" role="alert">
                    <ul>
                        <?php foreach ($signin_errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="auth-form" action="signin.php" method="post">
                <div class="auth-input-wrap">
                    <input type="email" name="email" autocomplete="email" placeholder="Email" aria-label="Email" value="<?php echo $safe_email; ?>" required>
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <polyline points="2,4 12,13 22,4"></polyline>
                        </svg>
                    </span>
                </div>

                <div class="auth-input-wrap">
                    <input type="password" name="password" id="password-field" autocomplete="current-password" placeholder="Password" aria-label="Password" required>
                    <span class="auth-input-icon auth-password-toggle" id="toggle-password" role="button" tabindex="0" aria-label="Toggle password visibility" title="Show/hide password">
                        <svg id="eye-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </span>
                </div>

                <div class="auth-row-options">
                    <label class="auth-remember-label">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a class="auth-forgot-link" href="forgot-password.php">Forgot password?</a>
                </div>

                <button class="auth-signin-button" type="submit">Sign In</button>

                <p class="auth-legal">
                    By signing in, you agree to Shenanovents'
                    <a href="../terms-of-service.php">Terms of Service</a> and <a href="../privacy-policy.php">Privacy Policy</a>.
                </p>

                <p class="auth-signup-row">
                    Don't have an account? <a href="signup.php">Sign up</a>
                </p>
            </form>
        </div>
    </main>

    <script src="../assets/js/main.js?v=<?php echo $safe_asset_version; ?>"></script>
</body>
</html>
