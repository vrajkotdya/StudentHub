# StudentHub — Complete Data Flow & Architecture Guide

This document provides a complete, crystal-clear map of **where every piece of data comes from**, **which PHP or JavaScript file processes it**, and **where that data goes** across the entire **StudentHub** project.

---

## 1. High-Level System Architecture Diagram

```mermaid
flowchart TD
    subgraph Frontend ["1. Frontend Pages (Browser)"]
        REG["register.html\n(Student Registration Form)"]
        LOG["login.html\n(Portal Login Form)"]
        EVT["events.html\n(Campus Events Catalog)"]
        FAQ["faq.html\n(FAQ Accordion)"]
        CON["contact.html\n(Contact Inquiry Form)"]
        FDB["feedback.html\n(Student Feedback Form)"]
    end

    subgraph Protected ["2. Logged-In Portal Pages (PHP Session Protected)"]
        DASH["dashboard.php\n(Student Dashboard)"]
        PROF["profile.php\n(My Profile View & Update)"]
        ADM["admin.php\n(Admin Student & Event CRUD)"]
        ESTU["edit_student.php / delete_student.php"]
        EEVT["edit_event.php / delete_event.php"]
    end

    subgraph Processors ["3. Backend PHP Processors"]
        PREG["process_register.php"]
        PLOG["process_login.php"]
        PCON["process_contact.php"]
        PFDB["process_feedback.php"]
        OUT["logout.php"]
    end

    subgraph Storage ["4. Data Storage Layer"]
        DB[("MySQL Database: studenthub_db\nTables: users, students, events, registrations")]
        JSON[("JSON Files: data/\nstudents.json, events.json, faqs.json,\ninquiries.json, feedback.json")]
        CSV[("CSV Logs: data/\ninquiries.csv, feedback.csv")]
        UPL[("File Uploads: uploads/posters/\nEvent Poster Images")]
        SESS[("PHP $_SESSION\nuser_id, email, role, name, roll_no")]
    end

    REG -->|POST Form Data| PREG
    PREG -->|INSERT user & student| DB
    PREG -->|sync_students_json()| JSON

    LOG -->|POST Credentials| PLOG
    PLOG -->|SELECT & password_verify()| DB
    PLOG -->|Writes Login State| SESS
    SESS -->|Protects & Personalizes| DASH
    SESS -->|Protects & Personalizes| PROF
    SESS -->|Protects Admin Role| ADM

    PROF -->|SELECT & UPDATE Student| DB
    PROF -->|sync_students_json()| JSON

    ADM -->|CRUD Students & Events| DB
    ADM -->|Upload Poster Image| UPL
    ADM -->|sync_students_json() / sync_events_json()| JSON
    ESTU -->|UPDATE / DELETE Student| DB
    EEVT -->|UPDATE / DELETE Event + Poster| DB

    EVT -->|JavaScript fetch()| JSON
    FAQ -->|JavaScript fetch()| JSON

    CON -->|POST Form Data| PCON
    PCON -->|Save Inquiry| JSON
    PCON -->|Append Row| CSV

    FDB -->|POST Form Data| PFDB
    PFDB -->|Save Feedback| JSON
    PFDB -->|Append Row| CSV
```

---

## 2. Master Data Flow Table (Page-by-Page Map)

| Page / Feature | Where Data Comes From (Source) | Processing File (JS / PHP) | Where Data Goes (Destination) |
| :--- | :--- | :--- | :--- |
| **Student Registration** (`register.html`) | User types Full Name, Email, Mobile, Course, Year, Gender, Password | 1. `js/validation.js` (Frontend check)<br>2. `process_register.php` (Backend save) | 1. **MySQL `users` table** (`username`, `email`, hashed `password`, `role='student'`)<br>2. **MySQL `students` table** (`user_id`, `roll_no`, `name`, `email`, `mobile`, `course`, `year`, `gender`)<br>3. **`data/students.json`** (via `sync_students_json()`) |
| **Portal Login** (`login.html`) | User selects Role (`student`/`admin`), types Email/Username & Password | `process_login.php` | 1. Reads from **MySQL `users`** & **`students`** tables<br>2. Verifies hash with `password_verify()`<br>3. Saves user info into **`$_SESSION`** (`user_id`, `email`, `role`, `name`, `roll_no`, `course`, `year`)<br>4. Redirects to `dashboard.php` (Student) or `admin.php` (Admin) |
| **Student Dashboard** (`dashboard.php`) | 1. **`$_SESSION`** (`user_id`, `email`)<br>2. **MySQL `students` table** (Student row)<br>3. **MySQL `events` table** (Top 3 upcoming events) | `dashboard.php` | Renders personalized student stat cards (Roll No, Course, Year, Status) and upcoming event cards on screen |
| **My Profile & Update** (`profile.php`) | **Read:** Loads logged-in student row from **MySQL `students` table** using `$_SESSION['email']`<br>**Write:** Student submits "Update My Profile" form (`name`, `mobile`, `course`, `year`, `gender`) | `profile.php` | 1. Updates row in **MySQL `students` table** (`UPDATE students SET ... WHERE id = ?`)<br>2. Updates **`$_SESSION`** (`name`, `course`, `year`)<br>3. Updates **`data/students.json`** (via `sync_students_json()`) |
| **Admin Student CRUD** (`admin.php`, `edit_student.php`, `delete_student.php`) | **Read:** `SELECT * FROM students` in MySQL<br>**Write:** Admin submits Quick Add Student, Edit Student, or clicks Delete | `admin.php`<br>`edit_student.php`<br>`delete_student.php` | 1. **MySQL `students` table** (`INSERT`, `UPDATE`, or `DELETE`)<br>2. **`data/students.json`** automatically rewritten from MySQL via `sync_students_json()` |
| **Admin Event CRUD & Poster Upload** (`admin.php`, `edit_event.php`, `delete_event.php`) | **Read:** `SELECT * FROM events` in MySQL<br>**Write:** Admin submits Event details + selects Poster Image file (`$_FILES['poster']`) | `admin.php`<br>`edit_event.php`<br>`delete_event.php` | 1. **Image File:** Saved to **`uploads/posters/poster_<timestamp>.<ext>`** using `move_uploaded_file()`<br>2. **MySQL `events` table:** Stores event details + relative file path in `poster` column<br>3. **`data/events.json`:** Automatically synced via `sync_events_json()` |
| **Public Events Catalog** (`events.html`) | Reads from **`data/events.json`** (which is automatically synced with MySQL `events`) | `js/events.js` (`fetch("data/events.json")`) | Renders event cards, poster images (`event.poster`), category badges, search results, sorting, and pagination in the browser |
| **FAQ Page** (`faq.html`) | Reads from **`data/faqs.json`** | `js/faq.js` (`fetch("data/faqs.json")`) | Renders searchable, filterable FAQ accordion items in the browser |
| **Contact Form** (`contact.html`) | User types Name, Email, Category, Subject, Message | `process_contact.php` | 1. Appends JSON object to **`data/inquiries.json`**<br>2. Appends row to **`data/inquiries.csv`**<br>3. Displays simple Success or Failed message |
| **Feedback Form** (`feedback.html`) | User types Name, Roll No, Category, Rating (1–5), Comments | `process_feedback.php` | 1. Appends JSON object to **`data/feedback.json`**<br>2. Appends row to **`data/feedback.csv`**<br>3. Displays simple Success or Failed message |
| **Events REST API** (`api/events.php`) | Frontend JavaScript calls `GET api/events.php?category=...&search=...` | `api/events.php` | Queries **MySQL `events` table**, outputs structured JSON (`status`, `count`, `data`) with `Content-Type: application/json` |
| **Students REST API** (`api/students.php`) | Frontend JavaScript calls `GET api/students.php?search=...&course=...` | `api/students.php` | Queries **MySQL `students` table**, outputs structured JSON array |
| **Portal Stats REST API** (`api/stats.php`) | Admin dashboard / mobile clients call `GET api/stats.php` | `api/stats.php` | Computes live KPI counts and course/category breakdowns from MySQL and outputs analytics JSON |
| **Logout** (`logout.php`) | User clicks **Log Out** button | `logout.php` | Clears `$_SESSION` (`session_unset()`, `session_destroy()`) and redirects to `login.html` |

---

## 3. MySQL Database Tables & Columns (`studenthub_db`)

All database connection logic is in **`php/db.php`** and schema definitions are in **`sql/schema.sql`**.

### Table 1: `users` (Authentication Accounts)
* **Where data comes from:** `process_register.php` (when a student registers) or `sql/schema.sql` (default admin seed).
* **Where data is used:** `process_login.php` reads this table to verify login credentials.

| Column | Type | Purpose |
| :--- | :--- | :--- |
| `id` | `INT AUTO_INCREMENT PRIMARY KEY` | Unique account ID (stored in `$_SESSION['user_id']` on login) |
| `username` | `VARCHAR(50) UNIQUE` | Login username (auto-generated from email prefix) |
| `email` | `VARCHAR(100) UNIQUE` | Registered college email address |
| `password` | `VARCHAR(255)` | 60-character **BCRYPT hash** from `password_hash()` |
| `role` | `ENUM('student', 'admin')` | Controls access (`student` -> `dashboard.php`, `admin` -> `admin.php`) |
| `created_at` | `TIMESTAMP` | Account creation timestamp |

---

### Table 2: `students` (Student Academic Profiles — Version 11 CRUD)
* **Where data comes from:** `process_register.php` (registration) and `admin.php` (Admin Quick Add).
* **Where data is updated/deleted:** `profile.php` (student self-update), `edit_student.php` (Admin edit), `delete_student.php` (Admin delete).
* **Where data is read:** `dashboard.php`, `profile.php`, `admin.php`, and synced to `data/students.json`.

| Column | Type | Purpose |
| :--- | :--- | :--- |
| `id` | `INT AUTO_INCREMENT PRIMARY KEY` | Unique student record ID |
| `user_id` | `INT NULL (FK -> users.id)` | Links student profile to their login account in `users` |
| `roll_no` | `VARCHAR(30) UNIQUE` | Generated roll number (e.g., `2026-CSE-001`) |
| `name` | `VARCHAR(100)` | Student's full name |
| `email` | `VARCHAR(100) UNIQUE` | Student's email address |
| `mobile` | `VARCHAR(15)` | 10-digit mobile phone number |
| `course` | `VARCHAR(50)` | Enrolled degree/course (`B.Tech CSE`, `BCA`, etc.) |
| `year` | `VARCHAR(20)` | Academic year (`1st Year` – `4th Year`) |
| `gender` | `VARCHAR(15)` | `male`, `female`, or `other` |
| `status` | `VARCHAR(20)` | Account standing (`Active`, `Probation`, `Inactive`) |

---

### Table 3: `events` (Campus Events & Posters — Version 12 CRUD)
* **Where data comes from:** `admin.php` (Create Event form with Poster Upload) and `sql/schema.sql`.
* **Where data is updated/deleted:** `edit_event.php` and `delete_event.php`.
* **Where data is read:** `admin.php`, `dashboard.php`, and synced to `data/events.json` (which feeds `events.html`).

| Column | Type | Purpose |
| :--- | :--- | :--- |
| `id` | `INT AUTO_INCREMENT PRIMARY KEY` | Unique event ID |
| `title` | `VARCHAR(150)` | Event title |
| `category` | `VARCHAR(50)` | Category slug (`technical`, `cultural`, `sports`, `seminar`, `workshop`) |
| `event_date` | `DATE` | Event date (`YYYY-MM-DD`) |
| `time_slot` | `VARCHAR(50)` | Time duration (e.g., `10:00 AM - 4:00 PM`) |
| `venue` | `VARCHAR(150)` | Campus location / auditorium |
| `description` | `TEXT` | Event summary description |
| `poster` | `VARCHAR(255) DEFAULT NULL` | Relative file path to uploaded poster (`uploads/posters/poster_...`) |
| `seats_available` | `INT DEFAULT 50` | Maximum available participant seats |
| `status` | `ENUM('Open','Closed','Cancelled')` | Event status |

---

## 4. PHP Session & Remember Me Cookie Architecture

When a user logs in through **`process_login.php`**, the server configures session lifetime and stores state:

### A. Session Cookie Lifetime Behavior:
- **If "Remember my login" is UNCHECKED:** The session cookie expires when the browser window closes (`lifetime = 0`). The user stays logged in across pages, but closing the browser window logs them out automatically.
- **If "Remember my login" is CHECKED:** The session cookie expires in **30 days** (`lifetime = 30 * 86400`), persisting across browser window restarts.
- **On Explicit Logout (`logout.php`):** Both `session_destroy()` and `setcookie(session_name(), '', time() - 42000)` are executed to wipe both server-side session memory and browser-side cookies immediately.

### B. Session Variables Stored:

| Session Variable | Set In | Used In | Purpose |
| :--- | :--- | :--- | :--- |
| `$_SESSION['user_id']` | `process_login.php` | `dashboard.php`, `profile.php`, `admin.php` | Proves the user is logged in; if missing, redirects to `login.html` |
| `$_SESSION['role']` | `process_login.php` | `admin.php`, `edit_student.php`, `edit_event.php` | Verifies `'admin'` role before allowing access to Admin CRUD pages |
| `$_SESSION['email']` | `process_login.php` | `dashboard.php`, `profile.php`, `admin.php` | Looks up the logged-in student's exact row in the `students` table |
| `$_SESSION['name']` | `process_login.php`, `profile.php` | `dashboard.php`, `profile.php` | Displays the logged-in student's name in the header badge & welcome banner |
| `$_SESSION['roll_no']` | `process_login.php` | `dashboard.php`, `profile.php` | Displays the student's Roll Number |
| `$_SESSION['course']` | `process_login.php`, `profile.php` | `dashboard.php`, `profile.php` | Displays the student's Course |
| `$_SESSION['year']` | `process_login.php`, `profile.php` | `dashboard.php`, `profile.php` | Displays the student's Academic Year |

---

## 5. How MySQL and JSON Files Stay Synchronized

To keep the project simple while supporting both **MySQL (Versions 8–12)** and **JavaScript Fetch API (`events.html` in Version 6)**, `php/db.php` includes two automatic sync functions:

1. **`sync_students_json()`**
   * Called automatically after **Register (`process_register.php`)**, **Profile Update (`profile.php`)**, **Admin Add/Edit/Delete Student (`admin.php`, `edit_student.php`, `delete_student.php`)**.
   * Queries `SELECT * FROM students` from MySQL and writes the updated array to `data/students.json`.

2. **`sync_events_json()`**
   * Called automatically after **Admin Add/Edit/Delete Event (`admin.php`, `edit_event.php`, `delete_event.php`)**.
   * Queries `SELECT * FROM events` from MySQL and writes the updated array (including the `poster` image path) to `data/events.json` so `events.html` immediately displays any new or edited events and their posters.
