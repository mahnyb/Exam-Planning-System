<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.html');
    exit();
}

// Fetch head of secretary's details
$hos_name = $_SESSION['username'];
$query = "SELECT * FROM Employee WHERE name='$hos_name'";
$result = mysqli_query($conn, $query);
$hos = mysqli_fetch_assoc($result);
$faculty_id = $hos['faculty_id'];

// Fetch courses for the faculty
$courses_query = "SELECT * FROM Courses WHERE department_id IN (SELECT department_id FROM Department WHERE faculty_id='$faculty_id')";
$courses_result = mysqli_query($conn, $courses_query);

// Fetch assistants for the faculty
$assistants_query = "SELECT * FROM Employee WHERE department_id IN (SELECT department_id FROM Department WHERE faculty_id='$faculty_id') AND role='Assistant'";
$assistants_result = mysqli_query($conn, $assistants_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Head of Secretary Page</title>
</head>
<body>
    <h2>Welcome, <?php echo $hos_name; ?></h2>
    <h3>Insert Faculty Course</h3>
    <form action="insert_faculty_course.php" method="post">
        <label for="course_name">Course Name:</label>
        <input type="text" id="course_name" name="course_name" required><br>
        <input type="submit" value="Insert Course">
    </form>

    <h3>Insert Exam</h3>
    <form action="insert_exam.php" method="post">
        <label for="course">Course:</label>
        <select id="course" name="course">
            <?php while ($course = mysqli_fetch_assoc($courses_result)) { ?>
                <option value="<?php echo $course['course_id']; ?>"><?php echo $course['course_name']; ?></option>
            <?php } ?>
        </select><br>
        <label for="exam_name">Exam Name:</label>
        <input type="text" id="exam_name" name="exam_name" required><br>
        <label for="exam_date">Exam Date:</label>
        <input type="date" id="exam_date" name="exam_date" required><br>
        <label for="exam_time">Exam Time:</label>
        <input type="time" id="exam_time" name="exam_time" required><br>
        <label for="num_classes">Number of Classes:</label>
        <input type="number" id="num_classes" name="num_classes" required><br>
        <input type="submit" value="Insert Exam">
    </form>

    <h3>Assistant Scores</h3>
    <table border="1">
        <tr>
            <th>Assistant Name</th>
            <th>Score</th>
        </tr>
        <?php while ($assistant = mysqli_fetch_assoc($assistants_result)) {
            $score_query = "SELECT COUNT(*) as score FROM ExamAssignments WHERE assistant_id='" . $assistant['employee_id'] . "'";
            $score_result = mysqli_query($conn, $score_query);
            $score = mysqli_fetch_assoc($score_result)['score'];
            echo "<tr><td>{$assistant['name']}</td><td>$score</td></tr>";
        } ?>
    </table>
</body>
</html>
