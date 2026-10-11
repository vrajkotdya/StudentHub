# StudentHub — College Student Portal

StudentHub is a simple, lightweight college student portal designed for managing student profiles, events, campus notices, feedback, and student-admin workflows.

---

## Project Overview

- **Project Name:** StudentHub
- **Level:** Beginner / Academic Viva Project
- **Tech Stack Progression:**
  - **Version 0:** Foundation & Documentation (Current)
  - **Version 1:** Semantic HTML5
  - **Version 2:** Basic CSS3 & Styling
  - **Version 3:** Responsive Web Design (Mobile/Tablet/Desktop)
  - **Version 4:** Frontend JavaScript Interactivity
  - **Version 5:** Frontend Form Validation
  - **Version 6:** JSON Data & Fetch API
  - **Version 7:** PHP Form Processing
  - **Version 8:** MySQL Database Foundation
  - **Version 9:** Secure Registration & Password Hashing
  - **Version 10:** Authentication, Sessions & Access Control
  - **Version 11:** Student CRUD Management
  - **Version 12:** Event CRUD & Poster Upload
  - **Version 13:** PHP JSON REST API & Async Fetch
  - **Version 14:** Admin Dashboard & Analytics
  - **Version 15:** Final Testing, Security Hardening & Deployment

---

## Folder Structure

```text
StudentHub/
├── docs/
│   ├── requirements.md     # Functional and non-functional requirements
│   ├── user-roles.md       # Role definitions and permissions
│   ├── sitemap.md          # Portal page hierarchy and flow
│   └── wireframes.md       # ASCII wireframes for core pages
├── .gitignore              # Ignored files (system/editor artifacts)
├── README.md               # Project documentation and roadmap
├── data/
│   ├── events.json         # Campus events data repository
│   ├── students.json       # Registered student directory records
│   ├── faqs.json           # Frequently asked questions catalog
│   ├── inquiries.json      # Contact inquiries JSON store
│   ├── feedback.json       # Student feedback JSON store
│   ├── registrations.csv   # Registration records CSV audit log
│   ├── inquiries.csv       # Contact inquiries CSV log
│   └── feedback.csv        # Student feedback CSV log
├── sql/
│   └── schema.sql          # MySQL database schema (tables, foreign keys, seed data)
├── php/
│   ├── functions.php       # Sanitization, JSON & CSV file helper functions
│   └── db.php              # MySQL database connection & query execution helpers
├── css/
│   ├── base.css            # Base resets, typography, and global spacing
│   ├── layout.css          # Header, navigation (flexbox), containers, footer
│   ├── components.css      # Buttons, forms, cards, tables, notices & server alerts
│   ├── responsive.css      # Mobile-first media queries, CSS Grid, and responsive wrappers
│   ├── theme.css           # Light/Dark mode themes and toggle button styling
│   ├── interactive.css     # Modals, banners, hamburger menu, accordion, and slider
│   ├── validation.css      # Form validation error states & password strength meter
│   ├── data-components.css # Loading, error, pagination & filter toolbar styles
│   └── style.css           # Master stylesheet aggregating all modules
├── js/
│   ├── theme.js            # Light/Dark theme toggle & localStorage persistence
│   ├── nav.js              # Mobile hamburger menu toggle & ARIA states
│   ├── notification.js     # Dismissible announcement banner & session storage
│   ├── faq.js              # FAQ fetch, search, filter & accordion interaction
│   ├── modal.js            # Modal popup dialog with delegated event handling
│   ├── slider.js           # Announcement carousel slider with controls
│   ├── validation.js       # Real-time form validation & password strength meter
│   ├── events.js           # Fetch API events catalog (search, filter, sort, pagination)
│   ├── admin-students.js   # Fetch API student directory (search, filter, sort, pagination)
│   └── main.js             # UI coordination script
├── setup_database.php      # Automated database installation & migration script
├── db_test.php             # Database diagnostics & relational SQL operations test
├── process_register.php    # PHP server POST processor (JSON, CSV & MySQL storage)
├── process_contact.php     # PHP server POST processor for contact inquiries
├── process_feedback.php    # PHP server POST processor for feedback reviews
├── index.php               # PHP home page entry point with live database counts
├── register.php            # Dynamic PHP registration page with sticky values
├── index.html              # Home page
├── about.html              # About page
├── register.html           # Student registration page (POST to process_register.php)
├── login.html              # Portal authentication page
├── dashboard.html          # Student dashboard
├── events.html             # Events listing & registration (Dynamic Fetch)
├── profile.html            # Student profile & update form
├── contact.html            # Contact & inquiry form (POST to process_contact.php)
├── faq.html                # Frequently asked questions (Dynamic Fetch)
├── feedback.html           # Student feedback form (POST to process_feedback.php)
└── admin.html              # Administrative control panel (Dynamic Fetch)
```

---

## Version Status

- **Current Version:** Version 9 — Secure Registration & Password Hashing
- **Status:** Complete
- **Next Version:** Version 10 — Authentication, Sessions & Access Control

---

## Local Web Server Setup (WAMP)

- **Local Server URL:** `http://localhost:8080/StudentHub/` (or `http://127.0.0.1:8080/StudentHub/`)
- **Apache Port:** `8080`
- **MySQL Port:** `3306` (Database: `studenthub_db`, User: `root`, Password: none)
- **Important Note on Form Submissions:** Browsers cannot execute PHP or connect to MySQL directly through `file:///` URLs (opening HTML files directly from File Explorer). Opening forms via `file://` causes the browser to download `.php` files instead of running them on Apache. Always access pages via `http://localhost:8080/StudentHub/`. Client-side scripts (`main.js` and `validation.js`) proactively detect the `file://` protocol, display a warning banner, and prevent form submission downloads.

---

## Security & Registration Architecture (Version 9)

- **Password Hashing:**
  - Uses PHP's native `password_hash($password, PASSWORD_DEFAULT)` generating standard 60-character BCRYPT hashes with automatic salting.
  - Plain-text passwords are **NEVER** stored in MySQL, JSON files, or CSV logs.
  - Future authentication checks in Version 10 use `password_verify($password, $hash)`.
- **Relational User & Student Records:**
  - `users` table: Holds `username`, `email`, `password` (hash), and `role` (`student`).
  - `students` table: Holds student academic info (`roll_no`, `name`, `course`, `year`, `mobile`, `gender`, `status`) and links `user_id` &rarr; `users.id` via foreign key with `ON DELETE SET NULL`.
- **Duplicate Prevention:**
  - Validates email uniqueness across both MySQL `users` and `students` tables, as well as JSON storage fallback.
- **Automated Database Setup:**
  - Run `php setup_database.php` via CLI or open `http://localhost:8080/StudentHub/setup_database.php` in the browser to re-seed tables with pre-hashed credentials.









