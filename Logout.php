<?php
// logout.php
session_start();

// Check if role change was the reason for logout
$role_change = isset($_GET['reason']) && $_GET['reason'] === 'role_change';

// Destroy all session data
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// If role change, show a specific message
if ($role_change) {
    // Redirect to login with a message
    header("Location: Login.php?message=role_changed");
} else {
    // Normal logout
    header("Location: Login.php");
}
exit;
?>