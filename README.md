# school-manegement-system
# 🎓 EduSmart - School Management System

A comprehensive, role-based School Management System (SMS) built with PHP, MySQL, and modern frontend technologies. EduSmart streamlines academic management for administrators, teachers, and students with an intuitive, futuristic interface.

![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-green)
![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen)

---

## ✨ Features

### 👨‍💼 Admin Panel
- **User Management** – Approve/reject/delete student & teacher accounts
- **Class Management** – Create, edit, delete classes with teacher assignment
- **Notice Board** – Publish announcements for everyone, all students, all teachers, or specific classes
- **Admin Account Creation** – Create additional admin accounts
- **Dashboard Analytics** – View system-wide statistics

### 👨‍🏫 Teacher Panel
- **My Classes** – View assigned classes with student lists
- **Gradebook** – Enter and manage student grades (Quizzes, Assignments, Midterms, Finals, Projects)
- **Assignment Management** – Upload assignments with files, set due dates & total marks
- **Attendance Tracking** – Mark daily attendance (Present/Absent/Late/Excused)
- **View Students** – Access student profiles in your classes

### 👨‍🎓 Student Panel
- **Dashboard** – Overview of enrolled courses, attendance rate, GPA, pending tasks
- **View Grades** – See subject-wise grades with letter grades and pass/fail status
- **Assignments** – View and submit assignments (text + file upload)
- **Class Schedule** – Weekly timetable with real-time "Today" highlighting
- **Notices** – View school announcements
- **Profile Management** – Update personal information (parent phone, address, DOB, gender)

---

## 🏗️ Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 7.4+ (Native) |
| Database | MySQL 5.7+ |
| Frontend | HTML5, CSS3, JavaScript |
| UI Framework | Custom CSS with Glassmorphism |
| Icons | Font Awesome 6 |
| Fonts | Google Fonts (Inter) |

---

## 📁 Project Structure
edusmart-sms/
├── admin_dash.php # Admin dashboard
├── manage_users.php # User approval & management
├── manage_classes.php # CRUD operations for classes
├── post_notice.php # Announcement publisher
├── teacher_dash.php # Teacher dashboard
├── my_classes.php # Teacher's assigned classes
├── upload_assignment.php # Assignment upload with files
├── enter_grades.php # Grade entry interface
├── attendance.php # Attendance marking
├── student_dash.php # Student dashboard
├── view_grades.php # Student grade viewer
├── view_assignments.php # Assignment submission
├── view_schedule.php # Weekly timetable
├── view_notices.php # Notice viewer
├── student_profile.php # Profile management
├── login.php # Authentication
├── signup.php # Registration (pending approval)
├── logout.php # Session destroy
├── config.php # Database configuration
├── student.css # Unified student styling
├── log.css # Authentication styling
├── index.html # Landing page
├── uploads/ # Assignment & submission files
└── sms_db.sql # Database schema (create this)


---

## 🗄️ Database Schema

The system uses a MySQL database named `sms_db` with the following main tables:

| Table | Purpose |
|-------|---------|
| `users` | Authentication & roles (admin/teacher/student) |
| `students` | Student profile data |
| `teachers` | Teacher profile data |
| `classes` | Class sections with teacher assignment |
| `assignments` | Assignment details & files |
| `submissions` | Student assignment submissions |
| `grades` | Student grades per subject/assessment |
| `attendance` | Daily attendance records |
| `notices` | Announcements with expiration dates |

> **Note:** Run the provided `sms_db.sql` script to create all tables with sample data.

---

## 🚀 Installation Guide

### Prerequisites
- XAMPP / WAMP / LAMP stack
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/edusmart-sms.git
   cd edusmart-sms
