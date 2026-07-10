<?php
// Privacy Policy Page Setup
$page_title = 'Shenanovents | Privacy Policy';
$current_page = 'home';
$info_kicker = 'Company';
$info_title = 'Privacy Policy';
$info_intro = 'This page explains how Shenanovents should handle user, organizer, event, and registration information in the student project workflow.';
$info_cards = [
    ['title' => 'Account data', 'body' => 'The system may store names, email addresses, account type, registration date, and account status.'],
    ['title' => 'Event data', 'body' => 'Organizer-created event details may include titles, descriptions, schedules, venues, capacity, and publishing status.'],
    ['title' => 'Registration data', 'body' => 'Attendee registration records help organizers monitor capacity and allow users to view their tickets.'],
];
$info_sections = [
    ['title' => 'How data is used', 'body' => 'Information is used to support sign in, event browsing, registration, ticket viewing, organizer dashboards, and administrator review.'],
    ['title' => 'Data protection approach', 'body' => 'When backend integration is added, user input should be validated, sanitized, and handled with prepared statements for database operations.'],
    ['title' => 'Access and control', 'body' => 'Administrators can manage users and events so the platform remains organized and appropriate for users.'],
    ['title' => 'Project note', 'body' => 'This frontend policy is written for academic demonstration and can be expanded when final backend requirements are approved.'],
];
$info_cta = ['title' => 'Questions about privacy?', 'body' => 'Send a support request if you need clarification about account or event data handling.', 'label' => 'Contact Support', 'href' => 'contact-support.php'];

// Shared Dependencies
require_once 'includes/info-page-template.php';
