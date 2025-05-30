<?php
require_once "db.php";
session_start(); // Start session to access user data

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {

        // Get user ID
        $user_id = $_SESSION['id'];

        // Get playlist data from POST
        if (!isset($_POST['remove_list'])) {
            throw new Exception("No playlist data provided");
        }
        $playlist_data = json_decode($_POST['remove_list'], true);
        if (!$playlist_data || !isset($playlist_data['id'])) {
            throw new Exception("Invalid playlist data");
        }

        $playlist_id = $playlist_data['id'];

        // Delete the playlist (cascade deletes playlist_songs)
        $stmt = $conn->prepare("DELETE FROM playlists WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare playlist delete query: " . $conn->error);
        }
        $stmt->bind_param('ii', $playlist_id, $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete playlist: " . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();
        header("Location: dashboard.php"); // Success redirect
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error removing playlist: " . $e->getMessage());
        $err_message = "Cannot remove playlist";
        header("Location: dashboard.php?err_message=" . urlencode($err_message));
        exit;
    }

    $conn->close();
}
?>