<?php
// 1. Auth Check & Config
require_once __DIR__ . '/includes/auth_check.php';

// Page Title
$pageTitle = "Game History";

// 2. Header
require_once __DIR__ . '/includes/header.php';

// 3. Sidebar
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Main Content Area -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $pageTitle; ?></h1>
        <!-- Optional Toolbar -->
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-filter me-1"></i> Filter Dates
            </button>
        </div>
    </div>

    <!-- Games Table -->
    <div class="table-responsive">
        <table id="gamesTable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>           <!-- dt: 0 -->
                    <th>Game ID</th>      <!-- dt: 1 -->
                    <th>Player Name</th>  <!-- dt: 2 -->
                    <th>Game Type</th>    <!-- dt: 3 -->
                    <th>Mode</th>         <!-- dt: 4 -->
                    <th>Outcome</th>      <!-- dt: 5 -->
                    <th>Balance +/-</th>  <!-- dt: 6 -->
                    <th>Start Time</th>   <!-- dt: 7 -->
                    <th>End Time</th>     <!-- dt: 8 -->
                    <th>Status</th>       <!-- dt: 9 -->
                    <th>Actions</th>      <!-- dt: 10 -->
                </tr>
            </thead>
            <tbody>
               <!-- Populated by DataTables AJAX -->
            </tbody>
            <tfoot>
                 <tr>
                    <!-- Footer search inputs -->
                    <th>ID</th>
                    <th>Game ID</th>
                    <th>Player Name</th>
                    <th>Game Type</th>
                    <th>Mode</th>
                    <th>Outcome</th>
                    <th></th> <!-- No search for Balance -->
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                    <th></th> <!-- No search for Actions -->
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- /Games Table -->

</main>
<!-- /Main Content Area -->

<?php
// 4. Footer (includes JS libraries)
require_once __DIR__ . '/includes/footer.php';
?>

<!-- Page Specific Script -->
<script>
$(document).ready(function() {

    // --- Add search inputs to footer ---
    $('#gamesTable tfoot th').each(function(i) {
        var title = $(this).text();
        // Add input field only if title is not empty
        if (title) {
            $(this).html('<input type="text" class="form-control form-control-sm column-search" placeholder="Search ' + title + '" data-column-index="'+i+'" />');
        }
    });

    // --- DataTable Initialization ---
    var table = $('#gamesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?php echo BASE_URL; ?>ajax_games.php",
            "type": "POST"
        },
        "columns": [ // Corresponds to 'dt' indexes in PHP and table header order
            { "data": 0 }, // ID
            { "data": 1 }, // gameID
            { "data": 2 }, // playerName (from join)
            { "data": 3 }, // gameType
            { "data": 4 }, // gameSkillMode
            { "data": 5, "searchable": false }, // gameOutcome (formatted) - maybe allow search on text?
            { "data": 6, "orderable": false, "searchable": false }, // balance (formatted)
            { "data": 7 }, // gameStartTime
            { "data": 8 }, // gameEndTime
            { "data": 9, "searchable": false }, // status (formatted) - maybe allow search on text?
            { "data": 10, "orderable": false, "searchable": false }  // Actions
        ],
        "order": [[7, 'desc']], // Default order by Start Time descending
        "destroy": true // Good practice
    });

    // --- Apply Column Search ---
    $('#gamesTable tfoot').on('keyup change clear', 'input.column-search', function() {
        var columnIndex = $(this).data('column-index');
        if (table.column(columnIndex).search() !== this.value) {
            table.column(columnIndex).search(this.value).draw();
        }
    });

    // --- Placeholder for Action Button ---
    window.viewGameDetails = function(id) {
         alert('View details for Game ID: ' + id);
         // TODO: Implement actual view logic (e.g., open modal, fetch full details via AJAX)
         console.log("View action for Game ID:", id);
     };

});
</script>