<?php
// Event Creation Page Setup
$page_title = 'Shenanovents | Create Event';
$current_page = 'events';
$body_class = 'create-event-body';
$base_path = '../';

// Shared Dependencies
require_once __DIR__ . '/../includes/participant-check.php';
require_once __DIR__ . '/../includes/participant-data.php';
require_once __DIR__ . '/../includes/countries.php';

$participant_id = participant_current_user_id();
$user = participant_fetch_profile($conn, $participant_id);

if (!$user) {
    $_SESSION['auth_error'] = 'Participant account was not found.';
    header('Location: ../auth/signin.php');
    exit;
}

$event_errors = [];
$full_name = trim($user['first_name'] . ' ' . $user['last_name']);
$editing_event_id = (int) ($_POST['event_id'] ?? ($_GET['event'] ?? 0));
$editing_event = $editing_event_id > 0 ? participant_fetch_owned_event_dashboard($conn, $participant_id, $editing_event_id) : null;
$is_review_mode = !empty($editing_event);
$location_can_update = $is_review_mode && participant_event_location_can_be_updated($editing_event);
$initial_step = (int) ($_GET['step'] ?? 1);
$initial_step = min(3, max(1, $initial_step));
$selected_country = $editing_event['event_country'] ?? 'Philippines';
$city_options = participant_country_city_options($selected_country);

if ($is_review_mode && !empty($editing_event['event_city']) && !in_array($editing_event['event_city'], $city_options, true)) {
    array_unshift($city_options, $editing_event['event_city']);
}

$review_event_date = $is_review_mode && !empty($editing_event['event_date'])
    ? date('m/d/Y', strtotime($editing_event['event_date']))
    : '';
$review_location_choice = 'Venue';

if ($is_review_mode && ($editing_event['event_type'] ?? '') === 'online') {
    $review_location_choice = 'Online event';
} elseif ($is_review_mode && ($editing_event['event_type'] ?? '') === 'tba') {
    $review_location_choice = 'To be announced';
}

$locked_attr = $is_review_mode ? ' disabled' : '';
$location_locked_attr = ($is_review_mode && !$location_can_update) ? ' disabled' : '';
$location_required_attr = (!$is_review_mode || $location_can_update) ? ' required' : '';
$details_locked_attr = $is_review_mode ? ' disabled' : '';
$publish_locked_attr = $is_review_mode ? ' disabled' : '';

// Form Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['form_action'] ?? 'create_event';
    $result = ['success' => false, 'errors' => ['Invalid form action.'], 'event_id' => 0];

    if ($form_action === 'update_event_location' && $is_review_mode) {
        $result = participant_update_owned_event_location($conn, $participant_id, $editing_event_id, $_POST);

        if ($result['success']) {
            participant_flash('success', 'Event location updated and locked successfully.');
            header('Location: create-event.php?event=' . $editing_event_id . '&step=1');
            exit;
        }
    } elseif ($form_action === 'create_event') {
        $result = participant_create_event($conn, $participant_id, $_POST, $_FILES);
    }

    if ($result['success']) {
        participant_flash('success', 'Event created successfully.');
        header('Location: ../participant/event-dashboard.php?event=' . (int) $result['event_id']);
        exit;
    }

    $event_errors = $result['errors'];
}

// Page Header
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Main Section -->
<section class="create-event-page" aria-label="Create event workflow">
    <!-- Form -->
    <form class="event-wizard-shell" action="create-event.php<?php echo $is_review_mode ? '?event=' . (int) $editing_event_id . '&step=' . (int) $initial_step : ''; ?>" method="post" enctype="multipart/form-data" data-event-wizard data-initial-step="<?php echo (int) $initial_step; ?>"<?php echo $is_review_mode ? ' data-existing-event="1"' : ''; ?><?php echo ($is_review_mode && !$location_can_update) ? ' data-location-locked="1"' : ''; ?> novalidate>
        <input type="hidden" name="form_action" value="<?php echo $is_review_mode ? 'update_event_location' : 'create_event'; ?>">
        <?php if ($is_review_mode): ?>
            <input type="hidden" name="event_id" value="<?php echo (int) $editing_event_id; ?>">
        <?php endif; ?>
        <script type="application/json" data-country-city-options><?php echo json_encode(participant_country_city_options_map(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>

        <aside class="wizard-sidebar" aria-label="Event publishing progress">
            <div class="wizard-event-preview">
                <strong data-sidebar-title><?php echo htmlspecialchars($editing_event['event_title'] ?? 'Event Title', ENT_QUOTES, 'UTF-8'); ?></strong>
                <span data-sidebar-date><?php echo htmlspecialchars($editing_event['date_text'] ?? 'Event Date', ENT_QUOTES, 'UTF-8'); ?></span>
                <span data-sidebar-time><?php echo htmlspecialchars($editing_event['time_text'] ?? 'Event Time', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <ol class="wizard-steps">
                <li>
                    <button class="wizard-step<?php echo $initial_step === 1 ? ' active' : ''; ?><?php echo $is_review_mode ? ' completed' : ''; ?>" type="button" data-step-nav="1">
                        <span class="wizard-step-marker">1</span>
                        <span class="wizard-step-copy">Basic Info</span>
                    </button>
                </li>
                <li>
                    <button class="wizard-step<?php echo $initial_step === 2 ? ' active' : ''; ?><?php echo $is_review_mode ? ' completed' : ''; ?>" type="button" data-step-nav="2">
                        <span class="wizard-step-marker">2</span>
                        <span class="wizard-step-copy">Details</span>
                    </button>
                </li>
                <li>
                    <button class="wizard-step<?php echo $initial_step === 3 ? ' active' : ''; ?><?php echo $is_review_mode ? ' completed' : ''; ?>" type="button" data-step-nav="3">
                        <span class="wizard-step-marker">3</span>
                        <span class="wizard-step-copy">Publish</span>
                    </button>
                </li>
            </ol>

            <a class="wizard-dashboard-link" href="<?php echo $is_review_mode ? '../participant/event-dashboard.php?event=' . (int) $editing_event_id : '../participant/dashboard.php'; ?>">
                <span class="wizard-dashboard-icon" aria-hidden="true"></span>
                Dashboard
            </a>

            <div class="wizard-user">
                <span class="wizard-user-icon" aria-hidden="true">
                    <span class="icon icon-user"></span>
                </span>
                <strong><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="wizard-panel" aria-live="polite">
            <?php participant_render_feedback('', '', $event_errors); ?>

            <section class="wizard-step-panel basic-info-panel" data-step-panel="1" aria-labelledby="basicInfoTitle"<?php echo $initial_step === 1 ? '' : ' hidden'; ?>>
                <div class="wizard-panel-header">
                    <h1 id="basicInfoTitle">Create an event with Shenanovents</h1>
                    <p>Answer a few questions about your event and Shenanovents will verify the event.</p>
                </div>

                <div class="wizard-form-stack">
                    <div class="wizard-question">
                        <label class="wizard-field wizard-field-full">
                            <span>Whats the name of your event?</span>
                            <small>This will be your event's title. Your title will be used to help create your event's summary, description, category, and tags - so be specific!</small>
                            <input type="text" name="event_name" placeholder="Event Title" value="<?php echo htmlspecialchars($editing_event['event_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required data-event-name<?php echo $locked_attr; ?>>
                        </label>
                    </div>

                    <div class="wizard-question">
                        <p class="wizard-question-title">When does your event start and end?</p>

                        <div class="wizard-date-time-grid">
                            <label class="sr-only" for="eventDate">Event Date</label>
                            <input id="eventDate" type="text" name="event_date" placeholder="MM/DD/YYYY" inputmode="numeric" maxlength="10" value="<?php echo htmlspecialchars($review_event_date, ENT_QUOTES, 'UTF-8'); ?>" required data-event-date<?php echo $locked_attr; ?>>

                            <label class="sr-only" for="eventStartTime">Start Time</label>
                            <input id="eventStartTime" type="time" name="event_start_time" value="<?php echo htmlspecialchars(substr((string) ($editing_event['event_time'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8'); ?>" required data-event-start-time<?php echo $locked_attr; ?>>

                            <label class="sr-only" for="eventEndTime">End Time</label>
                            <input id="eventEndTime" type="time" name="event_end_time" value="<?php echo htmlspecialchars(substr((string) ($editing_event['event_end_time'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8'); ?>" required data-event-end-time<?php echo $locked_attr; ?>>
                        </div>

                        <p class="wizard-date-preview" data-event-date-preview>Enter MM/DD/YYYY to preview the full event date.</p>
                    </div>

                    <div class="wizard-question">
                        <label class="wizard-field wizard-field-full">
                            <span>Tags</span>
                            <small>Improve discoverability of your event by adding tags relevant to the subject matter.</small>
                            <input type="text" name="event_tags" placeholder="Tags" value="<?php echo htmlspecialchars($editing_event['event_tags'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required data-event-tags<?php echo $locked_attr; ?>>
                        </label>
                    </div>

                    <div class="wizard-question">
                        <p class="wizard-question-title">Where is it located?</p>

                        <div class="location-choice-tabs pill-menu" aria-label="Event location type">
                            <button class="<?php echo $review_location_choice === 'Venue' ? 'active' : ''; ?>" type="button" data-location-type-option="Venue"<?php echo $location_locked_attr; ?>>Venue</button>
                            <button class="<?php echo $review_location_choice === 'Online event' ? 'active' : ''; ?>" type="button" data-location-type-option="Online event"<?php echo $location_locked_attr; ?>>Online event</button>
                            <button class="<?php echo $review_location_choice === 'To be announced' ? 'active' : ''; ?>" type="button" data-location-type-option="To be announced"<?php echo $location_locked_attr; ?>>To be announced</button>
                        </div>
                        <input type="hidden" name="event_location" value="<?php echo htmlspecialchars($review_location_choice, ENT_QUOTES, 'UTF-8'); ?>" data-event-location>

                        <div class="wizard-location-fields" data-venue-location-panel<?php echo $review_location_choice === 'Venue' ? '' : ' hidden'; ?>>
                            <label class="sr-only" for="eventVenue">Venue Name</label>
                            <input id="eventVenue" type="text" name="event_venue" placeholder="Venue Name" value="<?php echo htmlspecialchars($editing_event['event_venue'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"<?php echo $location_required_attr; ?> data-event-venue<?php echo $location_locked_attr; ?>>

                            <label class="sr-only" for="eventCountry">Country</label>
                            <select id="eventCountry" name="event_country"<?php echo $location_required_attr; ?> data-event-country<?php echo $location_locked_attr; ?>>
                                <option value="">Country</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $country === $selected_country ? ' selected' : ''; ?>>
                                        <?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label class="sr-only" for="eventCity">City</label>
                            <select id="eventCity" name="event_city"<?php echo $location_required_attr; ?> data-event-city data-selected-city="<?php echo htmlspecialchars($editing_event['event_city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"<?php echo $location_locked_attr; ?>>
                                <option value="">City</option>
                                <?php foreach ($city_options as $city): ?>
                                    <option value="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>"<?php echo ($editing_event['event_city'] ?? '') === $city ? ' selected' : ''; ?>>
                                        <?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label class="sr-only" for="eventAddress">Address</label>
                            <input id="eventAddress" type="text" name="event_address" placeholder="Address" value="<?php echo htmlspecialchars($editing_event['event_address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"<?php echo $location_required_attr; ?> data-event-address<?php echo $location_locked_attr; ?>>
                        </div>

                        <div class="wizard-location-fields online-location-fields" data-online-location-panel<?php echo $review_location_choice === 'Online event' ? '' : ' hidden'; ?>>
                            <label class="sr-only" for="eventMeetingLink">Meeting Link or Event URL</label>
                            <input id="eventMeetingLink" type="url" name="event_meeting_link" placeholder="Meeting Link or Event URL" value="<?php echo htmlspecialchars($editing_event['online_link'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-event-online-link<?php echo $location_locked_attr; ?>>

                            <label class="sr-only" for="eventPlatform">Platform</label>
                            <select id="eventPlatform" name="event_platform" data-event-platform<?php echo $location_locked_attr; ?>>
                                <option value="">Platform</option>
                                <?php foreach (['Zoom', 'Google Meet', 'Microsoft Teams', 'Discord', 'Facebook Live', 'YouTube Live', 'Other'] as $platform): ?>
                                    <option value="<?php echo htmlspecialchars($platform, ENT_QUOTES, 'UTF-8'); ?>"<?php echo ($editing_event['online_platform'] ?? '') === $platform ? ' selected' : ''; ?>>
                                        <?php echo htmlspecialchars($platform, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label class="sr-only" for="eventPlatformOther">Platform Name</label>
                            <input id="eventPlatformOther" type="text" name="event_platform_other" placeholder="Platform Name" data-event-platform-other hidden<?php echo $location_locked_attr; ?>>
                        </div>

                        <div class="location-message-card" data-tba-location-panel<?php echo $review_location_choice === 'To be announced' ? '' : ' hidden'; ?>>
                            <strong>Location to be announced</strong>
                            <span>This event location will be announced at a later time.</span>
                        </div>

                        <div class="map-preview" aria-label="Map preview" data-map-preview>
                            <span class="map-block map-green map-block-1"></span>
                            <span class="map-block map-green map-block-2"></span>
                            <span class="map-block map-green map-block-3"></span>
                            <span class="map-block map-blue map-block-4"></span>
                            <span class="map-block map-blue map-block-5"></span>
                            <span class="map-road map-road-1"></span>
                            <span class="map-road map-road-2"></span>
                            <span class="map-road map-road-3"></span>
                            <span class="map-road map-road-4"></span>
                            <div class="map-preview-card">
                                <strong data-map-preview-title>Venue preview</strong>
                                <span data-map-preview-location>Select a venue, city, and country</span>
                                <a class="map-preview-link" href="https://www.google.com/maps/search/?api=1&query=Philippines" target="_blank" rel="noopener" data-map-preview-link>View on Map</a>
                            </div>
                        </div>
                    </div>

                    <div class="wizard-question capacity-question">
                        <label class="wizard-field">
                            <span>What's the capacity for your event?</span>
                            <small>Event capacity is the total number of free registrations you're willing to accept.</small>
                            <input type="number" name="event_capacity" min="1" placeholder="Capacity" value="<?php echo htmlspecialchars((string) ($editing_event['capacity'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required data-event-capacity<?php echo $locked_attr; ?>>
                        </label>
                    </div>
                </div>

                <p class="wizard-error" data-step-error="1" role="alert"></p>

                <div class="wizard-actions">
                    <?php if ($is_review_mode && $location_can_update): ?>
                        <button class="button button-primary" type="submit">Save Venue Details</button>
                        <a class="button button-outline" href="../participant/event-dashboard.php?event=<?php echo (int) $editing_event_id; ?>">Dashboard</a>
                    <?php elseif ($is_review_mode): ?>
                        <a class="button button-primary" href="../participant/event-dashboard.php?event=<?php echo (int) $editing_event_id; ?>">Back to Dashboard</a>
                    <?php else: ?>
                        <button class="button button-primary" type="button" data-next-step="2">Save &amp; Continue</button>
                        <a class="button button-outline" href="../participant/events.php">Exit</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="wizard-step-panel details-panel" data-step-panel="2" aria-labelledby="detailsTitle"<?php echo $initial_step === 2 ? '' : ' hidden'; ?>>
                <div class="wizard-form-stack">
                    <div class="wizard-question">
                        <div class="wizard-section-heading">
                            <h1 id="detailsTitle">Main Event Image</h1>
                            <p>This is the first image attendees will see at the top of your listing. Use high-quality image.</p>
                        </div>

                        <label class="upload-card" data-banner-dropzone>
                            <input class="sr-only" type="file" name="event_banner" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-banner-input<?php echo $is_review_mode ? '' : ' required'; ?><?php echo $details_locked_attr; ?>>
                            <img alt="Event banner preview" data-banner-preview<?php echo empty($editing_event['banner_image']) ? ' hidden' : ''; ?><?php echo !empty($editing_event['banner_image']) ? ' src="' . htmlspecialchars('../' . ltrim($editing_event['banner_image'], '/'), ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
                            <span class="upload-placeholder"<?php echo !empty($editing_event['banner_image']) ? ' hidden' : ''; ?>>
                                <span class="upload-icon" aria-hidden="true"></span>
                                <strong>Upload your image here</strong>
                                <small>PNG, JPG, WEBP up to 10MB</small>
                            </span>
                        </label>
                    </div>

                    <div class="wizard-question">
                        <div class="wizard-section-heading">
                            <h2>Description</h2>
                            <p>Add more details to your event like your sponsors or featured guests.</p>
                        </div>

                        <label class="sr-only" for="eventSummary">Summary</label>
                        <input id="eventSummary" class="description-summary" type="text" name="event_summary" placeholder="Summary" value="<?php echo htmlspecialchars($editing_event['event_summary'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required data-event-summary<?php echo $details_locked_attr; ?>>

                        <label class="sr-only" for="eventDescription">Add additional details about your event</label>
                        <textarea id="eventDescription" name="event_description" rows="6" placeholder="Add additional details about your event" required data-event-description<?php echo $details_locked_attr; ?>><?php echo htmlspecialchars($editing_event['event_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                </div>

                <p class="wizard-error" data-step-error="2" role="alert"></p>

                <div class="wizard-actions">
                    <?php if ($is_review_mode): ?>
                        <a class="button button-primary" href="../participant/event-dashboard.php?event=<?php echo (int) $editing_event_id; ?>">Back to Dashboard</a>
                    <?php else: ?>
                        <button class="button button-primary" type="button" data-next-step="3">Save &amp; Continue</button>
                        <a class="button button-outline" href="../participant/events.php">Exit</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="wizard-step-panel publish-panel" data-step-panel="3" aria-labelledby="publishTitle"<?php echo $initial_step === 3 ? '' : ' hidden'; ?>>
                <div class="wizard-panel-header publish-heading">
                    <h1 id="publishTitle">Publish Your Event</h1>
                </div>

                <div class="publish-summary">
                    <div class="summary-banner" data-summary-banner>
                        <img alt="Selected event banner" data-summary-banner-image hidden>
                        <span data-summary-banner-placeholder>Image of the event</span>
                    </div>

                    <div class="summary-details" aria-label="Event summary">
                        <strong data-summary-title>Event Title</strong>
                        <span data-summary-date>Date and time of the event</span>
                        <span data-summary-location>Address of the event</span>
                        <span data-summary-price>Free Registration</span>
                        <span data-summary-description>Event Title - Summary of the Event</span>
                    </div>
                </div>

                <div class="publish-question">
                    <p class="wizard-question-title">Who can see your event?</p>

                    <label class="switch-row">
                        <input type="checkbox" name="event_visibility" value="private"<?php echo ($editing_event['visibility'] ?? 'private') === 'private' ? ' checked' : ''; ?> data-visibility-option<?php echo $publish_locked_attr; ?>>
                        <span class="switch-control" aria-hidden="true"></span>
                        <span>
                            <strong data-visibility-label>Private Event</strong>
                            <small data-visibility-help>Only available to a selected audience</small>
                        </span>
                    </label>
                    <input type="hidden" name="event_visibility_status" value="<?php echo htmlspecialchars($editing_event['visibility'] ?? 'private', ENT_QUOTES, 'UTF-8'); ?>" data-visibility-value>
                </div>

                <label class="wizard-field publish-select">
                    <span>Choose your audience</span>
                    <span data-private-audience-wrap>
                        <select name="private_audience" required data-private-audience-select<?php echo $publish_locked_attr; ?>>
                            <option value="">Audience</option>
                            <?php foreach (['Invite-only guests', 'Registered students', 'Organization members'] as $audience): ?>
                                <option value="<?php echo htmlspecialchars($audience, ENT_QUOTES, 'UTF-8'); ?>"<?php echo ($editing_event['audience'] ?? '') === $audience ? ' selected' : ''; ?>>
                                    <?php echo htmlspecialchars($audience, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </span>
                    <input type="text" value="Everyone" disabled hidden data-public-audience-display>
                </label>

                <div class="publish-question">
                    <p class="wizard-question-title">When should we publish your event?</p>

                    <label class="switch-row">
                        <input type="checkbox" name="publish_schedule" value="later"<?php echo !empty($editing_event['publish_date']) || !$is_review_mode ? ' checked' : ''; ?> data-schedule-option<?php echo $publish_locked_attr; ?>>
                        <span class="switch-control" aria-hidden="true"></span>
                        <span>
                            <strong>Schedule for later</strong>
                        </span>
                    </label>

                    <div class="schedule-fields" data-schedule-wrap>
                        <label class="sr-only" for="publishDate">Date</label>
                        <input id="publishDate" type="date" name="publish_date" value="<?php echo htmlspecialchars($editing_event['publish_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-publish-date<?php echo $publish_locked_attr; ?>>

                        <label class="sr-only" for="publishTime">Time</label>
                        <input id="publishTime" type="time" name="publish_time" value="<?php echo htmlspecialchars(substr((string) ($editing_event['publish_time'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8'); ?>" data-publish-time<?php echo $publish_locked_attr; ?>>
                    </div>
                </div>

                <p class="wizard-message" data-publish-message role="status"></p>

                <div class="wizard-actions">
                    <?php if ($is_review_mode): ?>
                        <a class="button button-primary" href="../participant/event-dashboard.php?event=<?php echo (int) $editing_event_id; ?>">Back to Dashboard</a>
                    <?php else: ?>
                        <button class="button button-primary" type="submit" data-create-event>Create</button>
                        <a class="button button-outline" href="../participant/events.php">Exit</a>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
