# StudentHub — Page Wireframes

This document provides simple wireframe layouts for the primary page types of StudentHub.

---

## 1. General Public Page Layout (`index.html`, `about.html`, `contact.html`, `faq.html`)

```text
+------------------------------------------------------------------+
|  LOGO / BRAND: StudentHub         [Home] [About] [Events] [Login]|
+------------------------------------------------------------------+
|                                                                  |
|  HERO / WELCOME BANNER                                           |
|  "Welcome to StudentHub — Your Campus Portal"                    |
|  [Explore Events]  [Register Now]                                |
|                                                                  |
+------------------------------------------------------------------+
|  MAIN CONTENT SECTION                                            |
|                                                                  |
|  +-----------------------+  +----------------------------------+ |
|  | Announcements & News  |  | Quick Services                   | |
|  | - Exam schedule out   |  | [Check Profile]                  | |
|  | - Tech Fest 2026      |  | [Campus Events]                  | |
|  | - Library notice      |  | [Submit Feedback]                | |
|  +-----------------------+  +----------------------------------+ |
|                                                                  |
|  +-------------------------------------------------------------+ |
|  | Key Highlights / Features Cards                             | |
|  +-------------------------------------------------------------+ |
+------------------------------------------------------------------+
|  FOOTER: Links | Contact Info | Copyright © 2026 StudentHub      |
+------------------------------------------------------------------+
```

---

## 2. Authentication & Form Layout (`register.html`, `login.html`, `feedback.html`)

```text
+------------------------------------------------------------------+
|  LOGO: StudentHub                 [Home] [About] [Events] [Login]|
+------------------------------------------------------------------+
|                                                                  |
|                     +-----------------------------+              |
|                     |       FORM TITLE            |              |
|                     |   (e.g., Student Register)  |              |
|                     +-----------------------------+              |
|                     | Full Name:                  |              |
|                     | [                         ] |              |
|                     | Email Address:              |              |
|                     | [                         ] |              |
|                     | Mobile / Phone:             |              |
|                     | [                         ] |              |
|                     | Course & Year:              |              |
|                     | [ Dropdown v ]  [ Dropdown v]|              |
|                     | Password:                   |              |
|                     | [                         ] |              |
|                     | Confirm Password:           |              |
|                     | [                         ] |              |
|                     | [x] Accept Terms            |              |
|                     |                             |              |
|                     | [       SUBMIT BUTTON     ] |              |
|                     |                             |              |
|                     | Already registered? [Login] |              |
|                     +-----------------------------+              |
|                                                                  |
+------------------------------------------------------------------+
|  FOOTER: Links | Copyright © 2026 StudentHub                     |
+------------------------------------------------------------------+
```

---

## 3. Student Dashboard Layout (`dashboard.html`)

```text
+------------------------------------------------------------------+
|  LOGO: StudentHub        [Dashboard] [Profile] [Events] [Logout] |
+------------------------------------------------------------------+
|  WELCOME BAR: "Welcome back, [Student Name] | Roll No: 2026-CS-042"|
+------------------------------------------------------------------+
|  +-------------------------+  +--------------------------------+ |
|  | Profile Summary Card    |  | Academic Overview              | |
|  | - Course: B.Tech CSE    |  | - Current Semester: 6          | |
|  | - Year: 3rd Year        |  | - Attendance: 88%              | |
|  | [Edit Profile]          |  | [View Details]                 | |
|  +-------------------------+  +--------------------------------+ |
|                                                                  |
|  +-------------------------------------------------------------+ |
|  | Upcoming Registered Events                                  | |
|  | - Web Dev Hackathon (Sep 20, 2026)                          | |
|  | - Annual Sports Meet (Oct 05, 2026)                         | |
|  +-------------------------------------------------------------+ |
|                                                                  |
|  +-------------------------------------------------------------+ |
|  | Recent Campus Notices / Announcements                        | |
|  +-------------------------------------------------------------+ |
+------------------------------------------------------------------+
|  FOOTER: Links | Support | Copyright © 2026 StudentHub           |
+------------------------------------------------------------------+
```

---

## 4. Events Listing Layout (`events.html`)

```text
+------------------------------------------------------------------+
|  LOGO: StudentHub                 [Home] [About] [Events] [Login]|
+------------------------------------------------------------------+
|  PAGE TITLE: Campus Events & Activities                          |
|  Search: [_____________]  Category: [ All Categories v ]  [Filter]|
+------------------------------------------------------------------+
|  +--------------------+  +--------------------+  +---------------+
|  | [Event Poster/Img] |  | [Event Poster/Img] |  | [Event Poster]|
|  | Hackathon 2026     |  | Cultural Fest 2026 |  | Robotics Expo |
|  | Date: Sep 20, 2026 |  | Date: Oct 12, 2026 |  | Date: Nov 01  |
|  | Venue: Lab 3       |  | Venue: Auditorium  |  | Venue: Hall B |
|  | [Register Event]   |  | [Register Event]   |  | [Register]    |
|  +--------------------+  +--------------------+  +---------------+
+------------------------------------------------------------------+
|  PAGINATION: [ < Previous ]  [ 1 ]  [ 2 ]  [ Next > ]             |
+------------------------------------------------------------------+
|  FOOTER: Links | Copyright © 2026 StudentHub                     |
+------------------------------------------------------------------+
```

---

## 5. Admin Dashboard Layout (`admin.html`)

```text
+------------------------------------------------------------------+
|  LOGO: StudentHub Admin    [Overview] [Students] [Events] [Logout]|
+------------------------------------------------------------------+
|  HEADER: Administrative Control Panel                            |
+------------------------------------------------------------------+
|  METRICS STATS CARDS:                                            |
|  +----------------+  +----------------+  +--------------------+  |
|  | Total Students |  | Active Events  |  | Total Registrations|  |
|  |      1,240     |  |       14       |  |        512         |  |
|  +----------------+  +----------------+  +--------------------+  |
+------------------------------------------------------------------+
|  MANAGEMENT SHORTCUTS:                                           |
|  [+ Add New Event]   [+ Register Student]   [Download Report]    |
+------------------------------------------------------------------+
|  RECENT REGISTRATIONS TABLE:                                     |
|  +----+--------------+-------------------+-------------+-------+ |
|  | ID | Student Name | Event Registered  | Date        | Action| |
|  +----+--------------+-------------------+-------------+-------+ |
|  | 01 | John Doe     | Hackathon 2026    | Sep 14, 2026| [View]| |
|  | 02 | Jane Smith   | Robotics Expo     | Sep 14, 2026| [View]| |
|  +----+--------------+-------------------+-------------+-------+ |
+------------------------------------------------------------------+
|  FOOTER: Admin Session Active | Copyright © 2026 StudentHub      |
+------------------------------------------------------------------+
```
