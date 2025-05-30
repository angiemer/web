<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Καταγραφή πρόσβασης για αποσφαλμάτωση
error_log("update_profile.php accessed. Session: " . print_r($_SESSION, true));

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Έλεγχος αν το αίτημα είναι POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Λήψη δεδομένων από το αίτημα
//./ $input = json_decode(file_get_contents('php://=input'), true);
// error_log("Input received: " . print_r($input, true));

$newFirstName = filter_input(INPUT_POST, "first_name", FILTER_SANITIZE_SPECIAL_CHARS);
$newLastName = filter_input(INPUT_POST, "last_name", FILTER_SANITIZE_SPECIAL_CHARS);
$newUsername = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
$newEmail = filter_input(INPUT_POST, "email", FILTER_SANITIZE_SPECIAL_CHARS);
$newAvatar = trim($_POST['avatar'] ?? '');
$newPassword = trim($_POST['password'] ?? '');
$loggedUser = $_SESSION['username'];

if ($newFirstName) {
    $stmt = $conn->prepare("UPDATE users SET first_name = ? WHERE username = ?");
    $stmt->bind_param('ss', $newFirstName, $loggedUser);
    $stmt->execute();
}
if ($newLastName) {
    $stmt = $conn->prepare("UPDATE users SET last_name = ? WHERE username = ?");
    $stmt->bind_param('ss', $newLastName, $loggedUser);
    $stmt->execute();
}
if ($newUsername) {
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE username = ?");
    $stmt->bind_param('ss', $newUsername, $loggedUser);
    $stmt->execute();
    $_SESSION['username'] = $newUsername;
    $loggedUser = $newUsername;
}
if ($newPassword) {
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt->bind_param('ss', $hashedPassword, $loggedUser);
    $stmt->execute();
}
if ($newEmail) {
    $stmt = $conn->prepare("UPDATE users SET email = ? WHERE username = ?");
    $stmt->bind_param('ss', $newEmail, $loggedUser);
    $stmt->execute();
}
if ($newAvatar) {
    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE username = ?");
    $stmt->bind_param('ss', $newAvatar, $loggedUser);
    $stmt->execute();
}

header('Location: dashboard.php');
exit;

?>