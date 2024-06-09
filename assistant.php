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

// Fetch the assistant's weekly program (courses and exams)
$weekly_program_sql = "
SELECT 
    c.course_name, 
    c.course_day,
    c.course_time,
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
$weekly_program_stmt = $conn->prepare($weekly_program_sql);
if ($weekly_program_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$weekly_program_stmt->bind_param("i", $user_id);
$weekly_program_stmt->execute();
$weekly_program_result = $weekly_program_stmt->get_result();
$weekly_program = $weekly_program_result->fetch_all(MYSQLI_ASSOC);

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

// Helper function to generate a weekly schedule table
function generate_schedule_table($weekly_program) {
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    $timeslots = ['08:00-10:00', '10:00-12:00', '12:00-14:00', '14:00-16:00', '16:00-18:00'];
    
    echo "<table border='1'>";
    echo "<tr><th>Timeslot</th>";
    foreach ($days as $day) {
        echo "<th>$day</th>";
    }
    echo "</tr>";

    foreach ($timeslots as $timeslot) {
        echo "<tr><td>$timeslot</td>";
        foreach ($days as $day) {
            $found = false;
            foreach ($weekly_program as $event) {
                if ($event['course_day'] == $day && $event['course_time'] == $timeslot) {
                    echo "<td>{$event['course_name']}<br>{$event['exam_name']}<br>{$event['exam_date']} {$event['exam_time']}</td>";
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                echo "<td></td>";
            }
        }
        echo "</tr>";
    }
    echo "</table>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome, <?php echo "$first_name $last_name"; ?></title>
</head>
<body>
    <h1>Welcome, <?php echo "$first_name $last_name"; ?></h1>
    
    <h2>Select Courses</h2>
    <form action="assistant.php" method="post">
        <label for="course_selection">Select Courses:</label>
        <select id="course_selection" name="course_selection[]" multiple>
            <?php while ($course = $courses_result->fetch_assoc()) { ?>
                <option value="<?php echo $course['course_id']; ?>"><?php echo $course['course_name']; ?></option>
            <?php } ?>
        </select>
        <br>
        <button type="submit" name="update_courses">Update Courses</button>
    </form>

    <h2>Your Weekly Program</h2>
    <form action="assistant.php" method="post">
        <button type="submit" name="refresh">Refresh Table</button>
    </form>
    <?php generate_schedule_table($weekly_program); ?>
</body>
</html>
