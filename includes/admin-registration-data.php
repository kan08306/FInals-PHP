<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/admin-event-data.php';
require_once __DIR__ . '/admin-user-data.php';

function admin_registration_status_options()
{
    return [
        'registered' => 'Registered',
        'cancelled' => 'Cancelled',
    ];
}

function admin_registration_flash($type, $message)
{
    $_SESSION['admin_registration_' . $type] = $message;
}

function admin_registration_get_flash($type)
{
    $key = 'admin_registration_' . $type;
    $message = $_SESSION[$key] ?? '';
    unset($_SESSION[$key]);

    return $message;
}

function admin_registration_label($value)
{
    $value = trim((string) $value);

    if ($value === '') {
        return 'Unknown';
    }

    return ucwords(str_replace(['_', '-'], ' ', strtolower($value)));
}

function admin_registration_count($conn, $sql)
{
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);

    return (int) ($row['total'] ?? 0);
}

function admin_registration_fetch_event_options($conn)
{
    $events = [];
    $sql = 'SELECT event_id, event_title, event_date, event_time, event_end_time, status
            FROM events
            ORDER BY event_date DESC, event_time DESC, created_at DESC';
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }

    return $events;
}

function admin_registration_fetch_summary($conn)
{
    return [
        'total' => admin_registration_count($conn, 'SELECT COUNT(*) AS total FROM registrations'),
        'registered' => admin_registration_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "registered"'),
        'cancelled' => admin_registration_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "cancelled"'),
    ];
}

function admin_registration_fetch_registrations($conn, $event_id = 0, $status_filter = '')
{
    $event_id = (int) $event_id;
    $status_filter = strtolower(trim((string) $status_filter));
    $allowed_statuses = array_keys(admin_registration_status_options());
    $where = [];

    if ($event_id > 0) {
        $where[] = 'r.event_id = ' . $event_id;
    }

    if (in_array($status_filter, $allowed_statuses, true)) {
        $where[] = 'LOWER(r.registration_status) = "' . mysqli_real_escape_string($conn, $status_filter) . '"';
    }

    $sql = 'SELECT r.registration_id, r.user_id, r.event_id, r.registration_full_name,
                   r.registration_email, r.contact_number, r.attendee_count, r.special_notes,
                   r.registration_status, r.attendance_code, r.attendance_status, r.attendance_marked_at, r.registered_at,
                   u.first_name, u.last_name, u.email AS user_email, u.profile_picture,
                   e.event_title, e.event_date, e.event_time, e.event_end_time, e.capacity,
                   e.status AS event_status
            FROM registrations r
            INNER JOIN users u ON u.user_id = r.user_id
            INNER JOIN events e ON e.event_id = r.event_id';

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY r.registered_at DESC, e.event_date DESC, e.event_time DESC';
    $registrations = [];
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $row['attendee_count'] = (int) ($row['attendee_count'] ?? 1);
        $row['full_name'] = trim((string) ($row['registration_full_name'] ?? ''));

        if ($row['full_name'] === '') {
            $row['full_name'] = trim((string) $row['first_name'] . ' ' . (string) $row['last_name']);
        }

        if ($row['full_name'] === '') {
            $row['full_name'] = $row['user_email'];
        }

        $row['display_email'] = trim((string) ($row['registration_email'] ?? ''));

        if ($row['display_email'] === '') {
            $row['display_email'] = $row['user_email'];
        }

        $row['marked_at'] = $row['attendance_marked_at'] ?? null;
        $row['attendance_label'] = admin_registration_label($row['attendance_status'] ?? 'pending');
        $registrations[] = $row;
    }

    return $registrations;
}

function admin_registration_fetch_registration_by_id($conn, $registration_id)
{
    $registration_id = (int) $registration_id;
    $sql = 'SELECT r.registration_id, r.event_id, r.attendee_count, r.registration_status,
                   e.capacity, e.event_title
            FROM registrations r
            INNER JOIN events e ON e.event_id = r.event_id
            WHERE r.registration_id = ?
            LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'i', $registration_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $registration = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $registration ?: null;
}

function admin_registration_can_restore($conn, $registration)
{
    $event_id = (int) ($registration['event_id'] ?? 0);
    $registration_id = (int) ($registration['registration_id'] ?? 0);
    $attendee_count = (int) ($registration['attendee_count'] ?? 1);
    $capacity = (int) ($registration['capacity'] ?? 0);
    $sql = 'SELECT COALESCE(SUM(attendee_count), 0) AS active_attendees
            FROM registrations
            WHERE event_id = ?
            AND registration_id <> ?
            AND LOWER(registration_status) = "registered"';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'ii', $event_id, $registration_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $active_attendees = (int) ($row['active_attendees'] ?? 0);

    return ($active_attendees + $attendee_count) <= $capacity;
}

function admin_registration_update_status($conn, $registration_id, $status)
{
    $registration_id = (int) $registration_id;
    $status = strtolower(trim((string) $status));
    $allowed_statuses = array_keys(admin_registration_status_options());

    if ($registration_id <= 0) {
        return ['success' => false, 'message' => 'Please select a valid registration record.'];
    }

    if (!in_array($status, $allowed_statuses, true)) {
        return ['success' => false, 'message' => 'Invalid registration status selected.'];
    }

    $registration = admin_registration_fetch_registration_by_id($conn, $registration_id);

    if (!$registration) {
        return ['success' => false, 'message' => 'Registration record was not found.'];
    }

    if ($status === 'registered' && !admin_registration_can_restore($conn, $registration)) {
        return ['success' => false, 'message' => 'This registration cannot be restored because the event capacity would be exceeded.'];
    }

    mysqli_begin_transaction($conn);

    if ($status === 'registered') {
        $sql = 'UPDATE registrations
                SET registration_status = "registered",
                    attendance_code = COALESCE(attendance_code, CONCAT("SHNV-", YEAR(CURDATE()), "-", UPPER(SUBSTRING(MD5(CONCAT(registration_id, "-", user_id, "-", event_id, "-", registered_at)), 1, 6)))),
                    attendance_status = "pending",
                    attendance_marked_at = NULL,
                    registered_at = NOW()
                WHERE registration_id = ?';
    } else {
        $sql = 'UPDATE registrations
                SET registration_status = "cancelled",
                    attendance_status = "pending",
                    attendance_marked_at = NULL
                WHERE registration_id = ?';
    }

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'Unable to prepare the registration update.'];
    }

    mysqli_stmt_bind_param($stmt, 'i', $registration_id);
    $updated = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$updated) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'Unable to update the registration.'];
    }

    if ($status === 'cancelled') {
        $attendance_sql = 'DELETE FROM attendance WHERE registration_id = ?';
        $attendance_stmt = mysqli_prepare($conn, $attendance_sql);

        if ($attendance_stmt) {
            mysqli_stmt_bind_param($attendance_stmt, 'i', $registration_id);
            mysqli_stmt_execute($attendance_stmt);
            mysqli_stmt_close($attendance_stmt);
        }
    }

    mysqli_commit($conn);

    return [
        'success' => true,
        'message' => 'Registration status updated to ' . admin_registration_label($status) . '.',
    ];
}

function admin_registration_handle_post($conn, $redirect_path)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['admin_registration_action'])) {
        return;
    }

    $action = $_POST['admin_registration_action'];
    $registration_id = (int) ($_POST['registration_id'] ?? 0);
    $result = ['success' => false, 'message' => 'Invalid registration action.'];

    if ($action === 'cancel_registration') {
        $result = admin_registration_update_status($conn, $registration_id, 'cancelled');
    } elseif ($action === 'restore_registration') {
        $result = admin_registration_update_status($conn, $registration_id, 'registered');
    }

    admin_registration_flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: ' . $redirect_path);
    exit;
}
