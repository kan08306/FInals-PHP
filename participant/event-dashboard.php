<?php
$page_title = 'Shenanovents | Event Dashboard';
$current_page = 'dashboard';
$base_path = '../';
$body_class = 'event-dashboard-body';
$asset_version = 'my-events-restore';

require_once __DIR__ . '/../includes/participant-check.php';
require_once __DIR__ . '/../includes/participant-data.php';

$participant_id = participant_current_user_id();
$selected_event_id = (int) ($_GET['event'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['dashboard_action'] ?? '') === 'close_event') {
    $posted_event_id = (int) ($_POST['event_id'] ?? 0);
    $dashboard_event = $posted_event_id > 0 ? participant_fetch_owned_event_dashboard($conn, $participant_id, $posted_event_id) : null;

    if (!$dashboard_event) {
        participant_flash('error', 'This event was not found, or it does not belong to your account.');
    } elseif (!participant_event_is_available($dashboard_event['status'] ?? '')) {
        participant_flash('error', 'This event is already closed or completed.');
    } elseif (participant_update_owned_event_status($conn, $participant_id, $posted_event_id, 'closed')) {
        participant_flash('success', 'Event closed successfully. New registrations are now blocked.');
    } else {
        participant_flash('error', 'Unable to close the selected event. Please try again.');
    }

    header('Location: event-dashboard.php?event=' . $posted_event_id);
    exit;
}

if ($selected_event_id <= 0) {
    $owned_events = participant_fetch_hosted_events($conn, $participant_id);
    $selected_event_id = (int) ($owned_events[0]['event_id'] ?? 0);
}

$event = $selected_event_id > 0 ? participant_fetch_owned_event_dashboard($conn, $participant_id, $selected_event_id) : null;
$registration_bars = $event ? participant_fetch_event_registration_bars($conn, $participant_id, (int) $event['event_id']) : [];
$attendance_bars = $event ? participant_build_event_dashboard_bars($event) : [];
$capacity = $event ? max(0, (int) $event['capacity']) : 0;
$registered_count = $event ? (int) $event['registered_count'] : 0;
$capacity_filled = $capacity > 0 ? min(100, (int) round(($registered_count / $capacity) * 100)) : 0;
$status = $event ? strtolower($event['status']) : '';
$status_class = $status === 'open' ? 'registered' : ($status === 'closed' ? 'cancelled' : $status);
$event_banner_src = $event ? participant_event_banner_src($event, '../') : '';
$success_message = participant_get_flash('success');
$error_message = participant_get_flash('error');

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (!$event): ?>
    <section class="dashboard-page" aria-labelledby="eventDashboardTitle">
        <div class="dashboard-section">
            <div class="dashboard-title-row">
                <div>
                    <h1 id="eventDashboardTitle">Event Dashboard</h1>
                    <p>Select one of your own created events before opening its dashboard summary.</p>
                </div>

                <a class="button button-outline" href="dashboard.php">Back to My Events</a>
            </div>

            <?php participant_render_empty_state('No event dashboard available', 'This event was not found, or it does not belong to your account.', 'dashboard.php', 'View My Events'); ?>
        </div>
    </section>
<?php else: ?>
    <section class="event-dashboard-page" aria-labelledby="eventDashboardTitle">
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

                <a class="wizard-dashboard-link active" href="event-dashboard.php?event=<?php echo (int) $event['event_id']; ?>">
                    <span class="wizard-dashboard-icon" aria-hidden="true"></span>
                    Dashboard
                </a>
                <a class="wizard-dashboard-link" href="event-attendance.php?event=<?php echo (int) $event['event_id']; ?>">
                    <span class="wizard-dashboard-icon" aria-hidden="true"></span>
                    Attendance
                </a>
            </aside>

            <div class="event-dashboard-main">
                <div class="dashboard-title-row">
                    <div>
                        <h1 id="eventDashboardTitle">Event Dashboard</h1>
                        <p>Track attendance, free registrations, and capacity for this selected event.</p>
                    </div>

                    <div class="dashboard-title-actions">
                        <a class="button button-primary" href="event-attendance.php?event=<?php echo (int) $event['event_id']; ?>">Attendance</a>
                        <a class="button button-outline" href="dashboard.php">Back to My Events</a>
                        <?php if (participant_event_is_available($event['status'] ?? '')): ?>
                            <form class="dashboard-close-form" action="event-dashboard.php?event=<?php echo (int) $event['event_id']; ?>" method="post">
                                <input type="hidden" name="dashboard_action" value="close_event">
                                <input type="hidden" name="event_id" value="<?php echo (int) $event['event_id']; ?>">
                                <button class="button dashboard-close-button" type="submit">Close Event</button>
                            </form>
                        <?php else: ?>
                            <span class="dashboard-status dashboard-status-<?php echo htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars(ucwords($event['status']), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php participant_render_feedback($success_message, $error_message); ?>

                <div class="event-dashboard-grid">
                    <article class="dashboard-card attendance-card">
                        <div class="dashboard-card-header">
                            <h2>Attendee Summary</h2>
                            <span>Event-specific counts</span>
                        </div>

                        <div class="attendance-bar-chart" aria-label="Attendance bar chart">
                            <?php foreach ($attendance_bars as $bar): ?>
                                <div class="attendance-bar-item attendance-bar-<?php echo htmlspecialchars($bar['class'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <span class="attendance-bar-value"><?php echo (int) $bar['count']; ?></span>
                                    <span class="attendance-bar-track" aria-hidden="true">
                                        <span class="attendance-bar-fill" style="--bar-height: <?php echo htmlspecialchars((string) $bar['height'], ENT_QUOTES, 'UTF-8'); ?>%;"></span>
                                    </span>
                                    <span class="attendance-bar-label"><?php echo htmlspecialchars($bar['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>

                    <article class="dashboard-card registration-rate-card">
                        <div class="dashboard-card-header">
                            <h2>Rate of Free Registrations</h2>
                            <span><?php echo $capacity_filled; ?>% capacity filled</span>
                        </div>

                        <div class="rate-bars" aria-label="Registration rate chart">
                            <?php foreach ($registration_bars as $bar): ?>
                                <span tabindex="0" style="--bar-height: <?php echo htmlspecialchars((string) $bar['height'], ENT_QUOTES, 'UTF-8'); ?>%" data-label="<?php echo htmlspecialchars($bar['label'], ENT_QUOTES, 'UTF-8'); ?>" data-value="<?php echo htmlspecialchars((string) $bar['value'], ENT_QUOTES, 'UTF-8'); ?> registrations"></span>
                            <?php endforeach; ?>
                        </div>
                    </article>

                    <article class="dashboard-card event-dashboard-preview-card">
                        <?php if ($event_banner_src !== ''): ?>
                            <img src="<?php echo htmlspecialchars($event_banner_src, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?> banner preview">
                        <?php else: ?>
                            <div class="event-dashboard-preview-fallback" aria-hidden="true">
                                <span><?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>
                        <div>
                            <span class="event-badge">Free</span>
                            <span class="dashboard-status dashboard-status-<?php echo htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars(ucwords($event['status']), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <h2><?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <p><?php echo htmlspecialchars($event['date_text'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($event['time_text'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><?php echo htmlspecialchars($event['event_location'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php if (($event['visibility'] ?? '') === 'private' && !empty($event['private_access_key'])): ?>
                                <p class="event-private-key-line">Private Code: <strong><?php echo htmlspecialchars($event['private_access_key'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                            <?php endif; ?>
                        </div>
                    </article>

                    <article class="dashboard-card event-performance-card">
                        <div class="dashboard-card-header">
                            <h2>Event Performance</h2>
                            <span>Free registration metrics</span>
                        </div>

                        <dl class="performance-list">
                            <div>
                                <dt>Total Registrations</dt>
                                <dd><?php echo (int) $event['registered_count']; ?> / <?php echo (int) $event['capacity']; ?></dd>
                            </div>
                            <div>
                                <dt>Registration Records</dt>
                                <dd><?php echo (int) $event['registration_records']; ?></dd>
                            </div>
                            <div>
                                <dt>Present Attendees</dt>
                                <dd><?php echo (int) $event['present_count']; ?></dd>
                            </div>
                            <div>
                                <dt>Pending Attendees</dt>
                                <dd><?php echo (int) $event['pending_count']; ?></dd>
                            </div>
                            <div>
                                <dt>Absent Attendees</dt>
                                <dd><?php echo (int) $event['absent_count']; ?></dd>
                            </div>
                            <div>
                                <dt>Remaining Slots</dt>
                                <dd><?php echo (int) $event['remaining_slots']; ?></dd>
                            </div>
                            <div>
                                <dt>Cancelled Attendees</dt>
                                <dd><?php echo (int) $event['cancelled_count']; ?></dd>
                            </div>
                        </dl>
                    </article>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
