<?php
require_once __DIR__ . '/../includes/functions.php';

// Destroy session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect to login with flash (use a new session)
session_start();
set_flash('success', "You've been signed out. See you soon!");
header("Location: /auth/login.php");
exit();