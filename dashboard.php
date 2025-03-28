<?php
// 1. Check if logged in
require_once __DIR__ . '/includes/auth_check.php'; // This also includes config.php

// Page specific variables
$pageTitle = "Dashboard";

// --- Fetch Dashboard Metrics ---
$totalPlayers = 0;
$onlinePlayers = 0;
$offlinePlayers = 0;
$ongoingGamesEstimate = 0; // Estimate based on player status

// Placeholder values for metrics requiring other data sources
$grossProfit = "N/A"; // Example: Needs transactions table
$winningPercentage = "N/A"; // Example: Needs game results table
$totalGamesPlayed = "N/A"; // Example: Needs games table
$systemHealth = "Nominal"; // Example: Could come from monitoring
$systemHealthBadge = "success"; // Corresponds to 'Nominal'

// Query to get player counts efficiently
$sql = "SELECT
            COUNT(*) AS totalPlayers,
            SUM(CASE WHEN loginStatus = 'Online' THEN 1 ELSE 0 END) AS onlinePlayers,
            SUM(CASE WHEN gameStatus = 'In Game' THEN 1 ELSE 0 END) AS ongoingGamesEstimate
            -- Add other counts here if needed from the players table in one query
        FROM players";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalPlayers = $row['totalPlayers'] ?? 0;
    $onlinePlayers = $row['onlinePlayers'] ?? 0;
    $offlinePlayers = $totalPlayers - $onlinePlayers; // Calculate offline based on total and online
    $ongoingGamesEstimate = $row['ongoingGamesEstimate'] ?? 0;
    $result->free();
} else {
    // Log error or handle the case where the query fails
    error_log("Error fetching dashboard metrics: " . $conn->error);
    // You might want to display an error on the dashboard itself
}

// --- Define Placeholders (adjust values as needed) ---
$grossProfit = "$5,430"; // Placeholder - fetch from actual financial data
$winningPercentage = "62%";   // Placeholder - calculate from game results
$totalGamesPlayed = "1,876"; // Placeholder - count from games table
// $ongoingGamesEstimate is calculated above
$systemHealth = "Nominal"; // Placeholder - fetch from monitoring system
$systemHealthBadge = "success"; // e.g., success, warning, danger

// --- End Fetch Dashboard Metrics ---


// 2. Include Header
require_once __DIR__ . '/includes/header.php';

// 3. Include Sidebar
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Main Content Area -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $pageTitle; ?></h1>
        <!-- Optional Toolbar -->
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                <i class="bi bi-calendar3 me-1"></i> This week
            </button>
        </div>
    </div>

    <!-- Welcome Message -->
    <!-- Optional: You can remove this if the cards are enough -->
    <!--
    <div class="alert alert-info">
        Welcome back, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?>!
    </div>
     -->

    <!-- Metric Cards Row 1 -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Total Players</h5>
                            <h3 class="fw-bold mb-0"><?php echo number_format($totalPlayers); ?></h3>
                        </div>
                        <i class="bi bi-people-fill" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                    <!-- <p class="card-text small mt-2">All registered users.</p> -->
                </div>
                <div class="card-footer bg-primary bg-opacity-75 border-0">
                     <a href="<?php echo BASE_URL; ?>players.php" class="text-white text-decoration-none stretched-link">View Details <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Players Online</h5>
                            <h3 class="fw-bold mb-0"><?php echo number_format($onlinePlayers); ?></h3>
                        </div>
                        <i class="bi bi-person-check-fill" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                </div>
                 <div class="card-footer bg-success bg-opacity-75 border-0">
                     <a href="<?php echo BASE_URL; ?>players.php?status=online" class="text-white text-decoration-none stretched-link">View Details <i class="bi bi-arrow-right-circle"></i></a> <!-- Link might need adjustments -->
                </div>
            </div>
        </div>
         <div class="col-md-6 col-lg-3">
            <div class="card text-dark bg-light h-100"> <!-- Or bg-secondary text-white -->
                <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Players Offline</h5>
                            <h3 class="fw-bold mb-0"><?php echo number_format($offlinePlayers); ?></h3>
                        </div>
                        <i class="bi bi-person-dash-fill text-secondary" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                </div>
                 <div class="card-footer bg-light bg-opacity-75 border-0">
                     <a href="<?php echo BASE_URL; ?>players.php?status=offline" class="text-secondary text-decoration-none stretched-link">View Details <i class="bi bi-arrow-right-circle"></i></a> <!-- Link might need adjustments -->
                </div>
            </div>
        </div>
         <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Gross Profit</h5>
                            <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($grossProfit); ?></h3>
                        </div>
                        <i class="bi bi-cash-stack" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                     <p class="card-text small mt-2 text-white-50">Requires financial data setup.</p>
                </div>
                 <div class="card-footer bg-info bg-opacity-75 border-0">
                     <a href="#" class="text-white text-decoration-none stretched-link">View Reports <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>
    </div> <!-- /row -->

    <!-- Metric Cards Row 2 -->
     <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card text-dark bg-warning h-100">
                <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Winning %</h5>
                            <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($winningPercentage); ?></h3>
                        </div>
                        <i class="bi bi-trophy-fill" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                    <p class="card-text small mt-2 text-dark-50">Requires game result data.</p>
                </div>
                 <div class="card-footer bg-warning bg-opacity-75 border-0">
                     <a href="#" class="text-dark text-decoration-none stretched-link">View Stats <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-secondary h-100">
                 <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Total Games Played</h5>
                            <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($totalGamesPlayed); ?></h3>
                        </div>
                        <i class="bi bi-controller" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                    <p class="card-text small mt-2 text-white-50">Requires game history data.</p>
                </div>
                 <div class="card-footer bg-secondary bg-opacity-75 border-0">
                     <a href="#" class="text-white text-decoration-none stretched-link">Game History <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>
         <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-danger h-100">
                 <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Ongoing Games</h5>
                            <h3 class="fw-bold mb-0"><?php echo number_format($ongoingGamesEstimate); ?></h3>
                        </div>
                        <i class="bi bi-play-circle-fill" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                    <p class="card-text small mt-2 text-white-50">Estimate based on player status.</p>
                </div>
                 <div class="card-footer bg-danger bg-opacity-75 border-0">
                     <a href="#" class="text-white text-decoration-none stretched-link">Live View <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>
         <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-dark h-100">
                 <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">System Health</h5>
                             <span class="badge bg-<?php echo $systemHealthBadge; ?> fs-5"><?php echo htmlspecialchars($systemHealth); ?></span>
                        </div>
                         <i class="bi bi-heart-pulse-fill" style="font-size: 2.5rem; opacity: 0.6;"></i>
                    </div>
                    <p class="card-text small mt-2 text-white-50">Requires monitoring integration.</p>
                </div>
                 <div class="card-footer bg-dark bg-opacity-75 border-0">
                     <a href="#" class="text-white text-decoration-none stretched-link">Monitoring <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>
    </div> <!-- /row -->


    <!-- You can add other dashboard elements like charts or recent activity here -->
    <!-- Example: Placeholder for a chart -->
    <!--
    <div class="my-4 w-100" id="myChart" width="900" height="380"></div>
    -->

</main>
<!-- /Main Content Area -->

<?php
// 4. Include Footer
require_once __DIR__ . '/includes/footer.php';
?>
