<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course'];
    $exam_name = $_POST['exam_name'];
    $exam_date = $_POST['exam_date'];
    $exam_time = $_POST['exam_time'];
    $num_classes = $_POST['num_classes'];

    $query = "INSERT INTO Exam (course_id, exam_name, exam_date, exam_time, num_classes) VALUES ('$course_id', '$exam_name', '$exam_date', '$exam_time', '$num_classes')";
    if (mysqli_query($conn, $query)) {
        echo "Exam inserted successfully.";
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}
?>
