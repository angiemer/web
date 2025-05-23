<?php
session_start();
require_once 'db.php'; // Διατηρείται αν χρειάζεται, αλλά πιθανότατα δεν είναι απαραίτητο

// Καθαρισμός όλων των μεταβλητών συνεδρίας
$_SESSION = array();

// Διαγραφή του session cookie αν χρησιμοποιούνται cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, 
    $params['path'], $params['domain'], 
    $params['secure'], $params['httponly']);
}

// Καταστροφή της συνεδρίας
session_destroy();

// Ανακατεύθυνση στη σελίδα σύνδεσης
header("Location: start.php");
exit;
?>