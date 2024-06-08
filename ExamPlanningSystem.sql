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
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    username VARCHAR(64) NOT NULL,
    password VARCHAR(255) NOT NULL,
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
INSERT INTO Faculty (faculty_name) VALUES 
('Engineering'), 
('Medicine'), 
('Science'), 
('Arts and Humanities'), 
('Social Sciences');

INSERT INTO Department (department_name, faculty_id) VALUES 
('Computer Engineering', 1), 
('Electrical Engineering', 1), 
('General Medicine', 2), 
('Surgery', 2), 
('Physics', 3), 
('Biology', 3), 
('Literature', 4), 
('Linguistics', 4), 
('Anthropology', 5), 
('Sociology', 5);

INSERT INTO Employee (first_name, last_name, username, password, role, department_id) VALUES 
('Gina', 'Linetti', 'parisOfPeople', 'beyonce', 'Assistant', 1),
('Charles', 'Boyle', 'chichi', 'motherDough', 'Assistant', 1),
('Amy', 'Santiago', 'ames', 'bindersAreCool', 'Head of Secretary', 1),
('Jacob', 'Peralta', 'JMcClane', 'NakatomiPlaza!84', 'Head of Department', 1),
('Terry', 'Jeffords', 'terBear', 'yogurt', 'Head of Department', 2),
('Raymond', 'Holt', 'rayHolt', 'password123', 'Dean', 1);

INSERT INTO Courses (course_name, department_id) VALUES 
('CSE348', 1), 
('EEE101', 2), 
('PHY201', 5), 
('BIO101', 6), 
('LIT200', 7), 
('SOC305', 10), 
('ANT210', 9);

INSERT INTO Exam (course_id, exam_name, exam_date, exam_time, num_classes) VALUES 
(1, 'CSE348 Final', '2024-04-30', '18:00:00', 2),
(2, 'EEE101 Midterm', '2024-05-15', '10:00:00', 1),
(3, 'PHY201 Quiz', '2024-06-10', '14:00:00', 3),
(4, 'BIO101 Midterm', '2024-03-20', '09:00:00', 2),
(5, 'LIT200 Final', '2024-05-25', '11:00:00', 3),
(6, 'SOC305 Final', '2024-06-01', '13:00:00', 1),
(7, 'ANT210 Midterm', '2024-04-18', '15:00:00', 2);
