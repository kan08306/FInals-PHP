<?php
$page_title = 'Shenanovents | Private Event Access';
$current_page = 'events';
$base_path = '../';

require_once __DIR__ . '/../includes/participant-check.php';
require_once __DIR__ . '/../includes/participant-data.php';

function private_event_safe_return_url($fallback = 'events.php')
{
    $return_to = trim((string) ($_POST['return_to'] ?? ''));

    if ($return_to === '' || preg_match('/^https?:\/\//i', $return_to) || strpos($return_to, '//') === 0) {
        return $fallback;
    }

    return $return_to;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}

$result = participant_unlock_private_event($conn, participant_current_user_id(), $_POST['private_event_code'] ?? '');

if ($result['success']) {
    header('Location: event-details.php?event=' . (int) $result['event_id']);
    exit;
}

$_SESSION['private_event_error'] = $result['message'];
$return_to = private_event_safe_return_url();
$separator = strpos($return_to, '?') === false ? '?' : '&';
header('Location: ' . $return_to . $separator . 'private_event=1');
exit;
