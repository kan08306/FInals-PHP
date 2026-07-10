<?php
// Admin Dashboard Data Helpers
// Shared Dependencies
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/admin-event-data.php';

// Dashboard Count
function admin_dashboard_count($conn, $sql)
{
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);

    return (int) ($row['total'] ?? 0);
}

// Dashboard Available Event SQL
function admin_dashboard_available_event_sql()
{
    return 'LOWER(status) IN ("open", "published", "approved", "active")
            AND (
                publish_date IS NULL
                OR publish_date < CURDATE()
                OR (
                    publish_date = CURDATE()
                    AND (publish_time IS NULL OR publish_time <= CURTIME())
                )
            )';
}

// Dashboard Public Event SQL
function admin_dashboard_public_event_sql()
{
    return admin_dashboard_available_event_sql() . '
            AND LOWER(visibility) <> "private"
            AND event_date >= CURDATE()';
}

// Dashboard Fetch Summary
function admin_dashboard_fetch_summary($conn)
{
    $public_event_sql = admin_dashboard_public_event_sql();
    $available_event_sql = admin_dashboard_available_event_sql();

    return [
        'total_users' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM users'),
        'new_users_month' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM users WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())'),
        'total_events' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM events WHERE LOWER(status) NOT IN ("closed", "cancelled")'),
        'active_events' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM events WHERE ' . $available_event_sql . ' AND event_date >= CURDATE()'),
        'pending_events' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM events WHERE LOWER(status) = "pending"'),
        'published_events' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM events WHERE ' . $public_event_sql),
        'private_events' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM events WHERE LOWER(visibility) = "private"'),
        'total_registrations' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "registered"'),
        'new_registrations_week' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "registered" AND YEARWEEK(registered_at, 1) = YEARWEEK(CURDATE(), 1)'),
        'total_attendance_records' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "registered" AND LOWER(attendance_status) IN ("present", "absent")'),
        'present_attendance_records' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "registered" AND LOWER(attendance_status) = "present"'),
        'absent_attendance_records' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM registrations WHERE LOWER(registration_status) = "registered" AND LOWER(attendance_status) = "absent"'),
    ];
}

// Dashboard Fetch Registration Activity
function admin_dashboard_fetch_registration_activity($conn)
{
    $activity = [];
    $current_week_start = strtotime('monday this week');
    $sql = 'SELECT COUNT(*) AS total
            FROM registrations
            WHERE LOWER(registration_status) = "registered"
            AND registered_at >= ?
            AND registered_at < ?';
    $stmt = mysqli_prepare($conn, $sql);

    for ($index = 4; $index >= 0; $index--) {
        $week_start = strtotime('-' . $index . ' weeks', $current_week_start);
        $week_end = strtotime('+1 week', $week_start);
        $start_date = date('Y-m-d 00:00:00', $week_start);
        $end_date = date('Y-m-d 00:00:00', $week_end);
        $total = 0;

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ss', $start_date, $end_date);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $total = (int) ($row['total'] ?? 0);
        }

        $activity[] = [
            'label' => 'Week ' . (5 - $index),
            'date_label' => date('M j', $week_start),
            'value' => $total,
        ];
    }

    if ($stmt) {
        mysqli_stmt_close($stmt);
    }

    return $activity;
}

// Dashboard Prepare Registration Chart
function admin_dashboard_prepare_registration_chart($activity)
{
    $max_value = 0;

    foreach ($activity as $item) {
        $max_value = max($max_value, (int) $item['value']);
    }

    $max_value = max(1, $max_value);
    $points = [];
    $svg_points = [];
    $total_items = max(1, count($activity) - 1);

    foreach ($activity as $index => $item) {
        $ratio = (int) $item['value'] / $max_value;
        $x = 18 + (($index / $total_items) * 484);
        $y = 170 - ($ratio * 126);
        $point_x = 4 + (($index / $total_items) * 92);
        $point_y = ($y / 200) * 100;
        $svg_points[] = round($x, 1) . ',' . round($y, 1);
        $points[] = [
            'label' => $item['label'],
            'date_label' => $item['date_label'],
            'value' => (int) $item['value'],
            'point_x' => round($point_x, 1),
            'point_y' => round($point_y, 1),
        ];
    }

    return [
        'svg_points' => implode(' ', $svg_points),
        'points' => $points,
    ];
}

// Dashboard Prepare Registration Bars
function admin_dashboard_prepare_registration_bars($activity)
{
    $max_value = 0;

    foreach ($activity as $item) {
        $max_value = max($max_value, (int) $item['value']);
    }

    $max_value = max(1, $max_value);
    $bars = [];

    foreach ($activity as $item) {
        $height = ((int) $item['value'] / $max_value) * 100;
        $bars[] = [
            'label' => $item['label'],
            'value' => (int) $item['value'],
            'height' => max(8, round($height)),
        ];
    }

    return $bars;
}

// Dashboard Fetch Event Status Summary
function admin_dashboard_fetch_event_status_summary($conn, $summary)
{
    $items = [
        ['label' => 'Published', 'value' => (int) $summary['published_events']],
        ['label' => 'Pending', 'value' => (int) $summary['pending_events']],
        ['label' => 'Private', 'value' => (int) $summary['private_events']],
        ['label' => 'Rejected', 'value' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM events WHERE LOWER(status) = "rejected"')],
    ];
    $max_value = 0;

    foreach ($items as $item) {
        $max_value = max($max_value, (int) $item['value']);
    }

    $max_value = max(1, $max_value);

    foreach ($items as $index => $item) {
        $height = ((int) $item['value'] / $max_value) * 100;
        $items[$index]['height'] = max(10, round($height));
    }

    return $items;
}

// Dashboard Fetch Approval Summary
function admin_dashboard_fetch_approval_summary($conn)
{
    return [
        'awaiting_review' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM events WHERE LOWER(status) = "pending"'),
        'public_submissions' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM events WHERE LOWER(status) = "pending" AND LOWER(visibility) = "public"'),
        'private_submissions' => admin_dashboard_count($conn, 'SELECT COUNT(*) AS total FROM events WHERE LOWER(status) = "pending" AND LOWER(visibility) = "private"'),
    ];
}

// Dashboard Relative Time
function admin_dashboard_relative_time($date_time)
{
    $timestamp = strtotime((string) $date_time);

    if (!$timestamp) {
        return 'Unknown time';
    }

    $difference = time() - $timestamp;

    if ($difference < 60) {
        return 'Just now';
    }

    if ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' ago';
    }

    if ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
    }

    if (date('Y-m-d', $timestamp) === date('Y-m-d', strtotime('-1 day'))) {
        return 'Yesterday';
    }

    $days = floor($difference / 86400);

    if ($days < 7) {
        return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
    }

    return date('M j, Y', $timestamp);
}

// Dashboard Fetch Recent Activity
function admin_dashboard_fetch_recent_activity($conn, $limit = 6)
{
    $limit = max(1, min(10, (int) $limit));
    $activities = [];
    $sql = 'SELECT activity_type, activity_title, activity_time
            FROM (
                SELECT "New User" AS activity_type,
                       CONCAT(first_name, " ", last_name, " created an account") AS activity_title,
                       created_at AS activity_time
                FROM users

                UNION ALL

                SELECT
                    CASE
                        WHEN LOWER(e.status) = "pending" THEN "Event Submitted"
                        WHEN LOWER(e.status) = "published" THEN "Event Published"
                        WHEN LOWER(e.status) = "rejected" THEN "Event Rejected"
                        WHEN LOWER(e.status) IN ("closed", "cancelled") THEN "Event Closed"
                        ELSE "Event Created"
                    END AS activity_type,
                    CONCAT(organizer.first_name, " ", organizer.last_name, " created ", e.event_title) AS activity_title,
                    e.created_at AS activity_time
                FROM events e
                INNER JOIN users organizer ON organizer.user_id = e.created_by

                UNION ALL

                SELECT "Registration" AS activity_type,
                       CONCAT(COALESCE(NULLIF(r.registration_full_name, ""), attendee.first_name), " registered for ", e.event_title) AS activity_title,
                       r.registered_at AS activity_time
                FROM registrations r
                INNER JOIN events e ON e.event_id = r.event_id
                INNER JOIN users attendee ON attendee.user_id = r.user_id
                WHERE LOWER(r.registration_status) = "registered"
            ) dashboard_activity
            ORDER BY activity_time DESC
            LIMIT ' . $limit;
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $row['relative_time'] = admin_dashboard_relative_time($row['activity_time']);
        $activities[] = $row;
    }

    return $activities;
}
