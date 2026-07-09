<?php
$page_title = 'Shenanovents | Manage Registrations';
$current_page = 'admin';
$base_path = '../';

require_once __DIR__ . '/../includes/admin-check.php';
require_once __DIR__ . '/../includes/admin-registration-data.php';

$event_filter = (int) ($_GET['event'] ?? 0);
$status_filter = strtolower(trim((string) ($_GET['status'] ?? '')));
$status_options = admin_registration_status_options();

if (!array_key_exists($status_filter, $status_options)) {
    $status_filter = '';
}

$redirect_params = [];

if ($event_filter > 0) {
    $redirect_params['event'] = $event_filter;
}

if ($status_filter !== '') {
    $redirect_params['status'] = $status_filter;
}

$redirect_path = 'admin-registrations.php' . (!empty($redirect_params) ? '?' . http_build_query($redirect_params) : '');
admin_registration_handle_post($conn, $redirect_path);

$event_options = admin_registration_fetch_event_options($conn);
$summary = admin_registration_fetch_summary($conn);
$registrations = admin_registration_fetch_registrations($conn, $event_filter, $status_filter);
$success_message = admin_registration_get_flash('success');
$error_message = admin_registration_get_flash('error');

require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-page" aria-labelledby="manageRegistrationsTitle">
    <div class="admin-section">
        <div class="dashboard-title-row">
            <div>
                <h1 id="manageRegistrationsTitle">Manage Registrations</h1>
                <p>Review participant registrations, restore valid records, and cancel invalid or withdrawn registrations.</p>
            </div>

            <a class="button button-primary" href="admin-attendance.php">Open Attendance</a>
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

        <div class="admin-summary-grid" aria-label="Registration summary">
            <article class="admin-summary-card">
                <span>Total Registrations</span>
                <strong><?php echo number_format($summary['total']); ?></strong>
                <small>All registration records</small>
            </article>
            <article class="admin-summary-card">
                <span>Active Registrations</span>
                <strong><?php echo number_format($summary['registered']); ?></strong>
                <small>Available for attendance</small>
            </article>
            <article class="admin-summary-card">
                <span>Cancelled Registrations</span>
                <strong><?php echo number_format($summary['cancelled']); ?></strong>
                <small>Inactive registrations</small>
            </article>
        </div>

        <form class="admin-toolbar admin-filter-form" method="get" action="admin-registrations.php">
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
                <span>Status</span>
                <select class="admin-sort-select" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($status_options as $status_key => $status_label): ?>
                        <option value="<?php echo htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $status_filter === $status_key ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="admin-filter-actions">
                <button class="button button-primary admin-small-button" type="submit">Apply Filters</button>
                <a class="button button-outline admin-small-button" href="admin-registrations.php">Reset</a>
            </div>
        </form>

        <div class="dashboard-table-card">
            <table class="dashboard-event-table admin-table">
                <thead>
                    <tr>
                        <th scope="col">Participant</th>
                        <th scope="col">Event</th>
                        <th scope="col">Attendees</th>
                        <th scope="col">Registration</th>
                        <th scope="col">Attendance</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registrations)): ?>
                        <tr>
                            <td colspan="6">No registrations match the selected filters.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($registrations as $registration): ?>
                        <?php
                        $status_key = strtolower($registration['registration_status']);
                        $status_class = preg_replace('/[^a-z0-9-]/', '', str_replace('_', '-', $status_key));
                        $attendance_key = strtolower($registration['attendance_label']);
                        $attendance_class = preg_replace('/[^a-z0-9-]/', '', $attendance_key);
                        $avatar_src = admin_user_profile_src($registration['profile_picture'], '../');
                        $initials = admin_user_initials($registration['first_name'], $registration['last_name'], $registration['display_email']);
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
                                        <strong><?php echo htmlspecialchars($registration['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <small><?php echo htmlspecialchars($registration['display_email'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    </span>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($registration['event_title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <small><?php echo htmlspecialchars(admin_event_format_date($registration['event_date']), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(admin_event_format_time_range($registration['event_time'], $registration['event_end_time']), ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td><?php echo number_format($registration['attendee_count']); ?></td>
                            <td>
                                <span class="dashboard-status dashboard-status-<?php echo htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(admin_registration_label($registration['registration_status']), ENT_QUOTES, 'UTF-8'); ?>
                                </span><br>
                                <small><?php echo htmlspecialchars(admin_event_format_date($registration['registered_at']), ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td>
                                <span class="dashboard-status dashboard-status-<?php echo htmlspecialchars($attendance_class, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($registration['attendance_label'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="admin-action-row">
                                    <?php if ($status_key === 'registered'): ?>
                                        <a class="button button-outline admin-small-button" href="admin-attendance.php?event=<?php echo (int) $registration['event_id']; ?>">Attendance</a>
                                        <form method="post" action="<?php echo htmlspecialchars($redirect_path, ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Cancel this registration? Attendance for this registration will be removed.');">
                                            <input type="hidden" name="admin_registration_action" value="cancel_registration">
                                            <input type="hidden" name="registration_id" value="<?php echo (int) $registration['registration_id']; ?>">
                                            <button class="button button-outline admin-small-button danger" type="submit">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="<?php echo htmlspecialchars($redirect_path, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="admin_registration_action" value="restore_registration">
                                            <input type="hidden" name="registration_id" value="<?php echo (int) $registration['registration_id']; ?>">
                                            <button class="button button-primary admin-small-button" type="submit">Restore</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
