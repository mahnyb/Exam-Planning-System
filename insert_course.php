<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_name = $_POST['course_name'];
    $secretary_name = $_SESSION['username'];

    // Fetch secretary's details
    $query = "SELECT * FROM Employee WHERE name='$secretary_name'";
    $result = mysqli_query($conn, $query);
    $secretary = mysqli_fetch_assoc($result);
    $department_id = $secretary['department_id'];

    // Insert new course
    $insert_query = "INSERT INTO Courses (course_name, department_id) VALUES ('$course_name', '$department_id')";
    if (mysqli_query($conn, $insert_query)) {
        echo "Course inserted successfully.";
    } else {
        echo "Error: " . $insert_query . "<br>" . mysqli_error($conn);
    }
}
?>
