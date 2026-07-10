<?php
// Public Page Setup
$page_title = 'Shenanovents | Contact';
$current_page = 'home';
$info_kicker = 'Company';
$info_title = 'Contact Shenanovents';
$info_intro = 'Connect with the Shenanovents team for general questions, project inquiries, event concerns, and support guidance.';
$info_cards = [
    ['title' => 'General inquiries', 'body' => 'Ask about Shenanovents features, project scope, documentation, or presentation details.'],
    ['title' => 'Event inquiries', 'body' => 'Reach out about event discovery, organizer submissions, city listings, or registration workflows.'],
    ['title' => 'Technical support', 'body' => 'Use Contact Support for specific account, ticket, event approval, or platform access concerns.'],
];
$info_sections = [
    ['title' => 'Contact details', 'items' => ['Email: hello@shenanovents.test', 'Support: support@shenanovents.test', 'Location: Manila, Philippines']],
    ['title' => 'Office schedule', 'body' => 'Messages are reviewed during weekday academic hours for project demonstration purposes.'],
    ['title' => 'Before sending a message', 'body' => 'Prepare your full name, email address, and the page or workflow related to your concern.'],
];
$info_cta = ['title' => 'Need support instead?', 'body' => 'For account, registration, and event issues, use the support page.', 'label' => 'Contact Support', 'href' => 'contact-support.php'];

require_once 'includes/info-page-template.php';


