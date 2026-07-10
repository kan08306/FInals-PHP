<?php
// Public Page Setup
$page_title = 'Shenanovents | Terms of Service';
$current_page = 'home';
$info_kicker = 'Company';
$info_title = 'Terms of Service';
$info_intro = 'These terms describe the expected use of Shenanovents for attendees, organizers, and administrators in the event management workflow.';
$info_cards = [
    ['title' => 'Use correct information', 'body' => 'Users should provide accurate account, registration, and event details when using Shenanovents.'],
    ['title' => 'Respect event policies', 'body' => 'Attendees should follow organizer instructions, event capacity limits, and platform rules.'],
    ['title' => 'Keep events appropriate', 'body' => 'Organizers should submit clear, complete, and appropriate event information for administrator review.'],
];
$info_sections = [
    ['title' => 'Attendee responsibilities', 'items' => ['Create only one account for personal use.', 'Register only when planning to attend.', 'Cancel or update participation if the final backend supports it.']],
    ['title' => 'Organizer responsibilities', 'items' => ['Provide accurate schedules and venue or online details.', 'Use clear event descriptions and capacity information.', 'Respond to administrator revision requests when needed.']],
    ['title' => 'Administrator responsibilities', 'body' => 'Administrators review users and submitted events to keep the platform organized, consistent, and appropriate.'],
    ['title' => 'Academic project scope', 'body' => 'These terms support the frontend demonstration and can be revised during final backend and documentation phases.'],
];
$info_cta = ['title' => 'Need help understanding a rule?', 'body' => 'Contact support for questions about account, event, or organizer responsibilities.', 'label' => 'Contact Support', 'href' => 'contact-support.php'];

require_once 'includes/info-page-template.php';


