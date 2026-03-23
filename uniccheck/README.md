# UniCheck — Student Result Portal

A full-stack PHP & MySQL web application for checking academic results by matric number, with a secure admin dashboard for managing students and uploading results.

## Features
- Student result lookup by matric number
- Filter by semester and academic session
- GPA calculation with class of degree remark
- Print / Download result as PDF
- Secure admin login
- Admin dashboard with stats
- Add & manage students
- Upload results per course

## Tech Stack
`PHP` `MySQL` `HTML5` `CSS3` `JavaScript`

## Setup (XAMPP)

1. Start **Apache** and **MySQL** in XAMPP
2. Open **phpMyAdmin** → create database → import `database.sql`
3. Copy the project folder into `htdocs/`
4. Open `http://localhost/uniccheck/`

## Admin Login
- **URL:** `http://localhost/uniccheck/admin/login.php`
- **Username:** `admin`
- **Password:** `admin123`

> Change the default password after first login via phpMyAdmin → admins table.

## File Structure
```
uniccheck/
├── index.php               ← Student portal
├── database.sql            ← DB setup
├── includes/
│   └── config.php          ← DB config & helpers
├── admin/
│   ├── login.php
│   ├── dashboard.php
│   ├── students.php
│   ├── add_result.php
│   └── logout.php
└── assets/
    ├── css/style.css
    └── js/main.js
```

## Built by
[Khaleed Olawale](https://github.com/khaleedolawale) — Software Engineering Student & Web Developer
