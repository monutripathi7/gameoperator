<?php
// 1. Auth Check & Config
require_once __DIR__ . '/includes/auth_check.php'; // Includes config.php

$playerID = null;
$player = null;
$gameSummary = [
    'totalGames' => 0,
    'wins' => 0,
    'losses' => 0,
    'draws' => 0,
    'pending' => 0,
    'cancelled' => 0,
];
$error_message = '';

// 2. Get and Validate Player ID from URL
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    // Redirect back to players list if ID is missing or invalid
    redirect(BASE_URL . 'players.php?error=Invalid player ID specified.');
}
$playerID = (int)$_GET['id'];

// 3. Fetch Player Details from Database
$sqlPlayer = "SELECT id, fullName, gender, age, phone, email, loginStatus, gameStatus, status, created_at
              FROM players
              WHERE id = ?
              LIMIT 1";
$stmtPlayer = $conn->prepare($sqlPlayer);

if ($stmtPlayer) {
    $stmtPlayer->bind_param("i", $playerID);
    $stmtPlayer->execute();
    $resultPlayer = $stmtPlayer->get_result();

    if ($resultPlayer->num_rows === 1) {
        $player = $resultPlayer->fetch_assoc();
    } else {
        $error_message = "Player with ID " . htmlspecialchars($playerID) . " not found.";
        // Optional: Redirect if player not found
        // redirect(BASE_URL . 'players.php?error=Player not found.');
    }
    $stmtPlayer->close();
} else {
    $error_message = "Database error fetching player details.";
    error_log("SQL Prepare Error (Player Details): " . $conn->error);
}

// 4. Fetch Game Summary from Database (only if player was found)
if ($player) {
    $sqlSummary = "SELECT
                        COUNT(*) AS totalGames,
                        SUM(CASE WHEN gameOutcome = 'Win' THEN 1 ELSE 0 END) AS wins,
                        SUM(CASE WHEN gameOutcome = 'Loss' THEN 1 ELSE 0 END) AS losses,
                        SUM(CASE WHEN gameOutcome = 'Draw' THEN 1 ELSE 0 END) AS draws,
                        SUM(CASE WHEN gameOutcome = 'Pending' THEN 1 ELSE 0 END) AS pending,
                        SUM(CASE WHEN gameOutcome = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled
                   FROM games
                   WHERE playerID = ?";
    $stmtSummary = $conn->prepare($sqlSummary);

    if ($stmtSummary) {
        $stmtSummary->bind_param("i", $playerID);
        $stmtSummary->execute();
        $resultSummary = $stmtSummary->get_result();

        if ($resultSummary->num_rows === 1) {
            $summaryData = $resultSummary->fetch_assoc();
            // Assign fetched counts, defaulting to 0 if null
            $gameSummary['totalGames'] = $summaryData['totalGames'] ?? 0;
            $gameSummary['wins'] = $summaryData['wins'] ?? 0;
            $gameSummary['losses'] = $summaryData['losses'] ?? 0;
            $gameSummary['draws'] = $summaryData['draws'] ?? 0;
            $gameSummary['pending'] = $summaryData['pending'] ?? 0;
            $gameSummary['cancelled'] = $summaryData['cancelled'] ?? 0;
        }
        // No else needed here, defaults are already 0 if no games found
        $stmtSummary->close();
    } else {
        $error_message = $error_message ? $error_message . "<br>" : ""; // Append error
        $error_message .= "Database error fetching game summary.";
        error_log("SQL Prepare Error (Game Summary): " . $conn->error);
    }
}


// Page Title (Set after fetching player name)
$pageTitle = $player ? "Player Details: " . htmlspecialchars($player['fullName']) : "View Player";

// 5. Include Header
require_once __DIR__ . '/includes/header.php';

// 6. Include Sidebar
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Main Content Area -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $pageTitle; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
             <a href="<?php echo BASE_URL; ?>players.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Players List
            </a>
        </div>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($player): // Only show details if player was found ?>
    <div class="row">
        <!-- Player Information Card -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person-badge me-2"></i>Player Information
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Full Name</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($player['fullName']); ?></dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($player['email'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($player['phone'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-4">Gender</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($player['gender']); ?></dd>

                        <dt class="col-sm-4">Age</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($player['age'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-4">Account Status</dt>
                        <dd class="col-sm-8">
                             <?php
                                $statusBadge = 'secondary';
                                if ($player['status'] == 'Active') $statusBadge = 'success';
                                elseif ($player['status'] == 'Banned') $statusBadge = 'danger';
                                elseif ($player['status'] == 'Inactive') $statusBadge = 'warning';
                                echo '<span class="badge bg-' . $statusBadge . '">' . htmlspecialchars($player['status']) . '</span>';
                            ?>
                        </dd>

                         <dt class="col-sm-4">Login Status</dt>
                        <dd class="col-sm-8">
                             <?php
                                $loginBadge = ($player['loginStatus'] == 'Online') ? 'success' : 'secondary';
                                echo '<span class="badge bg-' . $loginBadge . '">' . htmlspecialchars($player['loginStatus']) . '</span>';
                            ?>
                        </dd>

                         <dt class="col-sm-4">Current Game Status</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($player['gameStatus']); ?></dd>

                         <dt class="col-sm-4">Member Since</dt>
                        <dd class="col-sm-8"><?php echo date('M d, Y', strtotime($player['created_at'])); ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Game Summary Card -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                 <div class="card-header">
                    <i class="bi bi-joystick me-2"></i>Game Summary
                </div>
                 <div class="card-body">
                     <h5 class="card-title text-center mb-3">Total Games Played: <?php echo number_format($gameSummary['totalGames']); ?></h5>
                     <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Wins
                            <span class="badge bg-success rounded-pill"><?php echo number_format($gameSummary['wins']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Losses
                            <span class="badge bg-danger rounded-pill"><?php echo number_format($gameSummary['losses']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Draws
                            <span class="badge bg-info rounded-pill"><?php echo number_format($gameSummary['draws']); ?></span>
                        </li>
                         <li class="list-group-item d-flex justify-content-between align-items-center">
                            Pending / In Progress
                            <span class="badge bg-warning rounded-pill"><?php echo number_format($gameSummary['pending']); ?></span>
                        </li>
                         <li class="list-group-item d-flex justify-content-between align-items-center">
                            Cancelled / Aborted
                            <span class="badge bg-secondary rounded-pill"><?php echo number_format($gameSummary['cancelled']); ?></span>
                        </li>
                    </ul>
                     <!-- Optional: Link to full game history for this player -->
                     <div class="text-center mt-3">
                        <a href="<?php echo BASE_URL; ?>games.php?player_id=<?php echo $playerID; ?>" class="btn btn-sm btn-outline-primary">View Full Game History</a>
                        <!-- Note: games.php and ajax_games.php would need modification to handle filtering by player_id -->
                     </div>
                 </div>
            </div>
        </div>
    </div> <!-- /row -->
    <?php endif; ?>


</main>
<!-- /Main Content Area -->

<?php
// 7. Include Footer
require_once __DIR__ . '/includes/footer.php';
?>