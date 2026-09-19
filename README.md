# Employee Onboarding Portal

A web-based Employee Onboarding Portal designed to simplify and manage the employee onboarding process. The system allows employees to complete onboarding tasks, upload required documents, and track their onboarding progress, while administrators can monitor employee progress and manage submitted documents.

## Features

### Employee Features

- Employee registration and login
- Secure password authentication
- Employee dashboard
- View assigned onboarding tasks
- Mark onboarding tasks as completed
- Track onboarding progress
- Upload required documents
- View uploaded documents
- Check document approval status
- Logout functionality

### Admin Features

- Secure admin login
- Admin dashboard
- View registered employees
- Monitor employee onboarding progress
- View employee tasks and completion status
- View uploaded employee documents
- Open uploaded documents
- Approve documents
- Reject documents

## Tech Stack

### Frontend

- HTML5
- CSS3
- JavaScript

### Backend

- PHP
- MySQL
- Apache

### Development Tools

- Visual Studio Code
- XAMPP
- phpMyAdmin
- Git
- GitHub

## Project Structure

```text
employee-onboarding-portal/
│
├── backend/
│   ├── config/
│   │   ├── database.php
│   │   └── database.local.php
│   │
│   ├── uploads/
│   ├── login.php
│   ├── register.php
│   ├── update_document.php
│   ├── update_task.php
│   └── upload_document.php
│
├── database/
│
├── frontend/
│   ├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   ├── admin-dashboard.php
│   ├── dashboard.php
│   ├── dashboard.html
│   ├── index.html
│   ├── login.html
│   └── register.html
│
├── screenshots/
│
├── .gitignore
└── README.md

## Screenshots

### Home Page

![Home Page](screenshots/home.png)

### Login Page

![Login Page](screenshots/login.png)

### Employee Dashboard

![Employee Dashboard](screenshots/employee-dashboard.png)

### Admin Dashboard

![Admin Dashboard](screenshots/admin-dashboard.png)

### Document Management

![Document Management](screenshots/document-management.png)