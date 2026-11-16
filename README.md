# Court Case Management System (CCMS)

## 1. Project Overview

The **Court Case Management System (CCMS)** is a web-based application developed to manage court-related information and records efficiently. It helps administrators and staff maintain client details, manage cases, schedule hearings, upload documents, and generate reports through a centralized system.

The project is developed using PHP and MySQL and is designed to run locally using XAMPP.

## 2. Project Objectives

- Maintain client information digitally.
- Register and manage court cases.
- Schedule and manage hearing dates.
- Upload, view, download, and manage case documents.
- Provide reports and dashboard analytics.
- Control access through administrator and staff roles.
- Improve the organization and accessibility of court records.

## 3. Technologies Used

| Technology | Purpose                  |
| ---------- | ------------------------ |
| PHP        | Backend development      |
| MySQL      | Database management      |
| HTML       | Web page structure       |
| CSS        | Styling and layout       |
| JavaScript | Client-side interactions |
| XAMPP      | Local development server |
| phpMyAdmin | Database administration  |

## 4. Main Features and Modules

### Dashboard

- Displays an overview of system information.
- Provides access to the main modules.

### User Authentication

- Login system for registered users.
- Role-based access for administrators and staff.
- Password hashing and session management.

### Client Management

- Register new clients.
- View and update client information.
- Search client records.
- Delete client records when permitted.

### Case Management

- Create and manage court cases.
- Maintain case-related information.
- Associate cases with registered clients.

### Hearing Management

- Schedule court hearings.
- Maintain hearing dates and related case information.
- View hearing records.

### Document Management

- Upload case-related documents.
- Preview and download documents.
- Delete documents when permitted.

### Reports and Analytics

- View system reports and statistics.
- Monitor case and hearing information.

## 5. System Requirements

**Software requirements**

- Windows operating system
- XAMPP
- PHP
- MySQL or MariaDB
- A modern web browser
- Code editor (Visual Studio Code recommended)

## 6. Installation and Setup

Follow these steps to run the project locally.

### Step 1: Install XAMPP

Download and install XAMPP on your computer.

Start the following services from the XAMPP Control Panel:

- Apache
- MySQL

### Step 2: Copy the project

1. Extract the project folder.
2. Copy the folder into the XAMPP `htdocs` directory.
3. Keep the project folder name consistent with the configured application URL.

Example:

`C:\xampp\htdocs\Court Case Management System`

### Step 3: Create the database

1. Open your browser.
2. Visit `http://localhost/phpmyadmin/`.
3. Create or select the database configured in the project's database connection file.
4. Open the **Import** tab.
5. Select the project's SQL database export file.
6. Click **Import** or **Go**.

### Step 4: Configure the database connection

Open:

`config/database.php`

Check that the database connection settings match your local XAMPP configuration:

- Database host
- Database name
- Database username
- Database password

For a default local XAMPP installation, the username is commonly `root` and the password may be empty. Use the actual credentials configured on your computer.

### Step 5: Run the application

Open your browser and visit:

`http://localhost/Court%20Case%20Management%20System/`

Log in using an account already configured in the database.

## 7. Project Structure

The main project components include:

- `config/` — Database configuration.
- `includes/` — Shared application files, authentication, header, footer, and bootstrap.
- `modules/` — Application modules such as clients, cases, hearings, documents, and reports.
- `login.php` — Login page.
- `index.php` — Application entry point, if configured as the main page.

The exact folder structure may vary depending on the final project version.

## 8. Security Features

The application includes security measures such as:

- Password hashing.
- Session-based authentication.
- Role-based authorization.
- CSRF token generation and validation.
- Prepared SQL statements.
- Output escaping to reduce cross-site scripting risks.
- Login attempt protection.
- Basic HTTP security headers.

## 9. User Roles

### Administrator

The administrator has access to administrative functions and user management, according to the permissions implemented in the application.

### Staff

Staff members can access the application modules permitted by their assigned role.

## 10. Database

The application uses a MySQL/MariaDB database containing tables for:

- Users
- Clients
- Cases
- Hearings
- Documents
- Login attempts
- IP login attempts

Import the supplied SQL file to create the database tables and insert the included sample records.

## 11. Important Notes

- This project is intended for academic demonstration and local development.
- Change default database credentials before deployment.
- Configure HTTPS and production security settings before making the application publicly accessible.
- Use valid login credentials stored in the database.
- Keep uploaded documents and database backups secure.

## 12. Future Enhancements

Possible future improvements include:

- Calendar-based hearing management.
- Case activity history.
- PDF report generation.
- Advanced search and filtering.
- Automated database backups.
- Email notifications for upcoming hearings.

---

**Project Name:** Court Case Management System (CCMS)  
**Project Type:** Web-Based Application  
**Purpose:** Academic Mini Project
