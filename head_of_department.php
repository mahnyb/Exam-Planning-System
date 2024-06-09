<?php
session_start();
include 'db.php';

// Check if the user is logged in and is the Head of Department
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Head of Department') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$department_id = isset($_SESSION['department_id']) ? $_SESSION['department_id'] : 0; // Set default value

// Fetch exams for the head of department's department
$exams_sql = "SELECT exam_name, exam_date, exam_time, course_name 
              FROM Exam 
              JOIN Courses ON Exam.course_id = Courses.course_id 
              WHERE Courses.department_id = ? 
              ORDER BY exam_date ASC, exam_time ASC";
$exams_stmt = $conn->prepare($exams_sql);
if ($exams_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$exams_stmt->bind_param("i", $department_id);
$exams_stmt->execute();
$exams_result = $exams_stmt->get_result();

// Fetch assistant scores for the department
$scores_sql = "SELECT first_name, last_name, score FROM Employee WHERE department_id = ? AND role = 'Assistant'";
$scores_stmt = $conn->prepare($scores_sql);
if ($scores_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$scores_stmt->bind_param("i", $department_id);
$scores_stmt->execute();
$scores_result = $scores_stmt->get_result();
$assistant_scores = $scores_result->fetch_all(MYSQLI_ASSOC);

// Calculate total score for workload percentage calculation
$total_score = 0;
foreach ($assistant_scores as $score) {
    $total_score += $score['score'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo "Welcome, " . $first_name . " " . $last_name; ?></title>
</head>
<body>
    <h1>Welcome, <?php echo $first_name . " " . $last_name; ?></h1>

    <h2>Exam Schedule</h2>
    <table border="1">
        <tr>
            <th>Exam Name</th>
            <th>Exam Date</th>
            <th>Exam Time</th>
            <th>Course Name</th>
        </tr>
        <?php while ($exam = $exams_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $exam['exam_name']; ?></td>
                <td><?php echo $exam['exam_date']; ?></td>
                <td><?php echo $exam['exam_time']; ?></td>
                <td><?php echo $exam['course_name']; ?></td>
            </tr>
        <?php } ?>
    </table>

    <h2>Assistant Workloads</h2>
    <table border="1">
        <tr>
            <th>Assistant Name</th>
            <th>Score</th>
            <th>Workload Percentage</th>
        </tr>
        <?php foreach ($assistant_scores as $score) { 
            $workload_percentage = ($total_score > 0) ? ($score['score'] / $total_score) * 100 : 0;
            ?>
            <tr>
                <td><?php echo $score['first_name'] . " " . $score['last_name']; ?></td>
                <td><?php echo $score['score']; ?></td>
                <td><?php echo number_format($workload_percentage, 2) . '%'; ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
