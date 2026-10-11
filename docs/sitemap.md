# StudentHub — Sitemap & Navigation Flow

This document details the structure, page relationships, and navigation pathways for StudentHub.

---

## 1. Page Hierarchy

```text
StudentHub
│
├── Public Navigation
│   ├── Home (index.html)
│   ├── About (about.html)
│   ├── Events (events.html)
│   ├── FAQ (faq.html)
│   ├── Contact (contact.html)
│   └── Feedback (feedback.html)
│
├── Authentication
│   ├── Register (register.html)
│   └── Login (login.html)
│
├── Student Portal (Authenticated)
│   ├── Dashboard (dashboard.html)
│   └── Profile (profile.html)
│
└── Admin Portal (Authenticated)
    └── Admin Dashboard (admin.html)
```

---

## 2. Page Directory

| File | Page Name | Primary Audience | Description |
| :--- | :--- | :--- | :--- |
| `index.html` | Home | Public | Portal landing page, campus highlights, quick announcements |
| `about.html` | About | Public | Background info, portal vision, college mission statement |
| `register.html` | Register | Guest | Student sign-up form with input fields & validation |
| `login.html` | Login | All Users | Portal entry point with credentials for student and admin |
| `dashboard.html` | Student Dashboard | Student | Central overview for students: updates, enrolled courses, links |
| `events.html` | Events | Public / Student | Schedule of campus events, filters, and registration buttons |
| `profile.html` | Profile | Student | Student details, academic summary, editable contact details |
| `contact.html` | Contact | Public | Campus address, directory, map info, and inquiry form |
| `faq.html` | FAQ | Public | Common questions and answers regarding portal and college |
| `feedback.html` | Feedback | Student / Public | Feedback submission form for college facilities & portal |
| `admin.html` | Admin Dashboard | Admin | High-level metrics, management links for students and events |

---

## 3. Navigation Links Structure

### Main Header Navigation (Default / Public)
- Home (`index.html`)
- About (`about.html`)
- Events (`events.html`)
- FAQ (`faq.html`)
- Contact (`contact.html`)
- Register (`register.html`)
- Login (`login.html`)

### Student Navigation
- Dashboard (`dashboard.html`)
- Profile (`profile.html`)
- Events (`events.html`)
- Feedback (`feedback.html`)
- Logout (`login.html`)

### Admin Navigation
- Dashboard (`admin.html`)
- Students Management (CRUD)
- Events Management (CRUD)
- Logout (`login.html`)

### Footer Navigation
- About (`about.html`)
- Contact (`contact.html`)
- FAQ (`faq.html`)
- Feedback (`feedback.html`)
