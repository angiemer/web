<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json'); // response as json
require_once 'db.php';

// Εάν είναι POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['userUsername'] ?? '');
    $password = trim($_POST['userPassword'] ?? '');

    // Επικύρωση δεδομένων
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all fields']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT username, password FROM users WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Επαλήθευση κωδικού
            if (password_verify($password, $user['password'])) {
                // Επιτυχής σύνδεση
                $_SESSION['username'] = $user['username'];
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();

                // Επιστροφή JSON για AJAX ή ανακατεύθυνση για φόρμες
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
                } else {
                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                // Λανθασμένος κωδικός
                echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
            }
        } else {
            // Χρήστης δεν βρέθηκε
            echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'An error occurred during login. Please try again.']);
    }
} else {
    // Μη έγκυρο request
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn->close();
?>