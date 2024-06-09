<?php
session_start();
include 'db.php';

// Check if the user is logged in and is an Assistant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assistant') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Fetch the assistant's weekly program (courses and exams)
$sql = "
SELECT 
    c.course_name, 
    e.exam_name, 
    e.exam_date, 
    e.exam_time 
FROM 
    Courses c 
    LEFT JOIN Exam e ON c.course_id = e.course_id 
    LEFT JOIN Employee em ON em.department_id = c.department_id 
WHERE 
    em.employee_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$weekly_program = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo "Welcome, " . $first_name . " " . $last_name; ?></title>
</head>
<body>
    <h1>Welcome, <?php echo $first_name . " " . $last_name; ?></h1>
    <h2>Your Weekly Program</h2>
    <table border="1">
        <tr>
            <th>Course Name</th>
            <th>Exam Name</th>
            <th>Exam Date</th>
            <th>Exam Time</th>
        </tr>
        <?php foreach ($weekly_program as $program) { ?>
            <tr>
                <td><?php echo $program['course_name']; ?></td>
                <td><?php echo $program['exam_name']; ?></td>
                <td><?php echo $program['exam_date']; ?></td>
                <td><?php echo $program['exam_time']; ?></td>
            </tr>
        <?php } ?>
    </table>
    <form action="assistant.php" method="post">
        <label for="course_selection">Select Courses:</label>
        <select id="course_selection" name="course_selection[]" multiple>
            <?php
            $courses_sql = "SELECT course_id, course_name FROM Courses WHERE department_id = (SELECT department_id FROM Employee WHERE employee_id = ?)";
            $courses_stmt = $conn->prepare($courses_sql);
            $courses_stmt->bind_param("i", $user_id);
            $courses_stmt->execute();
            $courses_result = $courses_stmt->get_result();
            while ($course = $courses_result->fetch_assoc()) {
                echo "<option value='" . $course['course_id'] . "'>" . $course['course_name'] . "</option>";
            }
            ?>
        </select>
        <br>
        <button type="submit" name="update_courses">Update Courses</button>
    </form>
</body>
</html>

<?php
if (isset($_POST['update_courses'])) {
    $selected_courses = $_POST['course_selection'];
    foreach ($selected_courses as $course_id) {
        // Update the assistant's courses (you may need to adjust this part based on your actual database schema)
        $update_sql = "INSERT INTO AssistantCourses (employee_id, course_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE course_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iii", $user_id, $course_id, $course_id);
        $update_stmt->execute();
    }
    header("Location: assistant.php");
    exit();
}
?>
