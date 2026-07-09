<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../database/connection.php';

function admin_event_allowed_statuses()
{
    return ['pending', 'open', 'approved', 'published', 'rejected', 'closed', 'cancelled'];
}

function admin_event_status_options()
{
    return [
        'pending' => 'Pending',
        'open' => 'Open',
        'approved' => 'Approved',
        'published' => 'Published',
        'rejected' => 'Rejected',
        'closed' => 'Closed',
        'cancelled' => 'Cancelled',
    ];
}

function admin_event_flash($type, $message)
{
    $_SESSION['admin_event_' . $type] = $message;
}

function admin_event_get_flash($type)
{
    $key = 'admin_event_' . $type;
    $message = $_SESSION[$key] ?? '';
    unset($_SESSION[$key]);

    return $message;
}

function admin_event_status_label($status)
{
    $status = trim((string) $status);

    if ($status === '') {
        return 'Unknown';
    }

    return ucwords(str_replace(['_', '-'], ' ', strtolower($status)));
}

function admin_event_format_date($date)
{
    $timestamp = strtotime((string) $date);

    return $timestamp ? date('m/d/Y', $timestamp) : 'N/A';
}

function admin_event_format_time($time)
{
    $timestamp = strtotime((string) $time);

    return $timestamp ? date('g:i A', $timestamp) : 'N/A';
}

function admin_event_format_time_range($start_time, $end_time)
{
    $start = admin_event_format_time($start_time);
    $end = admin_event_format_time($end_time);

    if ($start === 'N/A') {
        return 'N/A';
    }

    return $end === 'N/A' ? $start : $start . ' - ' . $end;
}

function admin_event_publish_label($event)
{
    $publish_date = trim((string) ($event['publish_date'] ?? ''));

    if ($publish_date === '') {
        return 'Immediate';
    }

    $publish_time = trim((string) ($event['publish_time'] ?? ''));
    $label = admin_event_format_date($publish_date);

    if ($publish_time !== '') {
        $label .= ' at ' . admin_event_format_time($publish_time);
    }

    return $label;
}

function admin_event_banner_src($event, $base_path = '../')
{
    $banner_image = trim((string) ($event['banner_image'] ?? ''));

    if ($banner_image !== '') {
        return $base_path . ltrim($banner_image, '/');
    }

    return $base_path . 'assets/images/events/hero-event.png';
}

function admin_event_location_label($event)
{
    $event_type = strtolower(trim((string) ($event['event_type'] ?? '')));

    if ($event_type === 'online') {
        $online_parts = array_filter([
            $event['online_platform'] ?? '',
            $event['online_link'] ?? '',
        ]);

        return !empty($online_parts) ? implode(' - ', $online_parts) : 'Online event';
    }

    if ($event_type === 'tba') {
        return 'Location to be announced';
    }

    $location_parts = array_filter([
        $event['event_venue'] ?? '',
        $event['event_address'] ?? '',
        $event['event_city'] ?? '',
        $event['event_country'] ?? '',
    ]);

    if (!empty($location_parts)) {
        return implode(', ', $location_parts);
    }

    return $event['event_location'] ?? 'N/A';
}

function admin_event_registration_label($event)
{
    return (int) ($event['registered_count'] ?? 0) . ' / ' . (int) ($event['capacity'] ?? 0);
}

function admin_event_fetch_events($conn, $status_filter = '')
{
    $events = [];
    $status_filter = strtolower(trim((string) $status_filter));
    $sql = 'SELECT e.event_id, e.event_title, e.event_summary, e.event_description, e.event_tags,
                   e.event_category, e.event_type, e.event_location, e.event_country,
                   e.event_province, e.event_city, e.event_address, e.event_venue,
                   e.online_link, e.online_platform, e.event_date, e.event_time,
                   e.event_end_time, e.capacity, e.banner_image, e.visibility,
                   e.audience, e.publish_date, e.publish_time, e.status,
                   e.created_by, e.created_at,
                   CONCAT(organizer.first_name, " ", organizer.last_name) AS organizer_name,
                   organizer.email AS organizer_email,
                   COALESCE((
                        SELECT SUM(active_reg.attendee_count)
                        FROM registrations active_reg
                        WHERE active_reg.event_id = e.event_id
                        AND active_reg.registration_status = "registered"
                   ), 0) AS registered_count
            FROM events e
            INNER JOIN users organizer ON organizer.user_id = e.created_by';

    if ($status_filter !== '') {
        $sql .= ' WHERE LOWER(e.status) = ?';
    }

    $sql .= ' ORDER BY
                CASE WHEN LOWER(e.status) = "pending" THEN 0 ELSE 1 END,
                e.created_at DESC,
                e.event_date ASC,
                e.event_time ASC';

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    if ($status_filter !== '') {
        mysqli_stmt_bind_param($stmt, 's', $status_filter);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $row['registered_count'] = (int) ($row['registered_count'] ?? 0);
        $row['capacity'] = (int) ($row['capacity'] ?? 0);
        $events[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $events;
}

function admin_event_fetch_event_by_id($conn, $event_id)
{
    $events = admin_event_fetch_events($conn);

    foreach ($events as $event) {
        if ((int) $event['event_id'] === (int) $event_id) {
            return $event;
        }
    }

    return null;
}

function admin_event_fetch_recent_activity($conn, $limit = 5)
{
    $limit = max(1, min(10, (int) $limit));
    $events = [];
    $sql = 'SELECT e.event_title, e.status, e.created_at,
                   CONCAT(organizer.first_name, " ", organizer.last_name) AS organizer_name
            FROM events e
            INNER JOIN users organizer ON organizer.user_id = e.created_by
            WHERE LOWER(e.status) IN ("approved", "published", "rejected", "closed", "cancelled")
            ORDER BY e.created_at DESC
            LIMIT ' . $limit;
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }

    return $events;
}

function admin_event_update_status($conn, $event_id, $status)
{
    $event_id = (int) $event_id;
    $status = strtolower(trim((string) $status));

    if ($event_id <= 0) {
        return ['success' => false, 'message' => 'Invalid event selected.'];
    }

    if (!in_array($status, admin_event_allowed_statuses(), true)) {
        return ['success' => false, 'message' => 'Invalid event status selected.'];
    }

    if (!admin_event_fetch_event_by_id($conn, $event_id)) {
        return ['success' => false, 'message' => 'Event record was not found.'];
    }

    $sql = 'UPDATE events SET status = ? WHERE event_id = ?';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return ['success' => false, 'message' => 'Unable to prepare the event status update.'];
    }

    mysqli_stmt_bind_param($stmt, 'si', $status, $event_id);
    $updated = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$updated) {
        return ['success' => false, 'message' => 'Unable to update event status.'];
    }

    return ['success' => true, 'message' => 'Event status updated to ' . admin_event_status_label($status) . '.'];
}

function admin_event_approve_and_publish($conn, $event_id)
{
    $result = admin_event_update_status($conn, $event_id, 'published');

    if ($result['success']) {
        $result['message'] = 'Event approved and published successfully.';
    }

    return $result;
}

function admin_event_reject_and_close($conn, $event_id)
{
    $result = admin_event_update_status($conn, $event_id, 'rejected');

    if ($result['success']) {
        $result['message'] = 'Event rejected and closed from participant registration.';
    }

    return $result;
}

function admin_event_normalize_date($value, $required = true)
{
    $value = trim((string) $value);

    if ($value === '') {
        return $required ? '' : null;
    }

    $timestamp = strtotime($value);

    return $timestamp ? date('Y-m-d', $timestamp) : '';
}

function admin_event_normalize_time($value, $required = true)
{
    $value = trim((string) $value);

    if ($value === '') {
        return $required ? '' : null;
    }

    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
        return '';
    }

    return strlen($value) === 5 ? $value . ':00' : $value;
}

function admin_event_clean_optional($value)
{
    $value = trim((string) $value);

    return $value === '' ? null : $value;
}

function admin_event_update_event($conn, $form_data)
{
    $event_id = (int) ($form_data['event_id'] ?? 0);
    $existing_event = admin_event_fetch_event_by_id($conn, $event_id);

    if (!$existing_event) {
        return ['success' => false, 'message' => 'Event record was not found.'];
    }

    $title = trim((string) ($form_data['event_title'] ?? ''));
    $summary = admin_event_clean_optional($form_data['event_summary'] ?? '');
    $description = trim((string) ($form_data['event_description'] ?? ''));
    $tags = admin_event_clean_optional($form_data['event_tags'] ?? '');
    $category = admin_event_clean_optional($form_data['event_category'] ?? '');
    $event_type = strtolower(trim((string) ($form_data['event_type'] ?? 'physical')));
    $event_location = trim((string) ($form_data['event_location'] ?? ''));
    $country = trim((string) ($form_data['event_country'] ?? 'Philippines'));
    $city = admin_event_clean_optional($form_data['event_city'] ?? '');
    $address = admin_event_clean_optional($form_data['event_address'] ?? '');
    $venue = admin_event_clean_optional($form_data['event_venue'] ?? '');
    $online_link = admin_event_clean_optional($form_data['online_link'] ?? '');
    $online_platform = admin_event_clean_optional($form_data['online_platform'] ?? '');
    $event_date = admin_event_normalize_date($form_data['event_date'] ?? '');
    $event_time = admin_event_normalize_time($form_data['event_time'] ?? '');
    $event_end_time = admin_event_normalize_time($form_data['event_end_time'] ?? '', false);
    $capacity = (int) ($form_data['capacity'] ?? 0);
    $visibility = strtolower(trim((string) ($form_data['visibility'] ?? 'public')));
    $audience = admin_event_clean_optional($form_data['audience'] ?? '');
    $publish_date = admin_event_normalize_date($form_data['publish_date'] ?? '', false);
    $publish_time = admin_event_normalize_time($form_data['publish_time'] ?? '', false);
    $status = strtolower(trim((string) ($form_data['status'] ?? 'pending')));
    $errors = [];

    if ($title === '') {
        $errors[] = 'Event title is required.';
    }

    if ($description === '') {
        $errors[] = 'Event description is required.';
    }

    if (!in_array($event_type, ['physical', 'online', 'tba'], true)) {
        $errors[] = 'Invalid event type selected.';
    }

    if ($event_location === '') {
        $event_location = admin_event_location_label([
            'event_type' => $event_type,
            'event_venue' => $venue,
            'event_address' => $address,
            'event_city' => $city,
            'event_country' => $country,
            'online_platform' => $online_platform,
            'online_link' => $online_link,
        ]);
    }

    if ($country === '') {
        $country = 'Philippines';
    }

    if ($event_date === '') {
        $errors[] = 'Enter a valid event date.';
    }

    if ($event_time === '') {
        $errors[] = 'Enter a valid event start time.';
    }

    if ($capacity < 1) {
        $errors[] = 'Capacity must be at least 1.';
    }

    if ($capacity < (int) $existing_event['registered_count']) {
        $errors[] = 'Capacity cannot be lower than the current registered attendees.';
    }

    if (!in_array($visibility, ['public', 'private'], true)) {
        $errors[] = 'Invalid visibility selected.';
    }

    if (!in_array($status, admin_event_allowed_statuses(), true)) {
        $errors[] = 'Invalid event status selected.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'message' => implode(' ', $errors)];
    }

    $sql = 'UPDATE events
            SET event_title = ?, event_summary = ?, event_description = ?, event_tags = ?,
                event_category = ?, event_type = ?, event_location = ?, event_country = ?,
                event_city = ?, event_address = ?, event_venue = ?, online_link = ?,
                online_platform = ?, event_date = ?, event_time = ?, event_end_time = ?,
                capacity = ?, visibility = ?, audience = ?, publish_date = ?,
                publish_time = ?, status = ?
            WHERE event_id = ?';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return ['success' => false, 'message' => 'Unable to prepare the event update.'];
    }

    $types = str_repeat('s', 16) . 'i' . str_repeat('s', 5) . 'i';
    mysqli_stmt_bind_param(
        $stmt,
        $types,
        $title,
        $summary,
        $description,
        $tags,
        $category,
        $event_type,
        $event_location,
        $country,
        $city,
        $address,
        $venue,
        $online_link,
        $online_platform,
        $event_date,
        $event_time,
        $event_end_time,
        $capacity,
        $visibility,
        $audience,
        $publish_date,
        $publish_time,
        $status,
        $event_id
    );
    $updated = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$updated) {
        return ['success' => false, 'message' => 'Unable to update the event.'];
    }

    return ['success' => true, 'message' => 'Event information updated successfully.'];
}

function admin_event_delete_uploaded_banner($banner_path)
{
    $banner_path = trim((string) $banner_path);

    if ($banner_path === '') {
        return;
    }

    $upload_directory = realpath(dirname(__DIR__) . '/assets/uploads/event-banners');
    $file_path = realpath(dirname(__DIR__) . '/' . ltrim($banner_path, '/'));

    if ($upload_directory && $file_path && strpos($file_path, $upload_directory) === 0 && is_file($file_path)) {
        unlink($file_path);
    }
}

function admin_event_permanently_delete_event($conn, $event_id)
{
    $event_id = (int) $event_id;

    if ($event_id <= 0) {
        return ['success' => false, 'message' => 'Invalid event selected.'];
    }

    $event = admin_event_fetch_event_by_id($conn, $event_id);

    if (!$event) {
        return ['success' => false, 'message' => 'Event record was not found.'];
    }

    $sql = 'DELETE FROM events WHERE event_id = ?';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return ['success' => false, 'message' => 'Unable to prepare permanent deletion.'];
    }

    mysqli_stmt_bind_param($stmt, 'i', $event_id);
    $deleted = mysqli_stmt_execute($stmt) && mysqli_affected_rows($conn) > 0;
    mysqli_stmt_close($stmt);

    if (!$deleted) {
        return ['success' => false, 'message' => 'Unable to permanently delete the event.'];
    }

    admin_event_delete_uploaded_banner($event['banner_image'] ?? '');

    return [
        'success' => true,
        'message' => 'Event permanently deleted. Related registrations, likes, and attendance records were handled by database cascade rules.',
    ];
}

function admin_event_handle_post($conn, $redirect_path)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['admin_event_action'])) {
        return;
    }

    $action = $_POST['admin_event_action'];
    $event_id = (int) ($_POST['event_id'] ?? 0);
    $result = ['success' => false, 'message' => 'Invalid admin event action.'];

    if ($action === 'approve_publish_event') {
        $result = admin_event_approve_and_publish($conn, $event_id);
    } elseif ($action === 'reject_close_event') {
        $result = admin_event_reject_and_close($conn, $event_id);
    } elseif ($action === 'approve_event') {
        $result = admin_event_update_status($conn, $event_id, 'approved');
    } elseif ($action === 'publish_event') {
        $result = admin_event_update_status($conn, $event_id, 'published');
    } elseif ($action === 'reject_event') {
        $result = admin_event_update_status($conn, $event_id, 'rejected');
    } elseif ($action === 'archive_event') {
        $result = admin_event_update_status($conn, $event_id, 'closed');

        if ($result['success']) {
            $result['message'] = 'Event archived safely by setting its status to Closed.';
        }
    } elseif ($action === 'update_event') {
        $result = admin_event_update_event($conn, $_POST);
    } elseif ($action === 'permanent_delete_event') {
        $result = admin_event_permanently_delete_event($conn, $event_id);
    }

    admin_event_flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: ' . $redirect_path);
    exit;
}
