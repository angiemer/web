<?php
require_once "db.php";
session_start(); // Start session to access user data

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {
        // Verify database connection
        if (!$conn || $conn->connect_error) {
            throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "Connection not established"));
        }

        // Get user ID
        $user_id = $_SESSION['id'];

        // Get favorite song data from POST
        if (!isset($_POST['remove_fav'])) {
            throw new Exception("No favorite song data provided");
        }
        $favorite_data = json_decode($_POST['remove_fav'], true);
        if (!$favorite_data || !isset($favorite_data['song_id'])) {
            throw new Exception("Invalid favorite song data");
        }

        $video_id = $favorite_data['song_id'];

        // Step 1: Get the user's favorites entry
        $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare favorites select query: " . $conn->error);
        }
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows === 0) {
            throw new Exception("No favorites entry found for user");
        }
        $favorite = $result->fetch_assoc();
        $favor_id = $favorite['id'];

        // Step 2: Get the song ID from songs table
        $stmt = $conn->prepare("SELECT id FROM songs WHERE song_id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare song select query: " . $conn->error);
        }
        $stmt->bind_param('s', $video_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows === 0) {
            throw new Exception("Song not found");
        }
        $song = $result->fetch_assoc();
        $song_id = $song['id'];

        // Step 3: Delete the favorite_songs entry
        $stmt = $conn->prepare("DELETE FROM favorite_songs WHERE favor_id = ? AND song_id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare favorite_songs delete query: " . $conn->error);
        }
        $stmt->bind_param('ii', $favor_id, $song_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete favorite song: " . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();
        header("Location: dashboard.php"); // Success redirect
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error removing favorite song: " . $e->getMessage());
        $err_message = "Cannot remove song from favorites";
        header("Location: dashboard.php?err_message=" . urlencode($err_message));
        exit;
    }

    $conn->close();
}
?>