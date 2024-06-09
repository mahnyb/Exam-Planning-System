-- Create Database if it does not exist
CREATE DATABASE IF NOT EXISTS ExamPlanningSystem;

-- Use the database
USE ExamPlanningSystem;

-- Drop tables if they exist to avoid conflicts
DROP TABLE IF EXISTS AssistantCourses;
DROP TABLE IF EXISTS ExamAssistants;
DROP TABLE IF EXISTS Exam;
DROP TABLE IF EXISTS Courses;
DROP TABLE IF EXISTS Employee;
DROP TABLE IF EXISTS Department;
DROP TABLE IF EXISTS Faculty;

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
    score INT DEFAULT 0,
    FOREIGN KEY (department_id) REFERENCES Department(department_id)
);

-- Create Courses Table
CREATE TABLE Courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    department_id INT,
    course_day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    course_time ENUM('09:00-11:00', '11:00-13:00', '13:00-14:00', '14:00-16:00', '16:00-18:00') NOT NULL,
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

-- Create AssistantCourses Table
CREATE TABLE AssistantCourses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    course_id INT,
    FOREIGN KEY (employee_id) REFERENCES Employee(employee_id),
    FOREIGN KEY (course_id) REFERENCES Courses(course_id)
);

-- Create ExamAssistants Table
CREATE TABLE ExamAssistants (
    exam_assistant_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT,
    assistant_id INT,
    FOREIGN KEY (exam_id) REFERENCES Exam(exam_id),
    FOREIGN KEY (assistant_id) REFERENCES Employee(employee_id)
);

-- Insert sample data into Faculty
INSERT INTO Faculty (faculty_name) VALUES 
('Engineering'), 
('Medicine'), 
('Science'), 
('Arts and Humanities'), 
('Social Sciences');

-- Insert sample data into Department
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

-- Insert sample data into Employee
INSERT INTO Employee (first_name, last_name, username, password, role, department_id, score) VALUES 
('Gina', 'Linetti', 'parisOfPeople', 'beyonce', 'Assistant', 1, 0),
('Charles', 'Boyle', 'chichi', 'motherDough', 'Assistant', 1, 0),
('Micheal', 'Hitchcock', 'mikie', 'mr99', 'Assistant', 2, 0),
('Manny', 'Delgado', 'ManDel', 'poetry', 'Assistant', 2, 0),
('Phill', 'Dunphy', 'mrP', 'magic', 'Secretary', 1, -1), -- -1 because invalid for this role
('Norman', 'Scully', 'normSkull', 'foodie', 'Secretary', 2, -1), 
('Amy', 'Santiago', 'ames', 'bindersAreCool', 'Head of Secretary', 1, -1),
('Claire', 'Pritchett', 'CPrit', 'organization', 'Head of Secretary', 2, -1),
('Jacob', 'Peralta', 'JMcClane', 'NakatomiPlaza!84', 'Head of Department', 1, -1),
('Terry', 'Jeffords', 'terBear', 'yogurt', 'Head of Department', 2, -1),
('Raymond', 'Holt', 'rayHolt', 'password123', 'Dean', 1, -1),
('Jay', 'Pritchett', 'jayP', 'closests', 'Dean', 2, -1);

-- Insert sample data into Courses
INSERT INTO Courses (course_name, department_id, course_day, course_time) VALUES 
('CSE348', 1, 'Tuesday', '09:00-11:00'),
('CSE331', 1, 'Monday', '14:00-16:00'),
('CSE344', 1, 'Wednesday', '11:00-13:00'),   
('EEE101', 2, 'Tuesday', '09:00-11:00'),
('EEE202', 2, 'Friday', '11:00-13:00'),
('EEE348', 2, 'Thursday', '14:00-16:00'),  
('PHY201', 5, 'Wednesday', '11:00-13:00'), 
('BIO101', 6, 'Thursday', '14:00-16:00'), 
('LIT200', 7, 'Friday', '16:00-18:00'), 
('SOC305', 10, 'Monday', '09:00-11:00'), 
('ANT210', 9, 'Tuesday', '11:00-13:00');

-- Insert sample data into Exam
INSERT INTO Exam (course_id, exam_name, exam_date, exam_time, num_classes) VALUES 
(1, 'CSE348 Final', '2024-04-30', '18:00:00', 2),
(2, 'CSE331 Final', '2024-05-02', '13:00:00', 2),
(3, 'CSE344 Final', '2024-05-02', '16:00:00', 2),
(4, 'EEE101 Midterm', '2024-05-15', '14:00:00', 3),
(5, 'EEE202 Quiz', '2024-04-20', '11:00:00', 1),
(6, 'EEE348 Final', '2024-05-03', '09:30:00', 1),
(7, 'PHY201 Quiz', '2024-06-10', '14:00:00', 3),
(8, 'BIO101 Midterm', '2024-03-20', '09:00:00', 2),
(9, 'LIT200 Final', '2024-05-25', '11:00:00', 3),
(10, 'SOC305 Final', '2024-06-01', '13:00:00', 1),
(11, 'ANT210 Midterm', '2024-04-18', '15:00:00', 2);
