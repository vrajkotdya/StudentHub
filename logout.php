<?php
/**
 * StudentHub — Logout Script (logout.php)
 * Version 10: Completely destroys session data and invalidates the session cookie.
 */

session_start();

// Unset all session variables
$_SESSION = [];

// Delete the session cookie from the browser
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

// Delete client-side navigation cookies
setcookie('sh_logged_in', '', time() - 42000, '/');
setcookie('sh_user_name', '', time() - 42000, '/');

// Destroy session data on the server
session_destroy();

// Redirect user cleanly back to login page
header("Location: login.html");
exit;
