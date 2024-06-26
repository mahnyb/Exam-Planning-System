<?php
session_start();
include 'db.php';

// Check if the user is logged in and is the Head of Secretary
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Head of Secretary') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$faculty_id = isset($_SESSION['faculty_id']) ? $_SESSION['faculty_id'] : 0; // Set default value

// Fetch courses for the head of secretary's faculty
$courses_sql = "SELECT course_id, course_name FROM Courses WHERE department_id IN (SELECT department_id FROM Department WHERE faculty_id = ?)";
$courses_stmt = $conn->prepare($courses_sql);
if ($courses_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$courses_stmt->bind_param("i", $faculty_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();

// Fetch assistants for the faculty
$assistants_sql = "SELECT employee_id, first_name, last_name, score FROM Employee WHERE department_id IN (SELECT department_id FROM Department WHERE faculty_id = ?) AND role = 'Assistant' ORDER BY score ASC";
$assistants_stmt = $conn->prepare($assistants_sql);
if ($assistants_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$assistants_stmt->bind_param("i", $faculty_id);
$assistants_stmt->execute();
$assistants_result = $assistants_stmt->get_result();
$assistants = $assistants_result->fetch_all(MYSQLI_ASSOC);

// Fetch departments for course insertion
$departments_sql = "SELECT department_id, department_name FROM Department WHERE faculty_id = ?";
$departments_stmt = $conn->prepare($departments_sql);
$departments_stmt->bind_param("i", $faculty_id);
$departments_stmt->execute();
$departments_result = $departments_stmt->get_result();

// Fetch faculties for course insertion
$faculties_sql = "SELECT faculty_id, faculty_name FROM Faculty";
$faculties_result = $conn->query($faculties_sql);

// Handle course insertion
if (isset($_POST['insert_course'])) {
    $course_name = $_POST['course_name'];
    $selected_department_id = $_POST['department_id'];

    $insert_course_sql = "INSERT INTO Courses (course_name, department_id) VALUES (?, ?)";
    $insert_course_stmt = $conn->prepare($insert_course_sql);
    if ($insert_course_stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $insert_course_stmt->bind_param("si", $course_name, $selected_department_id);
    $insert_course_stmt->execute();

    header("Location: head_of_secretary.php");
    exit();
}

// Handle exam scheduling
if (isset($_POST['schedule_exam'])) {
    $course_id = $_POST['course_id'];
    $exam_name = $_POST['exam_name'];
    $exam_date = $_POST['exam_date'];
    $exam_time = $_POST['exam_time'];
    $num_classes = $_POST['num_classes'];
    $num_assistants = $_POST['num_assistants'];

    // Select assistants with the lowest scores
    $selected_assistants = [];
    $count = 0;
    foreach ($assistants as $assistant) {
        if ($count < $num_assistants && $assistant['score'] != -1) {
            $selected_assistants[] = $assistant['employee_id'];
            $count++;
        }
    }

    $insert_exam_sql = "INSERT INTO Exam (course_id, exam_name, exam_date, exam_time, num_classes) VALUES (?, ?, ?, ?, ?)";
    $insert_exam_stmt = $conn->prepare($insert_exam_sql);
    if ($insert_exam_stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $insert_exam_stmt->bind_param("isssi", $course_id, $exam_name, $exam_date, $exam_time, $num_classes);
    $insert_exam_stmt->execute();
    $exam_id = $conn->insert_id;

    foreach ($selected_assistants as $assistant_id) {
        $assign_assistant_sql = "INSERT INTO ExamAssistants (exam_id, assistant_id) VALUES (?, ?)";
        $assign_assistant_stmt = $conn->prepare($assign_assistant_sql);
        if ($assign_assistant_stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $assign_assistant_stmt->bind_param("ii", $exam_id, $assistant_id);
        $assign_assistant_stmt->execute();

        // Update assistant's score
        $update_score_sql = "UPDATE Employee SET score = score + 1 WHERE employee_id = ?";
        $update_score_stmt = $conn->prepare($update_score_sql);
        if ($update_score_stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $update_score_stmt->bind_param("i", $assistant_id);
        $update_score_stmt->execute();
    }

    header("Location: head_of_secretary.php");
    exit();
}

// Fetch assistant scores for the faculty
$scores_sql = "SELECT first_name, last_name, score FROM Employee WHERE department_id IN (SELECT department_id FROM Department WHERE faculty_id = ?) AND role = 'Assistant' ORDER BY score ASC";
$scores_stmt = $conn->prepare($scores_sql);
if ($scores_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$scores_stmt->bind_param("i", $faculty_id);
$scores_stmt->execute();
$scores_result = $scores_stmt->get_result();
$assistant_scores = $scores_result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo "Welcome, " . $first_name . " " . $last_name; ?></title>
</head>
<body>
    <h1>Welcome, <?php echo $first_name . " " . $last_name; ?></h1>
    
    <h2>Insert Course</h2>
    <form action="head_of_secretary.php" method="post">
        <label for="faculty_id">Faculty:</label>
        <select id="faculty_id" name="faculty_id" required>
            <?php while ($faculty = $faculties_result->fetch_assoc()) { ?>
                <option value="<?php echo $faculty['faculty_id']; ?>"><?php echo $faculty['faculty_name']; ?></option>
            <?php } ?>
        </select>
        <br>
        <label for="department_id">Department:</label>
        <select id="department_id" name="department_id" required>
            <?php while ($department = $departments_result->fetch_assoc()) { ?>
                <option value="<?php echo $department['department_id']; ?>"><?php echo $department['department_name']; ?></option>
            <?php } ?>
        </select>
        <br>
        <label for="course_name">Course Name:</label>
        <input type="text" id="course_name" name="course_name" required>
        <br>
        <button type="submit" name="insert_course">Insert Course</button>
    </form>

    <h2>Schedule Exam</h2>
    <form action="head_of_secretary.php" method="post">
        <label for="course_id">Course:</label>
        <select id="course_id" name="course_id" required>
            <?php while ($course = $courses_result->fetch_assoc()) { ?>
                <option value="<?php echo $course['course_id']; ?>"><?php echo $course['course_name']; ?></option>
            <?php } ?>
        </select>
        <br>
        <label for="exam_name">Exam Name:</label>
        <input type="text" id="exam_name" name="exam_name" required>
        <br>
        <label for="exam_date">Exam Date:</label>
        <input type="date" id="exam_date" name="exam_date" required>
        <br>
        <label for="exam_time">Exam Time:</label>
        <input type="time" id="exam_time" name="exam_time" required>
        <br>
        <label for="num_classes">Number of Classes:</label>
        <input type="number" id="num_classes" name="num_classes" required>
        <br>
        <label for="num_assistants">Number of Assistants:</label>
        <input type="number" id="num_assistants" name="num_assistants" required>
        <br>
        <button type="submit" name="schedule_exam">Schedule Exam</button>
    </form>

    <h2>Assistant Scores</h2>
    <table border="1">
        <tr>
            <th>Assistant Name</th>
            <th>Score</th>
        </tr>
        <?php foreach ($assistant_scores as $score) { ?>
            <tr>
                <td><?php echo $score['first_name'] . " " . $score['last_name']; ?></td>
                <td><?php echo $score['score']; ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
