<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json'); // response as json
require_once 'db.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['userUsername'] ?? '');
    $password = trim($_POST['userPassword'] ?? '');

    // Validate input
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all fields']);
        exit;
    }

    try {
        // Prepare query to fetch user data
        $stmt = $conn->prepare("SELECT username, password, first_name, last_name, email FROM users WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Successful login: Set session variables
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['image'] = './image.png';
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();
                $_SESSION['searched'] = false;
                $_SESSION['array_of_response'] = '';

                // Redirect to ggl.php for YouTube authentication
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'redirect' => 'ggl.php']);
                } else {
                    header('Location: ggl.php');
                    exit;
                }
            } else {
                // Invalid password
                echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
            }
        } else {
            // User not found
            echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'An error occurred during login. Please try again.']);
    }
} else {
    // Invalid request method
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn->close();
?>