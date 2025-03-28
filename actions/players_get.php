<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_login();

$player = null;
$id = isset($_GET['id']) ? filter_var(trim($_GET['id']), FILTER_VALIDATE_INT) : null;

if ($id) {
    try {
        $sql = "SELECT id, fullName, gender, age, phone, email, loginStatus, gameStatus, status
                FROM players
                WHERE id = :id AND status != 'Deleted'"; // Ensure we don't fetch deleted ones
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $player = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch as associative array

    } catch (PDOException $e) {
        // Log error $e->getMessage()
        // In this case, we just return null, the frontend will handle it
    }
}

echo json_encode($player); // Will output 'null' if not found or error
exit;
?>