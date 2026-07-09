<a name="readme-top"></a>

<br/>

<div align="center">
  <a href="#">
    <img src="./assets/images/logos/logo.png" alt="Shenanovents Logo" width="130" height="130">
  </a>

  <h1 align="center">Shenanovents Event Registration System</h1>

  <p align="center">
    A native PHP and MySQL Event Registration System that allows guests to discover events, participants to register and manage their tickets, event creators to organize events, and administrators to oversee the entire platform through a centralized dashboard.
  </p>
</div>

---

# Overview

Shenanovents is a database-driven Event Registration System developed as a BS Information Technology Final Project. The system is built using Native PHP, MySQL, HTML, CSS, and JavaScript while following a beginner-friendly architecture suitable for academic learning.

The platform provides different experiences for guests, participants, event creators, and administrators. Guests can browse public events, participants can register and manage their attendance, event creators can publish and manage events, and administrators oversee approvals, users, attendance, registrations, and reports.

The project follows a structured development roadmap beginning with frontend design, followed by backend integration, authentication, CRUD operations, attendance tracking, reporting, testing, and documentation.

---

# Features

## Guest

- Browse public events
- View event details
- Search events by category and country
- Sign Up
- Sign In

---

## Participant

- User Registration
- User Authentication
- Remember Me
- Forgot Password using Security Questions
- Edit Profile
- Upload Profile Picture
- Change Password
- Browse Events
- Like Events
- Register for Events
- Cancel Registration
- View Tickets
- Attendance Code
- Access Private Events using Private Event Key
- View Registered Events
- My Events Dashboard

---

## Event Creator

- Multi-step Event Creation
- Upload Event Banner
- Online and Physical Event Support
- Private and Public Events
- Publish Scheduling
- Event Dashboard
- Attendance Management
- Attendance Verification using Attendance Codes
- Registration Monitoring
- Capacity Management

---

## Administrator

- Dashboard Analytics
- Event Approvals
- Manage Events
- Manage Users
- Registration Management
- Attendance Management
- Reports
- Profile Management
- Event Status Management
- User Suspension and Reactivation

---

# Key Components

- Landing Page
- Authentication System
- Participant Dashboard
- Event Creation Wizard
- Event Registration System
- Event Dashboard
- Attendance Module
- Reports Module
- Admin Dashboard
- User Management
- Event Management
- Registration Management
- Private Event System
- Database Integration

---

# Technology Stack

![HTML](https://img.shields.io/badge/HTML-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/CSS-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white)

---

# Project Structure

```text
shenanovents/
│
├── admin/
|   ├── admin-approvals.php
|   ├── admin-attendance.php
|   ├── admin-dashboard.php
|   ├── admin-events.php
|   ├── admin-registration.php
|   ├── admin-reports.php
|   ├── admin-users.php
|   ├── edit-profile.php
|   └── profile.php
|
├── assets/
│   ├── css/
│   │   └── style.css
│   │
│   ├── images/
│   │   ├── cities/
│   │   ├── events/
│   │   ├── icons/
│   │   ├── logos/
│   │   └── users/
│   │
│   ├── js/
│   │   └── script.js
│   │
│   ├── images/
│   ├── icons/
│   └── uploads/
│
├── auth/
│   ├── forgot-password.php
│   ├── logout.php
│   ├── signin.php
│   └── signup.php
│
├── database/
│   ├── diagrams/ 
│   ├── exports/
│   ├── tables/
│   ├── connection.php
│   └── shenanovents.sql
│
├── docs/
│   ├── database/
│   │   └── database_documentation.md
│   │   
│   ├── database_dictionary.md
│   ├── erd_description.md 
│   └── project_roadmap.md
│ 
├── event-maker/
│   └── create-event.php
│
├── includes/
│   ├── admin-attendance-data.php
│   ├── admin-check.php
│   ├── admin-dashboard-data.php
│   ├── admin-event-data.php
│   ├── admin-registration-data.php
│   ├── admin-report-data.php
│   ├── admin-user-data.php
│   ├── auth-check.php
│   ├── countries.php
│   ├── footer.php
│   ├── header.php
│   ├── info-page-template.php
│   ├── participant-check.php
│   ├── participant-data.php
│   └── session.php
│       
├── participant/
│   ├── change-password.php
│   ├── city-events.php
│   ├── dashboard.php
│   ├── edit-profile.php
│   ├── event-attendance.php
│   ├── event-dashboard.php
│   ├── event-details.php
│   ├── events.php
│   ├── likes.php
│   ├── profile.php
│   ├── registered-events.php
│   └── tickets.php
│   
├── about-us.php
├── contact-support.php
├── contact.php
├── faq.php
├── help-center.php
├── index.php
├── privacy-policy.php
├── README.MD
└── terms-of-service.php
```

---

# Local Installation

## Requirements

- XAMPP
- PHP 8+
- MySQL / MariaDB
- Web Browser

---

## Setup

1. Clone or download the repository.

2. Place the project inside the XAMPP `htdocs` directory.

Example:

```text
C:\xampp\htdocs\shenanovents
```

3. Start:

- Apache
- MySQL

4. Open phpMyAdmin.

5. Create or import the database using:

```text
database/shenanovents.sql
```

6. Visit:

```text
http://localhost/shenanovents
```

---

# Demo Accounts

| Role | Email | Password | Security Answer |
|------|-------|----------|-----------------|
| Administrator | admin@shenanovents.test | Admin@123 | manila |
| Event Creator / Participant | ken@shenanovents.test | Participant@123 | adobo |
| Participant | hurris@shenanovents.test | Participant@123 | adobo |

---

# Database

Database File

```text
database/shenanovents.sql
```

Current Tables

- users
- events
- registrations
- attendance
- liked_events

The database includes classroom-friendly demo records for authentication, event creation, registrations, attendance, likes, reports, and dashboard testing.

---

# Resources

| Resource | Purpose |
|----------|---------|
| Pinterest | UI Inspiration |
| Canva | Front-end Design |
| Adobe Photoshop | Logo |
| Google | Event Photos |

---

# Academic Information

This project was developed as a Shananotech Final Project using Native PHP and MySQL. The implementation prioritizes educational value, beginner-friendly architecture, procedural PHP, and database-driven development while demonstrating authentication, CRUD operations, event management, attendance tracking, reporting, and role-based access control.

<p align="right">
(<a href="#readme-top">Back to Top</a>)
</p>