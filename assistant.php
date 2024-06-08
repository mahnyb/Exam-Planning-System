<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.html');
    exit();
}

// Fetch assistant's details
$assistant_name = $_SESSION['username'];
$query = "SELECT * FROM Employee WHERE name='$assistant_name'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching assistant details: " . mysqli_error($conn));
}

$assistant = mysqli_fetch_assoc($result);
$assistant_id = $assistant['employee_id'];

// Fetch courses for the assistant's department
$department_id = $assistant['department_id'];
$courses_query = "SELECT * FROM Courses WHERE department_id='$department_id'";
$courses_result = mysqli_query($conn, $courses_query);

if (!$courses_result) {
    die("Error fetching courses: " . mysqli_error($conn));
}

// Fetch exams for the assistant's department
$exams_query = "SELECT * FROM Exam WHERE course_id IN (SELECT course_id FROM Courses WHERE department_id='$department_id')";
$exams_result = mysqli_query($conn, $exams_query);

if (!$exams_result) {
    die("Error fetching exams: " . mysqli_error($conn));
}

// Fetch assistant's course selections
$selected_courses_query = "SELECT Courses.course_name, Courses.course_id FROM AssistantCourses 
                           JOIN Courses ON AssistantCourses.course_id = Courses.course_id 
                           WHERE AssistantCourses.assistant_id='$assistant_id'";
$selected_courses_result = mysqli_query($conn, $selected_courses_query);

if (!$selected_courses_result) {
    die("Error fetching selected courses: " . mysqli_error($conn));
}

// Create a weekly plan array
$weekly_plan = [];
while ($course = mysqli_fetch_assoc($selected_courses_result)) {
    // Assume each course has fixed time slots, this can be extended to fetch actual schedule
    $weekly_plan[] = ['course_name' => $course['course_name'], 'time_slot' => '9:00-11:00', 'day' => 'Monday'];
    // Add more time slots and days as per actual schedule
}

// Fetch exams and add to the weekly plan
while ($exam = mysqli_fetch_assoc($exams_result)) {
    $weekly_plan[] = ['course_name' => $exam['exam_name'], 'time_slot' => '18:00-20:00', 'day' => 'Tuesday']; // Example data
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assistant Page</title>
</head>
<body>
    <h2>Welcome, <?php echo $assistant_name; ?></h2>
    <h3>Select Courses</h3>
    <form action="select_courses.php" method="post">
        <label for="courses">Courses:</label>
        <select id="courses" name="courses[]" multiple>
            <?php while ($course = mysqli_fetch_assoc($courses_result)) { ?>
                <option value="<?php echo $course['course_id']; ?>"><?php echo $course['course_name']; ?></option>
            <?php } ?>
        </select><br>
        <input type="submit" value="Submit">
    </form>
    
    <h3>Weekly Plan</h3>
    <table border="1">
        <tr>
            <th>Time Slot</th>
            <th>Monday</th>
            <th>Tuesday</th>
            <th>Wednesday</th>
            <th>Thursday</th>
            <th>Friday</th>
        </tr>
        <?php
        // Generate the weekly plan table
        $time_slots = ['9:00-11:00', '11:00-13:00', '13:00-15:00', '15:00-17:00'];
        foreach ($time_slots as $slot) {
            echo "<tr>";
            echo "<td>$slot</td>";
            for ($i = 1; $i <= 5; $i++) {
                $day = date('l', strtotime("Sunday +$i days"));
                $course_name = '';
                foreach ($weekly_plan as $plan) {
                    if ($plan['time_slot'] == $slot && $plan['day'] == $day) {
                        $course_name = $plan['course_name'];
                    }
                }
                echo "<td>$course_name</td>";
            }
            echo "</tr>";
        }
        ?>
    </table>
    <button onclick="location.reload()">Refresh</button>
</body>
</html>
