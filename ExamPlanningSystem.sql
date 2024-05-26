-- Create Database
CREATE DATABASE ExamPlanningSystem;

-- Use the database
USE ExamPlanningSystem;

-- Create Faculty Table
CREATE TABLE Faculty (
    faculty_id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_name VARCHAR(255) NOT NULL
);

-- Create Department Table
CREATE TABLE Department (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(255) NOT NULL,
    faculty_id INT,
    FOREIGN KEY (faculty_id) REFERENCES Faculty(faculty_id)
);

-- Create Employee Table
CREATE TABLE Employee (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role ENUM('Assistant', 'Secretary', 'Head of Department', 'Head of Secretary', 'Dean') NOT NULL,
    department_id INT,
    FOREIGN KEY (department_id) REFERENCES Department(department_id)
);

-- Create Courses Table
CREATE TABLE Courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    department_id INT,
    FOREIGN KEY (department_id) REFERENCES Department(department_id)
);

-- Create Exam Table
CREATE TABLE Exam (
    exam_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    exam_name VARCHAR(255) NOT NULL,
    exam_date DATE,
    exam_time TIME,
    num_classes INT,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id)
);

-- Insert sample data
INSERT INTO Faculty (faculty_name) VALUES ('Engineering'), ('Science'), ('Arts');
INSERT INTO Department (department_name, faculty_id) VALUES ('Computer Engineering', 1), ('Electrical Engineering', 1), ('Physics', 2);
INSERT INTO Employee (name, role, department_id) VALUES ('John Doe', 'Assistant', 1), ('Jane Smith', 'Secretary', 1), ('Alice Johnson', 'Head of Department', 1);
INSERT INTO Courses (course_name, department_id) VALUES ('CSE348', 1), ('EEE101', 2), ('PHY201', 3);
INSERT INTO Exam (course_id, exam_name, exam_date, exam_time, num_classes) VALUES (1, 'CSE348 Final', '2024-04-30', '18:00:00', 2);
