<?php
session_start();

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Clear the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, // Expire far in the past for immediate deletion
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Clear all other cookies (if any exist)
if (isset($_COOKIE)) {
    foreach ($_COOKIE as $name => $value) {
        setcookie($name, '', time() - 42000, '/');
    }
}

// Redirect to start page
header('Location: start.php');
exit;
?>