<?php
require_once 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: start.php');
    exit;
}

    // Handle POST request for adding a new playlist
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $playlistName = filter_input(INPUT_POST, "playlist_name", FILTER_SANITIZE_SPECIAL_CHARS);
        

        if (!$conn || $conn->connect_error) {
            throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "Connection not established"));
        }

        // Insert new playlist (without song data)
        $stmt = $conn->prepare("INSERT INTO playlists (user_id, name) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("is", $_SESSION["id"], $playlistName);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }
        
        // Redirect to dashboard to show updated playlists
        header('Location: dashboard.php');
        $conn->close();
        exit;
    }
?>