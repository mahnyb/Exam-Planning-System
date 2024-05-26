<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_name = $_POST['course_name'];
    $hos_name = $_SESSION['username'];

    // Fetch head of secretary's details
    $query = "SELECT * FROM Employee WHERE name='$hos_name'";
    $result = mysqli_query($conn, $query);
    $hos = mysqli_fetch_assoc($result);
    $faculty_id = $hos['faculty_id'];

    // Insert new course
    $insert_query = "INSERT INTO Courses (course_name, department_id) VALUES ('$course_name', (SELECT department_id FROM Department WHERE faculty_id='$faculty_id' LIMIT 1))";
    if (mysqli_query($conn, $insert_query)) {
        echo "Course inserted successfully.";
    } else {
        echo "Error: " . $insert_query . "<br>" . mysqli_error($conn);
    }
}
?>
