<?php
// Require config to start the session before destroying it
require_once __DIR__ . '/config/config.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
if (session_destroy()) {
    // Destroy the session cookie as well (optional, good practice)
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    // Redirect to login page
    redirect(BASE_URL . 'login.php?message=You have been logged out.');
} else {
    // Handle logout error (rare)
    redirect(BASE_URL . 'dashboard.php?error=Logout failed.'); // Redirect back if fails
}
exit(); // Ensure no further code execution
?>