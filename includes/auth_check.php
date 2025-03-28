<?php
// Ensure config is included (which also starts the session)
require_once __DIR__ . '/../config/config.php';

// Check if the user session variable is not set
if (!isLoggedIn()) {
    // Store the intended URL in session (optional, for redirecting back after login)
    // $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    // Redirect to the login page
    redirect(BASE_URL . 'login.php?error=Please log in to access this page.');
}
?>