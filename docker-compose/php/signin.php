<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['signin'])) {
    // Sanitize input
    $userFirstname = trim($_POST['firstname'] ?? '');
    $userLastname = trim($_POST['lastname'] ?? '');
    $userUsername = trim($_POST['username'] ?? '');
    $userPassword = trim($_POST['password'] ?? '');
    $userEmail = trim($_POST['email'] ?? '');

    try {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $userUsername, $userEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = 'Username or email already exists.';
            header('Location: signup.php');
            $stmt->close();
            exit;
        }
        $stmt->close();

        // Hash the password
        $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

        // Insert the new user
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, password, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $userFirstname, $userLastname, $userUsername, $hashedPassword, $userEmail);

        if ($stmt->execute()) {
            // Set session variables
            $_SESSION['username'] = $userUsername;
            $_SESSION['first_name'] = $userFirstname;
            $_SESSION['last_name'] = $userLastname;
            $_SESSION['email'] = $userEmail;
            $_SESSION['image'] = './image.png';

            // Redirect to ggl.php for YouTube authentication
            header('Location: ggl.php');
            $stmt->close();
            $conn->close();
            exit;
        } else {
            // Failure: Redirect to signup.php
            $_SESSION['error'] = 'Failed to register user.';
            header('Location: signup.php');
            $stmt->close();
            $conn->close();
            exit;
        }
    } catch (Exception $e) {
        // Log error and redirect
        error_log("Signup error: " . $e->getMessage());
        $_SESSION['error'] = 'An error occurred during registration. Please try again.';
        header('Location: signup.php');
        $conn->close();
        exit;
    }
} else {
    // Invalid request
    header('Location: signup.php');
    exit;
}
?>