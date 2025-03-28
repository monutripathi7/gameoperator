<?php
// Include config (starts session)
require_once __DIR__ . '/config/config.php';

// --- Database Setup Assumption ---
// You need a 'users' table for this login to work.
// Example SQL:
/*
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL, -- Store hashed passwords!
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Example User (Password: 'password123') - Use password_hash() in PHP to generate the hash
-- echo password_hash('password123', PASSWORD_DEFAULT);
INSERT INTO users (username, email, password_hash) VALUES
('admin', 'admin@example.com', '$2y$10$...'); -- Replace with your actual hash
*/

$error_message = '';

// --- Redirect if already logged in ---
if (isLoggedIn()) {
    redirect(BASE_URL . 'dashboard.php');
}

// --- Handle Login Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        // --- Prepare statement to prevent SQL injection ---
        $sql = "SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();

                // --- Verify password ---
                // IMPORTANT: Use password_verify()!
                if (password_verify($password, $user['password_hash'])) {
                    // --- Password is correct, start session ---
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // --- Redirect to dashboard (or intended page) ---
                    // $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : BASE_URL . 'dashboard.php';
                    // unset($_SESSION['redirect_url']); // Clear the stored URL
                    redirect(BASE_URL . 'dashboard.php');

                } else {
                    // Invalid password
                    $error_message = "Invalid username or password.";
                }
            } else {
                // No user found
                $error_message = "Invalid username or password.";
            }
            $stmt->close();
        } else {
            // SQL error
            error_log("SQL Prepare Error: " . $conn->error); // Log error
            $error_message = "An error occurred. Please try again later.";
        }
    }
}

// Check for error messages passed via GET param (e.g., from auth_check)
if (isset($_GET['error']) && empty($error_message)) {
    $error_message = htmlspecialchars($_GET['error']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SaaS App</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        html, body { height: 100%; }
        body { display: flex; align-items: center; padding-top: 40px; padding-bottom: 40px; background-color: #f5f5f5; }
        .form-signin { width: 100%; max-width: 330px; padding: 15px; margin: auto; }
        .form-signin .checkbox { font-weight: 400; }
        .form-signin .form-floating:focus-within { z-index: 2; }
        .form-signin input[type="text"] { margin-bottom: -1px; border-bottom-right-radius: 0; border-bottom-left-radius: 0; }
        .form-signin input[type="password"] { margin-bottom: 10px; border-top-left-radius: 0; border-top-right-radius: 0; }
    </style>
</head>
<body class="text-center">
    <main class="form-signin">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- <img class="mb-4" src="/docs/5.1/assets/brand/bootstrap-logo.svg" alt="" width="72" height="57"> -->
            <i class="bi bi-lock-fill" style="font-size: 3rem;"></i>
            <h1 class="h3 mb-3 fw-normal">Please sign in</h1>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="form-floating">
                <input type="text" class="form-control" id="floatingInput" name="username" placeholder="Username" required autofocus>
                <label for="floatingInput">Username</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Password" required>
                <label for="floatingPassword">Password</label>
            </div>

            <!-- <div class="checkbox mb-3">
                <label>
                    <input type="checkbox" value="remember-me"> Remember me
                </label>
            </div> -->
            <button class="w-100 btn btn-lg btn-primary" type="submit" name="login">Sign in</button>
            <p class="mt-5 mb-3 text-muted">? <?php echo date("Y"); ?> Your Company</p>
        </form>
    </main>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
<?php
// Close the database connection if it was opened
if (isset($conn)) {
    $conn->close();
}
?>