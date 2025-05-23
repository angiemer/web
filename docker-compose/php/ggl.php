<?php
// header('Content-Type: application/json');

if(file_exists('./vendor/autoload.php')){
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
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    header('Location: ' . filter_var($client->getRedirectUri(), FILTER_SANITIZE_URL));
}

try {

    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
        $client->setAccessToken($_SESSION['access_token']);
        $youtube = new Google_Service_YouTube($client);

        // Search for videos
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $query = trim($_POST['query'] ?? '');
            $searchResponse = $youtube->search->listSearch('snippet', array(
                'q' => $query,
                'maxResults' => 10,
            ));
            $_SESSION["searched"] = true;
            $_SESSION["array_of_response"] = $searchResponse;


            // print_r($searchResponse);
            //header("location:dashboard.php");
            //exit();

        }

        // foreach ($searchResponse['items'] as $searchResult) {
        //     echo sprintf('<p>%s (Watch on YT: <a href="https://youtu.be/%s" target="_blank">https://youtu.be/%s</a>)</p>', $searchResult['snippet']['title'], $searchResult['id']['videoId'], $searchResult['id']['videoId']);
        // }
    } else {
        $authUrl = $client->createAuthUrl();
        echo '<a href="' . $authUrl . '">Authenticate with YouTube</a>';
        exit();
    }
} catch (Google\Service\Exception $e) {
    if ($e->getCode() == 401) {
        // Redirect to login page
        $authUrl = $client->createAuthUrl();
        header('Location: ' . $authUrl );
        exit();
    } else {
        // Handle other exceptions
        echo 'An error occurred: ' . $e->getMessage();
    }    
}
?>