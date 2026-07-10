<?php
// Administrator Page Setup
$page_title = 'Shenanovents | Attendance';
$current_page = 'admin';
$base_path = '../';

require_once __DIR__ . '/../includes/admin-check.php';
require_once __DIR__ . '/../includes/admin-attendance-data.php';

$event_filter = (int) ($_GET['event'] ?? 0);
$attendance_filter = strtolower(trim((string) ($_GET['status'] ?? 'all')));
$attendance_filter_options = admin_attendance_filter_options();

if (!array_key_exists($attendance_filter, $attendance_filter_options)) {
    $attendance_filter = 'all';
}

$redirect_params = [];

if ($event_filter > 0) {
    $redirect_params['event'] = $event_filter;
}

if ($attendance_filter !== 'all') {
    $redirect_params['status'] = $attendance_filter;
}

$redirect_path = 'admin-attendance.php' . (!empty($redirect_params) ? '?' . http_build_query($redirect_params) : '');
admin_attendance_handle_post($conn, $redirect_path);

$event_options = admin_registration_fetch_event_options($conn);
$summary = admin_attendance_fetch_summary($conn, $event_filter);
$registered_total = (int) $summary['registered'];
$pending_total = (int) $summary['pending'];
$present_total = (int) $summary['present'];
$absent_total = (int) $summary['absent'];
$participants = admin_attendance_fetch_participants($conn, $event_filter, $attendance_filter);
$attendance_records = admin_attendance_fetch_records($conn, $event_filter, $attendance_filter);
$success_message = admin_attendance_get_flash('success');
$error_message = admin_attendance_get_flash('error');

// Shared Layout Rendering
require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-page" aria-labelledby="attendanceTitle">
    <div class="admin-section">
        <div class="dashboard-title-row">
            <div>
                <h1 id="attendanceTitle">Attendance</h1>
                <p>Mark registered participants as present or absent and review saved attendance records.</p>
            </div>

            <a class="button button-outline" href="admin-registrations.php">Manage Registrations</a>
        </div>

        <?php if ($success_message !== ''): ?>
            <div class="participant-feedback participant-feedback-success" role="status">
                <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message !== ''): ?>
            <div class="participant-feedback participant-feedback-error" role="alert">
                <p><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        <?php endif; ?>

        <div class="admin-summary-grid" aria-label="Attendance summary">
            <article class="admin-summary-card">
                <span>Registered Participants</span>
                <strong><?php echo number_format($registered_total); ?></strong>
                <small>Active registrations only</small>
            </article>
            <article class="admin-summary-card">
                <span>Present</span>
                <strong><?php echo number_format($present_total); ?></strong>
                <small>Marked present</small>
            </article>
            <article class="admin-summary-card">
                <span>Absent</span>
                <strong><?php echo number_format($absent_total); ?></strong>
                <small>Marked absent</small>
            </article>
            <article class="admin-summary-card">
                <span>Pending</span>
                <strong><?php echo number_format($pending_total); ?></strong>
                <small>Needs attendance check</small>
            </article>
        </div>

        <form class="admin-toolbar admin-filter-form" method="get" action="admin-attendance.php">
            <label class="admin-filter-field">
                <span>Event</span>
                <select class="admin-sort-select" name="event">
                    <option value="0">All Events</option>
                    <?php foreach ($event_options as $event): ?>
                        <option value="<?php echo (int) $event['event_id']; ?>" <?php echo $event_filter === (int) $event['event_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="admin-filter-field">
                <span>Attendance Status</span>
                <select class="admin-sort-select" name="status">
                    <?php foreach ($attendance_filter_options as $status_key => $status_label): ?>
                        <option value="<?php echo htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $attendance_filter === $status_key ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="admin-filter-actions">
                <button class="button button-primary admin-small-button" type="submit">Apply Filters</button>
                <a class="button button-outline admin-small-button" href="admin-attendance.php">Reset</a>
            </div>
        </form>

        <div class="dashboard-table-card">
            <table class="dashboard-event-table admin-table">
                <thead>
                    <tr>
                        <th scope="col">Participant</th>
                        <th scope="col">Event</th>
                        <th scope="col">Registration Date</th>
                        <th scope="col">Registration Status</th>
                        <th scope="col">Attendance Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($participants)): ?>
                        <tr>
                            <td colspan="6">No registered participants match the selected filters.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($participants as $participant): ?>
                        <?php
                        $attendance_key = strtolower($participant['attendance_label']);
                        $attendance_class = preg_replace('/[^a-z0-9-]/', '', $attendance_key);
                        $avatar_src = admin_user_profile_src($participant['profile_picture'], '../');
                        $initials = admin_user_initials($participant['first_name'], $participant['last_name'], $participant['display_email']);
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
                            <td>
                                <strong><?php echo htmlspecialchars($participant['event_title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <small><?php echo htmlspecialchars(admin_event_format_date($participant['event_date']), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(admin_event_format_time_range($participant['event_time'], $participant['event_end_time']), ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php if (!empty($participant['attendance_code'])): ?>
                                    <br><small>Code: <?php echo htmlspecialchars($participant['attendance_code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(admin_attendance_format_datetime($participant['registered_at']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="dashboard-status dashboard-status-registered">Registered</span></td>
                            <td>
                                <span class="dashboard-status dashboard-status-<?php echo htmlspecialchars($attendance_class, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($participant['attendance_label'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <?php if (!empty($participant['marked_at'])): ?>
                                    <br><small><?php echo htmlspecialchars(admin_attendance_format_datetime($participant['marked_at']), ENT_QUOTES, 'UTF-8'); ?> by <?php echo htmlspecialchars($participant['marked_by_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="admin-action-row">
                                    <form method="post" action="<?php echo htmlspecialchars($redirect_path, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="admin_attendance_action" value="mark_attendance">
                                        <input type="hidden" name="registration_id" value="<?php echo (int) $participant['registration_id']; ?>">
                                        <input type="hidden" name="attendance_status" value="present">
                                        <button class="button button-primary admin-small-button" type="submit">Present</button>
                                    </form>
                                    <form method="post" action="<?php echo htmlspecialchars($redirect_path, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="admin_attendance_action" value="mark_attendance">
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

        <div class="dashboard-title-row admin-subsection-title">
            <div>
                <h2>Attendance Records</h2>
                <p>Saved attendance entries based on the same selected filters.</p>
            </div>
        </div>

        <div class="dashboard-table-card">
            <table class="dashboard-event-table admin-table">
                <thead>
                    <tr>
                        <th scope="col">Event</th>
                        <th scope="col">Participant</th>
                        <th scope="col">Attendance</th>
                        <th scope="col">Marked Date</th>
                        <th scope="col">Marked By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendance_records)): ?>
                        <tr>
                            <td colspan="5">No attendance records match the selected filters.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($attendance_records as $record): ?>
                        <?php $record_class = preg_replace('/[^a-z0-9-]/', '', strtolower($record['attendance_status'])); ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['event_title'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($record['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <small><?php echo htmlspecialchars($record['display_email'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td>
                                <span class="dashboard-status dashboard-status-<?php echo htmlspecialchars($record_class, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(admin_attendance_label($record['attendance_status']), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(admin_attendance_format_datetime($record['marked_at']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($record['marked_by_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


