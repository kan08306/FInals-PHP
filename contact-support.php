<?php
// Public Page Setup
$page_title = 'Shenanovents | Contact Support';
$current_page = 'home';
$info_kicker = 'Resources';
$info_title = 'Contact Support';
$info_intro = 'Reach the Shenanovents support team for account access, registration concerns, event publishing questions, or administrator review issues.';
$info_cards = [
    ['title' => 'Account concerns', 'body' => 'Ask for help with sign in, profile details, suspended accounts, or missing tickets.'],
    ['title' => 'Event concerns', 'body' => 'Report incorrect event information, registration issues, or missing event updates.'],
    ['title' => 'Organizer support', 'body' => 'Request assistance with event drafts, approval feedback, private event settings, or capacity details.'],
];
$info_sections = [
    ['title' => 'Support information to prepare', 'body' => 'Include your full name, registered email address, event title if applicable, and a short description of the issue.'],
    ['title' => 'Suggested response time', 'body' => 'For project demonstration purposes, support requests are treated as queued messages that administrators can review later.'],
    ['title' => 'Contact channels', 'items' => ['Email: support@shenanovents.test', 'Office hours: Monday to Friday, 9:00 AM to 5:00 PM', 'Location: Student Events Office, Manila']],
];
$info_cta = ['title' => 'Ready to continue?', 'body' => 'Return to event browsing after checking the support information.', 'label' => 'Browse Events', 'href' => 'participant/events.php'];

require_once 'includes/info-page-template.php';


