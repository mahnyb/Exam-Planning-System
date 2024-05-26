<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $faculty_id = $_POST['faculty'];
    $department_id = $_POST['department'];
    $course_name = $_POST['course_name'];

    $query = "INSERT INTO Courses (course_name, department_id) VALUES ('$course_name', '$department_id')";
    if (mysqli_query($conn, $query)) {
        echo "Course inserted successfully.";
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}
?>
