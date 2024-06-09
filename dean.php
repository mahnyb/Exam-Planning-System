<?php
session_start();
include 'db.php';

// Check if the user is logged in and is the Dean
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Dean') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$faculty_id = isset($_SESSION['faculty_id']) ? $_SESSION['faculty_id'] : 0; // Set default value

$departments = [];
$exams = [];

// Fetch departments under the dean's faculty
$departments_sql = "SELECT department_id, department_name FROM Department WHERE faculty_id = ?";
$departments_stmt = $conn->prepare($departments_sql);
if ($departments_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$departments_stmt->bind_param("i", $faculty_id);
$departments_stmt->execute();
$departments_result = $departments_stmt->get_result();
$departments = $departments_result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['department_id'])) {
    $department_id = $_POST['department_id'];

    // Fetch exams for the selected department
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
    $exams = $exams_result->fetch_all(MYSQLI_ASSOC);
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

    <h2>Select Department</h2>
    <form method="post" action="dean.php">
        <label for="department_id">Department:</label>
        <select id="department_id" name="department_id" required>
            <?php foreach ($departments as $department) { ?>
                <option value="<?php echo $department['department_id']; ?>"><?php echo $department['department_name']; ?></option>
            <?php } ?>
        </select>
        <br>
        <button type="submit">View Exams</button>
    </form>

    <?php if (!empty($exams)) { ?>
        <h2>Exam Schedule</h2>
        <table border="1">
            <tr>
                <th>Exam Name</th>
                <th>Exam Date</th>
                <th>Exam Time</th>
                <th>Course Name</th>
            </tr>
            <?php foreach ($exams as $exam) { ?>
                <tr>
                    <td><?php echo $exam['exam_name']; ?></td>
                    <td><?php echo $exam['exam_date']; ?></td>
                    <td><?php echo $exam['exam_time']; ?></td>
                    <td><?php echo $exam['course_name']; ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
</body>
</html>
