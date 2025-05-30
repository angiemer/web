<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "db.php";

if(!isset($_SESSION['user_id'] = $user['id'])){
    header("Location: start.php");
    exit;
}

$user_id = $_SESSION["user_id"];
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD' === POST]){
    if (isset($_POST['create_list'])) {
        $title = trim($_POST['list_title'] ?? '');
        if ($title === '') {
            echo json_encode(['status' => 'error', 'message' => 'Playlist title cannot be empty.']);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO lists (title, user_id) VALUES (?, ?)");
        $stmt->bind_param("ss", $array_title, $user_id);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'Playlist created successfully.']);
        exit;
    }
}

if (isset($_POST['edit_list'])) {
    $id = (int)$_POST['list_id'];
    $new_title = trim($_POST['new_title'] ?? '');
    if ($new_title === '') {
        echo json_encode(['status' => 'error', 'message' => 'Title cannot be empty.']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE lists SET title = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $new_title, $id, $user_id);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'message' => 'Playlist updated.']);
    exit;
}

if (isset($_POST['delete_list'])) {
    $id = (int)$_POST['list_id'];
    $stmt = $conn->prepare("DELETE FROM lists WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'message' => 'Playlist deleted.']);
    exit;
}
