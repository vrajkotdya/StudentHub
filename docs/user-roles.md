# StudentHub — User Roles & Permissions

This document outlines the user roles, access levels, and permitted actions across the StudentHub portal.

---

## 1. Role Definitions

### 1. Guest / Unauthenticated Visitor
A visitor browsing the portal without logging in.

- **Accessible Pages:**
  - Home (`index.html`)
  - About (`about.html`)
  - Events (`events.html` — view-only)
  - FAQ (`faq.html`)
  - Contact (`contact.html`)
  - Register (`register.html`)
  - Login (`login.html`)
- **Permissions:**
  - Browse public campus information and announcements.
  - View event listings.
  - Submit general contact inquiries.
  - Register for a student account.

---

### 2. Registered Student
An authenticated student who has registered and logged in with valid credentials.

- **Accessible Pages:**
  - All Guest pages
  - Student Dashboard (`dashboard.html`)
  - Profile (`profile.html`)
  - Feedback (`feedback.html`)
  - Event Registration
- **Permissions:**
  - View personalized student dashboard.
  - View and update personal profile details.
  - Register for college events.
  - Submit feedback and suggestions.
  - Log out securely.

---

### 3. College Administrator
An authenticated administrative staff member with supervisory privileges.

- **Accessible Pages:**
  - All Public pages
  - Admin Dashboard (`admin.html`)
  - Student Management (CRUD)
  - Event Management (CRUD + Poster Upload)
- **Permissions:**
  - View portal summary metrics (total students, active events, registrations).
  - Create, read, update, and delete student records.
  - Create, edit, and remove campus events and upload event posters.
  - Review event registrations and student submissions.
  - Log out securely.

---

## 2. Role & Permission Matrix

| Feature / Page | Guest | Student | Admin |
| :--- | :---: | :---: | :---: |
| View Home, About, Contact, FAQ | Yes | Yes | Yes |
| View Public Event Listing | Yes | Yes | Yes |
| Register Student Account | Yes | No | No |
| Login / Logout | Yes | Yes | Yes |
| View Student Dashboard | No | Yes | No |
| View / Edit Own Profile | No | Yes | No |
| Submit Event Registration | No | Yes | No |
| Submit Feedback | No | Yes | No |
| View Admin Dashboard | No | No | Yes |
| Manage Students (CRUD) | No | No | Yes |
| Manage Events (CRUD + Upload) | No | No | Yes |
