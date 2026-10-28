# StudentHub — Project Requirements

## 1. Project Purpose
StudentHub is a centralized college student portal designed to provide students with quick access to academic information, campus event updates, profile management, and feedback submission, while providing college administrators with tools to manage student records and events.

---

## 2. Functional Requirements

### 2.1 Public & Information Pages
- **Home (`index.html`):** Welcome banner, portal highlights, quick links, announcements.
- **About (`about.html`):** College information, portal mission, leadership/contact intro.
- **Contact (`contact.html`):** Campus address, contact directory, enquiry form.
- **FAQ (`faq.html`):** Frequently asked questions regarding admissions, portal access, events.
- **Feedback (`feedback.html`):** Student feedback submission form for college services.

### 2.2 Student Account & Profile
- **Registration (`register.html`):** Student sign-up collecting name, email, mobile, password, course, year, gender, and agreement to terms.
- **Login (`login.html`):** Email and password authentication with role-based access.
- **Student Dashboard (`dashboard.html`):** Student welcome, enrolled courses summary, upcoming events, quick action links.
- **Profile (`profile.html`):** Personal details display, academic info, editable profile form.

### 2.3 Events & Activities
- **Events (`events.html`):** Listing of campus workshops, sports, cultural events, and seminars with event details and registration links.

### 2.4 Administration
- **Admin Dashboard (`admin.html`):** Metrics overview (total students, active events, registrations), quick links for student and event management.

---

## 3. Non-Functional Requirements

- **Simplicity:** Code must be clear, standard, and easy to explain during academic vivas.
- **Accessibility:** Semantic HTML elements, logical heading structure, accessible form labels, and descriptive image alt attributes.
- **Responsiveness:** Usable across mobile, tablet, and desktop viewports without horizontal scrolling.
- **Maintainability:** Clear separation of concerns (markup, styles, scripts, backend logic).
- **Security:** Hashed passwords (`password_hash`), session validation, sanitized inputs, and prevention of plain-text credential leaks.

---

## 4. Scope & Constraints

- No external heavyweight frameworks (no Bootstrap, Tailwind, React, Vue).
- Built purely with standard web technologies (HTML5, CSS3, JavaScript, PHP, MySQL).
- Follows a strict incremental version roadmap (Versions 0 to 15).
