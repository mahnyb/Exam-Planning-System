<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['forgot_password'])) {
        // Handle the "Forgot password" functionality
        header("Location: reset_password.php"); // Redirect to a simple reset password page
        exit();
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // First query to validate the user
        $sql = "SELECT * FROM Employee WHERE username = ? AND password = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $_SESSION['user_id'] = $user['employee_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['department_id'] = $user['department_id'];

            // Second query to get the faculty_id
            $department_id = $user['department_id'];
            $faculty_sql = "SELECT faculty_id FROM Department WHERE department_id = ? LIMIT 1";
            $faculty_stmt = $conn->prepare($faculty_sql);
            $faculty_stmt->bind_param("i", $department_id);
            $faculty_stmt->execute();
            $faculty_result = $faculty_stmt->get_result();
            $faculty = $faculty_result->fetch_assoc();

            if ($faculty) {
                $_SESSION['faculty_id'] = $faculty['faculty_id'];
            } else {
                // Handle case where faculty_id is not found 
                $_SESSION['faculty_id'] = null;
            }

            // Redirect user based on their role
            switch ($user['role']) {
                case 'Assistant':
                    header("Location: assistant.php");
                    break;
                case 'Secretary':
                    header("Location: secretary_page.php");
                    break;
                case 'Head of Department':
                    header("Location: head_of_department.php");
                    break;
                case 'Head of Secretary':
                    header("Location: head_of_secretary.php");
                    break;
                case 'Dean':
                    header("Location: dean.php");
                    break;
                default:
                    $error = "Invalid user role.";
            }
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Welcome to the Exam Planning System ◝(ᵔᵕᵔ)◜</h1>
    <h5>By Mahny Barazandehtar - 20210702004</h5>
    <h2>Login</h2>
    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <form action="login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Login</button>
        <button type="submit" name="forgot_password">Forgot Password</button>
    </form>
</body>
</html>
