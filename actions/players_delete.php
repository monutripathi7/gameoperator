<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_login();

$response = ['status' => 'error', 'message' => 'Invalid request or missing ID.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = filter_var(trim($_POST['id']), FILTER_VALIDATE_INT);

    if ($id) {
        try {
            // --- Option 1: Soft Delete (Recommended) ---
             $sql = "UPDATE players SET status = 'Deleted' WHERE id = :id";

            // --- Option 2: Hard Delete (Permanent) ---
            // $sql = "DELETE FROM players WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                 if ($stmt->rowCount() > 0) {
                     $response['status'] = 'success';
                     $response['message'] = 'Player deleted successfully.';
                 } else {
                     $response['message'] = 'Player not found or already deleted.';
                 }
            } else {
                $response['message'] = 'Failed to delete player.';
            }

        } catch (PDOException $e) {
            // Log error $e->getMessage()
            $response['message'] = 'Database error during deletion.';
        }
    }
}

echo json_encode($response);
exit;
?>