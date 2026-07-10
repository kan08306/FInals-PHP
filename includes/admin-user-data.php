<?php
// Shared Include Setup
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../database/connection.php';

// A Dm In U Se R A Ll Ow Ed S Ta Tu Se S
function admin_user_allowed_statuses()
{
    return ['active', 'suspended', 'inactive'];
}

// A Dm In U Se R A Ll Ow Ed R Ol Es
function admin_user_allowed_roles()
{
    return ['admin', 'participant'];
}

// A Dm In U Se R F La Sh
function admin_user_flash($type, $message)
{
    $_SESSION['admin_user_' . $type] = $message;
}

// A Dm In U Se R G Et F La Sh
function admin_user_get_flash($type)
{
    $key = 'admin_user_' . $type;
    $message = $_SESSION[$key] ?? '';
    unset($_SESSION[$key]);

    return $message;
}

// A Dm In U Se R L Ab El
function admin_user_label($value)
{
    $value = trim((string) $value);

    if ($value === '') {
        return 'Unknown';
    }

    return ucwords(str_replace(['_', '-'], ' ', strtolower($value)));
}

// A Dm In U Se R F Or Ma T D At E
function admin_user_format_date($date)
{
    $timestamp = strtotime((string) $date);

    return $timestamp ? date('m/d/Y', $timestamp) : 'N/A';
}

// A Dm In U Se R I Ni Ti Al S
function admin_user_initials($first_name, $last_name, $email = '')
{
    $first_name = trim((string) $first_name);
    $last_name = trim((string) $last_name);
    $email = trim((string) $email);
    $initials = '';

    if ($first_name !== '') {
        $initials .= strtoupper(substr($first_name, 0, 1));
    }

    if ($last_name !== '') {
        $initials .= strtoupper(substr($last_name, 0, 1));
    }

    if ($initials === '' && $email !== '') {
        $initials = strtoupper(substr($email, 0, 1));
    }

    return $initials !== '' ? $initials : 'U';
}

// A Dm In U Se R P Ro Fi Le S Rc
function admin_user_profile_src($profile_picture, $base_path = '../')
{
    $profile_picture = trim((string) $profile_picture);

    if ($profile_picture === '') {
        return '';
    }

    return $base_path . ltrim($profile_picture, '/');
}

// A Dm In U Se R F Et Ch U Se Rs
function admin_user_fetch_users($conn)
{
    $users = [];
    $sql = 'SELECT u.user_id, u.first_name, u.last_name, u.email, u.role, u.status,
                   u.profile_picture, u.created_at,
                   COALESCE((
                        SELECT COUNT(*)
                        FROM registrations r
                        WHERE r.user_id = u.user_id
                        AND r.registration_status = "registered"
                   ), 0) AS registered_count,
                   COALESCE((
                        SELECT COUNT(*)
                        FROM liked_events l
                        WHERE l.user_id = u.user_id
                   ), 0) AS liked_count,
                   COALESCE((
                        SELECT COUNT(*)
                        FROM events e
                        WHERE e.created_by = u.user_id
                   ), 0) AS created_count
            FROM users u
            ORDER BY u.created_at DESC, u.last_name ASC, u.first_name ASC';
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $row['registered_count'] = (int) ($row['registered_count'] ?? 0);
        $row['liked_count'] = (int) ($row['liked_count'] ?? 0);
        $row['created_count'] = (int) ($row['created_count'] ?? 0);
        $users[] = $row;
    }

    return $users;
}

// A Dm In U Se R F Et Ch U Se R B Y I D
function admin_user_fetch_user_by_id($conn, $user_id)
{
    $user_id = (int) $user_id;
    $sql = 'SELECT user_id, first_name, last_name, email, role, status
            FROM users
            WHERE user_id = ?
            LIMIT 1';
// Prepared Statement Setup
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $user ?: null;
}

// A Dm In U Se R E Ma Il E Xi St S
function admin_user_email_exists($conn, $email, $except_user_id)
{
    $except_user_id = (int) $except_user_id;
    $sql = 'SELECT user_id
            FROM users
            WHERE email = ?
            AND user_id <> ?
            LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return true;
    }

    mysqli_stmt_bind_param($stmt, 'si', $email, $except_user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    return $exists;
}

// A Dm In U Se R U Pd At E U Se R
function admin_user_update_user($conn, $form_data, $current_admin_id)
{
    $user_id = (int) ($form_data['user_id'] ?? ($form_data['target_user_id'] ?? 0));

    if ($user_id <= 0) {
        return ['success' => false, 'message' => 'Please select a valid user record.'];
    }

    $existing_user = admin_user_fetch_user_by_id($conn, $user_id);

    if (!$existing_user) {
        return ['success' => false, 'message' => 'User record was not found.'];
    }

    $first_name = trim((string) ($form_data['first_name'] ?? ''));
    $last_name = trim((string) ($form_data['last_name'] ?? ''));
    $email = trim((string) ($form_data['email'] ?? ''));
    $role = strtolower(trim((string) ($form_data['role'] ?? 'participant')));
    $status = strtolower(trim((string) ($form_data['status'] ?? 'active')));
    $errors = [];

    if ($first_name === '') {
        $errors[] = 'First name is required.';
    }

    if ($last_name === '') {
        $errors[] = 'Last name is required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (!in_array($role, admin_user_allowed_roles(), true)) {
        $errors[] = 'Invalid account role selected.';
    }

    if (!in_array($status, admin_user_allowed_statuses(), true)) {
        $errors[] = 'Invalid account status selected.';
    }

    if ($email !== '' && admin_user_email_exists($conn, $email, $user_id)) {
        $errors[] = 'Another user already uses that email address.';
    }

    if ($user_id === (int) $current_admin_id && ($role !== 'admin' || $status !== 'active')) {
        $errors[] = 'You cannot remove your own admin access.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'message' => implode(' ', $errors)];
    }

    $sql = 'UPDATE users
            SET first_name = ?, last_name = ?, email = ?, role = ?, status = ?
            WHERE user_id = ?';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return ['success' => false, 'message' => 'Unable to prepare the user update.'];
    }

    mysqli_stmt_bind_param($stmt, 'sssssi', $first_name, $last_name, $email, $role, $status, $user_id);
    $updated = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$updated) {
        return ['success' => false, 'message' => 'Unable to update user information.'];
    }

    return ['success' => true, 'message' => 'User information updated successfully.'];
}

// A Dm In U Se R U Pd At E S Ta Tu S
function admin_user_update_status($conn, $user_id, $status, $current_admin_id)
{
    $user_id = (int) $user_id;
    $status = strtolower(trim((string) $status));

    if ($user_id <= 0) {
        return ['success' => false, 'message' => 'Please select a valid user record.'];
    }

    $existing_user = admin_user_fetch_user_by_id($conn, $user_id);

    if (!$existing_user) {
        return ['success' => false, 'message' => 'User record was not found.'];
    }

    if (!in_array($status, admin_user_allowed_statuses(), true)) {
        return ['success' => false, 'message' => 'Invalid account status selected.'];
    }

    if ($user_id === (int) $current_admin_id && $status !== 'active') {
        return ['success' => false, 'message' => 'You cannot suspend or delete your own admin account.'];
    }

    $sql = 'UPDATE users SET status = ? WHERE user_id = ?';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return ['success' => false, 'message' => 'Unable to prepare the status update.'];
    }

    mysqli_stmt_bind_param($stmt, 'si', $status, $user_id);
    $updated = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$updated) {
        return ['success' => false, 'message' => 'Unable to update account status.'];
    }

    return ['success' => true, 'message' => 'User status updated to ' . admin_user_label($status) . '.'];
}

// A Dm In U Se R S Us Pe Nd U Se R
function admin_user_suspend_user($conn, $user_id, $current_admin_id)
{
    return admin_user_update_status($conn, $user_id, 'suspended', $current_admin_id);
}

// A Dm In U Se R R Ea Ct Iv At E U Se R
function admin_user_reactivate_user($conn, $user_id, $current_admin_id)
{
    return admin_user_update_status($conn, $user_id, 'active', $current_admin_id);
}

// A Dm In U Se R S Af E D El Et E U Se R
function admin_user_safe_delete_user($conn, $user_id, $current_admin_id)
{
    $result = admin_user_update_status($conn, $user_id, 'inactive', $current_admin_id);

    if ($result['success']) {
        $result['message'] = 'User account deleted safely by setting its status to Inactive.';
    }

    return $result;
}

// A Dm In U Se R H An Dl E P Os T
function admin_user_handle_post($conn, $redirect_path)
{
// Form Submission Handling
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['admin_user_action'])) {
        return;
    }

    $action = $_POST['admin_user_action'];
    $user_id = (int) ($_POST['user_id'] ?? ($_POST['target_user_id'] ?? 0));
    $current_admin_id = (int) ($_SESSION['user_id'] ?? 0);
    $result = ['success' => false, 'message' => 'Invalid admin user action.'];

    if ($action === 'update_user') {
        $result = admin_user_update_user($conn, $_POST, $current_admin_id);
    } elseif ($action === 'suspend_user') {
        $result = admin_user_suspend_user($conn, $user_id, $current_admin_id);
    } elseif ($action === 'reactivate_user') {
        $result = admin_user_reactivate_user($conn, $user_id, $current_admin_id);
    } elseif ($action === 'delete_user') {
        $result = admin_user_safe_delete_user($conn, $user_id, $current_admin_id);
    }

    admin_user_flash($result['success'] ? 'success' : 'error', $result['message']);
// Redirect Handling
    header('Location: ' . $redirect_path);
    exit;
}





