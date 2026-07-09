<?php
$page_title = 'Shenanovents | Admin Profile';
$current_page = 'admin';
$base_path = '../';
$asset_version = 'admin-interface-standard';

require_once __DIR__ . '/../includes/admin-check.php';
require_once __DIR__ . '/../includes/participant-data.php';
require_once __DIR__ . '/../includes/admin-dashboard-data.php';

$admin_id = (int) ($_SESSION['user_id'] ?? 0);
$user = participant_fetch_profile($conn, $admin_id, 'admin');

if (!$user) {
    $_SESSION['auth_error'] = 'Admin account was not found.';
    header('Location: ../auth/signin.php');
    exit;
}

$summary = admin_dashboard_fetch_summary($conn);
$activity_items = admin_dashboard_fetch_recent_activity($conn, 4);
$success_message = participant_get_flash('success');
$error_message = participant_get_flash('error');
$full_name = trim($user['first_name'] . ' ' . $user['last_name']);
$joined_text = 'Joined ' . participant_format_date($user['created_at']);
$role_text = ucwords($user['role']);
$status_text = 'Status: ' . ucwords($user['status']);
$username_text = '@' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $user['first_name'] . $user['last_name']));

$statistics = [
    ['icon' => 'icon-user', 'label' => 'Total Users', 'total' => $summary['total_users']],
    ['icon' => 'icon-plus', 'label' => 'Total Events', 'total' => $summary['total_events']],
    ['icon' => 'icon-ticket', 'label' => 'Pending Approvals', 'total' => $summary['pending_events']],
    ['icon' => 'icon-heart', 'label' => 'Published Events', 'total' => $summary['published_events']],
    ['icon' => 'icon-user', 'label' => 'Private Events', 'total' => $summary['private_events']],
    ['icon' => 'icon-ticket', 'label' => 'Registrations', 'total' => $summary['total_registrations']],
];

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
                <h2>Admin Overview</h2>
                <p>Quick platform statistics connected to the administrator dashboard.</p>
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

        <section class="profile-content-section" aria-labelledby="adminActivityTitle">
            <div class="profile-section-heading">
                <div>
                    <h2 id="adminActivityTitle">Recent Platform Activity</h2>
                    <p>Latest database activity visible to administrators.</p>
                </div>
                <a class="button button-outline" href="admin-dashboard.php">Admin Dashboard</a>
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
        </section>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
