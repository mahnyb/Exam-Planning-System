<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assistant_name = $_SESSION['username'];
    $courses = $_POST['courses'];

    // Fetch assistant's details
    $query = "SELECT * FROM Employee WHERE name='$assistant_name'";
    $result = mysqli_query($conn, $query);
    $assistant = mysqli_fetch_assoc($result);
    $assistant_id = $assistant['employee_id'];

    // Delete existing course selections for the assistant
    $delete_query = "DELETE FROM AssistantCourses WHERE assistant_id='$assistant_id'";
    mysqli_query($conn, $delete_query);

    // Insert new course selections
    foreach ($courses as $course_id) {
        $insert_query = "INSERT INTO AssistantCourses (assistant_id, course_id) VALUES ('$assistant_id', '$course_id')";
        mysqli_query($conn, $insert_query);
    }

    echo "Courses selected successfully.";
}
?>
