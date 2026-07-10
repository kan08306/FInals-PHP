<?php
// Participant Page Setup
$page_title = 'Shenanovents | User Profile';
$current_page = 'events';
$base_path = '../';
$asset_version = 'participant-module-update';

require_once __DIR__ . '/../includes/participant-check.php';
require_once __DIR__ . '/../includes/participant-data.php';

$participant_id = participant_current_user_id();
$user = participant_fetch_profile($conn, $participant_id);

if (!$user) {
    $_SESSION['auth_error'] = 'Participant account was not found.';
// Redirect Handling
    header('Location: ../auth/signin.php');
    exit;
}

$summary = participant_fetch_registration_summary($conn, $participant_id);
$registered_events = participant_fetch_registered_events($conn, $participant_id, true);
$success_message = participant_get_flash('success');
$error_message = participant_get_flash('error');
$active_registered_events = array_values(array_filter($registered_events, function ($event) {
    return ($event['registration_status'] ?? '') === 'registered';
}));
$hosted_events = participant_fetch_hosted_events($conn, $participant_id);
$liked_events = participant_fetch_liked_events($conn, $participant_id);
$recent_activities = array_slice($registered_events, 0, 3);
$joined_events = array_slice($active_registered_events, 0, 2);
$ticket_events = array_slice($active_registered_events, 0, 2);
$liked_preview_events = array_slice($liked_events, 0, 2);

$full_name = trim($user['first_name'] . ' ' . $user['last_name']);
$joined_text = 'Joined ' . participant_format_date($user['created_at']);
$role_text = ucwords($user['role']);
$status_text = 'Status: ' . ucwords($user['status']);
$username_text = '@' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $user['first_name'] . $user['last_name']));

$statistics = [
    ['icon' => 'icon-ticket', 'label' => 'Tickets Registered', 'total' => $summary['registered']],
    ['icon' => 'icon-heart', 'label' => 'Liked Events', 'total' => $summary['liked']],
    ['icon' => 'icon-user', 'label' => 'Joined Events', 'total' => $summary['registered']],
    ['icon' => 'icon-plus', 'label' => 'Hosted Events', 'total' => $summary['hosted']],
    ['icon' => 'icon-ticket', 'label' => 'Upcoming Events', 'total' => $summary['upcoming']],
    ['icon' => 'icon-heart', 'label' => 'Cancelled Events', 'total' => $summary['cancelled']],
];

// R En De R P Ro Fi Le E Ve Nt C Ar D
function render_profile_event_card($event, $label, $action, $href)
{
    $event['registration_label'] = $label;
    ?>
    <article class="event-card profile-event-card" data-event-filter="<?php echo htmlspecialchars($event['time_filter'] ?? 'all', ENT_QUOTES, 'UTF-8'); ?>">
        <?php participant_render_event_image($event); ?>

        <div class="event-card-top">
            <div class="event-badges" aria-label="Event badges">
                <span class="event-badge"><?php echo htmlspecialchars($event['status_badge'] ?: ucwords($event['registration_status'] ?? $event['status'] ?? 'Event'), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>

        <div class="event-meta">
            <div class="event-body">
                <p class="event-time"><?php echo htmlspecialchars($event['date_time'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h3><?php echo htmlspecialchars($event['event_title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="event-location">
                    <span class="icon icon-location" aria-hidden="true"></span>
                    <?php echo htmlspecialchars($event['event_location'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </div>

            <div class="event-bottom-row">
                <div class="event-ticket-meta">
                    <p class="event-registration-label"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if (!empty($event['attendance_code']) && ($event['registration_status'] ?? '') === 'registered'): ?>
                        <small>Attendance Code: <?php echo htmlspecialchars($event['attendance_code'], ENT_QUOTES, 'UTF-8'); ?></small>
                        <small>Attendance: <?php echo htmlspecialchars($event['attendance_status_label'] ?? 'Pending', ENT_QUOTES, 'UTF-8'); ?></small>
                    <?php endif; ?>
                </div>
                <a class="event-register" href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </div>
        </div>
    </article>
    <?php
}

// R En De R P Ro Fi Le E Mp Ty S Ta Te
function render_profile_empty_state($message)
{
    ?>
    <div class="profile-empty-state">
        <span class="icon icon-ticket" aria-hidden="true"></span>
        <h3><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></h3>
        <p>Once activity becomes available, it will appear in this section.</p>
    </div>
    <?php
}

// Shared Layout Rendering
require_once __DIR__ . '/../includes/header.php';
?>

<section class="profile-page" aria-labelledby="profileTitle">
    <div class="profile-shell">
        <div class="profile-header-card">
            <div class="profile-avatar" aria-hidden="true">
                <?php participant_render_profile_avatar($user['profile_picture'] ?? '', $base_path); ?>
            </div>

            <div class="profile-identity">
                <span class="event-badge"><?php echo htmlspecialchars($role_text . ' - ' . $status_text, ENT_QUOTES, 'UTF-8'); ?></span>
                <h1 id="profileTitle"><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></h1>
                <div class="profile-user-details">
                    <span><?php echo htmlspecialchars($username_text, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><?php echo htmlspecialchars($joined_text, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>

            <a class="button button-primary" href="edit-profile.php">
                <span class="icon icon-user" aria-hidden="true"></span>
                Edit Profile
            </a>
        </div>

        <?php participant_render_feedback($success_message, $error_message); ?>

        <div class="profile-section-heading">
            <div>
                <h2>Profile Overview</h2>
                <p>Quick account statistics based on tickets, registrations, hosted events, and account activity.</p>
            </div>
        </div>

        <div class="profile-stat-grid">
            <?php foreach ($statistics as $statistic): ?>
                <article class="profile-stat-card">
                    <span class="profile-stat-icon" aria-hidden="true">
                        <span class="icon <?php echo htmlspecialchars($statistic['icon'], ENT_QUOTES, 'UTF-8'); ?>"></span>
                    </span>
                    <small><?php echo htmlspecialchars($statistic['label'], ENT_QUOTES, 'UTF-8'); ?></small>
                    <strong><?php echo (int) $statistic['total']; ?></strong>
                </article>
            <?php endforeach; ?>
        </div>

        <section class="profile-content-section" aria-labelledby="activityTitle">
            <div class="profile-section-heading">
                <div>
                    <h2 id="activityTitle">Recent Activity</h2>
                    <p>Latest registration activity from this Shenanovents account.</p>
                </div>
            </div>

            <?php if (!empty($recent_activities)): ?>
                <div class="event-grid profile-event-grid">
                    <?php foreach ($recent_activities as $event): ?>
                        <?php render_profile_event_card($event, 'Registration: ' . ucwords($event['registration_status']), 'View Tickets', 'tickets.php'); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?php render_profile_empty_state('No recent activity yet.'); ?>
            <?php endif; ?>
        </section>

        <section class="profile-content-section" aria-labelledby="hostedTitle">
            <div class="profile-section-heading">
                <div>
                    <h2 id="hostedTitle">Hosted Events</h2>
                    <p>Events created by this user with quick access to organizer dashboards.</p>
                </div>
                <a class="button button-outline" href="dashboard.php">View Dashboard</a>
            </div>

            <?php if (!empty($hosted_events)): ?>
                <div class="event-grid profile-event-grid profile-event-grid-two">
                    <?php foreach ($hosted_events as $event): ?>
                        <?php render_profile_event_card($event, 'Hosted Event', 'Dashboard', 'event-dashboard.php?event=' . urlencode($event['event_id'])); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?php render_profile_empty_state('No hosted events yet.'); ?>
            <?php endif; ?>
        </section>

        <section class="profile-content-section" aria-labelledby="joinedTitle">
            <div class="profile-section-heading">
                <div>
                    <h2 id="joinedTitle">Joined Events</h2>
                    <p>Registered events and upcoming activities.</p>
                </div>
            </div>

            <?php if (!empty($joined_events)): ?>
                <div class="event-grid profile-event-grid profile-event-grid-two">
                    <?php foreach ($joined_events as $event): ?>
                        <?php render_profile_event_card($event, 'Joined Event', 'View Tickets', 'tickets.php'); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?php render_profile_empty_state('No joined events yet.'); ?>
            <?php endif; ?>
        </section>

        <div class="profile-preview-layout">
            <section class="profile-content-section" aria-labelledby="likedTitle">
                <div class="profile-section-heading">
                    <div>
                        <h2 id="likedTitle">Liked Events Preview</h2>
                        <p>Saved events from the Likes page.</p>
                    </div>
                    <a class="button button-outline" href="likes.php">View All</a>
                </div>

                <?php if (!empty($liked_preview_events)): ?>
                    <div class="event-grid profile-event-grid profile-event-grid-single">
                        <?php foreach ($liked_preview_events as $event): ?>
                            <?php render_profile_event_card($event, 'Liked Event', 'View Details', 'event-details.php?event=' . urlencode($event['event_id'])); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php render_profile_empty_state('No liked events yet.'); ?>
                <?php endif; ?>
            </section>

            <section class="profile-content-section" aria-labelledby="ticketsTitle">
                <div class="profile-section-heading">
                    <div>
                        <h2 id="ticketsTitle">Tickets Preview</h2>
                        <p>Registered tickets ready for event entry.</p>
                    </div>
                    <a class="button button-outline" href="tickets.php">View All</a>
                </div>

                <?php if (!empty($ticket_events)): ?>
                    <div class="event-grid profile-event-grid profile-event-grid-single">
                        <?php foreach ($ticket_events as $event): ?>
                            <?php render_profile_event_card($event, 'Ticket Registered', 'View Ticket', 'tickets.php'); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php render_profile_empty_state('No upcoming tickets.'); ?>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




