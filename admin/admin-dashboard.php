<?php
// Administrator Page Setup
$page_title = 'Shenanovents | Admin Dashboard';
$current_page = 'admin';
$base_path = '../';

require_once __DIR__ . '/../includes/admin-check.php';
require_once __DIR__ . '/../includes/admin-dashboard-data.php';

$dashboard_summary = admin_dashboard_fetch_summary($conn);
$registration_activity = admin_dashboard_fetch_registration_activity($conn);
$registration_bars = admin_dashboard_prepare_registration_bars($registration_activity);
$status_summary = admin_dashboard_fetch_event_status_summary($conn, $dashboard_summary);
$activity_items = admin_dashboard_fetch_recent_activity($conn, 6);
$approval_summary = admin_dashboard_fetch_approval_summary($conn);

$summary_cards = [
    [
        'label' => 'Total Users',
        'value' => number_format($dashboard_summary['total_users']),
        'note' => '+' . number_format($dashboard_summary['new_users_month']) . ' this month',
    ],
    [
        'label' => 'Total Events',
        'value' => number_format($dashboard_summary['total_events']),
        'note' => number_format($dashboard_summary['active_events']) . ' active events',
    ],
    [
        'label' => 'Pending Event Approvals',
        'value' => number_format($dashboard_summary['pending_events']),
        'note' => 'Needs review',
    ],
    [
        'label' => 'Published Events',
        'value' => number_format($dashboard_summary['published_events']),
        'note' => 'Visible to users',
    ],
    [
        'label' => 'Private Events',
        'value' => number_format($dashboard_summary['private_events']),
        'note' => 'Based on visibility',
    ],
    [
        'label' => 'Total Registrations',
        'value' => number_format($dashboard_summary['total_registrations']),
        'note' => '+' . number_format($dashboard_summary['new_registrations_week']) . ' this week',
    ],
    [
        'label' => 'Attendance Records',
        'value' => number_format($dashboard_summary['total_attendance_records']),
        'note' => number_format($dashboard_summary['present_attendance_records']) . ' present, ' . number_format($dashboard_summary['absent_attendance_records']) . ' absent',
    ],
];

// Shared Layout Rendering
require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-page" aria-labelledby="adminDashboardTitle">
    <div class="admin-section">
        <div class="dashboard-title-row">
            <div>
                <h1 id="adminDashboardTitle">Admin Dashboard</h1>
                <p>Monitor platform activity, event approvals, user growth, and registrations from one Shenanovents workspace.</p>
            </div>

            <div class="dashboard-title-actions">
                <a class="button button-outline" href="admin-reports.php">View Reports</a>
                <a class="button button-primary" href="admin-approvals.php">Review Events</a>
            </div>
        </div>

        <div class="admin-summary-grid" aria-label="Platform summary">
            <?php foreach ($summary_cards as $card): ?>
                <article class="admin-summary-card">
                    <span><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <strong><?php echo htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    <small><?php echo htmlspecialchars($card['note'], ENT_QUOTES, 'UTF-8'); ?></small>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="admin-dashboard-grid">
            <article class="dashboard-card attendance-card admin-chart-card">
                <div class="dashboard-card-header">
                    <h2>Registration Activity</h2>
                    <span>Last 5 weeks</span>
                </div>

                <div class="attendance-bar-chart admin-registration-bars" aria-label="Registration bar chart">
                    <?php foreach ($registration_bars as $bar): ?>
                        <div class="attendance-bar-item">
                            <strong class="attendance-bar-value"><?php echo number_format($bar['value']); ?></strong>
                            <div class="attendance-bar-track">
                                <span class="attendance-bar-fill" style="--bar-height: <?php echo (int) $bar['height']; ?>%;"></span>
                            </div>
                            <span class="attendance-bar-label"><?php echo htmlspecialchars($bar['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="dashboard-card registration-rate-card">
                <div class="dashboard-card-header">
                    <h2>Event Status Mix</h2>
                    <span>Current platform</span>
                </div>

                <div class="rate-bars admin-rate-bars" aria-label="Event status bar chart">
                    <?php foreach ($status_summary as $status): ?>
                        <span
                            style="--bar-height: <?php echo (int) $status['height']; ?>%;"
                            tabindex="0"
                            data-label="<?php echo htmlspecialchars($status['label'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-value="<?php echo htmlspecialchars(number_format($status['value']), ENT_QUOTES, 'UTF-8'); ?>"
                        ></span>
                    <?php endforeach; ?>
                </div>
            </article>
        </div>

        <div class="admin-dashboard-grid">
            <article class="dashboard-card admin-activity-card">
                <div class="dashboard-card-header">
                    <h2>Recent Activity</h2>
                    <span>Database activity</span>
                </div>

                <div class="admin-activity-list">
                    <?php if (empty($activity_items)): ?>
                        <div class="admin-activity-item">
                            <span>No Activity</span>
                            <strong>No platform activity has been recorded yet.</strong>
                            <small>Database</small>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($activity_items as $item): ?>
                        <div class="admin-activity-item">
                            <span><?php echo htmlspecialchars($item['activity_type'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <strong><?php echo htmlspecialchars($item['activity_title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <small><?php echo htmlspecialchars($item['relative_time'], ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="dashboard-card event-performance-card">
                <div class="dashboard-card-header">
                    <h2>Approval Queue</h2>
                    <span>Current workflow</span>
                </div>

                <dl class="performance-list">
                    <div>
                        <dt>Awaiting first review</dt>
                        <dd><?php echo number_format($approval_summary['awaiting_review']); ?></dd>
                    </div>
                    <div>
                        <dt>Public submissions</dt>
                        <dd><?php echo number_format($approval_summary['public_submissions']); ?></dd>
                    </div>
                    <div>
                        <dt>Private submissions</dt>
                        <dd><?php echo number_format($approval_summary['private_submissions']); ?></dd>
                    </div>
                </dl>
            </article>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


