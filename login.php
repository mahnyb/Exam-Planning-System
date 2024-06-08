<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Debugging: Output the received username and password
    error_log("Username: $username");
    error_log("Password: $password");

    $sql = "SELECT * FROM Employee WHERE username = ? AND password = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Debugging: Output the fetched user data
    error_log("User data: " . print_r($user, true));

    if ($user) {
        $_SESSION['user_id'] = $user['employee_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
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
    <h1>Welcome to the Exam Planning System</h1>
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
    </form>
</body>
</html>
