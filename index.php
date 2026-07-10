<?php
// Landing Page Setup
$page_title = 'Shenanovents | Discover Events';
$current_page = 'home';

// Shared Dependencies
require_once 'includes/participant-data.php';
require_once 'includes/countries.php';

// Page Data Retrieval
$events = participant_fetch_landing_events($conn, 8);

$destinations = [
    ['name' => 'Manila', 'label' => 'Capital events and conferences'],
    ['name' => 'Quezon City', 'label' => 'Community expos and creative meetups'],
    ['name' => 'Cebu', 'label' => 'Workshops, expos, and meetups'],
    ['name' => 'Davao', 'label' => 'Business and culture events'],
    ['name' => 'Taguig', 'label' => 'Tech talks and startup sessions'],
    ['name' => 'Pasig', 'label' => 'Campus and professional gatherings'],
    ['name' => 'Makati', 'label' => 'Corporate events and conferences'],
    ['name' => 'Baguio', 'label' => 'Arts, education, and community events'],
    ['name' => 'Iloilo', 'label' => 'Festivals and local showcases'],
    ['name' => 'Bacolod', 'label' => 'Cultural events and food festivals'],
    ['name' => 'Cagayan de Oro', 'label' => 'Outdoor and community events'],
    ['name' => 'Angeles', 'label' => 'Entertainment and city experiences'],
    ['name' => 'Vigan', 'label' => 'Heritage and cultural gatherings'],
    ['name' => 'Bohol', 'label' => 'Travel, culture, and local events'],
    ['name' => 'Puerto Princesa', 'label' => 'Nature and community programs'],
    ['name' => 'Zamboanga', 'label' => 'Regional festivals and gatherings'],
    ['name' => 'General Santos', 'label' => 'Trade, sports, and community events'],
    ['name' => 'Dumaguete', 'label' => 'Campus and creative events'],
    ['name' => 'Tacloban', 'label' => 'Regional meetups and showcases'],
    ['name' => 'Batangas', 'label' => 'Local festivals and business events'],
];

$popular_cities = ['Manila', 'Quezon City', 'Pasig', 'Taguig'];

// Page Header
require_once 'includes/header.php';
?>

<!-- Main Section -->
<section class="page-section hero-section" id="about" aria-labelledby="heroTitle">
    <div class="hero-content">
        <p class="eyebrow">Shenanovents</p>
        <h1 id="heroTitle">Discover, register & manage events with ease.</h1>
        <p>
            Developed to address the challenges of traditional event registration,
            Shenanovents provides a centralized system for managing events,
            participants, and event discovery efficiently.
        </p>
        <div class="hero-actions">
            <a class="button button-primary" href="auth/signup.php">Create Account</a>
            <a class="button button-outline" href="#events" data-landing-browse-events>Browse Events</a>
        </div>
    </div>

    <div class="hero-placeholder hero-image-wrap" aria-label="Shenanovents landing page image">
        <img src="assets/images/logos/lpage.jpg" alt="Shenanovents featured event">
    </div>
</section>

<section class="page-section events-section" id="events" aria-labelledby="eventsTitle">
    <div class="section-heading">
        <div>
            <h2 id="eventsTitle">Explore Events Near You</h2>
        </div>
    </div>

    <div class="browse-controls">
        <div class="location-select" data-location-select>
            <button class="location-select-toggle" type="button" aria-expanded="false" aria-controls="countrySelector">
                <span class="icon icon-location" aria-hidden="true"></span>
                <span id="currentLocationLabel">Online Events</span>
                <span class="select-caret" aria-hidden="true"></span>
            </button>

            <div class="location-menu" id="countrySelector">
                <button class="active" type="button" data-location-mode="Online Events">Online Events</button>
                <button type="button" data-location-mode="Near Your Place">Near Your Place</button>
                <button type="button" data-location-mode="Select a Country" data-open-country-list>Select a Country</button>

                <div class="country-panel" aria-label="Country selector">
                    <p class="country-panel-title">Browse countries</p>

                    <div class="country-list">
                        <?php foreach ($countries as $country): ?>
                            <button type="button" data-country-option="<?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="filter-tabs pill-menu" aria-label="Event filters">
            <button class="active" type="button" data-filter="all">All</button>
            <button type="button" data-filter="today">Today</button>
            <button type="button" data-filter="weekend">This Weekend</button>
        </div>
    </div>

    <?php if (!empty($events)): ?>
        <div class="event-grid">
            <?php foreach ($events as $event): ?>
                <?php participant_render_guest_event_card($event); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <?php participant_render_empty_state('No events available yet', 'Published events will appear here once organizers create them.'); ?>
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
                    <a class="destination-card destination-<?php echo $index + 1; ?>" href="#" style="background-image: <?php echo htmlspecialchars(participant_destination_card_background($destination['name']), ENT_QUOTES, 'UTF-8'); ?>;" data-auth-required-city="<?php echo htmlspecialchars($destination['name'], ENT_QUOTES, 'UTF-8'); ?>">
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
                <a href="#" data-auth-required-city="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>">
                    Things to do in <?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>
                    <span class="icon icon-arrow" aria-hidden="true"></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
