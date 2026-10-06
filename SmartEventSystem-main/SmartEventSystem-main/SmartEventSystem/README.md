# SmartEventSystem

## Event Management System

SmartEventSystem is a web-based Event Management System developed using **PHP, MySQL, HTML, CSS, JavaScript, AngularJS, and Perl**.

The system helps administrators create and manage events, while users can register for events and view their registered events. Administrators can also generate event and registration reports.

---

## 1. Project Overview

The main purpose of SmartEventSystem is to provide a simple and efficient platform for managing events and participant registrations.

The system provides separate functionality for:

* Administrators
* Registered Users

Administrators can manage events and generate reports, while users can register for available events and manage their profiles.

---

## 2. Technologies Used

| Technology | Purpose                             |
| ---------- | ----------------------------------- |
| PHP        | Server-side application development |
| MySQL      | Database management                 |
| HTML5      | Web page structure                  |
| CSS3       | Website styling                     |
| JavaScript | Client-side functionality           |
| AngularJS  | Front-end framework support         |
| Perl       | Report generation                   |
| XAMPP      | Local development server            |
| MySQLi     | PHP-MySQL database connectivity     |

---

## 3. Main Features

### Admin Features

* Admin login
* Admin dashboard
* Add new events
* Edit existing events
* Delete events
* View all events
* Manage event status
* View event registrations
* Generate event reports
* Export reports as CSV
* Generate registration summary
* Generate attendance reports

### User Features

* User registration
* User login
* User dashboard
* View available events
* Register for events
* View registered events
* View and update profile
* Logout

---

## 4. Project Modules

### 4.1 Authentication Module

The authentication module manages:

* User login
* Session management
* Logout
* Role-based access
* Admin authentication
* User authentication

The system uses PHP sessions to maintain logged-in users.

---

### 4.2 Event Management Module

The event management module allows administrators to:

* Create events
* Update events
* Delete events
* View events
* Set event category
* Set event date and time
* Set venue
* Set maximum participants
* Set registration deadline
* Set event status
* Upload event images

---

### 4.3 User Management Module

The user module allows users to:

* Create an account
* Login
* View their profile
* Update personal information
* View registered events

---

### 4.4 Registration Module

Users can register for available events.

Registration information is stored in the `registrations` table and connected with:

* User
* Event
* Registration date
* Registration status

---

### 4.5 Reports Module

The reports module allows administrators to generate reports based on event information.

Available reports include:

* Event report
* Registration report
* Attendance report
* Registration summary

Reports can also be exported as CSV files.

---

### 4.6 Perl Reporting Module

Perl is used to generate command-line reports from the MySQL database.

The Perl module contains:

```text
attendance_report.pl
registration_summary.pl
```

---

## 5. Database

The project uses a MySQL database named:

```text
eventdb
```

The database contains tables required for users, events, and registrations.

### Users Table

Important fields:

```text
id
name
email
phone
password
role
created_at
```

### Events Table

Important fields:

```text
id
event_name
description
category
event_date
start_time
end_time
venue
max_participants
registration_deadline
image
status
created_at
```

### Registrations Table

The registration module uses:

```text
id
user_id
event_id
registration_date
status
```

---

## 6. Project Folder Structure

```text
SmartEventSystem/
│
├── index.php
├── login.php
├── logout.php
├── dashboard.php
│
├── css/
│   ├── style.css
│   └── login.css
│
├── js/
│   ├── validation.js
│   ├── app.js
│   └── angular.min.js
│
├── config/
│   └── db.php
│
├── admin/
│   ├── add_event.php
│   ├── edit_event.php
│   ├── delete_event.php
│   └── view_events.php
│
├── user/
│   ├── register.php
│   ├── myevents.php
│   └── profile.php
│
├── reports/
│   ├── report.php
│   └── export.php
│
├── perl/
│   ├── attendance_report.pl
│   └── registration_summary.pl
│
├── database/
│   └── eventdb.sql
│
└── README.md
```

---

## 7. Requirements

Before running the project, install the following:

* XAMPP
* Apache
* MySQL
* PHP
* Perl
* MySQL Perl DBI module
* Web browser

Recommended browsers:

* Google Chrome
* Microsoft Edge
* Mozilla Firefox

---

## 8. Installation

### Step 1: Install XAMPP

Install XAMPP on your computer.

Start:

```text
Apache
MySQL
```

from the XAMPP Control Panel.

---

### Step 2: Copy the Project

Copy the `SmartEventSystem` folder into the XAMPP `htdocs` directory.

Example:

```text
C:\xampp\htdocs\SmartEventSystem
```

---

### Step 3: Create the Database

Open:

```text
http://localhost/phpmyadmin
```

Create a database named:

```text
eventdb
```

---

### Step 4: Import the SQL File

In phpMyAdmin:

1. Select the `eventdb` database.
2. Click **Import**.
3. Select:

```text
database/eventdb.sql
```

4. Click **Import** or **Go**.

The required tables will be created.

---

## 9. Database Configuration

Open:

```text
config/db.php
```

Make sure the database configuration matches your MySQL setup.

Example:

```php
<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "eventdb";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die(
        "Database connection failed: " .
        $conn->connect_error
    );
}

$conn->set_charset("utf8mb4");

?>
```

If your MySQL username, password, or database name is different, update the values accordingly.

---

## 10. Running the Application

After starting Apache and MySQL, open the browser.

Use:

```text
http://localhost/SmartEventSystem/
```

The application will open through `index.php`.

---

## 11. Login

Users can access the login page through:

```text
http://localhost/SmartEventSystem/login.php
```

After successful login, the system redirects the user to the appropriate dashboard based on their role.

Roles:

```text
admin
user
```

---

## 12. Admin Operations

An administrator can:

1. Login to the system.
2. Open the dashboard.
3. Add events.
4. Edit events.
5. Delete events.
6. View events.
7. Generate reports.
8. Export reports.

Admin pages are located in:

```text
admin/
```

and:

```text
reports/
```

---

## 13. User Operations

A normal user can:

1. Register an account.
2. Login.
3. View events.
4. Register for an event.
5. View registered events.
6. Update profile information.
7. Logout.

User pages are located in:

```text
user/
```

---

## 14. Reports

The report page is:

```text
reports/report.php
```

Administrators can filter reports using:

* Event
* Start date
* End date
* Event status

Reports can be exported using:

```text
reports/export.php
```

The exported file is generated in CSV format.

---

## 15. Perl Reports

### Attendance Report

Run:

```bash
perl perl/attendance_report.pl
```

This report displays participant registration information for events.

---

### Registration Summary

Run:

```bash
perl perl/registration_summary.pl
```

This report displays:

* Event name
* Category
* Event date
* Venue
* Maximum participants
* Number of registrations
* Overall registration count

---

## 16. JavaScript

The project uses JavaScript for client-side functionality.

Main JavaScript files:

```text
js/validation.js
js/app.js
```

The JavaScript functionality includes:

* Form validation
* Required field checking
* Search functionality
* Alert messages
* Delete confirmation
* Password visibility
* Event date validation
* Event time validation
* Participant capacity validation
* Registration confirmation

---

## 17. AngularJS

AngularJS is included in:

```text
js/angular.min.js
```

It can be used for client-side application functionality and future enhancements.

AngularJS should be loaded before application-specific AngularJS code.

Example:

```html
<script src="js/angular.min.js"></script>
<script src="js/app.js"></script>
```

For pages inside subfolders:

```html
<script src="../js/angular.min.js"></script>
<script src="../js/app.js"></script>
```

---

## 18. Security Features

The project includes basic security practices such as:

* PHP session authentication
* Role-based authorization
* Prepared SQL statements
* Input validation
* Email validation
* Password fields
* Login protection
* Admin access protection
* Confirmation before deleting events

Prepared statements are used to reduce the risk of SQL injection.

---

## 19. Event Status

Events can have different statuses:

```text
upcoming
ongoing
completed
cancelled
```

The status is stored in the `events` table.

---

## 20. Future Enhancements

The following features can be added in future versions:

* Email notifications
* OTP verification
* Online payment
* QR code event registration
* QR code attendance
* Automatic email reminders
* Event calendar
* Advanced analytics dashboard
* PDF report generation
* Admin activity logs
* Password reset
* User profile image
* Mobile responsive improvements

---

## 21. Project Benefits

SmartEventSystem provides a centralized platform for managing events and registrations.

### Benefits for Administrators

* Easy event management
* Centralized registration information
* Faster report generation
* Participant tracking
* Reduced manual work

### Benefits for Users

* Easy event registration
* Quick access to event information
* Registered event tracking
* Simple profile management

---

## 22. Conclusion

SmartEventSystem provides a complete foundation for managing events, users, registrations, and reports.

By combining PHP, MySQL, JavaScript, AngularJS, and Perl, the project demonstrates web application development, database management, client-side scripting, server-side programming, and report generation.

The system can be further extended with advanced features such as online payments, QR-based attendance, email notifications, and analytics.

---

## 23. Developer

**Project Name:** SmartEventSystem

**Project Type:** Event Management System

**Technologies:** PHP, MySQL, HTML, CSS, JavaScript, AngularJS, Perl

**Database:** MySQL

**Development Environment:** XAMPP
