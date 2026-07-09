<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../database/connection.php';

if (isset($_GET['restart'])) {
    unset($_SESSION['password_reset_user_id']);
    unset($_SESSION['password_reset_email']);
    unset($_SESSION['password_reset_question']);
    unset($_SESSION['password_reset_verified']);
    header('Location: forgot-password.php');
    exit;
}

if (is_user_logged_in()) {
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        header('Location: ../admin/admin-dashboard.php');
        exit;
    }

    header('Location: ../participant/events.php');
    exit;
}

$page_title = 'Shenanovents | Forgot Password';
$asset_version = 'frontend-standards';
$forgot_errors = [];
$email = '';
$security_question = '';
$step = 'email';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'check_email';

    if ($action === 'check_email') {
        $email = trim($_POST['email'] ?? '');

        if ($email === '') {
            $forgot_errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $forgot_errors[] = 'Please enter a valid email address.';
        }

        if (empty($forgot_errors)) {
            $sql = 'SELECT user_id, security_question, security_answer FROM users WHERE email = ? LIMIT 1';
            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $user_id, $db_security_question, $db_security_answer);

                if (mysqli_stmt_fetch($stmt)) {
                    if ($db_security_question === null || $db_security_question === '' || $db_security_answer === null || $db_security_answer === '') {
                        $forgot_errors[] = 'This account does not have a security question yet. Please contact the administrator.';
                    } else {
                        $_SESSION['password_reset_user_id'] = (int) $user_id;
                        $_SESSION['password_reset_email'] = $email;
                        $_SESSION['password_reset_question'] = $db_security_question;
                        unset($_SESSION['password_reset_verified']);
                        $security_question = $db_security_question;
                        $step = 'answer';
                    }
                } else {
                    $forgot_errors[] = 'No account was found with that email address.';
                }

                mysqli_stmt_close($stmt);
            } else {
                $forgot_errors[] = 'Unable to check the email address right now.';
            }
        }
    } elseif ($action === 'verify_answer') {
        $email = $_SESSION['password_reset_email'] ?? '';
        $security_question = $_SESSION['password_reset_question'] ?? '';
        $user_id = (int) ($_SESSION['password_reset_user_id'] ?? 0);
        $security_answer = trim($_POST['security_answer'] ?? '');

        if ($user_id <= 0 || $email === '' || $security_question === '') {
            $forgot_errors[] = 'Please enter your email first.';
            $step = 'email';
        } elseif ($security_answer === '') {
            $forgot_errors[] = 'Security answer is required.';
            $step = 'answer';
        } else {
            $sql = 'SELECT security_answer FROM users WHERE user_id = ? LIMIT 1';
            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $saved_security_answer);

                if (mysqli_stmt_fetch($stmt) && password_verify(strtolower($security_answer), $saved_security_answer)) {
                    $_SESSION['password_reset_verified'] = true;
                    $step = 'reset';
                } else {
                    $forgot_errors[] = 'Security answer is incorrect.';
                    $step = 'answer';
                }

                mysqli_stmt_close($stmt);
            } else {
                $forgot_errors[] = 'Unable to verify the security answer right now.';
                $step = 'answer';
            }
        }
    } elseif ($action === 'reset_password') {
        $email = $_SESSION['password_reset_email'] ?? '';
        $security_question = $_SESSION['password_reset_question'] ?? '';
        $user_id = (int) ($_SESSION['password_reset_user_id'] ?? 0);
        $is_verified = !empty($_SESSION['password_reset_verified']);
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($user_id <= 0 || !$is_verified) {
            $forgot_errors[] = 'Please verify your security answer first.';
            $step = 'email';
        }

        if ($new_password === '') {
            $forgot_errors[] = 'New password is required.';
        } elseif (strlen($new_password) < 6) {
            $forgot_errors[] = 'New password must be at least 6 characters long.';
        }

        if ($confirm_password === '') {
            $forgot_errors[] = 'Confirm password is required.';
        } elseif ($new_password !== $confirm_password) {
            $forgot_errors[] = 'New password and confirm password must match.';
        }

        if (empty($forgot_errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = 'UPDATE users SET password = ?, remember_token_hash = NULL, remember_token_expires_at = NULL WHERE user_id = ?';
            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $hashed_password, $user_id);

                if (mysqli_stmt_execute($stmt)) {
                    unset($_SESSION['password_reset_user_id']);
                    unset($_SESSION['password_reset_email']);
                    unset($_SESSION['password_reset_question']);
                    unset($_SESSION['password_reset_verified']);
                    delete_remember_cookie();
                    $_SESSION['auth_success'] = 'Password reset successfully. Please sign in with your new password.';
                    mysqli_stmt_close($stmt);
                    header('Location: signin.php');
                    exit;
                }

                $forgot_errors[] = 'Unable to reset the password. Please try again.';
                mysqli_stmt_close($stmt);
            } else {
                $forgot_errors[] = 'Unable to prepare the password reset request.';
            }
        }

        if (!empty($forgot_errors) && $step !== 'email') {
            $step = $is_verified ? 'reset' : 'answer';
        }
    }
} elseif (!empty($_SESSION['password_reset_verified'])) {
    $email = $_SESSION['password_reset_email'] ?? '';
    $security_question = $_SESSION['password_reset_question'] ?? '';
    $step = 'reset';
} elseif (!empty($_SESSION['password_reset_user_id'])) {
    $email = $_SESSION['password_reset_email'] ?? '';
    $security_question = $_SESSION['password_reset_question'] ?? '';
    $step = 'answer';
}

$safe_page_title = htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8');
$safe_asset_version = urlencode($asset_version);
$safe_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$safe_security_question = htmlspecialchars($security_question, ENT_QUOTES, 'UTF-8');
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
    <main class="auth-main" aria-labelledby="forgotPasswordTitle">
        <div class="auth-card">
            <h1 class="auth-card-title" id="forgotPasswordTitle">Forgot Password</h1>
            <p class="auth-card-subtitle">Answer your security question to reset your account password.</p>

            <?php if (!empty($forgot_errors)): ?>
                <div class="auth-feedback auth-feedback-error" role="alert">
                    <ul>
                        <?php foreach ($forgot_errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($step === 'email'): ?>
                <form class="auth-form" action="forgot-password.php" method="post">
                    <input type="hidden" name="action" value="check_email">

                    <div class="auth-input-wrap">
                        <input type="email" name="email" autocomplete="email" placeholder="Email" aria-label="Email" value="<?php echo $safe_email; ?>" required>
                        <span class="auth-input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <polyline points="2,4 12,13 22,4"></polyline>
                            </svg>
                        </span>
                    </div>

                    <button class="auth-signin-button" type="submit">Continue</button>

                    <p class="auth-signup-row">
                        Remember your password? <a href="signin.php">Sign in</a>
                    </p>
                </form>
            <?php elseif ($step === 'answer'): ?>
                <form class="auth-form" action="forgot-password.php" method="post">
                    <input type="hidden" name="action" value="verify_answer">

                    <div class="auth-feedback auth-feedback-success" role="status">
                        <?php echo $safe_security_question; ?>
                    </div>

                    <div class="auth-input-wrap">
                        <input type="text" name="security_answer" autocomplete="off" placeholder="Security Answer" aria-label="Security Answer" required>
                    </div>

                    <button class="auth-signin-button" type="submit">Verify Answer</button>

                    <p class="auth-signup-row">
                        Use another email? <a href="forgot-password.php?restart=1">Start again</a>
                    </p>
                </form>
            <?php else: ?>
                <form class="auth-form" action="forgot-password.php" method="post">
                    <input type="hidden" name="action" value="reset_password">

                    <div class="auth-input-wrap">
                        <input type="password" name="new_password" autocomplete="new-password" placeholder="New Password" aria-label="New Password" required>
                    </div>

                    <div class="auth-input-wrap">
                        <input type="password" name="confirm_password" autocomplete="new-password" placeholder="Confirm New Password" aria-label="Confirm New Password" required>
                    </div>

                    <button class="auth-signin-button" type="submit">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/main.js?v=<?php echo $safe_asset_version; ?>"></script>
</body>
</html>
