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

$page_title = 'Sign Up - Shenanovents';
$asset_version = 'security-select-polish';
$base_path = '../';
$signup_errors = [];
$first_name = '';
$last_name = '';
$email = '';
$security_question = '';
$security_questions = [
    'What is your favorite childhood nickname?',
    'What is the name of your first pet?',
    'What city were you born in?',
    "What is your favorite teacher's name?",
    'What is your favorite food?',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $security_question = trim($_POST['security_question'] ?? '');
    $security_answer = trim($_POST['security_answer'] ?? '');

    if ($first_name === '') {
        $signup_errors[] = 'First name is required.';
    }

    if ($last_name === '') {
        $signup_errors[] = 'Last name is required.';
    }

    if ($email === '') {
        $signup_errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signup_errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $signup_errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $signup_errors[] = 'Password must be at least 6 characters long.';
    }

    if ($security_question === '') {
        $signup_errors[] = 'Security question is required.';
    } elseif (!in_array($security_question, $security_questions, true)) {
        $signup_errors[] = 'Please choose a valid security question.';
    }

    if ($security_answer === '') {
        $signup_errors[] = 'Security answer is required.';
    }

    if (empty($signup_errors)) {
        $check_sql = 'SELECT user_id FROM users WHERE email = ? LIMIT 1';
        $check_stmt = mysqli_prepare($conn, $check_sql);

        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, 's', $email);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $signup_errors[] = 'This email address is already registered.';
            }

            mysqli_stmt_close($check_stmt);
        } else {
            $signup_errors[] = 'Unable to check the email address right now.';
        }
    }

    if (empty($signup_errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $hashed_security_answer = password_hash(strtolower($security_answer), PASSWORD_DEFAULT);
        $role = 'participant';
        $status = 'active';
        $insert_sql = 'INSERT INTO users (first_name, last_name, email, password, role, status, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $insert_stmt = mysqli_prepare($conn, $insert_sql);

        if ($insert_stmt) {
            mysqli_stmt_bind_param($insert_stmt, 'ssssssss', $first_name, $last_name, $email, $hashed_password, $role, $status, $security_question, $hashed_security_answer);

            if (mysqli_stmt_execute($insert_stmt)) {
                $_SESSION['auth_success'] = 'Account created successfully. Please sign in.';
                mysqli_stmt_close($insert_stmt);
                header('Location: signin.php');
                exit;
            }

            $signup_errors[] = 'Unable to create the account. Please try again.';
            mysqli_stmt_close($insert_stmt);
        } else {
            $signup_errors[] = 'Unable to prepare the signup request.';
        }
    }
}

$safe_page_title = htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8');
$safe_asset_version = urlencode($asset_version);
$safe_first_name = htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8');
$safe_last_name = htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8');
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
<body class="signup-page">
    <div class="signup-layout">
        <aside class="signup-left" aria-labelledby="signupHeroTitle">
            <div class="signup-promo-group">
                <a class="signup-logo auth-logo-lockup" href="../index.php" aria-label="Shenanovents home">
                    <img src="../assets/images/logos/logonnmae.png" alt="Shenanovents logo">
                </a>

                <div class="signup-promo-main">
                    <div class="signup-hero-row">
                        <h1 id="signupHeroTitle">Get Started<br>with Us</h1>
                        <p>Complete these easy steps to register your account.</p>
                    </div>

                    <div class="signup-steps" aria-label="Registration steps">
                        <div class="signup-step-card active">
                            <div class="signup-step-badge"><span>1</span></div>
                            <p>Sign up your account</p>
                        </div>

                        <div class="signup-step-card inactive">
                            <div class="signup-step-badge"><span>2</span></div>
                            <p>Setup your events</p>
                        </div>

                        <div class="signup-step-card inactive">
                            <div class="signup-step-badge"><span>3</span></div>
                            <p>Register your events</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="signup-right">
            <section class="signup-form-shell" aria-labelledby="signupFormTitle">
                <h2 id="signupFormTitle">Sign up Account</h2>
                <p class="signup-subtitle">Enter your personal data to create your account.</p>

                <?php if (!empty($signup_errors)): ?>
                    <div class="auth-feedback auth-feedback-error" role="alert">
                        <ul>
                            <?php foreach ($signup_errors as $error): ?>
                                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="signup.php" method="post" class="signup-form">
                    <div class="signup-name-row">
                        <div class="signup-field">
                            <label class="sr-only" for="firstName">First Name</label>
                            <input type="text" id="firstName" name="first_name" placeholder="First Name" autocomplete="given-name" value="<?php echo $safe_first_name; ?>" required>
                        </div>

                        <div class="signup-field">
                            <label class="sr-only" for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="last_name" placeholder="Last Name" autocomplete="family-name" value="<?php echo $safe_last_name; ?>" required>
                        </div>
                    </div>

                    <div class="signup-field">
                        <label class="sr-only" for="emailAddress">Email</label>
                        <input type="email" id="emailAddress" name="email" placeholder="Email" autocomplete="email" value="<?php echo $safe_email; ?>" required>
                        <span class="signup-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <path d="M2 7l10 7 10-7"></path>
                            </svg>
                        </span>
                    </div>

                    <div class="signup-field">
                        <label class="sr-only" for="passwordInput">Password</label>
                        <input type="password" id="passwordInput" name="password" placeholder="Password" autocomplete="new-password" required>
                        <button class="signup-field-icon signup-password-toggle" type="button" aria-label="Show password" data-password-toggle>
                            <svg id="passwordEyeIcon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path>
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>

                    <div class="signup-field signup-security-field">
                        <label class="sr-only" for="securityQuestion">Security Question</label>
                        <span class="signup-security-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 3l7 3v5c0 4.5-2.8 8.4-7 10-4.2-1.6-7-5.5-7-10V6l7-3z"></path>
                                <path d="M9.6 10.2a2.4 2.4 0 1 1 3.8 1.9c-.7.5-1.1 1-1.1 1.8"></path>
                                <circle cx="12" cy="17" r=".5"></circle>
                            </svg>
                        </span>
                        <select class="signup-security-select" id="securityQuestion" name="security_question" required>
                            <option value="">Choose a security question</option>
                            <?php foreach ($security_questions as $question): ?>
                                <?php $safe_question = htmlspecialchars($question, ENT_QUOTES, 'UTF-8'); ?>
                                <option value="<?php echo $safe_question; ?>" <?php echo $security_question === $question ? 'selected' : ''; ?>>
                                    <?php echo $safe_question; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="signup-security-caret" aria-hidden="true"></span>
                    </div>

                    <div class="signup-field">
                        <label class="sr-only" for="securityAnswer">Security Answer</label>
                        <input type="text" id="securityAnswer" name="security_answer" placeholder="Security Answer" autocomplete="off" required>
                    </div>

                    <button class="signup-submit" type="submit">Sign Up</button>
                </form>

                <p class="signup-legal">
                    By signing up, you agree to Shenanovents'
                    <a href="../terms-of-service.php">Terms of Service</a> and <a href="../privacy-policy.php">Privacy Policy</a>.
                </p>

                <p class="signup-signin">Already have an account? <a href="signin.php">Sign in</a></p>
            </section>
        </main>
    </div>

    <script src="../assets/js/main.js?v=<?php echo $safe_asset_version; ?>"></script>
</body>
</html>
