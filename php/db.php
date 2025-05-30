<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'load_env.php';

$servername = $_ENV['DB_SERVER'] ?? 'db';
$username = $_ENV['DB_USERNAME'] ?? 'webuser';
$password = $_ENV['DB_PASSWORD'] ?? 'webpass';
$dbname = $_ENV['DB_NAME'] ?? 'di_internet_technologies_project';

// Creates connection to MySQL 
$conn = new mysqli('db', 'webuser', 'webpass', 'di_internet_technologies_project', 3306);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
