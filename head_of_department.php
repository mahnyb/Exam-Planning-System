<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.html');
    exit();
}

// Fetch head of department's details
$hod_name = $_SESSION['username'];
$query = "SELECT * FROM Employee WHERE name='$hod_name'";
$result = mysqli_query($conn, $query);
$hod = mysqli_fetch_assoc($result);
$department_id = $hod['department_id'];

// Fetch exams for the department
$exams_query = "SELECT * FROM Exam WHERE course_id IN (SELECT course_id FROM Courses WHERE department_id='$department_id') ORDER BY exam_date, exam_time";
$exams_result = mysqli_query($conn, $exams_query);

// Fetch assistant workloads
$assistants_query = "SELECT * FROM Employee WHERE department_id='$department_id' AND role='Assistant'";
$assistants_result = mysqli_query($conn, $assistants_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Head of Department Page</title>
</head>
<body>
    <h2>Welcome, <?php echo $hod_name; ?></h2>
    <h3>Exam Schedule</h3>
    <table border="1">
        <tr>
            <th>Exam Date</th>
            <th>Exam Time</th>
            <th>Exam Name</th>
        </tr>
        <?php while ($exam = mysqli_fetch_assoc($exams_result)) {
            echo "<tr><td>{$exam['exam_date']}</td><td>{$exam['exam_time']}</td><td>{$exam['exam_name']}</td></tr>";
        } ?>
    </table>

    <h3>Assistant Workloads</h3>
    <table border="1">
        <tr>
            <th>Assistant Name</th>
            <th>Workload Percentage</th>
        </tr>
        <?php while ($assistant = mysqli_fetch_assoc($assistants_result)) {
            $score_query = "SELECT COUNT(*) as score FROM ExamAssignments WHERE assistant_id='" . $assistant['employee_id'] . "'";
            $score_result = mysqli_query($conn, $score_query);
            $score = mysqli_fetch_assoc($score_result)['score'];
            echo "<tr><td>{$assistant['name']}</td><td>$score%</td></tr>";
        } ?>
    </table>
</body>
</html>
