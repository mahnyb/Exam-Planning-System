<?php
session_start();
include 'db.php';

// Check if the user is logged in and is a Secretary
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Secretary') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$department_id = isset($_SESSION['department_id']) ? $_SESSION['department_id'] : 0; // Set default value

// Fetch courses for the secretary's department
$courses_sql = "SELECT course_id, course_name FROM Courses WHERE department_id = ?";
$courses_stmt = $conn->prepare($courses_sql);
if ($courses_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$courses_stmt->bind_param("i", $department_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();

// Fetch assistants for the secretary's department
$assistants_sql = "SELECT employee_id, first_name, last_name, score FROM Employee WHERE department_id = ? AND role = 'Assistant' ORDER BY score ASC";
$assistants_stmt = $conn->prepare($assistants_sql);
if ($assistants_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$assistants_stmt->bind_param("i", $department_id);
$assistants_stmt->execute();
$assistants_result = $assistants_stmt->get_result();
$assistants = $assistants_result->fetch_all(MYSQLI_ASSOC);

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

    // Check for intersecting courses for selected assistants
    $intersecting_assistants = [];
    foreach ($selected_assistants as $assistant_id) {
        $intersect_sql = "
            SELECT course_id FROM AssistantCourses 
            WHERE employee_id = ? AND course_id IN (
                SELECT course_id FROM Courses 
                WHERE course_day = (SELECT DAYNAME(?) FROM dual) 
                AND course_time = ?
            )
        ";
        $intersect_stmt = $conn->prepare($intersect_sql);
        if ($intersect_stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $intersect_stmt->bind_param("iss", $assistant_id, $exam_date, $exam_time);
        $intersect_stmt->execute();
        $intersect_result = $intersect_stmt->get_result();
        if ($intersect_result->num_rows > 0) {
            $intersecting_assistants[] = $assistant_id;
        }
    }

    if (empty($intersecting_assistants)) {
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

        header("Location: secretary_page.php");
        exit();
    } else {
        echo "Error: Selected assistants have intersecting courses.";
    }
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

    <h2>Schedule Exam</h2>
    <form action="secretary_page.php" method="post">
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
</body>
</html>
