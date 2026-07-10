-- Shananotech / Shenanovents Event Registration System
-- Phase 6 Participant Module Schema
-- Native PHP + MySQL + mysqli project

-- Database Definition
CREATE DATABASE IF NOT EXISTS shenanovents_db;
USE shenanovents_db;

DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS liked_events;
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS users;

-- User Accounts Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'participant',
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    profile_picture VARCHAR(255) NULL,
    security_question VARCHAR(255) NULL,
    security_answer VARCHAR(255) NULL,
    remember_token_hash VARCHAR(255) NULL,
    remember_token_expires_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Event Management Table
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_title VARCHAR(150) NOT NULL,
    event_summary VARCHAR(255) NULL,
    event_description TEXT NOT NULL,
    event_tags VARCHAR(255) NULL,
    event_category VARCHAR(80) NULL,
    event_type VARCHAR(30) NOT NULL DEFAULT 'physical',
    event_location VARCHAR(150) NOT NULL,
    event_country VARCHAR(100) NOT NULL DEFAULT 'Philippines',
    event_province VARCHAR(100) NULL,
    event_city VARCHAR(100) NULL,
    event_address VARCHAR(255) NULL,
    event_venue VARCHAR(150) NULL,
    online_link VARCHAR(255) NULL,
    online_platform VARCHAR(100) NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    event_end_time TIME NULL,
    capacity INT NOT NULL,
    banner_image VARCHAR(255) NULL,
    visibility VARCHAR(30) NOT NULL DEFAULT 'public',
    audience VARCHAR(100) NULL,
    private_access_key VARCHAR(20) NULL UNIQUE,
    publish_date DATE NULL,
    publish_time TIME NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'open',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_created_by
        FOREIGN KEY (created_by)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- Registration Management Table
CREATE TABLE registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    registration_full_name VARCHAR(200) NULL,
    registration_email VARCHAR(150) NULL,
    contact_number VARCHAR(30) NULL,
    attendee_count INT NOT NULL DEFAULT 1,
    special_notes TEXT NULL,
    registration_status VARCHAR(30) NOT NULL DEFAULT 'registered',
    attendance_code VARCHAR(50) NULL UNIQUE,
    attendance_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    attendance_marked_at DATETIME NULL,
    registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_registrations_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_registrations_event
        FOREIGN KEY (event_id)
        REFERENCES events(event_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT uq_user_event_registration
        UNIQUE (user_id, event_id)
);

-- Liked Events Table
CREATE TABLE liked_events (
    liked_event_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_liked_events_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_liked_events_event
        FOREIGN KEY (event_id)
        REFERENCES events(event_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT uq_user_liked_event
        UNIQUE (user_id, event_id)
);

CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL UNIQUE,
    attendance_status VARCHAR(30) NOT NULL DEFAULT 'Absent',
    marked_by INT NOT NULL,
    marked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attendance_registration
        FOREIGN KEY (registration_id)
        REFERENCES registrations(registration_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_attendance_marked_by
        FOREIGN KEY (marked_by)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- Demo User Records
INSERT INTO users (user_id, first_name, last_name, email, password, role, status, profile_picture, security_question, security_answer, created_at) VALUES
(1, 'Admin', 'User', 'admin@shenanovents.test', '$2y$10$q1Pih.vnO6VXl1Xx512UIusFLax7.rMW4/PW0qVMzFh8SlVXmnR3u', 'admin', 'active', NULL, 'What city were you born in?', '$2y$10$gGwUCpx8xMIdyTVOks/KpeT6dLi1x1L9tbqRzuohGg80HHe1/05oC', '2026-07-03 20:50:53'),
(2, 'Ken', 'Bautista', 'ken@shenanovents.test', '$2y$10$xNlLDM54aYnPBXRS0ZgcEuBloWWX6aEqFvtmwRuHZuBATD7JihAZ2', 'participant', 'active', NULL, 'What is your favorite food?', '$2y$10$IuMg1D3FqeHKOLT/4pR5hOTvlhO6nDxnryQQAIYpiJMH7/Gnn.Ewm', '2026-07-03 20:50:53'),
(6, 'Hurris', 'Guansing', 'hurris@shenanovents.test', '$2y$10$hziawcDJWdFCbnGATnDm4O3eCQKZc86Hk1HjGPTVWYPm7sAPb3Wja', 'participant', 'active', NULL, 'What is your favorite food?', '$2y$10$IuMg1D3FqeHKOLT/4pR5hOTvlhO6nDxnryQQAIYpiJMH7/Gnn.Ewm', '2026-07-05 16:38:58');

-- Demo Event Records
INSERT INTO events (
    event_id, event_title, event_summary, event_description, event_tags, event_category, event_type,
    event_location, event_country, event_province, event_city, event_address, event_venue,
    online_link, online_platform, event_date, event_time, event_end_time, capacity,
    banner_image, visibility, audience, private_access_key, publish_date, publish_time, status, created_by, created_at
) VALUES
(1, 'Cybersecurity Awareness Conference', 'Beginner-friendly online safety session.', 'A beginner-friendly event about online safety, password protection, and common cyber threats.', 'cybersecurity, awareness, online safety', 'technology', 'online', 'Online Session', 'Philippines', NULL, NULL, NULL, NULL, 'https://meet.example.com/cyber-awareness', 'Google Meet', '2026-07-26', '09:00:00', '11:00:00', 200, NULL, 'public', 'Everyone', NULL, NULL, NULL, 'open', 1, '2026-07-03 20:50:53'),
(2, 'Startup Pitch Night', 'Student startup pitch and feedback night.', 'A student event where participants present simple business ideas and receive feedback.', 'startup, business, pitch', 'business', 'online', 'Online Business Hub', 'Philippines', NULL, NULL, NULL, NULL, 'https://zoom.example.com/startup-pitch', 'Zoom', '2026-08-01', '19:00:00', '21:00:00', 180, NULL, 'public', 'Everyone', NULL, NULL, NULL, 'open', 1, '2026-07-03 20:50:53'),
(3, 'Digital Music Production Workshop', 'Introductory digital music production class.', 'A completed workshop used for dashboard attendance samples.', 'music, production, workshop', 'music', 'online', 'Online Studio', 'Philippines', NULL, NULL, NULL, NULL, 'https://live.example.com/music-production', 'YouTube Live', '2026-07-02', '10:00:00', '12:00:00', 120, NULL, 'public', 'Everyone', NULL, NULL, NULL, 'closed', 2, '2026-07-03 20:50:53'),
(4, 'Campus Cyber Defense Lab', 'Hands-on campus cybersecurity practice lab.', 'A participant-created workshop where students practice basic incident response, password safety, and secure account habits.', 'cybersecurity, campus, workshop', 'technology', 'physical', 'FEU Tech Innovation Lab, P. Paredes Street, Manila, Metro Manila, Philippines', 'Philippines', 'Metro Manila', 'Manila', 'P. Paredes Street', 'FEU Tech Innovation Lab', NULL, NULL, '2026-09-18', '13:00:00', '16:00:00', 80, NULL, 'public', 'Everyone', NULL, NULL, NULL, 'open', 2, '2026-07-05 21:00:00'),
(5, 'Private Leadership Briefing', 'Invite-only event for selected student leaders.', 'A private planning session for invited student leaders and organization representatives.', 'leadership, private, planning', 'education', 'physical', 'FEU Tech Collaboration Room, Manila, Philippines', 'Philippines', 'Metro Manila', 'Manila', 'P. Paredes Street', 'FEU Tech Collaboration Room', NULL, NULL, '2026-09-25', '14:00:00', '16:00:00', 40, NULL, 'private', 'Invite-only guests', 'PRIVATE-SHNV-N5P6Q7', NULL, NULL, 'open', 2, '2026-07-05 21:30:00'),
(6, 'Scheduled Community Expo', 'A public event scheduled for later publishing.', 'A community expo that should stay hidden until its publish schedule is reached.', 'community, expo, scheduled', 'community', 'physical', 'Cebu City Convention Center, Cebu, Philippines', 'Philippines', 'Cebu', 'Cebu', 'Cebu Business Park', 'Cebu City Convention Center', NULL, NULL, '2026-10-05', '09:00:00', '17:00:00', 150, NULL, 'public', 'Everyone', NULL, '2026-09-20', '08:00:00', 'pending', 2, '2026-07-05 21:45:00');

-- Demo Registration Records
INSERT INTO registrations (registration_id, user_id, event_id, registration_full_name, registration_email, contact_number, attendee_count, special_notes, registration_status, attendance_code, attendance_status, attendance_marked_at, registered_at) VALUES
(1, 2, 1, 'Ken Bautista', 'ken@shenanovents.test', '09170000001', 1, 'Interested in password safety topics.', 'registered', 'SHNV-2026-A1B2C3', 'pending', NULL, '2026-07-05 19:25:52'),
(3, 2, 2, 'Ken Bautista', 'ken@shenanovents.test', '09170000001', 1, 'Schedule conflict after registration.', 'cancelled', 'SHNV-2026-D4E5F6', 'pending', NULL, '2026-07-05 19:20:47'),
(4, 6, 4, 'Hurris Guansing', 'hurris@shenanovents.test', '09170000002', 2, 'Bringing one classmate.', 'registered', 'SHNV-2026-G7H8J9', 'pending', NULL, '2026-07-05 21:05:00'),
(5, 6, 3, 'Hurris Guansing', 'hurris@shenanovents.test', '09170000002', 1, 'Attended the completed workshop.', 'registered', 'SHNV-2026-K2L3M4', 'present', '2026-07-02 12:05:00', '2026-07-01 14:00:00'),
(6, 6, 5, 'Hurris Guansing', 'hurris@shenanovents.test', '09170000002', 1, 'Invite confirmed by organizer.', 'registered', 'SHNV-2026-N5P6Q7', 'pending', NULL, '2026-07-05 22:00:00');

INSERT INTO liked_events (liked_event_id, user_id, event_id, created_at) VALUES
(1, 2, 1, '2026-07-05 21:10:00'),
(2, 2, 4, '2026-07-05 21:11:00'),
(3, 6, 2, '2026-07-05 21:12:00');

INSERT INTO attendance (attendance_id, registration_id, attendance_status, marked_by, marked_at) VALUES
(1, 5, 'Present', 1, '2026-07-02 12:05:00');

-- Sample aggregate queries for reports:
-- SELECT COUNT(*) AS total_users FROM users;
-- SELECT COUNT(*) AS total_events FROM events;
-- SELECT COUNT(*) AS total_registrations FROM registrations WHERE registration_status = 'registered';
-- SELECT attendance_status, COUNT(*) AS total FROM attendance GROUP BY attendance_status;
-- SELECT MAX(capacity) AS largest_event_capacity FROM events;
-- SELECT MIN(capacity) AS smallest_event_capacity FROM events;
-- SELECT AVG(capacity) AS average_event_capacity FROM events;
