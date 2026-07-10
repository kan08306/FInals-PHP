<?php
// Administrator Page Setup
$page_title = 'Shenanovents | Edit Admin Profile';
$current_page = 'admin';
$base_path = '../';
$asset_version = 'admin-interface-standard';

require_once __DIR__ . '/../includes/admin-check.php';
require_once __DIR__ . '/../includes/participant-data.php';

$admin_id = (int) ($_SESSION['user_id'] ?? 0);
$user = participant_fetch_profile($conn, $admin_id, 'admin');

if (!$user) {
    $_SESSION['auth_error'] = 'Admin account was not found.';
// Redirect Handling
    header('Location: ../auth/signin.php');
    exit;
}

$errors = [];
$password_errors = [];
$success_message = '';
$password_success_message = '';
$first_name = $user['first_name'];
$last_name = $user['last_name'];
$email = $user['email'];

// Form Submission Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['form_action'] ?? 'update_profile';

    if ($form_action === 'update_profile') {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $result = participant_update_profile($conn, $admin_id, $first_name, $last_name, $email, $_FILES['profile_picture'] ?? null, 'admin');

        if ($result['success']) {
            participant_flash('success', 'Admin profile updated successfully.');
            header('Location: profile.php');
            exit;
        }

        $errors = $result['errors'];
    }

    if ($form_action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $result = participant_change_password($conn, $admin_id, $current_password, $new_password, $confirm_password, 'admin');

        if ($result['success']) {
            $password_success_message = 'Password updated successfully.';
        } else {
            $password_errors = $result['errors'];
        }
    }
}

// Shared Layout Rendering
require_once __DIR__ . '/../includes/header.php';
?>

<section class="profile-page" aria-labelledby="editProfileTitle">
    <div class="profile-shell">
        <div class="profile-header-card">
            <div class="profile-avatar" aria-hidden="true">
                <?php participant_render_profile_avatar($user['profile_picture'] ?? '', $base_path); ?>
            </div>

            <div class="profile-identity">
                <span class="event-badge">Admin Account</span>
                <h1 id="editProfileTitle">Edit Profile</h1>
                <div class="profile-user-details">
                    <span>Update administrator account information</span>
                    <span><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>

            <a class="button button-outline" href="profile.php">Back to Profile</a>
        </div>

        <section class="profile-content-section" aria-labelledby="profileFormTitle">
            <div class="profile-section-heading">
                <div>
                    <h2 id="profileFormTitle">Account Details</h2>
                    <p>Update your basic account details and profile picture.</p>
                </div>
            </div>

            <?php participant_render_feedback($success_message, '', $errors); ?>

            <form class="participant-form-card" action="edit-profile.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="form_action" value="update_profile">

                <div class="participant-picture-row">
                    <div class="profile-avatar participant-picture-preview" aria-hidden="true">
                        <?php participant_render_profile_avatar($user['profile_picture'] ?? '', $base_path); ?>
                    </div>
                    <label class="participant-upload-field">
                        <span>Profile Picture</span>
                        <input type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.gif,.webp">
                        <small>Upload JPG, PNG, GIF, or WEBP up to 2MB. Leave blank to keep the current picture.</small>
                    </label>
                </div>

                <div class="participant-form-grid">
                    <label>
                        <span>First Name</span>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>

                    <label>
                        <span>Last Name</span>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>

                    <label class="participant-field-full">
                        <span>Email Address</span>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                </div>

                <div class="participant-form-actions">
                    <button class="button button-primary" type="submit">Save Changes</button>
                    <a class="button button-outline" href="profile.php">Cancel</a>
                </div>
            </form>
        </section>

        <section class="profile-content-section" aria-labelledby="editPasswordTitle">
            <div class="profile-section-heading">
                <div>
                    <h2 id="editPasswordTitle">Change Password</h2>
                    <p>For account safety, enter your current password before saving a new one.</p>
                </div>
            </div>

            <?php participant_render_feedback($password_success_message, '', $password_errors); ?>

            <form class="participant-form-card" action="edit-profile.php" method="post">
                <input type="hidden" name="form_action" value="change_password">

                <div class="participant-form-grid">
                    <label class="participant-field-full">
                        <span>Current Password</span>
                        <input type="password" name="current_password" required>
                    </label>

                    <label>
                        <span>New Password</span>
                        <input type="password" name="new_password" minlength="8" required>
                    </label>

                    <label>
                        <span>Confirm New Password</span>
                        <input type="password" name="confirm_password" minlength="8" required>
                    </label>
                </div>

                <div class="participant-form-actions">
                    <button class="button button-primary" type="submit">Save Password</button>
                </div>
            </form>
        </section>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




