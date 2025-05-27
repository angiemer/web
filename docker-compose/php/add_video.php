<?php
session_start();
require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $playlistName = filter_input(INPUT_POST, "playlistName", FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$playlistName) {
        $err_message = "Playlist name is required";
        header("Location: dashboard.php?err_message=" . urlencode($err_message));
        exit;
    }

    // Check if playlist exists
    $query = $conn->prepare("SELECT id FROM playlists WHERE name = ?");
    $query->bind_param("s", $playlistName);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows < 1) {
        $err_message = "Playlist name not found";
        header("Location: dashboard.php?err_message=" . urlencode($err_message));
        $query->close();
        $conn->close();
        exit;
    }

    $playlist = $result->fetch_assoc();
    $playlistId = $playlist['id'];
    $query->close();

    // Parse video data from POST
    $response = json_decode($_POST['values'], true);
    if (!$response || !isset($response['snippet']['title'], $response['id']['videoId'], $response['snippet']['thumbnails']['medium']['url'])) {
        $err_message = "Invalid video data";
        header("Location: dashboard.php?err_message=" . urlencode($err_message));
        $conn->close();
        exit;
    }

    $title = $response['snippet']['title'];
    $videoId = $response['id']['videoId'];
    $thumbnail = $response['snippet']['thumbnails']['medium']['url'];

    // Step 1: Check if the song already exists in songs table
    $checkSong = $conn->prepare("SELECT id FROM songs WHERE song_id = ?");
    $checkSong->bind_param("s", $videoId);
    $checkSong->execute();
    $songResult = $checkSong->get_result();

    if ($songResult->num_rows > 0) {
        $song = $songResult->fetch_assoc();
        $songDbId = $song['id'];
    } else {
        // Insert new song
        $insertSong = $conn->prepare("INSERT INTO songs (title, song_id, thumbnail) VALUES (?, ?, ?)");
        $insertSong->bind_param("sss", $title, $videoId, $thumbnail);
        $insertSong->execute();
        $songDbId = $insertSong->insert_id;
        $insertSong->close();
    }
    $checkSong->close();

    // Step 2: Check if the song is already in the playlist
    $checkLink = $conn->prepare("SELECT 1 FROM playlist_songs WHERE playlist_id = ? AND song_id = ?");
    $checkLink->bind_param("ii", $playlistId, $songDbId);
    $checkLink->execute();
    $linkResult = $checkLink->get_result();

    if ($linkResult->num_rows > 0) {
        $err_message = "Song already added in this playlist!";
        header("Location: dashboard.php?err_message=" . urlencode($err_message));
        $checkLink->close();
        $conn->close();
        exit;
    }
    $checkLink->close();

    // Step 3: Link song to playlist
    $insertLink = $conn->prepare("INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)");
    $insertLink->bind_param("ii", $playlistId, $songDbId);
    $insertLink->execute();
    $insertLink->close();

    $conn->close();
    header("Location: dashboard.php?success=Video added to playlist");
    exit;
}
?>
