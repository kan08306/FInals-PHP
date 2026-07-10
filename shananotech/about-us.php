<?php
// Public Page Setup
$page_title = 'Shenanovents | About Us';
$current_page = 'home';
$info_kicker = 'Company';
$info_title = 'About Shenanovents';
$info_intro = 'Shenanovents is a student-built event registration and discovery platform designed to make event browsing, registration, and organizer management easier.';
$info_cards = [
    ['title' => 'Purpose', 'body' => 'The system centralizes event discovery and registration so attendees can find activities without scattered announcements.'],
    ['title' => 'Learning focus', 'body' => 'The project demonstrates practical PHP, MySQL-ready workflows, frontend consistency, validation, and simple system organization.'],
    ['title' => 'Community value', 'body' => 'Shenanovents supports students, organizers, and administrators through one connected event management experience.'],
];
$info_sections = [
    ['title' => 'What the platform does', 'items' => ['Displays public and authenticated event listings.', 'Supports free event registration and ticket viewing.', 'Allows organizers to create and monitor events.', 'Allows administrators to approve events and manage platform records.']],
    ['title' => 'Design direction', 'body' => 'The interface uses a consistent green and gold Shenanovents identity, reusable cards, pill navigation, simple forms, and responsive layouts.'],
    ['title' => 'Academic project goal', 'body' => 'The system is built to show complete workflow understanding rather than unnecessary complexity.'],
];
$info_cta = ['title' => 'Explore the platform', 'body' => 'View available events and see how the Shenanovents experience works.', 'label' => 'Browse Events', 'href' => 'participant/events.php'];

// Shared Dependencies
require_once 'includes/info-page-template.php';
