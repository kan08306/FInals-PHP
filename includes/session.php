<?php
// Session Management
if (!defined('REMEMBER_COOKIE_NAME')) {
    define('REMEMBER_COOKIE_NAME', 'shenanovents_remember');
}

if (!defined('REMEMBER_COOKIE_DAYS')) {
    define('REMEMBER_COOKIE_DAYS', 30);
}

if (session_status() === PHP_SESSION_NONE) {
    $current_session_path = session_save_path();
    $fallback_session_path = dirname(__DIR__) . '/tmp/sessions';

    if ($current_session_path === '' || !is_dir($current_session_path) || !is_writable($current_session_path)) {
        if (!is_dir($fallback_session_path)) {
            mkdir($fallback_session_path, 0777, true);
        }

        session_save_path($fallback_session_path);
    }

    session_start();
}

if (!function_exists('is_user_logged_in')) {
    // Is User Logged In
    function is_user_logged_in()
    {
        return !empty($_SESSION['is_logged_in']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('set_login_session')) {
    // Set Login Session
    function set_login_session($user)
    {
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['is_logged_in'] = true;
    }
}

if (!function_exists('clear_login_session')) {
    // Clear Login Session
    function clear_login_session()
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        unset($_SESSION['is_logged_in']);
    }
}

if (!function_exists('get_remember_cookie_options')) {
    // Get Remember Cookie Options
    function get_remember_cookie_options($expires)
    {
        $is_secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        return [
            'expires' => $expires,
            'path' => '/',
            'secure' => $is_secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }
}

if (!function_exists('delete_remember_cookie')) {
    // Delete Remember Cookie
    function delete_remember_cookie()
    {
        setcookie(REMEMBER_COOKIE_NAME, '', get_remember_cookie_options(time() - 3600));
        unset($_COOKIE[REMEMBER_COOKIE_NAME]);
    }
}

if (!function_exists('create_remember_login')) {
    // Create Remember Login
    function create_remember_login($conn, $user_id)
    {
        $token = bin2hex(random_bytes(32));
        $token_hash = password_hash($token, PASSWORD_DEFAULT);
        $expires_time = time() + (REMEMBER_COOKIE_DAYS * 24 * 60 * 60);
        $expires_at = date('Y-m-d H:i:s', $expires_time);

        $sql = 'UPDATE users SET remember_token_hash = ?, remember_token_expires_at = ? WHERE user_id = ?';
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'ssi', $token_hash, $expires_at, $user_id);
        $saved = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($saved) {
            setcookie(REMEMBER_COOKIE_NAME, $user_id . ':' . $token, get_remember_cookie_options($expires_time));
            $_COOKIE[REMEMBER_COOKIE_NAME] = $user_id . ':' . $token;
        }

        return $saved;
    }
}

if (!function_exists('clear_remember_login')) {
    // Clear Remember Login
    function clear_remember_login($conn = null)
    {
        $user_id = (int) ($_SESSION['user_id'] ?? 0);

        if ($user_id <= 0 && !empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
            $cookie_parts = explode(':', $_COOKIE[REMEMBER_COOKIE_NAME], 2);
            $user_id = (int) ($cookie_parts[0] ?? 0);
        }

        if ($conn && $user_id > 0) {
            $sql = 'UPDATE users SET remember_token_hash = NULL, remember_token_expires_at = NULL WHERE user_id = ?';
            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        delete_remember_cookie();
    }
}

if (!function_exists('restore_login_from_remember_cookie')) {
    // Restore Login From Remember Cookie
    function restore_login_from_remember_cookie($conn)
    {
        if (is_user_logged_in() || empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
            return false;
        }

        $cookie_parts = explode(':', $_COOKIE[REMEMBER_COOKIE_NAME], 2);
        $user_id = (int) ($cookie_parts[0] ?? 0);
        $token = $cookie_parts[1] ?? '';

        if ($user_id <= 0 || $token === '') {
            delete_remember_cookie();
            return false;
        }

        $sql = 'SELECT user_id, first_name, last_name, email, role, status, remember_token_hash
                FROM users
                WHERE user_id = ?
                AND remember_token_hash IS NOT NULL
                AND remember_token_expires_at > NOW()
                LIMIT 1';
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            delete_remember_cookie();
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $db_user_id, $first_name, $last_name, $email, $role, $status, $token_hash);

        if (mysqli_stmt_fetch($stmt)) {
            $user = [
                'user_id' => $db_user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'role' => $role,
                'status' => $status,
            ];

            mysqli_stmt_close($stmt);

            if (strtolower($user['status']) === 'active' && password_verify($token, $token_hash)) {
                session_regenerate_id(true);
                set_login_session($user);
                return true;
            }

            clear_remember_login($conn);
            return false;
        }

        mysqli_stmt_close($stmt);
        delete_remember_cookie();
        return false;
    }
}

if (!is_user_logged_in() && !empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
    require_once __DIR__ . '/../database/connection.php';
    restore_login_from_remember_cookie($conn);
}
