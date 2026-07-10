<?php
// Admin Report Generation Helpers
// Shared Dependencies
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/admin-dashboard-data.php';
require_once __DIR__ . '/admin-registration-data.php';

// Report Attendance Status Options
function admin_report_attendance_status_options()
{
    return [
        'all' => 'All Attendance',
        'pending' => 'Pending',
        'present' => 'Present',
        'absent' => 'Absent',
    ];
}

// Report Percent
function admin_report_percent($value, $total)
{
    $value = (int) $value;
    $total = (int) $total;

    if ($total <= 0) {
        return '0%';
    }

    return (string) round(($value / $total) * 100) . '%';
}

// Report Format Datetime
function admin_report_format_datetime($date_time)
{
    $timestamp = strtotime((string) $date_time);

    return $timestamp ? date('m/d/Y g:i A', $timestamp) : 'N/A';
}

// Report Fetch Platform Summary
function admin_report_fetch_platform_summary($conn)
{
    return [
        'total_users' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM users'),
        'total_events' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM events'),
        'total_registrations' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "registered"'),
        'total_attendance' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "registered" AND LOWER(attendance_status) IN ("present", "absent")'),
        'pending_attendance' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "registered" AND LOWER(attendance_status) = "pending"'),
        'present_attendance' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "registered" AND LOWER(attendance_status) = "present"'),
        'absent_attendance' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "registered" AND LOWER(attendance_status) = "absent"'),
    ];
}

// Report Event Filter Condition
function admin_report_event_filter_condition($conn, $event_id = 0, $event_date = '')
{
    $event_id = (int) $event_id;
    $event_date = trim((string) $event_date);
    $where = [];

    if ($event_id > 0) {
        $where[] = 'e.event_id = ' . $event_id;
    }

    if ($event_date !== '') {
        $where[] = 'e.event_date = "' . mysqli_real_escape_string($conn, $event_date) . '"';
    }

    return $where;
}

// Report Fetch Event Participation
function admin_report_fetch_event_participation($conn, $event_id = 0, $event_date = '')
{
    $where = admin_report_event_filter_condition($conn, $event_id, $event_date);
    $sql = 'SELECT e.event_id, e.event_title, e.capacity, e.event_date, e.status,
                   CONCAT(organizer.first_name, " ", organizer.last_name) AS organizer_name,
                   COALESCE(SUM(CASE WHEN LOWER(r.registration_status) = "registered" THEN r.attendee_count ELSE 0 END), 0) AS total_registered_attendees,
                   COALESCE(SUM(CASE WHEN LOWER(r.registration_status) = "registered" THEN 1 ELSE 0 END), 0) AS registration_records,
                   COALESCE(SUM(CASE WHEN LOWER(r.registration_status) = "registered" AND LOWER(r.attendance_status) = "pending" THEN 1 ELSE 0 END), 0) AS pending_count,
                   COALESCE(SUM(CASE WHEN LOWER(r.registration_status) = "registered" AND LOWER(r.attendance_status) = "present" THEN 1 ELSE 0 END), 0) AS present_count,
                   COALESCE(SUM(CASE WHEN LOWER(r.registration_status) = "registered" AND LOWER(r.attendance_status) = "absent" THEN 1 ELSE 0 END), 0) AS absent_count,
                   COALESCE(SUM(CASE WHEN LOWER(r.registration_status) = "registered" AND LOWER(r.attendance_status) IN ("present", "absent") THEN 1 ELSE 0 END), 0) AS attendance_count
            FROM events e
            INNER JOIN users organizer ON organizer.user_id = e.created_by
            LEFT JOIN registrations r ON r.event_id = e.event_id';

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' GROUP BY e.event_id, e.event_title, e.capacity, e.event_date, e.status, organizer.first_name, organizer.last_name
              ORDER BY MAX(e.created_at) DESC, e.event_date DESC';
    $rows = [];
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $row['capacity'] = (int) ($row['capacity'] ?? 0);
        $row['total_registered_attendees'] = (int) ($row['total_registered_attendees'] ?? 0);
        $row['registration_records'] = (int) ($row['registration_records'] ?? 0);
        $row['pending_count'] = (int) ($row['pending_count'] ?? 0);
        $row['present_count'] = (int) ($row['present_count'] ?? 0);
        $row['absent_count'] = (int) ($row['absent_count'] ?? 0);
        $row['attendance_count'] = (int) ($row['attendance_count'] ?? 0);
        $row['remaining_slots'] = max(0, $row['capacity'] - $row['total_registered_attendees']);
        $row['attendance_percentage'] = admin_report_percent($row['present_count'], $row['registration_records']);
        $rows[] = $row;
    }

    return $rows;
}

// Report Fetch Attendance Summary
function admin_report_fetch_attendance_summary($conn, $event_id = 0, $event_date = '')
{
    $where = admin_report_event_filter_condition($conn, $event_id, $event_date);
    $sql = 'SELECT e.event_id, e.event_title, e.event_date,
                   COALESCE(SUM(CASE WHEN LOWER(r.registration_status) = "registered" THEN 1 ELSE 0 END), 0) AS registered_participants,
                   COALESCE(SUM(CASE WHEN LOWER(r.registration_status) = "registered" AND LOWER(r.attendance_status) = "pending" THEN 1 ELSE 0 END), 0) AS pending_count,
                   COALESCE(SUM(CASE WHEN LOWER(r.registration_status) = "registered" AND LOWER(r.attendance_status) = "present" THEN 1 ELSE 0 END), 0) AS present_count,
                   COALESCE(SUM(CASE WHEN LOWER(r.registration_status) = "registered" AND LOWER(r.attendance_status) = "absent" THEN 1 ELSE 0 END), 0) AS absent_count
            FROM events e
            LEFT JOIN registrations r ON r.event_id = e.event_id';

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' GROUP BY e.event_id, e.event_title, e.event_date
              ORDER BY e.event_date DESC, MAX(e.created_at) DESC';
    $rows = [];
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $row['registered_participants'] = (int) ($row['registered_participants'] ?? 0);
        $row['pending_count'] = (int) ($row['pending_count'] ?? 0);
        $row['present_count'] = (int) ($row['present_count'] ?? 0);
        $row['absent_count'] = (int) ($row['absent_count'] ?? 0);
        $row['attendance_rate'] = admin_report_percent($row['present_count'], $row['registered_participants']);
        $rows[] = $row;
    }

    return $rows;
}

// Report Fetch Attendance Records
function admin_report_fetch_attendance_records($conn, $event_id = 0, $marked_date = '', $attendance_status = 'all')
{
    $event_id = (int) $event_id;
    $marked_date = trim((string) $marked_date);
    $attendance_status = strtolower(trim((string) $attendance_status));
    $where = ['LOWER(r.registration_status) = "registered"'];

    if ($event_id > 0) {
        $where[] = 'e.event_id = ' . $event_id;
    }

    if ($marked_date !== '') {
        $where[] = 'DATE(r.attendance_marked_at) = "' . mysqli_real_escape_string($conn, $marked_date) . '"';
    }

    if (in_array($attendance_status, ['pending', 'present', 'absent'], true)) {
        $where[] = 'LOWER(r.attendance_status) = "' . mysqli_real_escape_string($conn, $attendance_status) . '"';
    }

    $sql = 'SELECT r.attendance_status, r.attendance_marked_at, r.attendance_code,
                   e.event_title,
                   r.registration_full_name, r.registration_email,
                   u.first_name, u.last_name, u.email AS user_email
            FROM registrations r
            INNER JOIN events e ON e.event_id = r.event_id
            INNER JOIN users u ON u.user_id = r.user_id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY r.attendance_marked_at DESC, e.event_title ASC';
    $rows = [];
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
        $row['marked_at'] = $row['attendance_marked_at'] ?? null;
        $rows[] = $row;
    }

    return $rows;
}
