<?php
session_start();

// Αν δεν είναι συνδεδεμένος, τον πετάμε πίσω
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];

// Σύνδεση DB
$servername = "di_inter_tech_2025_mysql";
$username = "webuser";
$password = "webpass";
$dbname = "di_internet_technologies_project";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

// Φέρνουμε τα αγαπημένα του χρήστη
$stmt = $conn->prepare("SELECT id, video_id, title, thumbnail FROM favorites WHERE user_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <title>My favorite songs</title>
  <style>
    .video-box {
        border: 1px solid #ccc;
        padding: 12px;
        margin-bottom: 15px;
        border-radius: 10px;
    }
    iframe {
        width: 100%;
        height: 300px;
        border: none;
    }
    .remove-btn {
        background-color: red;
        color: white;
        border: none;
        padding: 8px 12px;
        margin-top: 10px;
        cursor: pointer;
    }
  </style>
</head>
<body>

<h2>🎵 My favorites</h2>

<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $video_id = htmlspecialchars($row['video_id']);
        $title = htmlspecialchars($row['title']);
        $id = $row['id'];

        echo "<div class='video-box'>";
        echo "<h3>$title</h3>";
        echo "<iframe src='https://www.youtube.com/embed/$video_id' allowfullscreen></iframe>";
        echo "<form method='POST' action='remove_favorite.php'>";
        echo "<input type='hidden' name='id' value='$id'>";
        echo "<button class='remove-btn' type='submit'>🗑️ Remove</button>";
        echo "</form>";
        echo "</div>";
    }
} else {
    echo "<p>No favorites yet.</p>";
}

$conn->close();
?>

</body>
</html>
