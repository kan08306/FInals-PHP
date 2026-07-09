<?php
$page_title = 'Shenanovents | My Events';
$current_page = 'dashboard';
$base_path = '../';
$asset_version = 'my-events-restore';

require_once __DIR__ . '/../includes/participant-check.php';
require_once __DIR__ . '/../includes/participant-data.php';

$participant_id = participant_current_user_id();
$all_events = participant_fetch_hosted_events($conn, $participant_id);
$pagination = participant_paginate_items($all_events, participant_current_page(), 5);
$events = $pagination['items'];
$success_message = participant_get_flash('success');
$error_message = participant_get_flash('error');

require_once __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-page" aria-labelledby="dashboardTitle">
    <div class="dashboard-section">
        <div class="dashboard-title-row">
            <div>
                <h1 id="dashboardTitle">My Events</h1>
                <p>Select one of your created or published events to view its dashboard summary, attendance data, and registration performance.</p>
            </div>

            <a class="button button-primary" href="../event-maker/create-event.php">
                <span class="icon icon-plus" aria-hidden="true"></span>
                Create Event
            </a>
        </div>

        <?php participant_render_feedback($success_message, $error_message); ?>

        <div class="dashboard-filter-row">
            <div class="dashboard-filter-tabs pill-menu" aria-label="Created event status filters">
                <button class="active" type="button" data-dashboard-filter="all">All</button>
                <button type="button" data-dashboard-filter="open">Open</button>
                <button type="button" data-dashboard-filter="closed">Closed</button>
            </div>
        </div>

        <div class="dashboard-table-card">
            <table class="dashboard-event-table">
                <thead>
                    <tr>
                        <th scope="col">Event</th>
                        <th scope="col">Date</th>
                        <th scope="col">Capacity</th>
                        <th scope="col">Registrations</th>
                        <th scope="col">Status</th>
                        <th scope="col">Dashboard</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $event): ?>
                            <?php
                            $status = strtolower($event['status']);
                            $status_class = $status === 'open' ? 'registered' : ($status === 'closed' ? 'cancelled' : $status);
                            $event_image = participant_event_banner_src($event, '../');
                            $event_initial = strtoupper(substr($event['event_title'], 0, 1));
                            ?>
                            <tr data-dashboard-event-row data-dashboard-status="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
                                <td>
                                    <a class="dashboard-event-link" href="event-dashboard.php?event=<?php echo (int) $event['event_id']; ?>">
                                        <?php if ($event_image !== ''): ?>
                                            <img class="dashboard-event-thumb" src="<?php echo htmlspecialchars($event_image, ENT_QUOTES, 'UTF-8'); ?>" alt="">
                                        <?php else: ?>
                                            <span class="dashboard-event-thumb dashboard-event-thumb-fallback" aria-hidden="true"><?php echo htmlspecialchars($event_initial, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                        <span class="dashboard-event-copy">
                                            <strong><?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <small><?php echo htmlspecialchars($event['event_location'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <small>Created <?php echo htmlspecialchars(participant_format_date($event['created_at']), ENT_QUOTES, 'UTF-8'); ?></small>
                                        </span>
                                    </a>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($event['date_text'], ENT_QUOTES, 'UTF-8'); ?><br>
                                    <small><?php echo htmlspecialchars($event['time_text'], ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td><?php echo (int) $event['capacity']; ?></td>
                                <td><?php echo (int) $event['registered_count']; ?> / <?php echo (int) $event['capacity']; ?></td>
                                <td>
                                    <span class="dashboard-status dashboard-status-<?php echo htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars(ucwords($status), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <a class="button button-outline admin-small-button" href="event-dashboard.php?event=<?php echo (int) $event['event_id']; ?>">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr data-dashboard-event-row data-dashboard-status="all">
                            <td colspan="6">
                                <strong>No created events yet.</strong>
                                <small>Create or publish an event first, then it will appear here for dashboard review.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php participant_render_dashboard_pagination('dashboard.php', [], $pagination['current_page'], $pagination['total_pages']); ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
