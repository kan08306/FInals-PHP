<?php
// Participant Page Setup
$page_title = 'Shenanovents | Liked Events';
$current_page = 'events';
$base_path = '../';
$asset_version = 'participant-module-update';

require_once __DIR__ . '/../includes/participant-check.php';
require_once __DIR__ . '/../includes/participant-data.php';

participant_handle_registration_post($conn);

$participant_id = participant_current_user_id();
$location_filter = $_GET['location'] ?? '';
$time_filter = $_GET['time'] ?? '';
$country_filter = trim($_GET['country'] ?? '');
$event_filters = participant_normalize_event_filters([
    'location' => $location_filter,
    'time' => $time_filter,
    'country' => $country_filter,
]);
$all_events = participant_fetch_liked_events($conn, $participant_id);
$all_events = participant_filter_event_items($all_events, $event_filters);
$pagination = participant_paginate_items($all_events, participant_current_page(), 8);
$events = $pagination['items'];
$success_message = participant_get_flash('success');
$error_message = participant_get_flash('error');
$pagination_params = [
    'location' => $event_filters['location'],
    'time' => $event_filters['time'],
    'country' => $event_filters['country'],
];
$location_label = 'Liked Events';

if ($event_filters['location'] === 'online') {
    $location_label = 'Online Events';
} elseif ($event_filters['location'] === 'physical') {
    $location_label = 'Near Your Place';
} elseif ($event_filters['country'] !== '') {
    $location_label = $event_filters['country'];
}

$destinations = [
    ['name' => 'Manila', 'label' => 'Capital events and conferences'],
    ['name' => 'Quezon City', 'label' => 'Community expos and creative meetups'],
    ['name' => 'Cebu', 'label' => 'Workshops, expos, and meetups'],
    ['name' => 'Davao', 'label' => 'Business and culture events'],
    ['name' => 'Taguig', 'label' => 'Tech talks and startup sessions'],
    ['name' => 'Pasig', 'label' => 'Campus and professional gatherings'],
    ['name' => 'Makati', 'label' => 'Corporate events and conferences'],
    ['name' => 'Baguio', 'label' => 'Arts, education, and community events'],
];

$popular_cities = ['Manila', 'Quezon City', 'Pasig', 'Taguig'];

require_once __DIR__ . '/../includes/countries.php';
// Shared Layout Rendering
require_once __DIR__ . '/../includes/header.php';
?>

<section class="page-section listings-section" aria-labelledby="listingsTitle">
    <div class="explore-events-header">
        <h1 id="listingsTitle">Liked Events</h1>

        <div class="explore-controls">
            <div class="location-select" data-location-select>
                <button class="location-select-toggle" type="button" aria-expanded="false" aria-controls="countrySelector">
                    <span class="icon icon-location" aria-hidden="true"></span>
                    <span id="currentLocationLabel"><?php echo htmlspecialchars($location_label, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="select-caret" aria-hidden="true"></span>
                </button>

                <div class="location-menu" id="countrySelector">
                    <a class="<?php echo $event_filters['location'] === '' && $event_filters['country'] === '' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('likes.php', ['time' => $event_filters['time']]), ENT_QUOTES, 'UTF-8'); ?>" data-location-mode="Liked Events" data-location-filter="all">Liked Events</a>
                    <a class="<?php echo $event_filters['location'] === 'online' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('likes.php', ['location' => 'online', 'time' => $event_filters['time']]), ENT_QUOTES, 'UTF-8'); ?>" data-location-mode="Online Events" data-location-filter="online">Online Events</a>
                    <a class="<?php echo $event_filters['location'] === 'physical' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('likes.php', ['location' => 'physical', 'time' => $event_filters['time']]), ENT_QUOTES, 'UTF-8'); ?>" data-location-mode="Near Your Place" data-location-filter="physical">Near Your Place</a>
                    <button class="<?php echo $event_filters['country'] !== '' ? 'active' : ''; ?>" type="button" data-location-mode="Select a Country" data-location-filter="all" data-open-country-list>Select a Country</button>

                    <div class="country-panel" aria-label="Country selector">
                        <p class="country-panel-title">Browse countries</p>

                            <div class="country-list">
                                <?php foreach ($countries as $country): ?>
                                    <a class="<?php echo strcasecmp($event_filters['country'], $country) === 0 ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('likes.php', ['country' => $country, 'time' => $event_filters['time']]), ENT_QUOTES, 'UTF-8'); ?>" data-country-option="<?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                    </div>
                </div>
            </div>

            <div class="filter-tabs pill-menu" aria-label="Event filters">
                <a class="<?php echo $event_filters['time'] === '' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('likes.php', ['location' => $event_filters['location'], 'country' => $event_filters['country']]), ENT_QUOTES, 'UTF-8'); ?>" data-filter="all">All</a>
                <a class="<?php echo $event_filters['time'] === 'today' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('likes.php', ['location' => $event_filters['location'], 'country' => $event_filters['country'], 'time' => 'today']), ENT_QUOTES, 'UTF-8'); ?>" data-filter="today">Today</a>
                <a class="<?php echo $event_filters['time'] === 'weekend' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('likes.php', ['location' => $event_filters['location'], 'country' => $event_filters['country'], 'time' => 'weekend']), ENT_QUOTES, 'UTF-8'); ?>" data-filter="weekend">This Weekend</a>
            </div>
        </div>
    </div>

    <?php participant_render_feedback($success_message, $error_message); ?>

    <?php if (!empty($events)): ?>
        <div class="event-grid listings-grid">
            <?php foreach ($events as $event): ?>
                <?php participant_render_event_card($event, 'browse'); ?>
            <?php endforeach; ?>
        </div>

        <div hidden data-filter-empty>
            <?php participant_render_empty_state('No matching liked events found', 'Try another event option or filter to see your saved events.'); ?>
        </div>

        <?php participant_render_pagination('likes.php', $pagination_params, $pagination['current_page'], $pagination['total_pages']); ?>
    <?php else: ?>
        <?php participant_render_empty_state('No liked events yet', 'Click the heart button on an event to save it here.', 'events.php', 'Browse Events'); ?>
    <?php endif; ?>
</section>

<section class="page-section popular-section" aria-labelledby="popularTitle">
    <div class="section-heading">
        <div>
            <h2 id="popularTitle">Explore events by city</h2>
        </div>
    </div>

    <div class="destinations-intro">
        <h3>Top destinations in the Philippines</h3>
        <p>Find events in active city locations and discover places where students, organizers, and communities meet.</p>
    </div>

    <div class="destination-carousel" data-destination-carousel>
        <button class="carousel-button carousel-prev" type="button" aria-label="Previous destinations" data-carousel-prev>
            <span aria-hidden="true">&lt;</span>
        </button>

        <div class="destination-window">
            <div class="destination-track" data-carousel-track>
                <?php foreach ($destinations as $index => $destination): ?>
                    <a class="destination-card destination-<?php echo $index + 1; ?>" href="city-events.php?city=<?php echo urlencode($destination['name']); ?>" style="background-image: <?php echo htmlspecialchars(participant_destination_card_background($destination['name'], '../'), ENT_QUOTES, 'UTF-8'); ?>;" data-location-choice="<?php echo htmlspecialchars($destination['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <span><?php echo htmlspecialchars($destination['name'], ENT_QUOTES, 'UTF-8'); ?> events</span>
                        <strong><?php echo htmlspecialchars($destination['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <small><?php echo htmlspecialchars($destination['label'], ENT_QUOTES, 'UTF-8'); ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <button class="carousel-button carousel-next" type="button" aria-label="Next destinations" data-carousel-next>
            <span aria-hidden="true">&gt;</span>
        </button>
    </div>

    <div class="popular-cities-block">
        <div class="city-tags">
            <?php foreach ($popular_cities as $city): ?>
                <a href="city-events.php?city=<?php echo urlencode($city); ?>" data-location-choice="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>">
                    Things to do in <?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>
                    <span class="icon icon-arrow" aria-hidden="true"></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


