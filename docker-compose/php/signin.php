<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['signin'])) {
    // Sanitize input
    $userFirstname = filter_input(INPUT_POST, "firstname", FILTER_SANITIZE_SPECIAL_CHARS);
    $userLastname = filter_input(INPUT_POST, "lastname", FILTER_SANITIZE_SPECIAL_CHARS);
    $userUsername = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $userPassword = trim($_POST['password'] ?? '');
    $userEmail = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);

    try {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $userUsername, $userEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = 'Username or email already exists.';
            $err_message = "Username or email already exists.";
            header('Location: signup.php?err_message=' . $err_message);
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

            $query = $conn->prepare("SELECT id FROM users WHERE username = ?");

            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }

            $query->bind_param('s', $userUsername);
            $query->execute();
            $result = $query->get_result();
            $user = $result->fetch_assoc();

            $_SESSION['id'] = $user['id'];

            // Redirect to ggl.php for YouTube authentication
            header('Location: ggl.php');
            $stmt->close();
            $conn->close();
            exit;
        } else {
            // Failure: Redirect to signup.php
            $err_message = "Something went wrong :(";
            header('Location:signup.php?err_message=' . $err_message);
            $stmt->close();
            $conn->close();
            exit;
        }
    } catch (Exception $e) {
        // Log error and redirect
        $err_message = "Something went wrong :(";
        header('Location:signup.php?err_message=' . $err_message);
        $conn->close();
        exit;
    }
} else {
    // Invalid request
    $err_message = "Something went wrong :(";
    header('Location:signup.php?err_message=' . $err_message);
    exit;
}
?>