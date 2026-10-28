# StudentHub — Sitemap & Navigation Flow

This document details the complete page hierarchy, URL routing structure, and role-based access pathways for the **StudentHub** college portal.

---

## 1. System Architecture & Page Hierarchy

```text
StudentHub Portal
│
├── 🌐 Public & Guest Pages (Unauthenticated)
│   ├── Home Landing Page (index.php / index.html redirect)
│   ├── About College & Portal (about.html)
│   ├── Campus Events Directory (events.php / events.html redirect)
│   ├── Frequently Asked Questions (faq.html)
│   ├── Campus Contact & Inquiries (contact.php / contact.html redirect)
│   ├── Institutional Feedback & Reviews (feedback.php / feedback.html redirect)
│   ├── Student Registration (register.html / register.php)
│   └── Portal Login (login.html)
│
├── 🎓 Student Portal (Authenticated — Role: student)
│   ├── All-in-One Student Dashboard (dashboard.php)
│   │   ├── #overview      ── Academic metrics, attendance progress, timetable
│   │   ├── #events        ── Campus event catalog, search, and quick registration modal
│   │   ├── #feedback      ── In-dashboard feedback review submission and personal history
│   │   ├── #support       ── In-dashboard support inquiry submission and ticket history
│   │   └── #profile       ── Student profile viewer and instant MySQL record update
│   ├── My Profile Detailed View (profile.php)
│   └── Secure Logout (logout.php ── destroys session and cookies, returns to login.html)
│
├── 🛡️ Administrator Portal (Authenticated — Role: admin)
│   ├── Administrative Control Panel (admin.php)
│   │   ├── Overview       ── High-level analytics, enrollment counts, category breakdown
│   │   ├── #student-management  ── View all students, add record, search, and delete
│   │   ├── #event-management    ── View all events, create event, search, and delete
│   │   └── #feedback-inquiries  ── Review all student feedback reviews and contact inquiries
│   ├── Edit Student Record (edit_student.php)
│   ├── Delete Student Action (delete_student.php)
│   ├── Edit / Add Campus Event (edit_event.php)
│   ├── Delete Event Action (delete_event.php)
│   └── Secure Logout (logout.php)
│
└── ⚙️ Backend Data Handlers & API Endpoints
    ├── Authentication Processing (process_login.php)
    ├── Student Registration Processing (process_register.php)
    ├── Contact Inquiry Processing (process_contact.php)
    ├── Feedback Submission Processing (process_feedback.php)
    ├── Database Auto-Setup Utility (setup_database.php)
    └── DB Connection Test (db_test.php)
```

---

## 2. Page Directory & Access Control

| File / Route | Title | Target Role | Access Rules | Key Functionality |
| :--- | :--- | :--- | :--- | :--- |
| [`index.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/index.php) | Home | Public / Guest | Redirects to dashboard if logged in | Announcements slider, campus statistics, quick portal links |
| [`about.html`](file:///e:/Main%20Vault/1.Projects/Antigravity/about.html) | About | Public / Guest | Redirects to dashboard if logged in | College history, mission/vision cards, metric statistics |
| [`events.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/events.php) | Events | Public / Guest | Redirects to `dashboard.php#events` if logged in | Searchable event catalog, category filter pills, modal registration |
| [`faq.html`](file:///e:/Main%20Vault/1.Projects/Antigravity/faq.html) | FAQ | Public / Guest | Redirects to dashboard if logged in | Searchable accordion FAQs loaded dynamically from `data/faqs.json` |
| [`contact.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/contact.php) | Contact Us | Public / Guest | Redirects to `dashboard.php#support` if logged in | Department inquiry form, office location, contact directory |
| [`feedback.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/feedback.php) | Feedback | Public / Guest | Redirects to `dashboard.php#feedback` if logged in | Star-rating submission form for campus facilities and courses |
| [`register.html`](file:///e:/Main%20Vault/1.Projects/Antigravity/register.html) | Register | Guest | Redirects to dashboard if logged in | Real-time client-validated student signup form with strength meter |
| [`login.html`](file:///e:/Main%20Vault/1.Projects/Antigravity/login.html) | Login | Guest | Redirects to dashboard if logged in | Portal authentication for students and college administrators |
| [`dashboard.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/dashboard.php) | Student Portal | Student Only | Requires `$_SESSION['user_id']` (role: student) | Integrated single-page hub for metrics, events, feedback, inquiries, profile |
| [`profile.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/profile.php) | My Profile | Student Only | Requires `$_SESSION['user_id']` (role: student) | Comprehensive student profile view with live contact updating |
| [`admin.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/admin.php) | Admin Control Panel | Admin Only | Requires `$_SESSION['role'] === 'admin'` | Full administrative oversight, student/event CRUD, reviews & inquiries |
| [`edit_student.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/edit_student.php) | Edit Student | Admin Only | Requires `admin` privilege | Form to update any student's name, email, roll number, course, year |
| [`delete_student.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/delete_student.php) | Delete Student | Admin Only | Requires `admin` privilege | Secure record removal with foreign key integrity |
| [`edit_event.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/edit_event.php) | Edit Event | Admin Only | Requires `admin` privilege | Form to add a new event or update date, venue, capacity, category |
| [`delete_event.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/delete_event.php) | Delete Event | Admin Only | Requires `admin` privilege | Remove an event listing from database and JSON store |
| [`logout.php`](file:///e:/Main%20Vault/1.Projects/Antigravity/logout.php) | Logout | Authenticated | Clears session and cookies | Completely destroys PHP session, invalidates cookies, sends to `login.html` |

---

## 3. Strict Post-Login Isolation Paradigm

To ensure security and a focused user experience, **StudentHub** enforces a strict separation between public guest browsing and authenticated operations:

```
                  ┌──────────────────────────────────────────────┐
                  │                 Guest User                   │
                  │  (index.php, about.html, events.php, etc.)   │
                  └──────────────────────┬───────────────────────┘
                                         │
                                   [ login.html ]
                                         │
                         ┌───────────────┴───────────────┐
                   Role: student                   Role: admin
                         │                               │
                         ▼                               ▼
        ┌────────────────────────────────┐   ┌───────────────────────────────┐
        │        dashboard.php           │   │           admin.php           │
        │  ┌──────────────────────────┐  │   │  ┌──────────────────────────┐ │
        │  │ #overview (Metrics)      │  │   │  │ Overview Analytics       │ │
        │  │ #events (Event Register) │  │   │  │ #student-management     │ │
        │  │ #feedback (Reviews)      │  │   │  │ #event-management       │ │
        │  │ #support (Inquiries)     │  │   │  │ #feedback-inquiries     │ │
        │  │ #profile (Self-Edit)     │  │   │  └──────────────────────────┘ │
        │  └──────────────────────────┘  │   │                               │
        │                                │   │  [edit_student.php / event]   │
        │  [profile.php]                 │   └───────────────┬───────────────┘
        └───────────────┬────────────────┘                   │
                        │                                    │
                        └─────────────────┬──────────────────┘
                                          │
                                     [ logout.php ]
                                          │
                                          ▼
                                     login.html
```

### Isolation Rules:
1. **Public Page Access Block:** If an authenticated user enters any public URL (e.g., `about.html`, `login.html`, `events.php`), they are immediately intercepted and returned to their dashboard (`dashboard.php` for students, `admin.php` for administrators).
2. **In-Dashboard Single Hub:** Students do not need public pages to view campus events or submit feedback; all 5 services are embedded right inside `dashboard.php` with smooth anchor navigation (`#events`, `#feedback`, `#support`, `#profile`).
3. **Admin Inquiries Rule:** Administrators only **receive and review** inquiries and feedback; they do not send inquiries to themselves.
4. **Session Termination:** The only exit path from the authenticated portal is the **Log Out** button (`logout.php`), which destroys the server session and invalidates client navigation cookies.

---

## 4. Navigation Menu Structures

### Public & Guest Header Navigation
* **Brand Logo:** `StudentHub` &rarr; `index.php`
* **Home:** `index.php`
* **About:** `about.html`
* **Events:** `events.php`
* **FAQ:** `faq.html`
* **Contact:** `contact.php`
* **Feedback:** `feedback.php`
* **Register:** `register.html` *(Action pill)*
* **Login:** `login.html` *(Primary blue action pill)*

### Student Portal Header Navigation (`dashboard.php` & `profile.php`)
* **Brand Logo:** `StudentHub Portal` &rarr; `dashboard.php#overview`
* **Overview:** `dashboard.php#overview`
* **Campus Events:** `dashboard.php#events`
* **Feedback:** `dashboard.php#feedback`
* **Support:** `dashboard.php#support`
* **My Profile:** `dashboard.php#profile` (or `profile.php`)
* **User Status Badge:** `[Name] (Student)`
* **Log Out:** `logout.php` *(Red outline pill)*

### Administrator Header Navigation (`admin.php`)
* **Brand Logo:** `StudentHub Administration` &rarr; `admin.php`
* **Overview:** `admin.php`
* **Students:** `admin.php#student-management`
* **Events:** `admin.php#event-management`
* **Feedback & Inquiries:** `admin.php#feedback-inquiries`
* **Admin Badge:** `admin@studenthub.edu`
* **Log Out:** `logout.php` *(Red outline pill)*

### Public Footer Navigation
* **About Us:** `about.html`
* **Contact Office:** `contact.php`
* **Frequently Asked Questions:** `faq.html`
* **Submit Feedback:** `feedback.php`
* **Admin Access Portal:** `admin.php`
