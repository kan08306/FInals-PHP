<?php
$page_title = 'Shenanovents | Event Details';
$current_page = 'events';
$base_path = '../';
$asset_version = 'phase6-event-details';

require_once __DIR__ . '/../includes/participant-check.php';
require_once __DIR__ . '/../includes/participant-data.php';

participant_handle_registration_post($conn);

$participant_id = participant_current_user_id();
$event_id = (int) ($_GET['event'] ?? 0);
$event = $event_id > 0 ? participant_fetch_event_details($conn, $participant_id, $event_id) : null;
$success_message = participant_get_flash('success');
$error_message = participant_get_flash('error');

if ($event) {
    $page_title = 'Shenanovents | ' . $event['event_title'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="profile-page event-detail-page" aria-labelledby="eventDetailTitle">
    <div class="profile-shell">
        <div class="dashboard-title-row">
            <div>
                <h1 id="eventDetailTitle">Event Details</h1>
                <p>Review the event information before liking or registering.</p>
            </div>

            <a class="button button-outline" href="events.php">Back to Events</a>
        </div>

        <?php participant_render_feedback($success_message, $error_message); ?>

        <?php if (!$event): ?>
            <?php participant_render_empty_state('Event not found', 'The selected event may have been removed or is no longer available.', 'events.php', 'Browse Events'); ?>
        <?php else: ?>
            <article class="event-detail-card" data-registration-event data-event-id="event-<?php echo (int) $event['event_id']; ?>" data-event-title="<?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?>" data-event-date-time="<?php echo htmlspecialchars($event['date_time'], ENT_QUOTES, 'UTF-8'); ?>" data-event-location="<?php echo htmlspecialchars($event['event_location'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="event-detail-media">
                    <?php participant_render_event_image($event); ?>
                </div>

                <div class="event-detail-content">
                    <div class="event-card-top event-detail-top">
                        <div class="event-badges" aria-label="Event badges">
                            <span class="event-badge">Free</span>
                            <span class="event-badge"><?php echo htmlspecialchars($event['event_type_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if (($event['status_badge'] ?? '') !== ''): ?>
                                <span class="event-badge"><?php echo htmlspecialchars($event['status_badge'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php participant_render_like_button($event); ?>
                    </div>

                    <h2><?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="event-detail-summary"><?php echo htmlspecialchars($event['event_summary'] !== '' ? $event['event_summary'] : $event['event_description'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><?php echo htmlspecialchars($event['event_description'], ENT_QUOTES, 'UTF-8'); ?></p>

                    <dl class="event-detail-list">
                        <div>
                            <dt>Organizer</dt>
                            <dd><?php echo htmlspecialchars($event['organizer_name'], ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                        <div>
                            <dt>Date</dt>
                            <dd><?php echo htmlspecialchars($event['date_text'], ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                        <div>
                            <dt>Time</dt>
                            <dd><?php echo htmlspecialchars($event['time_text'], ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                        <div>
                            <dt>Location</dt>
                            <dd><?php echo htmlspecialchars($event['event_location'], ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                        <div>
                            <dt>Country</dt>
                            <dd><?php echo htmlspecialchars($event['event_country'] !== '' ? $event['event_country'] : 'Not specified', ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                        <div>
                            <dt>Capacity</dt>
                            <dd><?php echo (int) $event['registered_count']; ?> / <?php echo (int) $event['capacity']; ?> registered</dd>
                        </div>
                        <div>
                            <dt>Remaining Slots</dt>
                            <dd><?php echo (int) $event['remaining_slots']; ?></dd>
                        </div>
                        <div>
                            <dt>Registration Status</dt>
                            <dd><?php echo htmlspecialchars($event['registration_label'], ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                    </dl>

                    <div class="participant-form-actions">
                        <?php participant_render_event_action($event, 'browse'); ?>
                        <a class="button button-outline" href="events.php">Browse More</a>
                    </div>
                </div>
            </article>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
