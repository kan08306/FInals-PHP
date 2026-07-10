<?php
// Help Center Page Setup
$page_title = 'Shenanovents | Help Center';
$current_page = 'home';
$info_kicker = 'Resources';
$info_title = 'Help Center';
$info_intro = 'Use these guides to understand the main Shenanovents workflows from account access to event approval.';
$info_cards = [
    ['title' => 'Account access', 'body' => 'Sign in with your registered email address, then use the header menu to access your profile, tickets, likes, and event dashboard.'],
    ['title' => 'Event discovery', 'body' => 'Browse events by time, category, or city. Authenticated city pages keep the selected location active while showing matching events.'],
    ['title' => 'Event management', 'body' => 'Organizers can create events, review registrations, and monitor status from the dashboard.'],
];
$info_sections = [
    ['title' => 'Getting started', 'body' => 'Create an account, sign in, browse events, and register for a listing that fits your schedule.'],
    ['title' => 'Managing tickets', 'body' => 'After registration, tickets are shown in the Tickets page using the same event card design as the browsing experience.'],
    ['title' => 'Creating an event', 'body' => 'Use the Create Event workflow to enter event name, date, time, location, image, description, capacity, and publishing settings.'],
    ['title' => 'Administrator review', 'body' => 'Submitted events can be approved, rejected, or sent back to the organizer with requested changes.'],
];
$info_cta = ['title' => 'Need direct assistance?', 'body' => 'Send a support request with your concern and account details.', 'label' => 'Contact Support', 'href' => 'contact-support.php'];

// Shared Dependencies
require_once 'includes/info-page-template.php';
