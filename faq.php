<?php
$page_title = 'Shenanovents | FAQ';
$current_page = 'home';
$info_kicker = 'Resources';
$info_title = 'Frequently Asked Questions';
$info_intro = 'Find quick answers about browsing events, creating an account, registering for events, and managing listings.';
$info_cards = [
    ['title' => 'For attendees', 'body' => 'Learn how to browse events, register for free listings, like events, and view tickets after signing in.'],
    ['title' => 'For organizers', 'body' => 'Understand how event drafts, publishing options, private events, and registration limits work.'],
    ['title' => 'For administrators', 'body' => 'Review how event approvals, user management, and platform monitoring fit into the Shenanovents workflow.'],
];
$info_sections = [
    ['title' => 'Do I need an account to register for events?', 'body' => 'Yes. Visitors can browse public information, but event registration, liking events, ticket viewing, and city browsing require signing in.'],
    ['title' => 'Are Shenanovents registrations free?', 'body' => 'The current student version focuses on free registration events. Payment features can be added later if the project scope changes.'],
    ['title' => 'How are events published?', 'body' => 'Organizers create event drafts, then administrators review submitted events before they become visible to attendees.'],
    ['title' => 'Can events be private?', 'body' => 'Yes. Organizers can mark events as private for selected audiences while still managing the listing through the dashboard.'],
];
$info_cta = ['title' => 'Still have questions?', 'body' => 'Contact support and include the page or feature you need help with.', 'label' => 'Contact Support', 'href' => 'contact-support.php'];

require_once 'includes/info-page-template.php';
