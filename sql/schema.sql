-- ==============================================================================
-- StudentHub — Database Schema & Initial Seed Data (sql/schema.sql)
-- Version 9: Secure Registration & Password Hashing
-- Compatible with MySQL 8.x and MariaDB 10.x / 11.x
-- ==============================================================================

-- 1. Create Database
CREATE DATABASE IF NOT EXISTS `studenthub_db`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub_db`;

-- Disable foreign key checks during table creation/reset
SET FOREIGN_KEY_CHECKS = 0;

-- 2. Drop existing tables if re-running
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- Table: users
-- Purpose: System login credentials and role management (student, admin)
-- Passwords stored as secure BCRYPT hashes (generated via password_hash)
-- ==============================================================================
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- Table: students
-- Purpose: Academic and personal records of registered college students
-- Relationship: Many-to-One with users (user_id -> users.id)
-- ==============================================================================
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `roll_no` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `mobile` VARCHAR(15) NOT NULL,
    `course` VARCHAR(50) NOT NULL,
    `year` VARCHAR(20) NOT NULL,
    `gender` ENUM('male', 'female', 'other') NOT NULL,
    `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_students_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- Table: events
-- Purpose: Campus workshops, cultural festivals, seminars, and sports meets
-- ==============================================================================
CREATE TABLE `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `event_date` DATE NOT NULL,
    `time_slot` VARCHAR(50) NOT NULL,
    `venue` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `seats_available` INT NOT NULL DEFAULT 50,
    `status` ENUM('Open', 'Closed', 'Cancelled') NOT NULL DEFAULT 'Open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- Table: registrations
-- Purpose: Event registrations linking students and events (Many-to-Many)
-- Relationships:
--   - student_id -> students.id (CASCADE on delete)
--   - event_id   -> events.id   (CASCADE on delete)
-- ==============================================================================
CREATE TABLE `registrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('Confirmed', 'Pending', 'Cancelled') NOT NULL DEFAULT 'Confirmed',
    CONSTRAINT `fk_registrations_student`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_registrations_event`
        FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `uq_student_event` UNIQUE (`student_id`, `event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- Initial Seed Data
-- ==============================================================================

-- 1. Insert Default Users (Hashed passwords for Version 9 security standard)
-- Default passwords:
--   admin -> 'admin123'
--   students -> 'student123'
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'admin', 'admin@studenthub.edu', '$2y$10$nqx8mIG8XyKHiyUrdjhwQOIIXoQW5/vTnOIbDzqCS.0cA2idpR2zO', 'admin'),
(2, 'alex.johnson', 'alex.johnson@studenthub.edu', '$2y$10$A9WC4VRjfTDYmaU3QS9HAOJKjYKidmvEBZpKETEyCbb.hOeWo2Kj2', 'student'),
(3, 'priya.sharma', 'priya.sharma@studenthub.edu', '$2y$10$A9WC4VRjfTDYmaU3QS9HAOJKjYKidmvEBZpKETEyCbb.hOeWo2Kj2', 'student'),
(4, 'rohan.patel', 'rohan.patel@studenthub.edu', '$2y$10$A9WC4VRjfTDYmaU3QS9HAOJKjYKidmvEBZpKETEyCbb.hOeWo2Kj2', 'student'),
(5, 'meera.nair', 'meera.nair@studenthub.edu', '$2y$10$A9WC4VRjfTDYmaU3QS9HAOJKjYKidmvEBZpKETEyCbb.hOeWo2Kj2', 'student');

-- 2. Insert Students (Migrated from students.json)
INSERT INTO `students` (`id`, `user_id`, `roll_no`, `name`, `email`, `mobile`, `course`, `year`, `gender`, `status`) VALUES
(1, 2, '2026-CS-042', 'Alex Johnson', 'alex.johnson@studenthub.edu', '9876543201', 'B.Tech CSE', '3rd Year', 'male', 'Active'),
(2, 3, '2026-IT-018', 'Priya Sharma', 'priya.sharma@studenthub.edu', '9876543202', 'B.Tech IT', '2nd Year', 'female', 'Active'),
(3, 4, '2026-EC-055', 'Rohan Patel', 'rohan.patel@studenthub.edu', '9876543203', 'B.Tech ECE', '4th Year', 'male', 'Active'),
(4, 5, '2026-BCA-009', 'Meera Nair', 'meera.nair@studenthub.edu', '9876543204', 'BCA', '1st Year', 'female', 'Active'),
(5, NULL, '2026-CS-089', 'Sneha Reddy', 'sneha.reddy@studenthub.edu', '9876543205', 'B.Tech CSE', '2nd Year', 'female', 'Active'),
(6, NULL, '2026-IT-034', 'David Miller', 'david.miller@studenthub.edu', '9876543206', 'B.Tech IT', '3rd Year', 'male', 'Active'),
(7, NULL, '2026-MCA-012', 'Ananya Roy', 'ananya.roy@studenthub.edu', '9876543207', 'MCA', '1st Year', 'female', 'Active'),
(8, NULL, '2026-CS-105', 'Kabir Verma', 'kabir.verma@studenthub.edu', '9876543208', 'B.Tech CSE', '4th Year', 'male', 'Active'),
(9, NULL, '2026-BSC-021', 'Tanvi Joshi', 'tanvi.joshi@studenthub.edu', '9876543209', 'B.Sc CS', '2nd Year', 'female', 'Active'),
(10, NULL, '2026-EC-072', 'Vikram Singh', 'vikram.singh@studenthub.edu', '9876543210', 'B.Tech ECE', '3rd Year', 'male', 'Active'),
(11, NULL, '2026-BCA-033', 'Aarav Gupta', 'aarav.gupta@studenthub.edu', '9876543211', 'BCA', '3rd Year', 'male', 'Active'),
(12, NULL, '2026-CS-015', 'Neha Kulkarni', 'neha.kulkarni@studenthub.edu', '9876543212', 'B.Tech CSE', '1st Year', 'female', 'Active');

-- 3. Insert Events (Migrated from events.json)
INSERT INTO `events` (`id`, `title`, `category`, `event_date`, `time_slot`, `venue`, `description`, `seats_available`, `status`) VALUES
(1, 'Web Development Hackathon 2026', 'Technical', '2026-09-20', '9:00 AM – 5:00 PM', 'Computer Science Lab 3, Block B', 'An 8-hour sprint building real-world web applications. Cash prizes and certificates for top three teams.', 30, 'Open'),
(2, 'Annual Cultural Fest — Spandan 2026', 'Cultural', '2026-10-12', '10:00 AM onwards', 'College Main Auditorium', 'Three days of music, dance, theater, street plays, and fine art exhibitions celebrating student creativity.', 250, 'Open'),
(3, 'Robotics & AI Embedded Systems Workshop', 'Workshop', '2026-10-25', '11:00 AM – 4:00 PM', 'Electronics Seminar Hall, 1st Floor', 'Hands-on workshop on microcontroller programming, computer vision basics, and autonomous sensor robotics.', 45, 'Open'),
(4, 'Inter-Department Cricket Championship', 'Sports', '2026-11-05', '8:30 AM onwards', 'College Sports Ground', 'Annual inter-department knockout tournament. Department captains must register full playing squads.', 16, 'Open'),
(5, 'Cybersecurity & Ethical Hacking Seminar', 'Seminar', '2026-11-18', '2:00 PM – 4:30 PM', 'Auditorium Hall B', 'Industry experts break down modern threat models, penetration testing fundamentals, and bug bounty hunting.', 80, 'Open'),
(6, 'Data Science & Python Bootcamp', 'Workshop', '2026-11-28', '10:00 AM – 3:00 PM', 'Data Analytics Lab, Block C', 'Learn exploratory data analysis, Pandas, Matplotlib, and scikit-learn models through beginner-friendly projects.', 40, 'Open'),
(7, 'Annual Photography & Digital Art Exhibition', 'Cultural', '2026-12-05', '11:00 AM – 6:00 PM', 'Central Library Gallery', 'Showcase of photography, digital paintings, and campus life portraits submitted by students across all years.', 100, 'Open'),
(8, 'Inter-College Chess Championship 2026', 'Sports', '2026-12-12', '9:30 AM – 4:00 PM', 'Indoor Sports Complex', 'FIDE-rated blitz and rapid tournaments. Medals and trophies awarded to top collegiate contenders.', 64, 'Open'),
(9, 'Cloud Computing with AWS & Docker Workshop', 'Technical', '2026-12-18', '1:00 PM – 5:00 PM', 'Computer Science Lab 1, Block B', 'Practical guide to containerizing web apps, configuring CI/CD pipelines, and hosting on cloud instances.', 35, 'Open');

-- 4. Insert Sample Registrations (Testing Many-to-Many relationship)
INSERT INTO `registrations` (`id`, `student_id`, `event_id`, `registration_date`, `status`) VALUES
(1, 1, 1, '2026-09-14 10:30:00', 'Confirmed'),
(2, 2, 3, '2026-09-14 11:15:00', 'Confirmed'),
(3, 3, 4, '2026-09-13 14:00:00', 'Confirmed'),
(4, 5, 1, '2026-09-13 16:45:00', 'Confirmed'),
(5, 1, 5, '2026-09-15 09:20:00', 'Confirmed'),
(6, 4, 2, '2026-09-15 12:10:00', 'Confirmed');
