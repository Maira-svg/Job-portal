# JobPortal - Complete Recruitment Management System

## 📋 Overview
A fully functional job portal system with Admin and Employer panels. Complete CRUD operations built into a single file.

## 🚀 Installation Guide

### Step 1: Prerequisites
- WAMP/XAMPP installed on your system
- PHP 7.0 or higher
- MySQL 5.6 or higher

### Step 2: Installation

1. **Create folder** in your WAMP www directory:
C:\wamp64\www\jobportal\

text

2. **Create file** `index.php` in the above folder

3. **Copy the complete code** from the provided solution into `index.php`

4. **Start WAMP server** (make sure icon is green)

5. **Open browser** and navigate to:
http://localhost/jobportal/

text

### Step 3: First Login

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@admin.com` | `admin123` |

> ℹ️ **Note:** Database, tables, and admin account are created automatically on first run. No manual database setup needed!

## 📁 File Structure
C:\wamp64\www\jobportal
└── index.php (Single file - Complete application)

text

**No other files needed!** Everything is in one file.

## 👥 User Roles

### 1. Admin
- **Full system control**
- Manage categories (Add/Delete)
- View all employers
- Delete any employer
- View all jobs
- Delete any job
- View all applications
- Delete any application

### 2. Employer
- **Registration required**
- Post new jobs
- View own jobs
- Delete own jobs
- Toggle job status (Active/Closed)
- View applications received
- Update application status (Pending/Shortlisted/Rejected/Hired)
- Delete applications
- View cover letters

### 3. Job Seekers (Public)
- Browse all jobs
- Filter by category
- Apply for jobs
- Submit applications with cover letters

## 🛠️ Features

### Complete CRUD Operations

| Operation | Admin | Employer | Public |
|-----------|-------|----------|--------|
| **Create** | Add categories, Register employers | Post jobs | Submit applications |
| **Read** | View all data | View own jobs & applications | Browse jobs |
| **Update** | - | Update job status, Update application status | - |
| **Delete** | Delete categories, employers, jobs, applications | Delete own jobs & applications | - |

### Database Tables (Auto-created)

| Table | Description |
|-------|-------------|
| `users` | Stores admin and employer accounts |
| `categories` | Job categories (Technology, AI/ML, etc.) |
| `jobs` | Job postings with all details |
| `applications` | Job applications from candidates |

## 🎯 How to Use

### For Admin:
1. Login with `admin@admin.com` / `admin123`
2. Go to **Categories** - Add or delete job categories
3. Go to **Employers** - View and manage employer accounts
4. Go to **All Jobs** - Monitor and delete any job
5. Go to **Applications** - View all applications

### For Employers:
1. Click **Register** on homepage
2. Fill registration form with company details
3. Login with your credentials
4. Go to **Post Job** - Create new job listings
5. Go to **My Jobs** - Manage your jobs (delete/toggle status)
6. Go to **Applications** - View and process candidate applications

### For Job Seekers:
1. Visit homepage
2. Click **Browse Jobs**
3. Filter by category if needed
4. Click **Apply Now** on any job
5. Fill application form with cover letter
6. Submit application

## 📊 Database Access

### Via phpMyAdmin:
http://localhost/phpmyadmin/

text
Database name: `jobportal_db`

### Via MySQL Command Line:
```sql
USE jobportal_db;
SHOW TABLES;
SELECT * FROM users;
SELECT * FROM categories;
SELECT * FROM jobs;
SELECT * FROM applications;
🔧 Troubleshooting
Issue: Database not created
Solution: Check if MySQL is running in WAMP. Refresh the page - database creates automatically.

Issue: Cannot login
Solution:

Admin: admin@admin.com / admin123

Clear browser cache

Check if session started properly

Issue: Cannot post job
Solution: Make sure you are logged in as Employer (not Admin)

Issue: White screen
Solution:

Enable PHP errors in php.ini

Check WAMP PHP error logs

Verify MySQL is running

📝 Default Data
On first run, the system automatically creates:

Admin Account: admin@admin.com / admin123

Categories: Technology, AI/ML, Data Science, Cybersecurity, Cloud Computing, DevOps

Empty Tables: Ready for you to add jobs and applications

🌐 URLs
Page	URL
Homepage	http://localhost/jobportal/
Browse Jobs	http://localhost/jobportal/?page=jobs
Login	http://localhost/jobportal/?page=login
Register	http://localhost/jobportal/?page=register
💻 System Requirements
Web Server: Apache (via WAMP/XAMPP)

PHP: Version 7.0+

Database: MySQL 5.6+

Browser: Chrome, Firefox, Edge, Safari (any modern browser)

📱 Responsive Design
Works on desktop, tablet, and mobile devices

Bootstrap 5 framework

Clean and professional UI

🔒 Security Features
Password encryption using MD5

SQL injection prevention using real_escape_string

Session-based authentication

Role-based access control

📞 Support
For issues:

Check WAMP server is running (green icon)

Verify MySQL service is active

Clear browser cache

Check PHP error logs in C:\wamp64\logs\

📄 License
Free to use for learning and commercial purposes.

🎯 Quick Commands Reference
To start WAMP:
Click WAMP icon → Start All Services

To access phpMyAdmin:
text
http://localhost/phpmyadmin/
To access MySQL console:
text
C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe -u root
To reset everything:
Delete C:\wamp64\www\jobportal\ folder

Recreate folder and index.php

Refresh browser - fresh database created
