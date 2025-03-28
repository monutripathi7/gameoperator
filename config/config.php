<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Database Configuration ---
define('DB_HOST', 'localhost');      // Your database host (e.g., 'localhost' or IP)
define('DB_USER', 'root');           // Your database username
define('DB_PASS', '');               // Your database password
define('DB_NAME', 'gamedb');    // Your database name

// --- Base URL ---
// Detect protocol (http or https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
// Get server name and current script directory
$server_name = $_SERVER['SERVER_NAME'];
$script_path = dirname($_SERVER['SCRIPT_NAME']);
// Handle root directory case
$base_path = ($script_path == '/' || $script_path == '\\') ? '' : $script_path;
// Define the base URL (ensure trailing slash)
define('BASE_URL', $protocol . $server_name . $base_path . '/');


// --- Create MySQLi Connection ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// --- Check Connection ---
if ($conn->connect_error) {
    // Don't show detailed errors in production! Log them instead.
    die("Database Connection Failed: " . $conn->connect_error);
}

// --- Set Character Set (Good Practice) ---
$conn->set_charset("utf8mb4");

// --- Optional: Global Helper Functions ---
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// --- Error Reporting (Development vs Production) ---
// For development: show all errors
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// For production: log errors, don't display them
// error_reporting(0);
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// ini_set('error_log', '/path/to/your/php-error.log');

?>