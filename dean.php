<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch dean's details
$dean_name = $_SESSION['username'];
$query = "SELECT * FROM Employee WHERE name='$dean_name'";
$result = mysqli_query($conn, $query);
$dean = mysqli_fetch_assoc($result);
$faculty_id = $dean['faculty_id'];

// Fetch departments for the faculty
$departments_query = "SELECT * FROM Department WHERE faculty_id='$faculty_id'";
$departments_result = mysqli_query($conn, $departments_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dean Page</title>
</head>
<body>
    <h2>Welcome, <?php echo $dean_name; ?></h2>
    <h3>Department Exam Schedules</h3>
    <form action="view_department_exams.php" method="post">
        <label for="department">Department:</label>
        <select id="department" name="department">
            <?php while ($department = mysqli_fetch_assoc($departments_result)) { ?>
                <option value="<?php echo $department['department_id']; ?>"><?php echo $department['department_name']; ?></option>
            <?php } ?>
        </select><br>
        <input type="submit" value="View Exams">
    </form>

    <h3>Exam Schedule</h3>
    <table border="1">
        <tr>
            <th>Exam Date</th>
            <th>Exam Time</th>
            <th>Exam Name</th>
        </tr>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $department_id = $_POST['department'];
            $exams_query = "SELECT * FROM Exam WHERE course_id IN (SELECT course_id FROM Courses WHERE department_id='$department_id') ORDER BY exam_date, exam_time";
            $exams_result = mysqli_query($conn, $exams_query);
            while ($exam = mysqli_fetch_assoc($exams_result)) {
                echo "<tr><td>{$exam['exam_date']}</td><td>{$exam['exam_time']}</td><td>{$exam['exam_name']}</td></tr>";
            }
        }
        ?>
    </table>
</body>
</html>
