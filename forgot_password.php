<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];

    // Debugging: Output the received username and new password
    error_log("Username: $username");
    error_log("New Password: $new_password");

    $sql = "UPDATE Employee SET password = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $new_password, $username);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<p>Password has been reset successfully.</p>";
    } else {
        echo "<p>Error: Could not reset password. Please check the username.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
</head>
<body>
    <h1>Forgot Password</h1>
    <p>If you dare to lie about this not being your account, your soul will be irrevocably claimed by the devil.<br>
    You will be condemned to an eternity of unimaginable torment in the darkest depths of hell,<br>
    where the screams of the damned will be your only company,<br>
    and every moment will be an unending nightmare from which there is no escape.</p>

    <form action="forgot_password.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required><br>
        <input type="submit" value="Reset Password">
    </form>
</body>
</html>
