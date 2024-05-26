<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department_id = $_POST['department'];

    $query = "SELECT * FROM Exam WHERE course_id IN (SELECT course_id FROM Courses WHERE department_id='$department_id')";
    $result = mysqli_query($conn, $query);

    echo "<h3>Exam Schedule</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Exam Date</th><th>Exam Time</th><th>Exam Name</th></tr>";

    while ($exam = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $exam['exam_date'] . "</td>";
        echo "<td>" . $exam['exam_time'] . "</td>";
        echo "<td>" . $exam['exam_name'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
}
?>
