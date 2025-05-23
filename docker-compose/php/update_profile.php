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

$newFirstName = trim($_POST['first_name'] ?? '');
$newLastName = trim($_POST['last_name'] ?? '');
$newUsername = trim($_POST['username'] ?? '');
$newEmail = trim($_POST['email'] ?? '');
$newAvatar = trim($_POST['avatar'] ?? '');
$newPassword = trim($_POST['password'] ?? '');
$loggedUser = $_SESSION['username'];

// Add password update if provided
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

// try {
//     // Έλεγχος αν το username ή το email χρησιμοποιούνται από άλλον χρήστη
//     $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND username != ?");
//     if (!$stmt) {
//         throw new Exception("Failed to prepare statement: " . $conn->error);
//     }
//     $stmt->bind_param('sss', $newUsername, $newEmail, $_SESSION['username']);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     if ($result->num_rows > 0) {
//         http_response_code(400);
//         header('Content-Type: application/json');
//         echo json_encode(['success' => false, 'error' => 'Username or email already exists']);
//         $stmt->close();
//         exit;
//     }
//     $stmt->close();

//     $query .= " WHERE username = ?";
//     $params[] = $_SESSION['username'];
//     $types .= 's';

//     // Εκτέλεση του query
//     $stmt = $conn->prepare($query);
//     if (!$stmt) {
//         throw new Exception("Failed to prepare update statement: " . $conn->error);
//     }
//     $stmt->bind_param($types, ...$params);
//     if ($stmt->execute()) {
//         // Ενημέρωση της συνεδρίας
//         $_SESSION['username'] = $newUsername;
//         header('Content-Type: application/json');
//         echo json_encode(['success' => true]);
//     } else {
//         http_response_code(500);
//         header('Content-Type: application/json');
//         echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
//     }
//     $stmt->close();
// } catch (Exception $e) {
//     http_response_code(500);
//     echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
// }
// $conn->close();
?>