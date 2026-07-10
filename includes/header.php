<?php
// Shared Header Setup
// Shared Dependencies
require_once __DIR__ . '/session.php';

$current_page = $current_page ?? 'home';
$is_events_page = $current_page === 'events';
$is_dashboard_page = $current_page === 'dashboard';
$is_admin_page = $current_page === 'admin';
$is_app_page = $is_events_page || $is_dashboard_page || $is_admin_page;
$current_file = basename($_SERVER['SCRIPT_NAME'] ?? '');
$app_nav_has_active = in_array($current_file, ['tickets.php', 'likes.php', 'create-event.php'], true);
$dashboard_nav_has_active = in_array($current_file, ['dashboard.php', 'event-dashboard.php', 'tickets.php', 'likes.php', 'profile.php'], true);
$admin_nav_has_active = in_array($current_file, ['admin-dashboard.php', 'admin-users.php', 'admin-events.php', 'admin-approvals.php'], true);
$base_path = $base_path ?? '';
$logo_href = $is_admin_page ? $base_path . 'admin/admin-dashboard.php' : ($is_app_page ? $base_path . 'participant/events.php' : $base_path . 'index.php');
$logo_label = $is_admin_page ? 'Shenanovents admin dashboard' : ($is_app_page ? 'Shenanovents event listings' : 'Shenanovents home');
$asset_version = $asset_version ?? 'frontend-standards';
$safe_page_title = htmlspecialchars($page_title ?? 'Shenanovents', ENT_QUOTES, 'UTF-8');
$safe_logo_href = htmlspecialchars($logo_href, ENT_QUOTES, 'UTF-8');
$safe_logo_label = htmlspecialchars($logo_label, ENT_QUOTES, 'UTF-8');
$safe_base_path = htmlspecialchars($base_path, ENT_QUOTES, 'UTF-8');
$safe_asset_version = urlencode($asset_version);
$display_user_name = $_SESSION['user_name'] ?? ($is_admin_page ? 'Admin Name' : 'User Name');
$safe_display_user_name = htmlspecialchars($display_user_name, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $safe_page_title; ?></title>
    <link rel="stylesheet" href="<?php echo $safe_base_path; ?>assets/css/style.css?v=<?php echo $safe_asset_version; ?>">
</head>
<body<?php echo !empty($body_class) ? ' class="' . htmlspecialchars($body_class, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
    <!-- Header -->
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="<?php echo $safe_logo_href; ?>" aria-label="<?php echo $safe_logo_label; ?>">
                <img src="<?php echo $safe_base_path; ?>assets/images/logos/logonnmae.png" alt="Shenanovents logo">
            </a>

            <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <?php if ($is_admin_page): ?>
                <!-- Navigation -->
                <nav class="main-nav app-nav admin-nav pill-menu<?php echo $admin_nav_has_active ? '' : ' no-active'; ?>" aria-label="Administrator navigation">
                    <a class="<?php echo $current_file === 'admin-dashboard.php' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>admin/admin-dashboard.php">
                        Dashboard
                    </a>
                    <a class="<?php echo $current_file === 'admin-users.php' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>admin/admin-users.php">
                        Users
                    </a>
                    <a class="<?php echo $current_file === 'admin-events.php' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>admin/admin-events.php">
                        Events
                    </a>
                    <a class="<?php echo $current_file === 'admin-approvals.php' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>admin/admin-approvals.php">
                        Approvals
                    </a>
                </nav>
            <?php elseif ($is_dashboard_page): ?>
                <nav class="main-nav app-nav pill-menu<?php echo $dashboard_nav_has_active ? '' : ' no-active'; ?>" aria-label="Main navigation">
                    <a class="<?php echo in_array($current_file, ['dashboard.php', 'event-dashboard.php'], true) ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>participant/dashboard.php">
                        Events
                    </a>
                    <a class="<?php echo $current_file === 'tickets.php' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>participant/tickets.php">
                        <span class="icon icon-ticket" aria-hidden="true"></span>
                        Tickets
                    </a>
                    <a class="<?php echo $current_file === 'likes.php' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>participant/likes.php">
                        <span class="icon icon-heart" aria-hidden="true"></span>
                        Likes
                    </a>
                </nav>
            <?php elseif ($is_events_page): ?>
                <nav class="main-nav app-nav pill-menu<?php echo $app_nav_has_active ? '' : ' no-active'; ?>" aria-label="Main navigation">
                    <a class="<?php echo $current_file === 'tickets.php' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>participant/tickets.php">
                        <span class="icon icon-ticket" aria-hidden="true"></span>
                        Tickets
                    </a>
                    <a class="<?php echo $current_file === 'likes.php' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>participant/likes.php">
                        <span class="icon icon-heart" aria-hidden="true"></span>
                        Likes
                    </a>
                    <a class="<?php echo $current_file === 'create-event.php' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>event-maker/create-event.php">
                        <span class="icon icon-plus" aria-hidden="true"></span>
                        Create
                    </a>
                </nav>
            <?php else: ?>
                <nav class="main-nav pill-menu" aria-label="Main navigation">
                    <a class="<?php echo $current_page === 'home' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>index.php">Home</a>
                    <a class="<?php echo $current_page === 'signin' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>auth/signin.php">Sign In</a>
                    <a class="<?php echo $current_page === 'signup' ? 'active' : ''; ?>" href="<?php echo $safe_base_path; ?>auth/signup.php">Create Account</a>
                </nav>
            <?php endif; ?>

            <?php if ($is_app_page): ?>
                <div class="header-user-menu" data-profile-menu>
                    <button class="header-user-display" type="button" aria-expanded="false" data-profile-toggle>
                        <span><?php echo $safe_display_user_name; ?></span>
                        <span class="header-user-icon" aria-hidden="true">
                            <span class="icon icon-user"></span>
                        </span>
                    </button>
                    <div class="header-user-dropdown" aria-label="User profile options">
                        <a href="<?php echo $is_admin_page ? $safe_base_path . 'admin/profile.php' : $safe_base_path . 'participant/profile.php'; ?>">Profile</a>
                        <a href="<?php echo $is_admin_page ? $safe_base_path . 'admin/admin-dashboard.php' : $safe_base_path . 'participant/dashboard.php'; ?>">
                            <?php echo $is_admin_page ? 'Admin Panel' : 'My Events'; ?>
                        </a>
                        <?php if ($is_admin_page): ?>
                            <a href="<?php echo $safe_base_path; ?>admin/admin-registrations.php">Registrations</a>
                            <a href="<?php echo $safe_base_path; ?>admin/admin-attendance.php">Attendance</a>
                            <a href="<?php echo $safe_base_path; ?>admin/admin-reports.php">Reports</a>
                        <?php else: ?>
                            <a href="#" data-private-event-open>Private Events</a>
                        <?php endif; ?>
                        <a href="<?php echo $safe_base_path; ?>auth/logout.php">Logout</a>
                    </div>
                </div>
            <?php elseif ($current_page === 'home'): ?>
                <a class="header-about-button" href="<?php echo $safe_base_path; ?>index.php#about">About</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content -->
    <main>
