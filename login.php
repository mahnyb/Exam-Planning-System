<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate credentials
    $query = "SELECT * FROM Employee WHERE name='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $_SESSION['username'] = $username;
        // Redirect based on role
        $row = mysqli_fetch_assoc($result);
        switch ($row['role']) {
            case 'Assistant':
                header('Location: assistant.php');
                break;
            case 'Secretary':
                header('Location: secretary.php');
                break;
            // Add other cases for different roles
        }
    } else {
        echo "Invalid username or password.";
    }
}
?>