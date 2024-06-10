<?php
session_start();
include 'db.php';

// Check if the user is logged in and is an Assistant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assistant') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Fetch the assistant's courses for selection
$courses_sql = "SELECT course_id, course_name FROM Courses WHERE department_id = (SELECT department_id FROM Employee WHERE employee_id = ?)";
$courses_stmt = $conn->prepare($courses_sql);
if ($courses_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$courses_stmt->bind_param("i", $user_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();

// Fetch the assistant's exams and courses
$exams_sql = "
SELECT 
    e.exam_name, 
    e.exam_date, 
    e.exam_time,
    c.course_name, 
    c.course_day,
    c.course_time
FROM 
    Exam e
    INNER JOIN ExamAssistants ea ON ea.exam_id = e.exam_id 
    INNER JOIN Courses c ON e.course_id = c.course_id
WHERE 
    ea.assistant_id = ?
";
$exams_stmt = $conn->prepare($exams_sql);
if ($exams_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$exams_stmt->bind_param("i", $user_id);
$exams_stmt->execute();
$exams_result = $exams_stmt->get_result();
$exams = $exams_result->fetch_all(MYSQLI_ASSOC);

// Process course selection update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_courses'])) {
    $selected_courses = $_POST['course_selection'];
    foreach ($selected_courses as $course_id) {
        // Update the assistant's courses
        $update_sql = "INSERT INTO AssistantCourses (employee_id, course_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE course_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $update_stmt->bind_param("iii", $user_id, $course_id, $course_id);
        $update_stmt->execute();
    }
    header("Location: assistant.php");
    exit();
}

// Function to generate a table for exams and courses
function generate_exams_courses_table($exams) {
    echo "<table border='1'>";
    echo "<tr><th>Course Name</th><th>Day of Week</th><th>Course Time</th><th>Exam Name</th><th>Exam Date</th><th>Exam Time</th></tr>";

    foreach ($exams as $exam) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($exam['course_name']) . "</td>";
        echo "<td>" . htmlspecialchars($exam['course_day']) . "</td>";
        echo "<td>" . htmlspecialchars($exam['course_time']) . "</td>";
        echo "<td>" . htmlspecialchars($exam['exam_name']) . "</td>";
        echo "<td>" . htmlspecialchars($exam['exam_date']) . "</td>";
        echo "<td>" . htmlspecialchars($exam['exam_time']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome, <?php echo htmlspecialchars("$first_name $last_name"); ?></title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars("$first_name $last_name"); ?></h1>
    
    <h2>Select Courses</h2>
    <form action="assistant.php" method="post">
        <label for="course_selection">Select Courses:</label>
        <select id="course_selection" name="course_selection[]" multiple>
            <?php while ($course = $courses_result->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($course['course_id']); ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
            <?php } ?>
        </select>
        <br>
        <button type="submit" name="update_courses">Update Courses</button>
    </form>

    <h2>Your Exams and Courses</h2>
    <form action="assistant.php" method="post">
        <button type="submit" name="refresh">Refresh Table</button>
    </form>
    <?php generate_exams_courses_table($exams); ?>
</body>
</html>
