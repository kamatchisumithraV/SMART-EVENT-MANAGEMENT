# Smart Event Management and Registration System with Automated Reporting

## Project Overview

The **Smart Event Management and Registration System with Automated Reporting** is a web-based application developed to simplify event management and online registration. Administrators can create and manage events, while users can view available events and register online. The system stores user, event and registration details in a MySQL database and provides automated reports using Perl.

## Objectives

* To provide an easy platform for event management.
* To allow users to register for events online.
* To store event and registration details securely.
* To prevent duplicate registrations.
* To manage event capacity and registration deadlines.
* To generate registration and attendance reports automatically.

## Technologies Used

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Database:** MySQL
* **Reporting:** Perl
* **Server:** XAMPP / Apache

## Main Modules

* **User Registration & Login:** Allows users to create accounts and securely log in.
* **Event Management:** Administrators can add, edit, view and delete events.
* **Event Registration:** Users can register for available events.
* **My Events:** Users can view their registered events.
* **User Profile:** Users can update their personal information.
* **Reporting:** Administrators can view registration and event reports.
* **Perl Reporting:** Perl scripts generate registration summary and attendance reports.
* **CSV Export:** Reports can be exported as CSV files.

## Database

The project uses a MySQL database named **`eventdb`**. The main tables are `users`, `events`, `registrations` and `attendance`. The database SQL file is available in the `database/eventdb.sql` file.

## Installation

1. Install XAMPP and start **Apache** and **MySQL**.
2. Copy the project folder into the XAMPP `htdocs` directory.
3. Create a MySQL database named **`eventdb`**.
4. Import `database/eventdb.sql` into the database.
5. Configure the database connection in `config/db.php`.
6. Open the project using:

```text
http://localhost/SmartEventSystem/
```

## Team Contribution

This project was developed by **three team members**. One member worked mainly on the **front-end and user interface**, the second member handled **PHP backend and MySQL database development**, and the third member worked on **Perl reporting, CSV export, testing and documentation**.

## Future Enhancements

Future improvements may include **email notifications, QR-code attendance, certificate generation, event reminders, mobile-friendly design and advanced graphical reports**.

## Conclusion

The project provides a simple and efficient solution for managing events and registrations. It reduces manual work and combines **PHP, MySQL, JavaScript and Perl** to provide event management, online registration and automated reporting in a single system.

## GitHub Repository

**GitHub URL:** Add your repository URL here.
