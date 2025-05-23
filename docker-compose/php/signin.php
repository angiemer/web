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
            // Success: Redirect to login.php
            $_SESSION['username'] = $userUsername;
            header('Location: dashboard.php');
            $stmt->close();
            $conn->close();
            exit;
        } else {
            // Failure: Exit with error
            $_SESSION['error'] = 'Failed to register user.';
            header('Location: signin.php');
            $stmt->close();
            $conn->close();
            exit;
        }
    } catch (Exception $e) {
        // Log error and exit
        error_log("Signin error: " . $e->getMessage());
        $_SESSION['error'] = 'An error occurred during registration. Please try again.';
        header('Location: signin.php');
        $conn->close();
        exit;
    }
} else {
    // Invalid request
    header('Location: signin.php');
    exit;
}
?>