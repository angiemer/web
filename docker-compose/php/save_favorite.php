<?php
session_start();

// Αν δεν είναι συνδεδεμένος, τον μπλοκάρουμε
if (!isset($_SESSION['email'])) {
    die("You have to be loged in.");
}

$email = $_SESSION['email'];

$servername = "di_inter_tech_2025_mysql";
$username = "webuser";
$password = "webpass";
$dbname = "di_internet_technologies_project";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

// Παίρνουμε τα δεδομένα από το AJAX POST
$video_id = $_POST['video_id'];
$title = $_POST['title'];
$thumbnail = $_POST['thumbnail'];

// Αποφυγή διπλοεγγραφής του ίδιου βίντεο από τον ίδιο χρήστη
$check = $conn->prepare("SELECT id FROM favorites WHERE video_id = ? AND user_email = ?");
$check->bind_param("ss", $video_id, $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "🔁Song is already in your favorites.";
} else {
    $stmt = $conn->prepare("INSERT INTO favorites (video_id, title, thumbnail, user_email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $video_id, $title, $thumbnail, $email);
    if ($stmt->execute()) {
        echo "✅ Song saved in your favorites!";
    } else {
        echo "❌ Error for saving.";
    }
}

$conn->close();
?>
