<?php
require_once "db.php";
session_start(); // Start session to access user data

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['id'])) {
        error_log("User not logged in");
        exit;
    }

    try {
        // Verify database connection
        if (!$conn || $conn->connect_error) {
            throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "Connection not established"));
        }

        // Get user ID
        $user_id = $_SESSION['id'];

        // Get song data from POST
        if (!isset($_POST['song_values'])) {
            throw new Exception("No song data provided");
        }
        $song_data = json_decode($_POST['song_values'], true);
        if (!$song_data) {
            throw new Exception("Invalid song data");
        }

        // Extract video_id, title, and thumbnail based on data format
        if (isset($song_data['id']['videoId'], $song_data['snippet']['title'], $song_data['snippet']['thumbnails']['medium']['url'])) {
            // YouTube API format (from videos_section)
            $video_id = $song_data['id']['videoId'];
            $title = $song_data['snippet']['title'];
            $thumbnail = $song_data['snippet']['thumbnails']['medium']['url'];
        } elseif (isset($song_data['song_id'], $song_data['title'], $song_data['thumbnail'])) {
            // Playlist format (from playlistsSection)
            $video_id = $song_data['song_id'];
            $title = $song_data['title'];
            $thumbnail = $song_data['thumbnail'];
        } else {
            throw new Exception("Invalid song data structure");
        }

        // Start transaction
        $conn->begin_transaction();

        // Step 1: Insert or retrieve song in songs table
        $stmt = $conn->prepare("SELECT id FROM songs WHERE song_id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare song select query: " . $conn->error);
        }
        $stmt->bind_param('s', $video_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            // Song exists, get its ID
            $song = $result->fetch_assoc();
            $song_id = $song['id'];
        } else {
            // Insert new song
            $stmt = $conn->prepare("INSERT INTO songs (title, song_id, thumbnail) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Failed to prepare song insert query: " . $conn->error);
            }
            $stmt->bind_param('sss', $title, $video_id, $thumbnail);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert song: " . $stmt->error);
            }
            $song_id = $conn->insert_id;
            $stmt->close();
        }

        // Step 2: Check if user has a favorites entry
        $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare favorites select query: " . $conn->error);
        }
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            // Favorites entry exists, get its ID
            $favorite = $result->fetch_assoc();
            $favor_id = $favorite['id'];
        } else {
            // Create new favorites entry
            $stmt = $conn->prepare("INSERT INTO favorites (user_id) VALUES (?)");
            if (!$stmt) {
                throw new Exception("Failed to prepare favorites insert query: " . $conn->error);
            }
            $stmt->bind_param('i', $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert favorites: " . $stmt->error);
            }
            $favor_id = $conn->insert_id;
            $stmt->close();
        }

        // Step 3: Link song to favorites in favorite_songs (avoid duplicates)
        $stmt = $conn->prepare("SELECT 1 FROM favorite_songs WHERE favor_id = ? AND song_id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare favorite_songs check query: " . $conn->error);
        }
        $stmt->bind_param('ii', $favor_id, $song_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows == 0) {
            // Insert into favorite_songs
            $stmt = $conn->prepare("INSERT INTO favorite_songs (favor_id, song_id) VALUES (?, ?)");
            if (!$stmt) {
                throw new Exception("Failed to prepare favorite_songs insert query: " . $conn->error);
            }
            $stmt->bind_param('ii', $favor_id, $song_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert favorite_songs: " . $stmt->error);
            }
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();
        header("Location: dashboard.php"); // Success redirect
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error adding favorite song: " . $e->getMessage());
        $err_message = "Cannot add song to favorites";
        header("Location: dashboard.php?err_message=" . urlencode($err_message));
        exit;
    }

    $conn->close();
}
?>