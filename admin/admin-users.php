<?php
$page_title = 'Shenanovents | Manage Users';
$current_page = 'admin';
$base_path = '../';
$asset_version = 'admin-users-fix-20260707';

require_once __DIR__ . '/../includes/admin-check.php';
require_once __DIR__ . '/../includes/admin-user-data.php';

admin_user_handle_post($conn, 'admin-users.php');

$users = admin_user_fetch_users($conn);
$admin_page_size = 5;
$admin_total_pages = max(1, (int) ceil(count($users) / $admin_page_size));
$success_message = admin_user_get_flash('success');
$error_message = admin_user_get_flash('error');
$current_admin_id = (int) ($_SESSION['user_id'] ?? 0);

require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-page" aria-labelledby="manageUsersTitle">
    <div class="admin-section">
        <div class="dashboard-title-row">
            <div>
                <h1 id="manageUsersTitle">Manage Users</h1>
                <p>Search, filter, and review Shenanovents accounts using live database records.</p>
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

        <div class="admin-toolbar">
            <label class="admin-search-field">
                <span class="sr-only">Search users</span>
                <input type="search" placeholder="Search name or email" data-admin-search>
            </label>

            <select class="admin-sort-select" data-admin-sort aria-label="Sort users">
                <option value="name">Sort by name</option>
                <option value="date">Sort by registration date</option>
                <option value="status">Sort by status</option>
            </select>
        </div>

        <div class="dashboard-filter-row">
            <div class="dashboard-filter-tabs pill-menu" aria-label="User status filters">
                <button class="active" type="button" data-admin-filter="all">All</button>
                <button type="button" data-admin-filter="active">Active</button>
                <button type="button" data-admin-filter="suspended">Suspended</button>
            </div>
        </div>

        <div class="dashboard-table-card">
            <table class="dashboard-event-table admin-table">
                <thead>
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Email Address</th>
                        <th scope="col">Account Type</th>
                        <th scope="col">Registration Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6">No user accounts are available yet.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($users as $user): ?>
                        <?php
                        $user_id = (int) $user['user_id'];
                        $first_name = trim((string) $user['first_name']);
                        $last_name = trim((string) $user['last_name']);
                        $full_name = trim($first_name . ' ' . $last_name);
                        $full_name = $full_name !== '' ? $full_name : $user['email'];
                        $role_key = strtolower(trim((string) $user['role']));
                        $role_label = admin_user_label($role_key);
                        $status_key = strtolower(trim((string) $user['status']));
                        $status_class = preg_replace('/[^a-z0-9-]/', '', str_replace('_', '-', $status_key));
                        $status_class = $status_class !== '' ? $status_class : 'pending';
                        $status_label = admin_user_label($status_key);
                        $registered_label = admin_user_format_date($user['created_at']);
                        $avatar_src = admin_user_profile_src($user['profile_picture'], '../');
                        $initials = admin_user_initials($first_name, $last_name, $user['email']);
                        $search_text = strtolower(implode(' ', [
                            $full_name,
                            $user['email'],
                            $role_label,
                            $status_label,
                        ]));
                        ?>
                        <tr
                            data-admin-row
                            data-admin-id="<?php echo $user_id; ?>"
                            data-admin-current="<?php echo $user_id === $current_admin_id ? '1' : '0'; ?>"
                            data-admin-status="<?php echo htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-search-text="<?php echo htmlspecialchars($search_text, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-name="<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-first-name="<?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-last-name="<?php echo htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-email="<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-role="<?php echo htmlspecialchars($role_key, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-type="<?php echo htmlspecialchars($role_label, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-type-label="<?php echo htmlspecialchars($role_label, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-date="<?php echo htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-date-label="<?php echo htmlspecialchars($registered_label, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-last-updated="N/A"
                            data-admin-status-label="<?php echo htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-registered-count="<?php echo (int) $user['registered_count']; ?>"
                            data-admin-joined-count="<?php echo (int) $user['registered_count']; ?>"
                            data-admin-liked-count="<?php echo (int) $user['liked_count']; ?>"
                            data-admin-created-count="<?php echo (int) $user['created_count']; ?>"
                            data-admin-avatar-src="<?php echo htmlspecialchars($avatar_src, ENT_QUOTES, 'UTF-8'); ?>"
                            data-admin-initials="<?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <td>
                                <span class="admin-user-cell">
                                    <span class="admin-avatar" aria-hidden="true">
                                        <?php if ($avatar_src !== ''): ?>
                                            <img class="admin-avatar-image" src="<?php echo htmlspecialchars($avatar_src, ENT_QUOTES, 'UTF-8'); ?>" alt="" width="42" height="42">
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?>
                                        <?php endif; ?>
                                    </span>
                                    <strong><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></strong>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($role_label, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($registered_label, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="dashboard-status dashboard-status-<?php echo htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td>
                                <div class="admin-action-row">
                                    <button class="button button-outline admin-small-button" type="button" data-user-detail>View</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($admin_total_pages > 1): ?>
            <div class="dashboard-pagination pill-menu" data-admin-pagination aria-label="User pagination">
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
    <section class="registration-modal-card admin-detail-modal admin-view-modal" role="dialog" aria-modal="true" aria-labelledby="userDetailTitle">
        <button class="registration-modal-close" type="button" aria-label="Close user details" data-modal-close>&times;</button>

        <div class="admin-detail-user-heading">
            <span class="admin-avatar admin-detail-avatar" aria-hidden="true">
                <img class="admin-avatar-image" src="" alt="" width="58" height="58" data-admin-detail-avatar-image hidden>
                <span data-admin-detail-avatar-initials>U</span>
            </span>
            <div class="admin-detail-user-copy">
                <h2 id="userDetailTitle" data-admin-detail-title>User profile</h2>
                <p data-admin-detail-email></p>
                <div class="event-badges admin-detail-badges">
                    <span class="event-badge" data-admin-detail-role-badge>Role</span>
                    <span class="dashboard-status" data-admin-detail-status-badge>Status</span>
                </div>
            </div>
        </div>

        <div class="admin-detail-section">
            <h3>Account Information</h3>
            <dl class="admin-detail-info-grid">
                <div class="admin-detail-info-card">
                    <dt>Full Name</dt>
                    <dd data-admin-detail-full-name></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Email</dt>
                    <dd data-admin-detail-email-row></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Account Type</dt>
                    <dd data-admin-detail-type></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Status</dt>
                    <dd data-admin-detail-status></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Registration Date</dt>
                    <dd data-admin-detail-date></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Last Updated</dt>
                    <dd data-admin-detail-updated></dd>
                </div>
            </dl>
        </div>

        <div class="admin-detail-section">
            <h3>Activity Summary</h3>
            <dl class="admin-detail-info-grid">
                <div class="admin-detail-info-card">
                    <dt>Registered Events</dt>
                    <dd data-admin-detail-registered-count></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Joined Events</dt>
                    <dd data-admin-detail-joined-count></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Liked Events</dt>
                    <dd data-admin-detail-liked-count></dd>
                </div>
                <div class="admin-detail-info-card">
                    <dt>Created Events</dt>
                    <dd data-admin-detail-created-count></dd>
                </div>
            </dl>
        </div>

        <div class="admin-detail-actions admin-detail-actions-compact">
            <div class="admin-detail-action-row admin-detail-action-row-status" data-admin-user-action-group="status">
                <form method="post" action="admin-users.php" data-admin-user-action="suspend" data-admin-user-action-form>
                    <input type="hidden" name="admin_user_action" value="suspend_user">
                    <input type="hidden" name="user_id" value="" data-admin-user-id>
                    <button class="button button-outline admin-small-button" type="submit">Suspend</button>
                </form>
                <form method="post" action="admin-users.php" data-admin-user-action="reactivate" data-admin-user-action-form>
                    <input type="hidden" name="admin_user_action" value="reactivate_user">
                    <input type="hidden" name="user_id" value="" data-admin-user-id>
                    <button class="button button-primary admin-small-button" type="submit">Reactivate</button>
                </form>
            </div>

            <div class="admin-detail-action-row admin-detail-action-row-record">
                <button class="button button-primary admin-small-button" type="button" data-user-detail-edit>Edit</button>

                <form method="post" action="admin-users.php" data-admin-user-action="delete" data-admin-user-action-form onsubmit="return confirm('Delete this user account safely by setting it to inactive?');">
                    <input type="hidden" name="admin_user_action" value="delete_user">
                    <input type="hidden" name="user_id" value="" data-admin-user-id>
                    <button class="button button-outline admin-small-button danger" type="submit">Delete</button>
                </form>
            </div>
        </div>
    </section>
</div>

<div class="registration-modal-overlay" data-admin-user-edit-modal aria-hidden="true" hidden>
    <section class="registration-modal-card admin-detail-modal" role="dialog" aria-modal="true" aria-labelledby="userEditTitle">
        <button class="registration-modal-close" type="button" aria-label="Close edit user form" data-modal-close>&times;</button>
        <p class="registration-modal-kicker">Edit User</p>
        <h2 id="userEditTitle">Update account information</h2>
        <form method="post" action="admin-users.php" class="event-form admin-edit-form" data-admin-user-edit-form>
            <input type="hidden" name="admin_user_action" value="update_user">
            <input type="hidden" name="user_id" value="" data-admin-user-edit-field="user_id">

            <div class="registration-field-grid">
                <label class="form-field">
                    <span>First Name</span>
                    <input type="text" name="first_name" required data-admin-user-edit-field="first_name">
                </label>
                <label class="form-field">
                    <span>Last Name</span>
                    <input type="text" name="last_name" required data-admin-user-edit-field="last_name">
                </label>
                <label class="form-field">
                    <span>Email Address</span>
                    <input type="email" name="email" required data-admin-user-edit-field="email">
                </label>
                <label class="form-field">
                    <span>Role</span>
                    <select name="role" required data-admin-user-edit-field="role">
                        <option value="participant">Participant</option>
                        <option value="admin">Admin</option>
                    </select>
                </label>
                <label class="form-field">
                    <span>Status</span>
                    <select name="status" required data-admin-user-edit-field="status">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </label>
            </div>

            <div class="registration-modal-actions">
                <button class="button button-outline" type="button" data-modal-close>Cancel</button>
                <button class="button button-primary" type="submit">Save Changes</button>
            </div>
        </form>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
