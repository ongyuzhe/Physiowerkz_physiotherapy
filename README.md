# Physiowerkz Website System

## Overview

**Physiowerkz** is an advanced physiotherapy management system designed to digitalise and streamline operations in physiotherapy clinics. The system caters to patients, staff, and administrators by offering tailored features for managing appointments, health assessments, treatment records, and feedback. 

Built with usability in mind, Physiowerkz is a secure, efficient, and responsive platform for modern healthcare providers. With integrated user roles, Physiowerkz ensures the right information and tools are accessible to the appropriate stakeholders, maintaining privacy and operational efficiency.

---

## Key Features

### Patient-Focused Features
- **Health Questionnaire:** Intuitive module for patients to complete detailed health assessments, enabling personalised treatment plans.
- **Online Appointment Booking:** View available slots and book appointments directly with therapists.
- **Treatment Records:** Access a comprehensive history of treatments, including doctor comments and prescribed follow-ups.
- **Review and Feedback:** Provide star ratings and written reviews about experiences and treatments.

### Staff and Administrative Tools
- **Appointment Scheduling and Management:** Add, update, and manage appointment schedules efficiently.
- **Treatment Plan Management:** Update treatment records, document session details, and track patient progress.
- **Staff Performance Analytics:** Monitor staff performance through reviews and data insights.
- **Role-Based Dashboards:** Tailored interfaces for staff, administrators, and superadmins.

### Security and Authentication
- **Secure User Authentication:** Includes login, registration, password reset, and session management.
- **Data Privacy:** Ensures secure handling of sensitive patient data with access restrictions.

### Reviews and Feedback Management
- **Patient Reviews:** Submit feedback and star ratings after sessions.
- **Review Insights:** Analyse reviews to improve service quality.

### Advanced Functionalities
- **Automated Notifications:** Uses PHPMailer for appointment reminders and password reset emails.
- **Customisable Scheduling:** Create flexible schedules for staff and therapists.
- **Media Uploads:** Supports uploading patient-related videos and files.
- **Debugging Tools:** Log errors and monitor system performance.

### User Interface
- **Responsive Design:** Compatible with desktops, tablets, and mobile devices.
- **Customisable Themes:** CSS-based styling for personalisation.

---

## File Structure

### Core Functionalities
- `index.php`: Landing page and entry point for users.
- `add_review.php` & `view_reviews.php`: Handle patient reviews and feedback.
- `booking.php`: Module for booking appointments.
- `profile.php`: Manage patient and staff profiles.
- `health_questionnaire.php`: Form for submitting health details.
- `manage_appointments.php`: Administrative dashboard for appointments.
- `manage_staff.php` & `manage_patient.php`: Tools for managing staff and patient records.
- `reset_password.php` & `login.php`: Secure login and password management.

### Utilities
- `PHPMailer/`: Library for email notifications and SMTP configuration.
- `includes/settings.php`: Configuration file for database and system settings.
- `styles/`: CSS files for UI/UX styling.
- `debug.txt`: Log file for error tracking.

### Database
- `physiowerkz.sql`: MySQL database schema containing tables for users, appointments, health questionnaires, and reviews.

---

## Technologies Used

### Frontend
- **HTML5, CSS3, and JavaScript:** For building a responsive and user-friendly interface.
- **Bootstrap (optional):** To enhance the design and layout consistency.

### Backend
- **PHP:** Server-side scripting for handling core functionalities.
- **MySQL:** Database management for storing user data, appointments, and other records.

### Email Integration
- **PHPMailer:** For reliable email notifications, including password reset links.

### Hosting
- **Apache Server (XAMPP/WAMP):** Local server setup for development and testing.

---

## Installation Instructions

To set up the Physiowerkz website system on your local environment, follow these steps:

### 1. Clone the Repository
Clone the project repository from GitHub using the following command:

git clone https://github.com/YourUsername/Physiowerkz.git

### 2. Database Setup
1. Open your MySQL management tool (e.g., phpMyAdmin).
2. Create a new database, for example: physiowerkz_db.
3. Import the physiowerkz.sql file located in the project directory into your newly created database.

### 3. Configuration
Update the database connection settings in the includes/settings.php file:
Code:
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "physiowerkz_db";

### 4. Deploy Files
1. Copy all project files to your web server's root directory (e.g., htdocs if you are using XAMPP).
2. Ensure that your server is running.

### 5. Access the System
Open your web browser and navigate to: http://localhost/Physiowerkz
