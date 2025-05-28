<?php
require_once 'db.php'; // Ensure database connection
if (file_exists('./vendor/autoload.php')) {
    require_once './vendor/autoload.php';
}

// Load environment variables from .env file
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set up the Google client
$client = new Google_Client();
$client->setClientId($_ENV['CLIENT_ID']);
$client->setClientSecret($_ENV['CLIENT_SECRET']);
$client->setRedirectUri($_ENV['REDIRECT_URI']);
$client->addScope(Google_Service_YouTube::YOUTUBE_READONLY);

session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: start.php');
    exit;
}

// Authenticate the user
if (isset($_GET['code'])) {
    try {
        $client->authenticate($_GET['code']);
        $_SESSION['access_token'] = $client->getAccessToken();
        // Redirect to dashboard.php after successful authentication
        header('Location: dashboard.php');
        exit();
    } catch (Exception $e) {
        error_log("OAuth authentication error: " . $e->getMessage());
        echo "Authentication failed: " . htmlspecialchars($e->getMessage());
        exit();
    }
}

try {
    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
        $client->setAccessToken($_SESSION['access_token']);
        $youtube = new Google_Service_YouTube($client);

        // Search for videos
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $query = trim($_POST['query'] ?? '');
            $searchResponse = $youtube->search->listSearch('snippet', [
                'q' => $query,
                'maxResults' => 10,
            ]);

            $_SESSION["searched"] = true;
            $_SESSION["array_of_response"] = $searchResponse;

            header("Location: dashboard.php");
            exit();
        }
        if ($_SESSION["searched"] == false) {
            $_SESSION["searched"] = true;

            // Get user ID
            $user_id = $_SESSION['id'];

            // Fetch a random favorite song title
            $stmt = $conn->prepare("
                SELECT s.title
                FROM favorites f
                JOIN favorite_songs fs ON f.id = fs.favor_id
                JOIN songs s ON fs.song_id = s.id
                WHERE f.user_id = ?
                ORDER BY RAND()
                LIMIT 1
            ");
            if (!$stmt) {
                throw new Exception("Failed to prepare favorites select query: " . $conn->error);
            }
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0) {
                $favorite = $result->fetch_assoc();
                $query = $favorite['title'];
            } else {
                $query = "Three Little Birds (Don't Worry About a Thing)"; // Fallback query
            }

            $searchResponse = $youtube->search->listSearch('snippet', [
                'q' => $query,
                'maxResults' => 10,
            ]);

            $_SESSION["array_of_response"] = $searchResponse;

            header("Location: dashboard.php");
            exit();
        }
    } else {
        // Redirect to Google OAuth URL instead of displaying a link
        $authUrl = $client->createAuthUrl();
        header('Location: ' . $authUrl);
        exit();
    }
} catch (Google_Service_Exception $e) {
    if ($e->getCode() == 401) {
        // Redirect to Google OAuth URL for re-authentication
        $authUrl = $client->createAuthUrl();
        header('Location: ' . $authUrl);
        exit();
    } else {
        // Handle other exceptions
        echo 'An error occurred: ' . $e->getMessage();
    }
}
?>