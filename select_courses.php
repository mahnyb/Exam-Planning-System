<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['courses']) || empty($_POST['courses'])) {
        echo "No courses selected.";
        exit();
    }

    $assistant_name = $_SESSION['username'];
    $courses = $_POST['courses'];

    // Fetch assistant's details
    $query = "SELECT * FROM Employee WHERE name='$assistant_name'";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Error fetching assistant details: " . mysqli_error($conn));
    }
    $assistant = mysqli_fetch_assoc($result);
    $assistant_id = $assistant['employee_id'];

    // Delete existing course selections for the assistant
    $delete_query = "DELETE FROM AssistantCourses WHERE assistant_id='$assistant_id'";
    if (!mysqli_query($conn, $delete_query)) {
        die("Error deleting existing courses: " . mysqli_error($conn));
    }

    // Insert new course selections
    foreach ($courses as $course_id) {
        $insert_query = "INSERT INTO AssistantCourses (assistant_id, course_id) VALUES ('$assistant_id', '$course_id')";
        if (!mysqli_query($conn, $insert_query)) {
            die("Error inserting courses: " . mysqli_error($conn));
        }
    }

    echo "Courses selected successfully.";
}
?>
