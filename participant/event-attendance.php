<?php
// Event Attendance Page Setup
$page_title = 'Shenanovents | Event Attendance';
$current_page = 'dashboard';
$base_path = '../';
$body_class = 'event-dashboard-body';
$asset_version = 'my-events-restore';

// Shared Dependencies
require_once __DIR__ . '/../includes/participant-check.php';
require_once __DIR__ . '/../includes/participant-data.php';

$participant_id = participant_current_user_id();
$selected_event_id = (int) ($_GET['event'] ?? ($_POST['event_id'] ?? 0));

// Form Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event = $selected_event_id > 0 ? participant_fetch_owned_event_dashboard($conn, $participant_id, $selected_event_id) : null;
    $action = $_POST['attendance_action'] ?? '';
    $result = ['success' => false, 'message' => 'Invalid attendance action.'];

    if (!$event) {
        $result = ['success' => false, 'message' => 'This event was not found, or it does not belong to your account.'];
    } elseif ($action === 'verify_code') {
        $result = participant_verify_owned_attendance_code($conn, $participant_id, $selected_event_id, $_POST['attendance_code'] ?? '');
    } elseif ($action === 'mark_attendance') {
        $result = participant_mark_owned_registration_attendance($conn, $participant_id, (int) ($_POST['registration_id'] ?? 0), $_POST['attendance_status'] ?? '');
    } elseif ($action === 'finalize_attendance') {
        $result = participant_finalize_owned_event_attendance($conn, $participant_id, $selected_event_id);
    }

    participant_flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: event-attendance.php?event=' . $selected_event_id);
    exit;
}

if ($selected_event_id <= 0) {
    $owned_events = participant_fetch_hosted_events($conn, $participant_id);
    $selected_event_id = (int) ($owned_events[0]['event_id'] ?? 0);
}

$event = $selected_event_id > 0 ? participant_fetch_owned_event_dashboard($conn, $participant_id, $selected_event_id) : null;
$participants = $event ? participant_fetch_owned_event_attendance($conn, $participant_id, (int) $event['event_id']) : [];
$success_message = participant_get_flash('success');
$error_message = participant_get_flash('error');
$status = $event ? strtolower($event['status']) : '';
$status_class = $status === 'open' ? 'registered' : ($status === 'closed' ? 'cancelled' : $status);

// Page Header
require_once __DIR__ . '/../includes/header.php';
?>

<?php if (!$event): ?>
    <!-- Main Section -->
    <section class="dashboard-page" aria-labelledby="eventAttendanceTitle">
        <div class="dashboard-section">
            <div class="dashboard-title-row">
                <div>
                    <h1 id="eventAttendanceTitle">Event Attendance</h1>
                    <p>Select one of your own created events before opening attendance management.</p>
                </div>

                <a class="button button-outline" href="dashboard.php">Back to My Events</a>
            </div>

            <?php participant_render_empty_state('No attendance page available', 'This event was not found, or it does not belong to your account.', 'dashboard.php', 'View My Events'); ?>
        </div>
    </section>
<?php else: ?>
    <section class="event-dashboard-page" aria-labelledby="eventAttendanceTitle">
        <div class="event-dashboard-shell">
            <aside class="event-dashboard-sidebar" aria-label="Event dashboard navigation">
                <div class="wizard-event-preview dashboard-event-preview">
                    <strong><?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    <span><?php echo htmlspecialchars($event['date_text'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><?php echo htmlspecialchars($event['time_text'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <ol class="wizard-steps dashboard-progress-list">
                    <li>
                        <a class="wizard-step completed" href="../event-maker/create-event.php?event=<?php echo (int) $event['event_id']; ?>&step=1">
                            <span class="wizard-step-marker"></span>
                            <span class="wizard-step-copy">Basic Info</span>
                        </a>
                    </li>
                    <li>
                        <a class="wizard-step completed" href="../event-maker/create-event.php?event=<?php echo (int) $event['event_id']; ?>&step=2">
                            <span class="wizard-step-marker"></span>
                            <span class="wizard-step-copy">Details</span>
                        </a>
                    </li>
                    <li>
                        <a class="wizard-step completed" href="../event-maker/create-event.php?event=<?php echo (int) $event['event_id']; ?>&step=3">
                            <span class="wizard-step-marker"></span>
                            <span class="wizard-step-copy">Publish</span>
                        </a>
                    </li>
                </ol>

                <a class="wizard-dashboard-link" href="event-dashboard.php?event=<?php echo (int) $event['event_id']; ?>">
                    <span class="wizard-dashboard-icon" aria-hidden="true"></span>
                    Dashboard
                </a>
                <a class="wizard-dashboard-link active" href="event-attendance.php?event=<?php echo (int) $event['event_id']; ?>">
                    <span class="wizard-dashboard-icon" aria-hidden="true"></span>
                    Attendance
                </a>
            </aside>

            <div class="event-dashboard-main">
                <div class="dashboard-title-row">
                    <div>
                        <h1 id="eventAttendanceTitle">Event Attendance</h1>
                        <p>Verify attendance codes and mark actual participants for this selected event.</p>
                    </div>

                    <div class="dashboard-title-actions">
                        <a class="button button-outline" href="event-dashboard.php?event=<?php echo (int) $event['event_id']; ?>">Back to Dashboard</a>
                        <a class="button button-outline" href="dashboard.php">Back to My Events</a>
                    </div>
                </div>

                <?php participant_render_feedback($success_message, $error_message); ?>

                <div class="admin-summary-grid event-attendance-summary" aria-label="Event attendance summary">
                    <article class="admin-summary-card">
                        <span>Registered Participants</span>
                        <strong><?php echo number_format((int) $event['registration_records']); ?></strong>
                        <small>Active registration records</small>
                    </article>
                    <article class="admin-summary-card">
                        <span>Pending</span>
                        <strong><?php echo number_format((int) $event['pending_count']); ?></strong>
                        <small>Needs code check</small>
                    </article>
                    <article class="admin-summary-card">
                        <span>Present</span>
                        <strong><?php echo number_format((int) $event['present_count']); ?></strong>
                        <small>Verified attendees</small>
                    </article>
                    <article class="admin-summary-card">
                        <span>Absent</span>
                        <strong><?php echo number_format((int) $event['absent_count']); ?></strong>
                        <small>Marked absent</small>
                    </article>
                </div>

                <article class="dashboard-card attendance-code-card">
                    <div class="dashboard-card-header">
                        <h2>Attendance Code Verification</h2>
                        <span><?php echo htmlspecialchars(ucwords($event['status']), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>

                    <!-- Form -->
                    <form class="attendance-code-form" method="post" action="event-attendance.php?event=<?php echo (int) $event['event_id']; ?>">
                        <input type="hidden" name="attendance_action" value="verify_code">
                        <input type="hidden" name="event_id" value="<?php echo (int) $event['event_id']; ?>">
                        <label>
                            <span>Attendance Code</span>
                            <input type="text" name="attendance_code" placeholder="SHNV-2026-8F3K2A" required>
                        </label>
                        <button class="button button-primary" type="submit">Verify Code</button>
                    </form>
                </article>

                <div class="dashboard-title-row admin-subsection-title">
                    <div>
                        <h2>Registered Participants</h2>
                        <p>Cancelled registrations are excluded from this attendance list.</p>
                    </div>

                    <form method="post" action="event-attendance.php?event=<?php echo (int) $event['event_id']; ?>" onsubmit="return confirm('Finalize attendance and mark all pending participants as absent?');">
                        <input type="hidden" name="attendance_action" value="finalize_attendance">
                        <input type="hidden" name="event_id" value="<?php echo (int) $event['event_id']; ?>">
                        <button class="button button-outline admin-small-button danger" type="submit">Finalize Attendance</button>
                    </form>
                </div>

                <div class="dashboard-table-card">
                    <!-- Data Table -->
                    <table class="dashboard-event-table admin-table event-attendance-table">
                        <thead>
                            <tr>
                                <th scope="col">Participant</th>
                                <th scope="col">Registered</th>
                                <th scope="col">Registration</th>
                                <th scope="col">Attendance</th>
                                <th scope="col">Attendance Code</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($participants)): ?>
                                <tr>
                                    <td colspan="6">No registered participants are available for attendance checking.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($participants as $participant): ?>
                                <?php
                                $attendance_class = preg_replace('/[^a-z0-9-]/', '', strtolower($participant['attendance_status']));
                                $avatar_src = participant_profile_picture_src($participant['profile_picture'] ?? '', '../');
                                $initials = strtoupper(substr((string) ($participant['full_name'] ?? 'P'), 0, 1));
                                ?>
                                <tr>
                                    <td>
                                        <span class="admin-user-cell">
                                            <span class="admin-avatar" aria-hidden="true">
                                                <?php if ($avatar_src !== ''): ?>
                                                    <img class="admin-avatar-image" src="<?php echo htmlspecialchars($avatar_src, ENT_QUOTES, 'UTF-8'); ?>" alt="" width="42" height="42">
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?>
                                                <?php endif; ?>
                                            </span>
                                            <span class="dashboard-event-copy">
                                                <strong><?php echo htmlspecialchars($participant['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <small><?php echo htmlspecialchars($participant['display_email'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            </span>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars(participant_format_date($participant['registered_at']), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="dashboard-status dashboard-status-registered">Registered</span></td>
                                    <td>
                                        <span class="dashboard-status dashboard-status-<?php echo htmlspecialchars($attendance_class, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($participant['attendance_label'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <?php if (!empty($participant['attendance_marked_at'])): ?>
                                            <br><small><?php echo htmlspecialchars(participant_format_date($participant['attendance_marked_at']), ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="attendance-code-pill"><?php echo htmlspecialchars($participant['attendance_code'] ?: 'No code', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td>
                                        <div class="admin-action-row">
                                            <form method="post" action="event-attendance.php?event=<?php echo (int) $event['event_id']; ?>">
                                                <input type="hidden" name="attendance_action" value="mark_attendance">
                                                <input type="hidden" name="event_id" value="<?php echo (int) $event['event_id']; ?>">
                                                <input type="hidden" name="registration_id" value="<?php echo (int) $participant['registration_id']; ?>">
                                                <input type="hidden" name="attendance_status" value="present">
                                                <button class="button button-primary admin-small-button" type="submit">Present</button>
                                            </form>
                                            <form method="post" action="event-attendance.php?event=<?php echo (int) $event['event_id']; ?>">
                                                <input type="hidden" name="attendance_action" value="mark_attendance">
                                                <input type="hidden" name="event_id" value="<?php echo (int) $event['event_id']; ?>">
                                                <input type="hidden" name="registration_id" value="<?php echo (int) $participant['registration_id']; ?>">
                                                <input type="hidden" name="attendance_status" value="absent">
                                                <button class="button button-outline admin-small-button danger" type="submit">Absent</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
