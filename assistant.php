<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in and is an assistant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assistant') {
    header('Location: index.html');
    exit();
}

$assistant_id = $_SESSION['user_id'];
$department_id = $_SESSION['department_id'];

// Fetch courses
$sql = "SELECT course_id, course_name FROM Courses WHERE department_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch exams
$sql = "SELECT e.exam_name, e.exam_date, e.exam_time, c.course_name 
        FROM Exam e 
        JOIN Courses c ON e.course_id = c.course_id 
        WHERE c.department_id = ? AND EXISTS (
            SELECT 1 FROM AssistantExam ae WHERE ae.exam_id = e.exam_id AND ae.assistant_id = ?
        )";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $department_id, $assistant_id);
$stmt->execute();
$exams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assistant Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h1>
    <h2>Your Weekly Schedule</h2>

    <form method="post" action="assistant.php">
        <label for="course_selection">Select Courses:</label>
        <select name="course_selection[]" id="course_selection" multiple>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo htmlspecialchars($course['course_id']); ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" name="submit_courses" value="Update Courses">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_courses'])) {
        $selected_courses = $_POST['course_selection'];
        // Save selected courses to the database
        foreach ($selected_courses as $course_id) {
            $sql = "INSERT INTO AssistantCourse (assistant_id, course_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $assistant_id, $course_id);
            $stmt->execute();
        }
    }

    // Fetch updated courses and exams
    $sql = "SELECT c.course_name, c.course_time, e.exam_name, e.exam_date, e.exam_time 
            FROM Courses c 
            LEFT JOIN Exam e ON c.course_id = e.course_id 
            WHERE c.course_id IN (SELECT course_id FROM AssistantCourse WHERE assistant_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assistant_id);
    $stmt->execute();
    $schedule = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>

    <h2>Your Updated Schedule</h2>
    <table border="1">
        <tr>
            <th>Day</th>
            <th>Time</th>
            <th>Course/Exam</th>
        </tr>
        <?php foreach ($schedule as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars(date('l', strtotime($item['exam_date'] ?? $item['course_time']))); ?></td>
                <td><?php echo htmlspecialchars($item['exam_time'] ?? $item['course_time']); ?></td>
                <td><?php echo htmlspecialchars($item['exam_name'] ?? $item['course_name']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <button onclick="location.reload();">Refresh</button>
</body>
</html>
