# StudentHub — University Student & Administrative Portal

> **Subject:** Web Development Frameworks (ITUE203)  
> **Course:** 3rd Semester B.Tech (ODD 2026-27)  
> **Department:** Faculty of Technology and Engineering (FTE)  
> **Institution:** Charotar University of Science and Technology (CHARUSAT)  

---

## 📌 Practical 1: Project Initiation, Requirement Analysis, Sitemap, Wireframe, and GitHub Setup

### 1. Problem Definition & Scope
In conventional university ecosystems, academic notifications, examination updates, campus event registrations, profile management, and student feedback mechanisms are scattered across disparate bulletin boards, messaging channels, and isolated web forms. This fragmentation leads to missed academic deadlines, inefficient event coordination, and administrative overhead.

**StudentHub** is conceived as a unified, accessible, and responsive university portal designed to bridge this divide. It provides students with a single digital hub for managing their academic identity, discovering department events, accessing verified circulars, and submitting feedback, while providing administrators with centralized supervision, student directory management, and activity auditing.

---

### 2. User Roles & Personas

| Role | Target User | Key Responsibilities & Capabilities |
|---|---|---|
| **1. Public / Guest** | Prospective students, parents, campus visitors | Browse public institutional info, review campus events calendar, view FAQ, submit contact inquiries, access registration & login gateways. |
| **2. Registered Student** | Enrolled undergraduate/postgraduate students | Manage student profile and digital ID, register for technical fests/workshops, track enrolled events, read department notices, submit course and facility feedback. |
| **3. Administrator** | Department coordinators, faculty admins | Supervise student enrollment records, publish and manage university events, view platform metrics, audit security activities and logins. |

---

### 3. Key Modules & Functional Requirements

1. **Authentication & Identity Module**:
   - Secure student registration with comprehensive input validation.
   - Dual-role authentication gate (Student vs. Administrator).
   - Session management and role-based access control (RBAC).

2. **Student Services & Academic Dashboard**:
   - Personalized student home dashboard with enrolled events and quick links.
   - Digital student ID card with semester, roll number, and department details.
   - Interactive profile editor for updating contact info.

3. **Event & Workshop Management**:
   - Chronological listing of upcoming seminars, hackathons, and sports events.
   - Event detail preview, category tags, and one-click registration.
   - Admin tools for creating, updating, and removing events with poster attachments.

4. **Institutional Notice Board & Circulars**:
   - Official announcements categorized by Exam, Academic, and General alerts.
   - Search, filter, and pagination capabilities for historical notices.

5. **Student Voice & Feedback System**:
   - Standardized feedback form with category selection and rating scales.
   - Accessible feedback submission with validation.

6. **Administrative Governance & Audit**:
   - Metric cards (Total Students, Active Events, Registrations).
   - Searchable and filterable student roster with management operations.
   - System audit trail recording significant actions with timestamps.

---

### 4. Portal Sitemap & Navigation Architecture

```
StudentHub Portal (Root)
│
├── Public Navigation (Global Header)
│   ├── Home (index.html)
│   ├── About University & Portal (about.html)
│   ├── Campus Events (events.html)
│   ├── Frequently Asked Questions (faq.html)
│   ├── Contact Us & Helpdesk (contact.html)
│   ├── Student Feedback (feedback.html)
│   ├── Sign In (login.html)
│   └── Create Account (register.html)
│
├── Student Portal (Authenticated)
│   ├── Student Dashboard (student-dashboard.html)
│   └── Student Profile & ID Card (student-profile.html)
│
└── Administrative Portal (Role-Protected)
    └── Admin Management Dashboard (admin-dashboard.html)
```

---

### 5. Low-Fidelity Wireframes

#### 5.1 Public Home Page (`index.html`)
```text
+-----------------------------------------------------------------------+
| [LOGO] StudentHub          Home  About  Events  FAQ  Contact  [Login] |
+-----------------------------------------------------------------------+
| [ HERO BANNER ]                                                       |
|  Empowering CHARUSAT Students Through a Unified Digital Campus        |
|  [ Explore Campus Events ]    [ Student Registration ]                |
+-----------------------------------------------------------------------+
| QUICK STATS: [ 5000+ Students ] [ 50+ Events ] [ 100% Digital ]       |
+-----------------------------------------------------------------------+
| FEATURED MODULES (3-Column Grid)                                      |
| +-------------------+  +-------------------+  +-------------------+  |
| | Academic Notices  |  | Event Discovery  |  | Student Services   |  |
| | Latest circulars  |  | Tech fests & hack |  | ID card, profile   |  |
| +-------------------+  +-------------------+  +-------------------+  |
+-----------------------------------------------------------------------+
| FOOTER: © 2026-27 CHARUSAT FTE | Quick Links | Privacy | Contact      |
+-----------------------------------------------------------------------+
```

#### 5.2 Student Registration Form (`register.html`)
```text
+-----------------------------------------------------------------------+
| [LOGO] StudentHub                                   [ Back to Home ]  |
+-----------------------------------------------------------------------+
|                     CREATE STUDENT ACCOUNT                            |
|                                                                       |
|  Full Name:             [___________________________]                 |
|  Student ID / Roll No:  [___________________________]                 |
|  CHARUSAT Email:        [___________________________]                 |
|  Phone Number:          [___________________________]                 |
|  Department / Course:   [ Select Department      |v]                 |
|  Academic Year:         ( ) 1st  ( ) 2nd  (*) 3rd  ( ) 4th            |
|  Gender:                ( ) Male  ( ) Female  ( ) Other               |
|  Password:              [___________________________]                 |
|  Confirm Password:      [___________________________]                 |
|  [ ] I accept the Student Code of Conduct & Terms                     |
|                                                                       |
|                     [  Register Account  ]                            |
|             Already have an account? [ Login Here ]                   |
+-----------------------------------------------------------------------+
```

#### 5.3 Student Dashboard (`student-dashboard.html`)
```text
+-----------------------------------------------------------------------+
| [LOGO] StudentHub | Dashboard   Events   Profile   Notices   [Logout] |
+-----------------------------------------------------------------------+
| Welcome back, Alex Patel! [3rd Sem - Computer Engineering]            |
|                                                                       |
| METRIC CARDS:                                                         |
| [ Registered Events: 3 ]   [ Pending Notices: 2 ]   [ Attendance: 88%]|
|                                                                       |
| YOUR REGISTERED EVENTS                  LATEST CAMPUS NOTICES         |
| +-------------------------------+      +----------------------------+ |
| | HackCHARUSAT 2026             |      | Mid-Sem Exam Schedule Out  | |
| | Date: Oct 12, 2026            |      | Date: Sep 20, 2026         | |
| +-------------------------------+      +----------------------------+ |
| | AI/ML Workshop                |      | Annual Sports Day Trial    | |
| | Date: Nov 05, 2026            |      | Date: Sep 25, 2026         | |
| +-------------------------------+      +----------------------------+ |
+-----------------------------------------------------------------------+
```

#### 5.4 Admin Dashboard (`admin-dashboard.html`)
```text
+-----------------------------------------------------------------------+
| [LOGO] StudentHub Admin | Overview  Students  Events  Logs  [Logout]  |
+-----------------------------------------------------------------------+
| Administrative Supervision & Real-Time Monitoring                    |
|                                                                       |
| OVERVIEW COUNTERS:                                                    |
| [ Total Students: 1,240 ]  [ Active Events: 8 ]  [ New Signups: 34 ]  |
|                                                                       |
| RECENT STUDENT ENROLLMENTS                                            |
| | Student ID | Name            | Department | Year | Status  | Action||
| | 24CE001    | Priya Sharma    | CE         | 3rd  | Active  | [View]||
| | 24IT042    | Rohan Verma     | IT         | 2nd  | Active  | [View]||
|                                                                       |
| SYSTEM AUDIT LOG PREVIEW                                              |
| • [10:14 AM] Student 24CE001 registered for HackCHARUSAT 2026        |
| • [09:30 AM] Admin published new notice: Mid-Sem Timetable            |
+-----------------------------------------------------------------------+
```

---

### 6. Project Directory Structure

```text
studenthub/
│
├── .gitignore                      # Git exclusion rules
├── README.md                       # Comprehensive Project Specification & Manual
│
├── index.html                      # 1. Portal Public Homepage
├── about.html                      # 2. About University, FTE, & StudentHub
├── events.html                     # 3. Campus Events & Workshops Showcase
├── faq.html                        # 4. Frequently Asked Questions
├── contact.html                    # 5. Campus Helpdesk & Inquiry Directory
├── feedback.html                   # 6. Student Feedback & Satisfaction Ratings
├── register.html                   # 7. Student Registration Form
├── login.html                      # 8. Unified Portal Login Gateway
│
├── student-dashboard.html          # 9. Authenticated Student Dashboard
├── student-profile.html            # 10. Student Profile & Digital ID Card
└── admin-dashboard.html            # 11. Administrative Oversight & Statistics
```

---

### 7. Viva Questions & Concept Clarifications (Practical 1)

#### Q1: What is a URL and what are its parts?
**Answer:**  
A **Uniform Resource Locator (URL)** is a standard web address used to locate and access a unique resource on the internet.  
**Parts of a URL:**
`https://www.charusat.edu.in:443/students/events.html?dept=ce&year=3#schedule`
- **Protocol / Scheme (`https`):** The communication protocol (Hypertext Transfer Protocol Secure).
- **Subdomain (`www`):** Secondary domain prefix.
- **Domain Name / Host (`charusat.edu.in`):** Registered address of the web server.
- **Port (`:443`):** Numerical network port (443 for HTTPS, 80 for HTTP). Usually implicit.
- **Path (`/students/events.html`):** Exact hierarchical directory path to the requested file on the server.
- **Query String (`?dept=ce&year=3`):** Key-value parameter pairs preceded by `?` and separated by `&`, used to pass data to the page.
- **Fragment / Anchor (`#schedule`):** Internal page anchor bookmark preceded by `#` that scrolls the browser to a specific element ID.

#### Q2: How is an HTML file processed in a web browser?
**Answer:**  
The browser rendering engine processes an HTML file via the **Critical Rendering Path**:
1. **Network Fetching & Bytes Conversion:** Browser receives raw bytes over HTTP/HTTPS and converts them into characters based on character encoding (UTF-8).
2. **Tokenization:** Browser parses characters into distinct HTML tokens (`<html>`, `<head>`, `<body>`, tags, attributes).
3. **DOM Tree Construction:** Tokens are converted into Node objects structured as a hierarchical tree called the **Document Object Model (DOM)**.
4. **CSSOM Tree Construction:** Linked stylesheets and `<style>` blocks are parsed into the **CSS Object Model (CSSOM)**.
5. **Render Tree Generation:** DOM and CSSOM are merged to form the **Render Tree**, which contains only visible nodes and their computed styles (`display: none` elements are excluded).
6. **Layout (Reflow):** Browser computes exact geometric positions and dimensions of each element on the viewport.
7. **Painting (Repaint) & Compositing:** Elements are rasterized into pixels on the screen, organized across GPU layers, and drawn.

#### Q3: How will page navigation flow be managed among all HTML pages?
**Answer:**  
- **Consistent Global Header & Navigation Menu:** Every public page shares a uniform `<header>` containing a `<nav>` with relative anchor hyperlinks (`href="events.html"`).
- **Role-Based Portals:** Public users access informational pages; authenticated students access `/student-dashboard.html` and `/student-profile.html`; administrators access `/admin-dashboard.html`.
- **Breadcrumb Navigation:** Multi-level pages include accessible breadcrumbs (`Home > Events > Register`) using `<nav aria-label="Breadcrumb">` to maintain spatial user context.
- **Visual Feedback:** Active links are marked using descriptive classes or `aria-current="page"` to indicate the current active page.

#### Q4: How will GitHub commits be maintained after each practical?
**Answer:**  
- **Atomic Commits:** Code for each practical is committed in logical, isolated units rather than a single massive dump.
- **Conventional Commit Messages:** Follow standard semantic formats, for example:
  - `feat(p1): initiate studenthub architecture, sitemap and wireframes`
  - `feat(p2): implement semantic html5 pages with accessibility tags`
- **Milestone Git Tags:** Each completed practical is tagged with a clear version label (`v1.0-p1`, `v1.0-p2`, etc.), allowing instructors and evaluators to inspect the state of the project at any specific laboratory milestone.

---

### 8. Git Versioning Checkpoint
- **Release:** Phase 1 (Version 1.0)
- **Tag:** `v1.0-p1-initiation`
