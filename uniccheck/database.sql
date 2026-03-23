-- UniCheck Database Setup
-- Run this in phpMyAdmin or MySQL CLI before starting the app

CREATE DATABASE IF NOT EXISTS uniccheck;
USE uniccheck;

-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matric_number VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    level ENUM('100','200','300','400','500') NOT NULL,
    department VARCHAR(100) NOT NULL,
    faculty VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Results table
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matric_number VARCHAR(20) NOT NULL,
    course_code VARCHAR(20) NOT NULL,
    course_title VARCHAR(150) NOT NULL,
    credit_unit TINYINT NOT NULL,
    score TINYINT NOT NULL,
    grade CHAR(2) NOT NULL,
    grade_point DECIMAL(3,1) NOT NULL,
    semester ENUM('First','Second') NOT NULL,
    session VARCHAR(10) NOT NULL,
    level ENUM('100','200','300','400','500') NOT NULL,
    FOREIGN KEY (matric_number) REFERENCES students(matric_number) ON DELETE CASCADE
);

-- Default admin account (username: admin / password: admin123)
INSERT INTO admins (username, password, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

-- Sample student
INSERT INTO students (matric_number, full_name, level, department, faculty) VALUES
('AHU/2021/001', 'Amara Chukwuemeka', '300', 'Computer Science', 'Computing & Applied Sciences');

-- Sample results for that student
INSERT INTO results (matric_number, course_code, course_title, credit_unit, score, grade, grade_point, semester, session, level) VALUES
('AHU/2021/001', 'CSC301', 'Data Structures & Algorithms', 3, 78, 'B', 4.0, 'First', '2023/2024', '300'),
('AHU/2021/001', 'CSC303', 'Database Management Systems', 3, 85, 'A', 5.0, 'First', '2023/2024', '300'),
('AHU/2021/001', 'CSC305', 'Software Engineering', 2, 72, 'B', 4.0, 'First', '2023/2024', '300'),
('AHU/2021/001', 'MTH301', 'Numerical Methods', 2, 65, 'C', 3.0, 'First', '2023/2024', '300'),
('AHU/2021/001', 'CSC307', 'Computer Networks', 3, 90, 'A', 5.0, 'First', '2023/2024', '300'),
('AHU/2021/001', 'CSC302', 'Operating Systems', 3, 80, 'A', 5.0, 'Second', '2023/2024', '300'),
('AHU/2021/001', 'CSC304', 'Web Technologies', 3, 88, 'A', 5.0, 'Second', '2023/2024', '300'),
('AHU/2021/001', 'CSC306', 'Artificial Intelligence', 2, 70, 'B', 4.0, 'Second', '2023/2024', '300'),
('AHU/2021/001', 'GNS301', 'Entrepreneurship', 2, 75, 'B', 4.0, 'Second', '2023/2024', '300');
