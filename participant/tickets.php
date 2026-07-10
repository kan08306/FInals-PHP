<?php
// Participant Tickets Page Setup
$page_title = 'Shenanovents | Tickets Registered';
$current_page = 'events';
$base_path = '../';
$asset_version = 'participant-module-update';

// Shared Dependencies
require_once __DIR__ . '/../includes/participant-check.php';
require_once __DIR__ . '/../includes/participant-data.php';

// Registration Management
participant_handle_registration_post($conn);

$participant_id = participant_current_user_id();
$ticket_status_filter = $_GET['status'] ?? 'registered';
$ticket_status_filter = in_array($ticket_status_filter, ['registered', 'cancelled'], true) ? $ticket_status_filter : 'registered';
$time_filter = $_GET['time'] ?? '';
$country_filter = trim($_GET['country'] ?? '');
$all_events = participant_fetch_registered_events($conn, $participant_id, true);
$all_events = array_values(array_filter($all_events, function ($event) use ($ticket_status_filter) {
    return ($event['registration_status'] ?? '') === $ticket_status_filter;
}));
$all_events = participant_filter_event_items($all_events, [
    'time' => $time_filter,
    'country' => $country_filter,
]);
$pagination = participant_paginate_items($all_events, participant_current_page(), 8);
// Page Data Retrieval
$events = $pagination['items'];
$success_message = participant_get_flash('success');
$error_message = participant_get_flash('error');
$pagination_params = [
    'status' => $ticket_status_filter,
    'time' => participant_normalize_event_filters(['time' => $time_filter])['time'],
    'country' => $country_filter,
];
$ticket_label = $ticket_status_filter === 'cancelled' ? 'Cancelled Events' : 'Registered Events';

if ($country_filter !== '') {
    $ticket_label = $country_filter;
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
// Page Header
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Main Section -->
<section class="page-section listings-section" aria-labelledby="listingsTitle">
    <div class="explore-events-header">
        <h1 id="listingsTitle">Tickets Registered</h1>

        <div class="explore-controls">
            <div class="location-select" data-location-select>
                <button class="location-select-toggle" type="button" aria-expanded="false" aria-controls="countrySelector">
                    <span class="icon icon-location" aria-hidden="true"></span>
                    <span id="currentLocationLabel"><?php echo htmlspecialchars($ticket_label, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="select-caret" aria-hidden="true"></span>
                </button>

                <div class="location-menu" id="countrySelector">
                    <a class="<?php echo $ticket_status_filter === 'registered' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('tickets.php', ['status' => 'registered', 'time' => $pagination_params['time'], 'country' => $country_filter]), ENT_QUOTES, 'UTF-8'); ?>" data-location-mode="Registered Events" data-ticket-status-filter="registered">Registered Events</a>
                    <a class="<?php echo $ticket_status_filter === 'cancelled' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('tickets.php', ['status' => 'cancelled', 'time' => $pagination_params['time'], 'country' => $country_filter]), ENT_QUOTES, 'UTF-8'); ?>" data-location-mode="Cancelled Events" data-ticket-status-filter="cancelled">Cancelled Events</a>

                    <div class="country-panel" aria-label="Country selector">
                        <p class="country-panel-title">Browse countries</p>

                            <div class="country-list">
                                <?php foreach ($countries as $country): ?>
                                    <a class="<?php echo strcasecmp($country_filter, $country) === 0 ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('tickets.php', ['status' => $ticket_status_filter, 'time' => $pagination_params['time'], 'country' => $country]), ENT_QUOTES, 'UTF-8'); ?>" data-country-option="<?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                    </div>
                </div>
            </div>

            <div class="filter-tabs pill-menu" aria-label="Event filters">
                <a class="<?php echo $pagination_params['time'] === '' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('tickets.php', ['status' => $ticket_status_filter, 'country' => $country_filter]), ENT_QUOTES, 'UTF-8'); ?>" data-filter="all">All</a>
                <a class="<?php echo $pagination_params['time'] === 'today' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('tickets.php', ['status' => $ticket_status_filter, 'country' => $country_filter, 'time' => 'today']), ENT_QUOTES, 'UTF-8'); ?>" data-filter="today">Today</a>
                <a class="<?php echo $pagination_params['time'] === 'weekend' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(participant_build_url('tickets.php', ['status' => $ticket_status_filter, 'country' => $country_filter, 'time' => 'weekend']), ENT_QUOTES, 'UTF-8'); ?>" data-filter="weekend">This Weekend</a>
            </div>
        </div>
    </div>

    <?php participant_render_feedback($success_message, $error_message); ?>

    <?php if (!empty($events)): ?>
        <div class="event-grid listings-grid">
            <?php foreach ($events as $event): ?>
                <?php participant_render_event_card($event, 'registered'); ?>
            <?php endforeach; ?>
        </div>

        <div hidden data-filter-empty>
            <?php participant_render_empty_state('No matching tickets found', 'Choose another ticket option or browse events to register.', 'events.php', 'Browse Events'); ?>
        </div>

        <?php participant_render_pagination('tickets.php', $pagination_params, $pagination['current_page'], $pagination['total_pages']); ?>
    <?php else: ?>
        <?php participant_render_empty_state('No registered events yet', 'Events you register for will appear here with their registration status.', 'events.php', 'Browse Events'); ?>
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
