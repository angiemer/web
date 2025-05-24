<?php
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