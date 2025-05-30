<?php
session_start();
session_unset();
session_destroy();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
if (isset($_COOKIE)) {
    foreach ($_COOKIE as $name => $value) {
        setcookie($name, '', time() - 42000, '/');
    }
}
?>
<script>
    localStorage.removeItem("darkMode");
    window.location.href = "start.php";
</script>
<?php
exit;
?>