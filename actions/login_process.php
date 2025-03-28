<?php
require_once '../config/db.php'; // Go up one directory

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_username = trim($_POST['email_or_username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email_or_username) || empty($password)) {
        $_SESSION['error_message'] = "Please enter both email/username and password.";
        redirect(BASE_URL . 'login.php');
    }

    try {
        // Check if input is email or username
        $sql = "SELECT id, username, email, password FROM users WHERE email = :identifier OR username = :identifier";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':identifier', $email_or_username);
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user) {
            // Verify password
            // IMPORTANT: Assumes password in DB is hashed with password_hash()
            if (password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                 // Regenerate session ID for security
                session_regenerate_id(true);

                // Handle "Remember Me" - Basic example using cookies (more robust solutions exist)
                if (isset($_POST['remember_me'])) {
                    // Generate a token, store it in DB and cookie (complex setup)
                    // Simple example: set a longer-lived session cookie (less secure)
                    // ini_set('session.cookie_lifetime', 60*60*24*7); // 1 week
                }

                redirect(BASE_URL . 'dashboard.php');

            } else {
                // Invalid password
				$pass_hash = password_hash("admin", PASSWORD_BCRYPT);
                $_SESSION['error_message'] = "Invalid email/username or password." . $pass_hash;
                redirect(BASE_URL . 'login.php');
            }
        } else {
            // No user found
            $_SESSION['error_message'] = "Invalid email/username or password.";
            redirect(BASE_URL . 'login.php');
        }

    } catch (PDOException $e) {
        // Log error properly in production
        $_SESSION['error_message'] = "An error occurred during login. Please try again.";
        // Log $e->getMessage()
        redirect(BASE_URL . 'login.php');
    }

} else {
    // Not a POST request, redirect to login
    redirect(BASE_URL . 'login.php');
}
?>