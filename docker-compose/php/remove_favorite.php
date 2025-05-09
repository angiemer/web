<?php
session_start();

// Έλεγχος αν είναι συνδεδεμένος ο χρήστης
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];
$video_id = $_POST['video_id'] ?? null;

if (!$video_id) {
    echo "❌ Song not found for removal.";
    exit();
}

// Σύνδεση με την βάση
$servername = "di_inter_tech_2025_mysql";
$username = "webuser";
$password = "webpass";
$dbname = "di_internet_technologies_project";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Αφαίρεση του τραγουδιού από τα αγαπημένα
$stmt = $conn->prepare("DELETE FROM favorites WHERE user_email = ? AND video_id = ?");
$stmt->bind_param("ss", $email, $video_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "✔️ Song removed from favorites";
} else {
    echo "❌ Song not found in favorites.";
}

$stmt->close();
$conn->close();
?>
