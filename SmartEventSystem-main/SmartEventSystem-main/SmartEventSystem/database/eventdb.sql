CREATE DATABASE IF NOT EXISTS eventdb;

USE eventdb;

-- =========================================
-- USERS TABLE
-- =========================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(15),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================
-- EVENTS TABLE
-- =========================================

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(150) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    event_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME,
    venue VARCHAR(200) NOT NULL,
    max_participants INT DEFAULT 100,
    registration_deadline DATE,
    image VARCHAR(255),
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled')
           DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================
-- REGISTRATIONS TABLE
-- =========================================

CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'cancelled') DEFAULT 'registered',

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (event_id)
        REFERENCES events(id)
        ON DELETE CASCADE,

    UNIQUE KEY unique_registration (user_id, event_id)
);


-- =========================================
-- ATTENDANCE TABLE
-- =========================================

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    attendance_status ENUM('present', 'absent')
                       DEFAULT 'absent',
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (registration_id)
        REFERENCES registrations(id)
        ON DELETE CASCADE
);


-- =========================================
-- SAMPLE ADMIN
-- =========================================

INSERT INTO users
(name, email, phone, password, role)
VALUES
(
    'System Admin',
    'admin@gmail.com',
    '0987654321',
    'admin@123',
    'admin'
);


-- =========================================
-- SAMPLE USER
-- =========================================

INSERT INTO users
(name, email, phone, password, role)
VALUES
(
    'Test User',
    'user@gmail.com',
    '1234567890',
    'user@123',
    'user'
);


-- =========================================
-- SAMPLE EVENTS
-- =========================================

INSERT INTO events
(
    event_name,
    description,
    category,
    event_date,
    start_time,
    end_time,
    venue,
    max_participants,
    registration_deadline,
    status
)
VALUES
(
    'Technical Symposium 2026',
    'A technical event featuring coding, paper presentation and technical competitions.',
    'Technical',
    '2026-09-20',
    '09:00:00',
    '17:00:00',
    'College Auditorium',
    200,
    '2026-09-18',
    'upcoming'
),

(
    'Web Development Workshop',
    'Hands-on workshop covering modern web development technologies.',
    'Workshop',
    '2026-09-25',
    '10:00:00',
    '16:00:00',
    'Computer Lab',
    100,
    '2026-09-23',
    'upcoming'
),

(
    'Cultural Fest 2026',
    'Annual college cultural festival with various competitions and performances.',
    'Cultural',
    '2026-09-30',
    '09:30:00',
    '18:00:00',
    'Open Auditorium',
    500,
    '2026-09-28',
    'upcoming'
);