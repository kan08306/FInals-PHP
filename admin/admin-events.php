<?php
$page_title = 'Shenanovents | Manage Events';
$current_page = 'admin';
$base_path = '../';

require_once __DIR__ . '/../includes/admin-check.php';
require_once __DIR__ . '/../includes/admin-event-data.php';

admin_event_handle_post($conn, 'admin-events.php');

$events = admin_event_fetch_events($conn);
$admin_page_size = 5;
$admin_total_pages = max(1, (int) ceil(count($events) / $admin_page_size));
$success_message = admin_event_get_flash('success');
$error_message = admin_event_get_flash('error');

require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-page" aria-labelledby="manageEventsTitle">
    <div class="admin-section">
        <div class="dashboard-title-row">
            <div>
                <h1 id="manageEventsTitle">Manage Events</h1>
                <p>Review event listings, check approval status, and manage platform visibility.</p>
            </div>

            <a class="button button-primary" href="admin-approvals.php">Approval Queue</a>
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

        <div class="admin-toolbar">
            <label class="admin-search-field">
                <span class="sr-only">Search events</span>
                <input type="search" placeholder="Search event or organizer" data-admin-search>
            </label>

            <select class="admin-sort-select" data-admin-sort aria-label="Sort events">
                <option value="name">Sort by event name</option>
                <option value="date">Sort by event date</option>
                <option value="status">Sort by status</option>
            </select>
        </div>

        <div class="dashboard-filter-row">
            <div class="dashboard-filter-tabs pill-menu admin-wide-tabs" aria-label="Event status filters">
                <button class="active" type="button" data-admin-filter="all">All</button>
                <button type="button" data-admin-filter="pending">Pending</button>
                <button type="button" data-admin-filter="approved">Approved</button>
                <button type="button" data-admin-filter="private">Private</button>
                <button type="button" data-admin-filter="rejected">Rejected</button>
            </div>
        </div>

        <div class="dashboard-table-card">
            <table class="dashboard-event-table admin-table">
                <thead>
                    <tr>
                        <th scope="col">Event</th>
                        <th scope="col">Organizer</th>
                        <th scope="col">Type</th>
                        <th scope="col">Event Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Registrations</th>
                        <th scope="col">Created</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="8">No events are available for admin review yet.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($events as $event): ?>
                        <?php
                        $status_key = strtolower($event['status']);
                        $visibility_key = strtolower($event['visibility']);
                        $event_title = $event['event_title'];
                        $organizer = $event['organizer_name'];
                        $event_location = admin_event_location_label($event);
                        $event_date_label = admin_event_format_date($event['event_date']);
                        $event_start_label = admin_event_format_time($event['event_time']);
                        $event_end_label = !empty($event['event_end_time']) ? admin_event_format_time($event['event_end_time']) : 'N/A';
                        $event_time_label = admin_event_format_time_range($event['event_time'], $event['event_end_time']);
                        $registration_label = admin_event_registration_label($event);
                        $publish_label = admin_event_publish_label($event);
                        $created_label = admin_event_format_date($event['created_at']);
                        $banner_src = admin_event_banner_src($event, '../');
                        $search_text = strtolower(implode(' ', [
                            $event_title,
                            $organizer,
                            $event['organizer_email'],
                            $event['event_category'],
                            $event_location,
                            $event['event_country'],
                            $event['event_city'],
                            $event['status'],
                            $event['visibility'],
                        ]));
                        ?>
                        <tr
                            data-admin-row
                            data-admin-id="<?php echo (int) $event['event_id']; ?>"
                            data-admin-status="<?php echo htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-visibility="<?php echo htmlspecialchars($visibility_key, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-search-text="<?php echo htmlspecialchars($search_text, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-name="<?php echo htmlspecialchars($event_title, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-summary="<?php echo htmlspecialchars($event['event_summary'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-description="<?php echo htmlspecialchars($event['event_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-tags="<?php echo htmlspecialchars($event['event_tags'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-category="<?php echo htmlspecialchars($event['event_category'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-organizer="<?php echo htmlspecialchars($organizer, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-organizer-email="<?php echo htmlspecialchars($event['organizer_email'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-banner="<?php echo htmlspecialchars($banner_src, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-type="<?php echo htmlspecialchars($event['event_type'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-type-label="<?php echo htmlspecialchars(admin_event_status_label($event['event_type']), ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-location="<?php echo htmlspecialchars($event_location, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-country="<?php echo htmlspecialchars($event['event_country'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-city="<?php echo htmlspecialchars($event['event_city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-address="<?php echo htmlspecialchars($event['event_address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-venue="<?php echo htmlspecialchars($event['event_venue'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-online-link="<?php echo htmlspecialchars($event['online_link'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-online-platform="<?php echo htmlspecialchars($event['online_platform'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-date="<?php echo htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-date-label="<?php echo htmlspecialchars($event_date_label, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-time="<?php echo htmlspecialchars(substr((string) $event['event_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-end-time="<?php echo htmlspecialchars(substr((string) ($event['event_end_time'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-start-time-label="<?php echo htmlspecialchars($event_start_label, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-end-time-label="<?php echo htmlspecialchars($event_end_label, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-time-label="<?php echo htmlspecialchars($event_time_label, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-status-label="<?php echo htmlspecialchars(admin_event_status_label($event['status']), ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-registrations="<?php echo htmlspecialchars($registration_label, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-capacity="<?php echo (int) $event['capacity']; ?>"
                            data-admin-visibility-label="<?php echo htmlspecialchars(admin_event_status_label($event['visibility']), ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-audience="<?php echo htmlspecialchars($event['audience'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-publish-date="<?php echo htmlspecialchars($event['publish_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-publish-time="<?php echo htmlspecialchars(substr((string) ($event['publish_time'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-publish-label="<?php echo htmlspecialchars($publish_label, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-created="<?php echo htmlspecialchars($created_label, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <td>
                                <span class="dashboard-event-link admin-event-link">
                                    <img class="dashboard-event-thumb" src="<?php echo htmlspecialchars($banner_src, ENT_QUOTES, 'UTF-8'); ?>" alt="">
                                    <span class="dashboard-event-copy">
                                        <strong><?php echo htmlspecialchars($event_title, ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <small><?php echo htmlspecialchars($event_location, ENT_QUOTES, 'UTF-8'); ?></small>
                                    </span>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($organizer, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(admin_event_status_label($event['event_type']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php echo htmlspecialchars($event_date_label, ENT_QUOTES, 'UTF-8'); ?><br>
                                <small><?php echo htmlspecialchars($event_time_label, ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td><span class="dashboard-status dashboard-status-<?php echo htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(admin_event_status_label($event['status']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td><?php echo htmlspecialchars($registration_label, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($created_label, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <div class="admin-action-row">
                                    <button class="button button-outline admin-small-button" type="button" data-event-detail>View</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($admin_total_pages > 1): ?>
            <div class="dashboard-pagination pill-menu" data-admin-pagination aria-label="Event pagination">
                <button type="button" data-admin-page-previous>Previous</button>
                <?php for ($page = 1; $page <= $admin_total_pages; $page++): ?>
                    <button class="<?php echo $page === 1 ? 'active' : ''; ?>" type="button" data-admin-page="<?php echo (int) $page; ?>"><?php echo (int) $page; ?></button>
                <?php endfor; ?>
                <button type="button" data-admin-page-next>Next</button>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="registration-modal-overlay" data-admin-detail-modal aria-hidden="true" hidden>
    <section class="registration-modal-card admin-detail-modal admin-view-modal" role="dialog" aria-modal="true" aria-labelledby="eventDetailTitle">
        <button class="registration-modal-close" type="button" aria-label="Close event details" data-modal-close>&times;</button>

        <div class="admin-detail-hero">
            <img class="admin-detail-banner" src="../assets/images/events/hero-event.png" alt="" data-admin-detail-banner>
            <div class="admin-detail-heading">
                <div class="admin-detail-heading-copy">
                    <h2 id="eventDetailTitle" data-admin-detail-title>Event profile</h2>
                    <p class="admin-detail-organizer-line">
                        <span data-admin-detail-organizer-line></span>
                        <span class="admin-detail-separator" aria-hidden="true">/</span>
                        <span data-admin-detail-organizer-email></span>
                    </p>
                </div>
                <span class="dashboard-status" data-admin-detail-status-badge>Status</span>
            </div>
            <p class="admin-detail-summary" data-admin-detail-summary>No event summary available.</p>
            <div class="event-badges admin-detail-badges">
                <span class="event-badge" data-admin-detail-category-badge>Category</span>
                <span class="event-badge" data-admin-detail-type-badge>Event Type</span>
            </div>
        </div>

        <div class="admin-detail-section">
            <h3>Event Information</h3>
            <dl class="admin-detail-info-grid">
                <div class="admin-detail-info-card">
                    <dt>Category</dt>
                    <dd data-admin-detail-category></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Event Date</dt>
                    <dd data-admin-detail-date></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Time</dt>
                    <dd data-admin-detail-time></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Venue or Online Information</dt>
                    <dd data-admin-detail-venue></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Country</dt>
                    <dd data-admin-detail-country></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>City</dt>
                    <dd data-admin-detail-city></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Capacity</dt>
                    <dd data-admin-detail-capacity></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Current Registrations</dt>
                    <dd data-admin-detail-registrations></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Visibility</dt>
                    <dd data-admin-detail-visibility></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Publish Schedule</dt>
                    <dd data-admin-detail-publish></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Created</dt>
                    <dd data-admin-detail-created></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Status</dt>
                    <dd data-admin-detail-status></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Event Type</dt>
                    <dd data-admin-detail-type></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Location</dt>
                    <dd data-admin-detail-location></dd>
                </div>
                <div class="admin-detail-info-card admin-detail-info-card-wide">
                    <dt>Full Address</dt>
                    <dd data-admin-detail-address></dd>
                </div>
                <div class="admin-detail-info-card admin-detail-info-card-wide">
                    <dt>Additional Details</dt>
                    <dd data-admin-detail-description></dd>
                </div>
            </dl>
        </div>

        <div class="admin-detail-section">
            <h3>Organizer Information</h3>
            <dl class="admin-detail-info-grid">
                <div class="admin-detail-info-card">
                    <dt>Organizer</dt>
                    <dd data-admin-detail-organizer></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Email</dt>
                    <dd data-admin-detail-organizer-email></dd>
                </div>
            </dl>
        </div>

        <div class="admin-detail-actions admin-detail-actions-compact" data-admin-detail-actions>
            <div class="admin-detail-action-row admin-detail-action-row-status" data-admin-detail-action-group="status">
                <form action="admin-events.php" method="post" data-admin-detail-action="approve-publish">
                    <input type="hidden" name="event_id" data-admin-detail-event-id>
                    <button class="button button-primary admin-small-button" type="submit" name="admin_event_action" value="approve_publish_event">Approve</button>
                </form>

                <form action="admin-events.php" method="post" data-admin-detail-action="reject-close" onsubmit="return confirm('Reject this event? It will no longer appear as an available event to participants.');">
                    <input type="hidden" name="event_id" data-admin-detail-event-id>
                    <button class="button button-outline admin-small-button danger" type="submit" name="admin_event_action" value="reject_close_event">Reject</button>
                </form>
            </div>

            <div class="admin-detail-action-row admin-detail-action-row-record">
                <button class="button button-primary admin-small-button" type="button" data-event-detail-edit>Edit</button>

                <form action="admin-events.php" method="post" data-admin-detail-action="delete" onsubmit="return confirm('Permanently delete this event? This will permanently remove the event and related records. This action cannot be undone.');">
                    <input type="hidden" name="event_id" data-admin-detail-event-id>
                    <button class="button button-outline admin-small-button danger admin-permanent-delete-button" type="submit" name="admin_event_action" value="permanent_delete_event">Delete</button>
                </form>
            </div>
        </div>
    </section>
</div>

<div class="registration-modal-overlay" data-admin-edit-modal aria-hidden="true" hidden>
    <section class="registration-modal-card event-registration-card admin-event-edit-modal" role="dialog" aria-modal="true" aria-labelledby="adminEditEventTitle">
        <button class="registration-modal-close" type="button" aria-label="Close edit event form" data-modal-close>&times;</button>
        <form class="event-registration-form" action="admin-events.php" method="post">
            <input type="hidden" name="admin_event_action" value="update_event">
            <input type="hidden" name="event_id" data-admin-edit-field="event_id">

            <div class="registration-modal-heading">
                <p class="registration-modal-kicker">Edit Event</p>
                <h2 id="adminEditEventTitle">Update event information</h2>
            </div>

            <div class="registration-field-grid">
                <label class="registration-field-full">
                    <span>Event Title</span>
                    <input type="text" name="event_title" required data-admin-edit-field="event_title">
                </label>

                <label class="registration-field-full">
                    <span>Summary</span>
                    <input type="text" name="event_summary" data-admin-edit-field="event_summary">
                </label>

                <label class="registration-field-full">
                    <span>Description</span>
                    <textarea name="event_description" rows="5" required data-admin-edit-field="event_description"></textarea>
                </label>

                <label>
                    <span>Category</span>
                    <input type="text" name="event_category" data-admin-edit-field="event_category">
                </label>

                <label>
                    <span>Tags</span>
                    <input type="text" name="event_tags" data-admin-edit-field="event_tags">
                </label>

                <label>
                    <span>Event Type</span>
                    <select name="event_type" required data-admin-edit-field="event_type">
                        <option value="physical">Physical</option>
                        <option value="online">Online</option>
                        <option value="tba">To Be Announced</option>
                    </select>
                </label>

                <label>
                    <span>Status</span>
                    <select name="status" required data-admin-edit-field="status">
                        <?php foreach (admin_event_status_options() as $status_value => $status_label): ?>
                            <option value="<?php echo htmlspecialchars($status_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <span>Event Date</span>
                    <input type="date" name="event_date" required data-admin-edit-field="event_date">
                </label>

                <label>
                    <span>Start Time</span>
                    <input type="time" name="event_time" required data-admin-edit-field="event_time">
                </label>

                <label>
                    <span>End Time</span>
                    <input type="time" name="event_end_time" data-admin-edit-field="event_end_time">
                </label>

                <label>
                    <span>Capacity</span>
                    <input type="number" name="capacity" min="1" required data-admin-edit-field="capacity">
                </label>

                <label class="registration-field-full">
                    <span>Location Display</span>
                    <input type="text" name="event_location" required data-admin-edit-field="event_location">
                </label>

                <label>
                    <span>Country</span>
                    <input type="text" name="event_country" required data-admin-edit-field="event_country">
                </label>

                <label>
                    <span>City</span>
                    <input type="text" name="event_city" data-admin-edit-field="event_city">
                </label>

                <label>
                    <span>Venue</span>
                    <input type="text" name="event_venue" data-admin-edit-field="event_venue">
                </label>

                <label>
                    <span>Address</span>
                    <input type="text" name="event_address" data-admin-edit-field="event_address">
                </label>

                <label>
                    <span>Online Platform</span>
                    <input type="text" name="online_platform" data-admin-edit-field="online_platform">
                </label>

                <label>
                    <span>Online Link</span>
                    <input type="url" name="online_link" data-admin-edit-field="online_link">
                </label>

                <label>
                    <span>Visibility</span>
                    <select name="visibility" required data-admin-edit-field="visibility">
                        <option value="public">Public</option>
                        <option value="private">Private</option>
                    </select>
                </label>

                <label>
                    <span>Audience</span>
                    <input type="text" name="audience" data-admin-edit-field="audience">
                </label>

                <label>
                    <span>Publish Date</span>
                    <input type="date" name="publish_date" data-admin-edit-field="publish_date">
                </label>

                <label>
                    <span>Publish Time</span>
                    <input type="time" name="publish_time" data-admin-edit-field="publish_time">
                </label>
            </div>

            <div class="registration-modal-actions">
                <button class="button button-primary" type="submit">Save Changes</button>
                <button class="button button-outline" type="button" data-modal-close>Cancel</button>
            </div>
        </form>
    </section>
</div>

<div class="registration-modal-overlay" data-admin-message-modal aria-hidden="true" hidden>
    <section class="registration-modal-card admin-detail-modal" role="dialog" aria-modal="true" aria-labelledby="adminEventMessageTitle">
        <button class="registration-modal-close" type="button" aria-label="Close action message" data-modal-close>&times;</button>
        <p class="registration-modal-kicker">Admin Action</p>
        <h2 id="adminEventMessageTitle" data-admin-message-title>Action ready</h2>
        <p data-admin-message-copy>This frontend action is ready for backend integration.</p>
        <div class="registration-modal-actions">
            <button class="button button-primary" type="button" data-modal-close>Done</button>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
