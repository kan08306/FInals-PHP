<?php
$page_title = 'Shenanovents | Reports';
$current_page = 'admin';
$base_path = '../';

require_once __DIR__ . '/../includes/admin-check.php';
require_once __DIR__ . '/../includes/admin-report-data.php';

$event_filter = (int) ($_GET['event'] ?? 0);
$date_filter = trim((string) ($_GET['date'] ?? ''));
$attendance_filter = strtolower(trim((string) ($_GET['status'] ?? 'all')));
$attendance_status_options = admin_report_attendance_status_options();

if (!array_key_exists($attendance_filter, $attendance_status_options)) {
    $attendance_filter = 'all';
}

if ($date_filter !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_filter)) {
    $date_filter = '';
}

$event_options = admin_registration_fetch_event_options($conn);
$platform_summary = admin_report_fetch_platform_summary($conn);
$participation_report = admin_report_fetch_event_participation($conn, $event_filter, $date_filter);
$attendance_summary = admin_report_fetch_attendance_summary($conn, $event_filter, $date_filter);
$attendance_records = admin_report_fetch_attendance_records($conn, $event_filter, $date_filter, $attendance_filter);

require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-page" aria-labelledby="reportsTitle">
    <div class="admin-section">
        <div class="dashboard-title-row">
            <div>
                <h1 id="reportsTitle">Reports</h1>
                <p>Review platform totals, event participation, and attendance summaries using live database data.</p>
            </div>

            <a class="button button-outline" href="admin-attendance.php">Open Attendance</a>
        </div>

        <div class="admin-summary-grid" aria-label="Platform report summary">
            <article class="admin-summary-card">
                <span>Total Users</span>
                <strong><?php echo number_format($platform_summary['total_users']); ?></strong>
                <small>From users table</small>
            </article>
            <article class="admin-summary-card">
                <span>Total Events</span>
                <strong><?php echo number_format($platform_summary['total_events']); ?></strong>
                <small>All event records</small>
            </article>
            <article class="admin-summary-card">
                <span>Total Registrations</span>
                <strong><?php echo number_format($platform_summary['total_registrations']); ?></strong>
                <small>Active registrations</small>
            </article>
            <article class="admin-summary-card">
                <span>Attendance Records</span>
                <strong><?php echo number_format($platform_summary['total_attendance']); ?></strong>
                <small><?php echo number_format($platform_summary['pending_attendance']); ?> pending, <?php echo number_format($platform_summary['present_attendance']); ?> present, <?php echo number_format($platform_summary['absent_attendance']); ?> absent</small>
            </article>
        </div>

        <form class="admin-toolbar admin-filter-form" method="get" action="admin-reports.php">
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
                <span>Event / Marked Date</span>
                <input class="admin-sort-select" type="date" name="date" value="<?php echo htmlspecialchars($date_filter, ENT_QUOTES, 'UTF-8'); ?>">
            </label>

            <label class="admin-filter-field">
                <span>Attendance Status</span>
                <select class="admin-sort-select" name="status">
                    <?php foreach ($attendance_status_options as $status_key => $status_label): ?>
                        <option value="<?php echo htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $attendance_filter === $status_key ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="admin-filter-actions">
                <button class="button button-primary admin-small-button" type="submit">Apply Filters</button>
                <a class="button button-outline admin-small-button" href="admin-reports.php">Reset</a>
            </div>
        </form>

        <div class="dashboard-title-row admin-subsection-title">
            <div>
                <h2>Event Participation Report</h2>
                <p>Capacity, registration totals, remaining slots, and attendance percentage per event.</p>
            </div>
        </div>

        <div class="dashboard-table-card">
            <table class="dashboard-event-table admin-table">
                <thead>
                    <tr>
                        <th scope="col">Event</th>
                        <th scope="col">Organizer</th>
                        <th scope="col">Capacity</th>
                        <th scope="col">Registered Participants</th>
                        <th scope="col">Present Participants</th>
                        <th scope="col">Absent Participants</th>
                        <th scope="col">Remaining Slots</th>
                        <th scope="col">Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($participation_report)): ?>
                        <tr>
                            <td colspan="8">No event participation records match the selected filters.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($participation_report as $event): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <small><?php echo htmlspecialchars(admin_event_format_date($event['event_date']), ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($event['organizer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo number_format($event['capacity']); ?></td>
                            <td><?php echo number_format($event['registration_records']); ?></td>
                            <td><?php echo number_format($event['present_count']); ?></td>
                            <td><?php echo number_format($event['absent_count']); ?></td>
                            <td><?php echo number_format($event['remaining_slots']); ?></td>
                            <td><?php echo htmlspecialchars($event['attendance_percentage'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="dashboard-title-row admin-subsection-title">
            <div>
                <h2>Attendance Summary</h2>
                <p>Present, absent, and attendance-rate calculations per event.</p>
            </div>
        </div>

        <div class="dashboard-table-card">
            <table class="dashboard-event-table admin-table">
                <thead>
                    <tr>
                        <th scope="col">Event</th>
                        <th scope="col">Registered Participants</th>
                        <th scope="col">Present</th>
                        <th scope="col">Absent</th>
                        <th scope="col">Pending</th>
                        <th scope="col">Attendance Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendance_summary)): ?>
                        <tr>
                            <td colspan="6">No attendance summary records match the selected filters.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($attendance_summary as $event): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <small><?php echo htmlspecialchars(admin_event_format_date($event['event_date']), ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td><?php echo number_format($event['registered_participants']); ?></td>
                            <td><?php echo number_format($event['present_count']); ?></td>
                            <td><?php echo number_format($event['absent_count']); ?></td>
                            <td><?php echo number_format($event['pending_count']); ?></td>
                            <td><?php echo htmlspecialchars($event['attendance_rate'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="dashboard-title-row admin-subsection-title">
            <div>
                <h2>Attendance Detail Records</h2>
                <p>Filtered attendance records for checking report totals.</p>
            </div>
        </div>

        <div class="dashboard-table-card">
            <table class="dashboard-event-table admin-table">
                <thead>
                    <tr>
                        <th scope="col">Event</th>
                        <th scope="col">Participant</th>
                        <th scope="col">Code</th>
                        <th scope="col">Attendance</th>
                        <th scope="col">Marked Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendance_records)): ?>
                        <tr>
                            <td colspan="5">No attendance detail records match the selected filters.</td>
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
                            <td><?php echo htmlspecialchars($record['attendance_code'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="dashboard-status dashboard-status-<?php echo htmlspecialchars($record_class, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(admin_registration_label($record['attendance_status']), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(admin_report_format_datetime($record['marked_at']), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
