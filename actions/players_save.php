<?php
header('Content-Type: application/json'); // Set content type to json
require_once '../config/db.php';
require_login();

$response = ['status' => 'error', 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Basic Data Sanitization/Retrieval ---
    $id = isset($_POST['id']) ? filter_var(trim($_POST['id']), FILTER_VALIDATE_INT) : null;
    $fullName = filter_var(trim($_POST['fullName'] ?? ''), FILTER_SANITIZE_STRING);
    $gender = filter_var(trim($_POST['gender'] ?? ''), FILTER_SANITIZE_STRING);
    $age = isset($_POST['age']) && trim($_POST['age']) !== '' ? filter_var(trim($_POST['age']), FILTER_VALIDATE_INT) : null;
    $phone = filter_var(trim($_POST['phone'] ?? ''), FILTER_SANITIZE_STRING); // Consider more specific phone sanitization/validation
    $email = isset($_POST['email']) && trim($_POST['email']) !== '' ? filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) : null;
    $loginStatus = in_array($_POST['loginStatus'] ?? '', ['Online', 'Offline']) ? $_POST['loginStatus'] : 'Offline';
    $gameStatus = filter_var(trim($_POST['gameStatus'] ?? ''), FILTER_SANITIZE_STRING);
    $status = in_array($_POST['status'] ?? '', ['Active', 'Inactive', 'Banned']) ? $_POST['status'] : 'Inactive'; // Default to inactive if invalid

    // --- Basic Validation ---
    $errors = [];
    if (empty($fullName)) {
        $errors['fullName'] = 'Full Name is required.';
    }
    if ($email === false && isset($_POST['email']) && trim($_POST['email']) !== '') { // Check if FILTER_VALIDATE_EMAIL failed
         $errors['email'] = 'Invalid Email format.';
    }
     if ($age === false && isset($_POST['age']) && trim($_POST['age']) !== '') { // Check if FILTER_VALIDATE_INT failed
         $errors['age'] = 'Invalid Age format (must be a number).';
    }
    // Add more specific validation (e.g., phone number format, age range)

    // Check for unique email IF email is provided and it's either a new record or email changed
     if ($email && count($errors) == 0) {
        try {
            $sqlCheck = "SELECT id FROM players WHERE email = :email AND status != 'Deleted'";
            if ($id) { // If editing, exclude the current player's ID from the check
                $sqlCheck .= " AND id != :id";
            }
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->bindParam(':email', $email);
            if ($id) {
                $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
            }
            $stmtCheck->execute();
            if ($stmtCheck->fetch()) {
                $errors['email'] = 'This email address is already in use.';
            }
        } catch (PDOException $e) {
             $response['message'] = 'Database error during email check.';
             echo json_encode($response);
             exit;
        }
    }


    if (!empty($errors)) {
        $response['message'] = 'Validation failed.';
        $response['errors'] = $errors;
    } else {
        try {
            if ($id) {
                // --- Update Existing Player ---
                $sql = "UPDATE players SET
                            fullName = :fullName,
                            gender = :gender,
                            age = :age,
                            phone = :phone,
                            email = :email,
                            loginStatus = :loginStatus,
                            gameStatus = :gameStatus,
                            status = :status
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            } else {
                // --- Insert New Player ---
                $sql = "INSERT INTO players (fullName, gender, age, phone, email, loginStatus, gameStatus, status)
                        VALUES (:fullName, :gender, :age, :phone, :email, :loginStatus, :gameStatus, :status)";
                $stmt = $pdo->prepare($sql);
            }

            // Bind parameters
            $stmt->bindParam(':fullName', $fullName);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':age', $age, PDO::PARAM_INT); // Bind as INT or NULL
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email); // Can be NULL
            $stmt->bindParam(':loginStatus', $loginStatus);
            $stmt->bindParam(':gameStatus', $gameStatus);
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Player ' . ($id ? 'updated' : 'added') . ' successfully.';
            } else {
                $response['message'] = 'Failed to save player.';
            }

        } catch (PDOException $e) {
            // Log error $e->getMessage() in production
            $response['message'] = 'Database error: ' . $e->getMessage(); // Show detailed error for debugging
        }
    }
}

echo json_encode($response);
exit;
?>