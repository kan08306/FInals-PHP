<?php
// Admin Attendance Management Helpers
// Shared Dependencies
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/admin-registration-data.php';

// Attendance Status Options
function admin_attendance_status_options()
{
    return [
        'present' => 'Present',
        'absent' => 'Absent',
    ];
}

// Attendance Filter Options
function admin_attendance_filter_options()
{
    return [
        'all' => 'All Attendance',
        'pending' => 'Pending',
        'present' => 'Present',
        'absent' => 'Absent',
    ];
}

// Attendance Flash
function admin_attendance_flash($type, $message)
{
    $_SESSION['admin_attendance_' . $type] = $message;
}

// Attendance Get Flash
function admin_attendance_get_flash($type)
{
    $key = 'admin_attendance_' . $type;
    $message = $_SESSION[$key] ?? '';
    unset($_SESSION[$key]);

    return $message;
}

// Attendance Label
function admin_attendance_label($value)
{
    return admin_registration_label($value);
}

// Attendance Format Datetime
function admin_attendance_format_datetime($date_time)
{
    $timestamp = strtotime((string) $date_time);

    return $timestamp ? date('m/d/Y g:i A', $timestamp) : 'N/A';
}

// Attendance Count
function admin_attendance_count($conn, $sql)
{
    return admin_registration_count($conn, $sql);
}

// Attendance Fetch Summary
function admin_attendance_fetch_summary($conn, $event_id = 0)
{
    $event_id = (int) $event_id;
    $event_condition = $event_id > 0 ? ' AND r.event_id = ' . $event_id : '';

    return [
        'registered' => admin_attendance_count($conn, 'SELECT COUNT(*) AS total FROM registrations r WHERE LOWER(r.registration_status) = "registered"' . $event_condition),
        'pending' => admin_attendance_count($conn, 'SELECT COUNT(*) AS total FROM registrations r WHERE LOWER(r.registration_status) = "registered" AND LOWER(r.attendance_status) = "pending"' . $event_condition),
        'present' => admin_attendance_count($conn, 'SELECT COUNT(*) AS total FROM registrations r WHERE LOWER(r.registration_status) = "registered" AND LOWER(r.attendance_status) = "present"' . $event_condition),
        'absent' => admin_attendance_count($conn, 'SELECT COUNT(*) AS total FROM registrations r WHERE LOWER(r.registration_status) = "registered" AND LOWER(r.attendance_status) = "absent"' . $event_condition),
        'records' => admin_attendance_count($conn, 'SELECT COUNT(*) AS total FROM registrations r WHERE LOWER(r.registration_status) = "registered" AND LOWER(r.attendance_status) IN ("present", "absent")' . $event_condition),
    ];
}

// Attendance Filter Clause
function admin_attendance_filter_clause($conn, $attendance_filter)
{
    $attendance_filter = strtolower(trim((string) $attendance_filter));

    if ($attendance_filter === 'unmarked') {
        $attendance_filter = 'pending';
    }

    if (in_array($attendance_filter, ['pending', 'present', 'absent'], true)) {
        return 'LOWER(r.attendance_status) = "' . mysqli_real_escape_string($conn, $attendance_filter) . '"';
    }

    return '';
}

// Attendance Fetch Participants
function admin_attendance_fetch_participants($conn, $event_id = 0, $attendance_filter = 'all')
{
    $event_id = (int) $event_id;
    $where = ['LOWER(r.registration_status) = "registered"'];

    if ($event_id > 0) {
        $where[] = 'r.event_id = ' . $event_id;
    }

    $status_clause = admin_attendance_filter_clause($conn, $attendance_filter);

    if ($status_clause !== '') {
        $where[] = $status_clause;
    }

    $sql = 'SELECT r.registration_id, r.event_id, r.user_id, r.registration_full_name,
                   r.registration_email, r.attendee_count, r.registration_status, r.registered_at,
                   r.attendance_code, r.attendance_status, r.attendance_marked_at,
                   u.first_name, u.last_name, u.email AS user_email, u.profile_picture,
                   e.event_title, e.event_date, e.event_time, e.event_end_time,
                   marker.first_name AS marker_first_name, marker.last_name AS marker_last_name
            FROM registrations r
            INNER JOIN users u ON u.user_id = r.user_id
            INNER JOIN events e ON e.event_id = r.event_id
            LEFT JOIN attendance a ON a.registration_id = r.registration_id
            LEFT JOIN users marker ON marker.user_id = a.marked_by
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY e.event_date DESC, r.registered_at DESC, u.last_name ASC, u.first_name ASC';
    $participants = [];
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

        $row['display_email'] = trim((string) ($row['registration_email'] ?? '')) ?: $row['user_email'];
        $marker_name = trim((string) ($row['marker_first_name'] ?? '') . ' ' . (string) ($row['marker_last_name'] ?? ''));
        $row['marked_by_name'] = $marker_name !== '' ? $marker_name : 'N/A';
        $row['attendance_status'] = strtolower(trim((string) ($row['attendance_status'] ?? 'pending')));
        $row['attendance_label'] = admin_attendance_label($row['attendance_status']);
        $row['marked_at'] = $row['attendance_marked_at'] ?? null;
        $participants[] = $row;
    }

    return $participants;
}

// Attendance Fetch Records
function admin_attendance_fetch_records($conn, $event_id = 0, $attendance_filter = 'all')
{
    $event_id = (int) $event_id;
    $where = ['LOWER(r.registration_status) = "registered"'];

    if ($event_id > 0) {
        $where[] = 'r.event_id = ' . $event_id;
    }

    $status_clause = admin_attendance_filter_clause($conn, $attendance_filter);

    if ($status_clause !== '') {
        $where[] = $status_clause;
    } else {
        $where[] = 'LOWER(r.attendance_status) IN ("present", "absent")';
    }

    $sql = 'SELECT r.registration_id, r.registration_full_name, r.registration_email,
                   r.attendance_status, r.attendance_marked_at,
                   u.first_name, u.last_name, u.email AS user_email,
                   e.event_title,
                   marker.first_name AS marker_first_name, marker.last_name AS marker_last_name
            FROM registrations r
            INNER JOIN users u ON u.user_id = r.user_id
            INNER JOIN events e ON e.event_id = r.event_id
            LEFT JOIN attendance a ON a.registration_id = r.registration_id
            LEFT JOIN users marker ON marker.user_id = a.marked_by
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY r.attendance_marked_at DESC, e.event_title ASC';
    $records = [];
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $full_name = trim((string) ($row['registration_full_name'] ?? ''));

        if ($full_name === '') {
            $full_name = trim((string) $row['first_name'] . ' ' . (string) $row['last_name']);
        }

        $row['full_name'] = $full_name !== '' ? $full_name : $row['user_email'];
        $row['display_email'] = trim((string) ($row['registration_email'] ?? '')) ?: $row['user_email'];
        $row['marked_by_name'] = trim((string) $row['marker_first_name'] . ' ' . (string) $row['marker_last_name']);
        $row['marked_at'] = $row['attendance_marked_at'] ?? null;
        $records[] = $row;
    }

    return $records;
}

// Attendance Sync Record
function admin_attendance_sync_record($conn, $registration_id, $attendance_status, $admin_id)
{
    $status_label = admin_attendance_label($attendance_status);
    $sql = 'INSERT INTO attendance (registration_id, attendance_status, marked_by)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                attendance_status = VALUES(attendance_status),
                marked_by = VALUES(marked_by),
                marked_at = CURRENT_TIMESTAMP';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'isi', $registration_id, $status_label, $admin_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $success;
}

// Attendance Mark Registration
function admin_attendance_mark_registration($conn, $registration_id, $attendance_status, $admin_id)
{
    $registration_id = (int) $registration_id;
    $admin_id = (int) $admin_id;
    $attendance_status = strtolower(trim((string) $attendance_status));
    $status_options = admin_attendance_status_options();

    if ($registration_id <= 0) {
        return ['success' => false, 'message' => 'Please select a valid registration.'];
    }

    if ($admin_id <= 0) {
        return ['success' => false, 'message' => 'Admin session was not found. Please sign in again.'];
    }

    if (!array_key_exists($attendance_status, $status_options)) {
        return ['success' => false, 'message' => 'Invalid attendance status selected.'];
    }

    $check_sql = 'SELECT r.registration_id
                  FROM registrations r
                  INNER JOIN events e ON e.event_id = r.event_id
                  INNER JOIN users u ON u.user_id = r.user_id
                  WHERE r.registration_id = ?
                  AND LOWER(r.registration_status) = "registered"
                  LIMIT 1';
    $check_stmt = mysqli_prepare($conn, $check_sql);

    if (!$check_stmt) {
        return ['success' => false, 'message' => 'Unable to verify the registration.'];
    }

    mysqli_stmt_bind_param($check_stmt, 'i', $registration_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    $is_valid = mysqli_stmt_num_rows($check_stmt) > 0;
    mysqli_stmt_close($check_stmt);

    if (!$is_valid) {
        return ['success' => false, 'message' => 'Attendance can only be marked for active registered participants.'];
    }

    $update_sql = 'UPDATE registrations
                   SET attendance_status = ?,
                       attendance_marked_at = NOW()
                   WHERE registration_id = ?';
    $update_stmt = mysqli_prepare($conn, $update_sql);

    if (!$update_stmt) {
        return ['success' => false, 'message' => 'Unable to prepare the attendance update.'];
    }

    mysqli_stmt_bind_param($update_stmt, 'si', $attendance_status, $registration_id);
    $updated = mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);

    if (!$updated || !admin_attendance_sync_record($conn, $registration_id, $attendance_status, $admin_id)) {
        return ['success' => false, 'message' => 'Unable to save attendance.'];
    }

    $status_label = $status_options[$attendance_status];

    return [
        'success' => true,
        'message' => 'Attendance marked as ' . $status_label . '.',
    ];
}

// Attendance Handle Post
function admin_attendance_handle_post($conn, $redirect_path)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['admin_attendance_action'])) {
        return;
    }

    $action = $_POST['admin_attendance_action'];
    $registration_id = (int) ($_POST['registration_id'] ?? 0);
    $attendance_status = $_POST['attendance_status'] ?? '';
    $admin_id = (int) ($_SESSION['user_id'] ?? 0);
    $result = ['success' => false, 'message' => 'Invalid attendance action.'];

    if ($action === 'mark_attendance') {
        $result = admin_attendance_mark_registration($conn, $registration_id, $attendance_status, $admin_id);
    }

    admin_attendance_flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: ' . $redirect_path);
    exit;
}
