<?php
// Admin Approval Queue Page Setup
$page_title = 'Shenanovents | Event Approvals';
$current_page = 'admin';
$base_path = '../';

// Shared Dependencies
require_once __DIR__ . '/../includes/admin-check.php';
require_once __DIR__ . '/../includes/admin-event-data.php';

// Event Management Form Processing
admin_event_handle_post($conn, 'admin-approvals.php');

$submitted_events = admin_event_fetch_events($conn, 'pending');
$recent_events = admin_event_fetch_recent_activity($conn, 5);
$first_event = $submitted_events[0] ?? null;
$success_message = admin_event_get_flash('success');
$error_message = admin_event_get_flash('error');

// Page Header
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Main Section -->
<section class="admin-page" aria-labelledby="approvalsTitle">
    <div class="admin-section">
        <div class="dashboard-title-row">
            <div>
                <h1 id="approvalsTitle">Event Approvals</h1>
                <p>Review submitted event drafts as they will appear to users, then approve, reject, or request revisions.</p>
            </div>
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

        <div class="admin-approval-layout" data-approval-workspace>
            <aside class="dashboard-card admin-approval-list" aria-label="Submitted events">
                <div class="dashboard-card-header">
                    <h2>Submitted Events</h2>
                    <span><?php echo count($submitted_events); ?> pending</span>
                </div>

                <?php if (empty($submitted_events)): ?>
                    <div class="admin-activity-item">
                        <span>No Pending Events</span>
                        <strong>There are no submitted events waiting for admin review.</strong>
                        <small>Queue clear</small>
                    </div>
                <?php endif; ?>

                <?php foreach ($submitted_events as $index => $event): ?>
                    <?php
                    $event_location = admin_event_location_label($event);
                    $event_time = admin_event_format_time_range($event['event_time'], $event['event_end_time']);
                    $banner_src = admin_event_banner_src($event, '../');
                    ?>
                    <button
                        class="<?php echo $index === 0 ? 'active' : ''; ?>"
                        type="button"
                        data-review-select="<?php echo $index; ?>"
                        data-event-id="<?php echo (int) $event['event_id']; ?>"
                        data-title="<?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-organizer="<?php echo htmlspecialchars($event['organizer_name'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-category="<?php echo htmlspecialchars($event['event_category'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?>"
                        data-date="<?php echo htmlspecialchars(admin_event_format_date($event['event_date']), ENT_QUOTES, 'UTF-8'); ?>"
                        data-time="<?php echo htmlspecialchars($event_time, ENT_QUOTES, 'UTF-8'); ?>"
                        data-location="<?php echo htmlspecialchars($event_location, ENT_QUOTES, 'UTF-8'); ?>"
                        data-capacity="<?php echo (int) $event['capacity']; ?>"
                        data-registrations="<?php echo (int) $event['registered_count']; ?>"
                        data-description="<?php echo htmlspecialchars($event['event_description'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-details="<?php echo htmlspecialchars($event['event_summary'] ?? $event['event_tags'] ?? 'No additional details.', ENT_QUOTES, 'UTF-8'); ?>"
                        data-visibility="<?php echo htmlspecialchars(admin_event_status_label($event['visibility']), ENT_QUOTES, 'UTF-8'); ?>"
                        data-publish="<?php echo htmlspecialchars(admin_event_publish_label($event), ENT_QUOTES, 'UTF-8'); ?>"
                        data-banner="<?php echo htmlspecialchars($banner_src, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                        <strong><?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <span><?php echo htmlspecialchars($event['organizer_name'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($event['event_category'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                <?php endforeach; ?>
            </aside>

            <article class="dashboard-card admin-review-card">
                <?php if ($first_event): ?>
                    <?php
                    $first_location = admin_event_location_label($first_event);
                    $first_time = admin_event_format_time_range($first_event['event_time'], $first_event['event_end_time']);
                    $first_banner = admin_event_banner_src($first_event, '../');
                    ?>
                    <div class="admin-review-banner">
                        <img src="<?php echo htmlspecialchars($first_banner, ENT_QUOTES, 'UTF-8'); ?>" alt="" data-review-banner>
                        <span class="event-badge">Submitted Draft</span>
                    </div>

                    <div class="admin-review-content">
                        <div class="dashboard-card-header">
                            <div>
                                <h2 data-review-title><?php echo htmlspecialchars($first_event['event_title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                <span data-review-organizer>Organizer: <?php echo htmlspecialchars($first_event['organizer_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <span class="dashboard-status dashboard-status-pending" data-review-status>Pending</span>
                        </div>

                        <p data-review-description><?php echo htmlspecialchars($first_event['event_description'], ENT_QUOTES, 'UTF-8'); ?></p>

                        <div class="admin-review-grid">
                            <div><span>Category</span><strong data-review-category><?php echo htmlspecialchars($first_event['event_category'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?></strong></div>
                            <div><span>Event Date</span><strong data-review-date><?php echo htmlspecialchars(admin_event_format_date($first_event['event_date']), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                            <div><span>Time</span><strong data-review-time><?php echo htmlspecialchars($first_time, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                            <div><span>Venue or Online Information</span><strong data-review-location><?php echo htmlspecialchars($first_location, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                            <div><span>Capacity</span><strong data-review-capacity><?php echo (int) $first_event['capacity']; ?> attendees</strong></div>
                            <div><span>Current Registrations</span><strong data-review-registrations><?php echo (int) $first_event['registered_count']; ?> attendees</strong></div>
                            <div><span>Visibility</span><strong data-review-visibility><?php echo htmlspecialchars(admin_event_status_label($first_event['visibility']), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                            <div><span>Publish Schedule</span><strong data-review-publish><?php echo htmlspecialchars(admin_event_publish_label($first_event), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                            <div><span>Additional Details</span><strong data-review-details><?php echo htmlspecialchars($first_event['event_summary'] ?? $first_event['event_tags'] ?? 'No additional details.', ENT_QUOTES, 'UTF-8'); ?></strong></div>
                        </div>

                        <!-- Form -->
                        <form class="admin-review-actions" action="admin-approvals.php" method="post">
                            <input type="hidden" name="event_id" value="<?php echo (int) $first_event['event_id']; ?>" data-review-event-id>
                            <button class="button button-primary" type="submit" name="admin_event_action" value="approve_publish_event">Approve</button>
                            <button class="button button-outline danger" type="submit" name="admin_event_action" value="reject_close_event">Reject</button>
                            <button class="button button-outline" type="button" data-request-changes>Request Changes</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="admin-review-content">
                        <div class="dashboard-card-header">
                            <div>
                                <h2>No events waiting for approval</h2>
                                <span>The approval queue is currently clear.</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </article>

            <aside class="dashboard-card admin-history-card">
                <div class="dashboard-card-header">
                    <h2>Approval History</h2>
                    <span>Database log</span>
                </div>

                <div class="admin-activity-list" data-approval-history>
                    <?php if (empty($recent_events)): ?>
                        <div class="admin-activity-item">
                            <span>No History</span>
                            <strong>No approved, rejected, closed, or cancelled events have been recorded yet.</strong>
                            <small>Database</small>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($recent_events as $event): ?>
                        <div class="admin-activity-item">
                            <span><?php echo htmlspecialchars(admin_event_status_label($event['status']), ENT_QUOTES, 'UTF-8'); ?></span>
                            <strong><?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?> by <?php echo htmlspecialchars($event['organizer_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <small><?php echo htmlspecialchars(admin_event_format_date($event['created_at']), ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>
    </div>
</section>

<div class="registration-modal-overlay" data-revision-modal aria-hidden="true" hidden>
    <section class="registration-modal-card event-registration-card admin-detail-modal" role="dialog" aria-modal="true" aria-labelledby="revisionTitle">
        <button class="registration-modal-close" type="button" aria-label="Close revision request" data-modal-close>&times;</button>
        <form data-revision-form>
            <p class="registration-modal-kicker">Request Changes</p>
            <h2 id="revisionTitle">Send feedback to organizer</h2>
            <label class="registration-field-full admin-feedback-field">
                <span>Feedback</span>
                <textarea name="feedback" rows="5" placeholder="Explain what the organizer needs to revise." required data-revision-feedback></textarea>
            </label>
            <div class="registration-modal-actions">
                <button class="button button-primary" type="submit">Send Request</button>
                <button class="button button-outline" type="button" data-modal-close>Cancel</button>
            </div>
        </form>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
