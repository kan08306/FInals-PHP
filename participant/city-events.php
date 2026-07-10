<?php
// City Events Page Setup
$current_page = 'events';
$base_path = '../';
$asset_version = 'participant-module-update';

// Shared Dependencies
require_once __DIR__ . '/../includes/participant-check.php';
require_once __DIR__ . '/../includes/participant-data.php';

// Registration Management
participant_handle_registration_post($conn);

$requested_city = trim($_GET['city'] ?? 'Manila');
$selected_city = preg_replace('/[^a-zA-Z ]/', '', $requested_city);
$selected_city = $selected_city !== '' ? ucwords(strtolower($selected_city)) : 'Manila';
$page_title = 'Shenanovents | Explore Events in ' . $selected_city;

$participant_id = participant_current_user_id();
$location_filter = $_GET['location'] ?? '';
$time_filter = $_GET['time'] ?? '';
$event_filters = participant_normalize_event_filters([
    'city' => $selected_city,
    'location' => $location_filter,
    'time' => $time_filter,
]);
$all_events = participant_fetch_events($conn, $participant_id, $event_filters);
$pagination = participant_paginate_items($all_events, participant_current_page(), 8);
// Page Data Retrieval
$events = $pagination['items'];
$success_message = participant_get_flash('success');
$error_message = participant_get_flash('error');
$popular_cities = ['Manila', 'Quezon City', 'Cebu', 'Davao', 'Baguio'];
$pagination_params = [
    'city' => $selected_city,
    'location' => $event_filters['location'],
    'time' => $event_filters['time'],
];

if (!in_array($selected_city, $popular_cities, true)) {
    $popular_cities[] = $selected_city;
}

require_once __DIR__ . '/../includes/countries.php';
// Page Header
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Main Section -->
<section class="page-section listings-section city-events-section" aria-labelledby="listingsTitle">
    <div class="explore-events-header">
        <h1 id="listingsTitle">Explore Events in <?php echo htmlspecialchars($selected_city, ENT_QUOTES, 'UTF-8'); ?></h1>

        <div class="explore-controls">
            <div class="location-select" data-location-select>
                <button class="location-select-toggle" type="button" aria-expanded="false" aria-controls="countrySelector">
                    <span class="icon icon-location" aria-hidden="true"></span>
                    <span id="currentLocationLabel"><?php echo htmlspecialchars($selected_city, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="select-caret" aria-hidden="true"></span>
                </button>

                <div class="location-menu" id="countrySelector">
                    <a href="events.php?location=online" data-location-mode="Online Events" data-location-filter="online">Online Events</a>
                    <a class="<?php echo $event_filters['location'] === 'physical' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('city-events.php', ['city' => $selected_city, 'location' => 'physical', 'time' => $event_filters['time']]), ENT_QUOTES, 'UTF-8'); ?>" data-location-mode="Near Your Place" data-location-filter="physical">Near Your Place</a>
                    <button type="button" data-location-mode="Select a Country" data-location-filter="all" data-open-country-list>Select a Country</button>

                    <div class="country-panel" aria-label="Country selector">
                        <p class="country-panel-title">Browse countries</p>

                            <div class="country-list">
                                <?php foreach ($countries as $country): ?>
                                    <a href="<?php echo htmlspecialchars(participant_build_url('events.php', ['country' => $country]), ENT_QUOTES, 'UTF-8'); ?>" data-country-option="<?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                    </div>
                </div>
            </div>

            <div class="filter-tabs pill-menu" aria-label="Event filters">
                <a class="<?php echo $event_filters['time'] === '' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('city-events.php', ['city' => $selected_city, 'location' => $event_filters['location']]), ENT_QUOTES, 'UTF-8'); ?>" data-filter="all">All</a>
                <a class="<?php echo $event_filters['time'] === 'today' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('city-events.php', ['city' => $selected_city, 'location' => $event_filters['location'], 'time' => 'today']), ENT_QUOTES, 'UTF-8'); ?>" data-filter="today">Today</a>
                <a class="<?php echo $event_filters['time'] === 'weekend' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('city-events.php', ['city' => $selected_city, 'location' => $event_filters['location'], 'time' => 'weekend']), ENT_QUOTES, 'UTF-8'); ?>" data-filter="weekend">This Weekend</a>
            </div>
        </div>
    </div>

    <?php participant_render_feedback($success_message, $error_message); ?>

    <div class="city-page-switcher">
        <p class="browse-filter-label">Browse another city</p>
        <div class="city-tags">
            <?php foreach ($popular_cities as $city): ?>
                <a class="<?php echo strtolower($city) === strtolower($selected_city) ? 'active' : ''; ?>" href="city-events.php?city=<?php echo urlencode($city); ?>" data-location-choice="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>">
                    Things to do in <?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>
                    <span class="icon icon-arrow" aria-hidden="true"></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($events)): ?>
        <div class="event-grid listings-grid">
            <?php foreach ($events as $event): ?>
                <?php participant_render_event_card($event, 'browse'); ?>
            <?php endforeach; ?>
        </div>

        <div hidden data-filter-empty>
            <?php participant_render_empty_state('No matching events found', 'Try another event option or city filter to see available events.'); ?>
        </div>

        <?php participant_render_pagination('city-events.php', $pagination_params, $pagination['current_page'], $pagination['total_pages']); ?>
    <?php else: ?>
        <?php participant_render_empty_state('No events available in ' . $selected_city . ' yet', 'Try another city or check back once organizers publish more events in this location.', 'events.php', 'Browse All Events'); ?>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
