<?php
// 1. Check if logged in
require_once __DIR__ . '/includes/auth_check.php';

// Page specific variables
$pageTitle = "Manage Players";

// 2. Include Header
require_once __DIR__ . '/includes/header.php';

// 3. Include Sidebar
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Main Content Area -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $pageTitle; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-success">
                <i class="bi bi-plus-circle me-1"></i> Add New Player
            </button>
        </div>
    </div>

    <!-- Player Table -->
    <div class="table-responsive">
        <table id="playersTable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>         <!-- dt: 0 -->
                    <th>Full Name</th>  <!-- dt: 1 -->
                    <th>Gender</th>     <!-- dt: 2 -->
                    <th>Age</th>        <!-- dt: 3 -->
                    <th>Phone</th>      <!-- dt: 4 -->
                    <th>Email</th>      <!-- dt: 5 -->
                    <th>Login Status</th><!-- dt: 6 -->
                    <th>Game Status</th><!-- dt: 7 -->
                    <th>Status</th>     <!-- dt: 8 -->
                    <th>Actions</th>    <!-- dt: 9 -->
                </tr>
            </thead>
             <!-- The table body will be populated by DataTables AJAX -->
            <tbody>
               <!-- ** REMOVE THE PHP LOOP THAT WAS HERE ** -->
            </tbody>
            <tfoot>
                 <tr>
                    <!-- Add input fields matching the columns for searching -->
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Login Status</th>
                    <th>Game Status</th>
                    <th>Status</th>
                    <th></th> <!-- No search for actions column -->
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- /Player Table -->

</main>
<!-- /Main Content Area -->

<?php
// 4. Include Footer (this contains the jQuery/Bootstrap/DataTables JS includes)
require_once __DIR__ . '/includes/footer.php';
?>

<!-- Add Page Specific Script Here (after footer includes) -->
<script>
$(document).ready(function() {

    // --- Setup - Add a text input to each footer cell ---
    $('#playersTable tfoot th').each(function(i) {
        var title = $(this).text();
        // Add input field only if title is not empty (skip Actions column)
        if (title) {
             // Use class for easier selection later, add placeholder
            $(this).html('<input type="text" class="form-control form-control-sm column-search" placeholder="Search ' + title + '" data-column-index="'+i+'" />');
        }
    });

    // --- DataTable Initialization ---
    var table = $('#playersTable').DataTable({
        "processing": true,     // Show processing indicator
        "serverSide": true,     // Enable server-side processing
        "ajax": {
            "url": "<?php echo BASE_URL; ?>ajax_players.php", // URL to the server-side script
            "type": "POST"      // Use POST method
        },
        "columns": [            // Define columns (must match 'dt' in PHP and table header order)
            { "data": 0 }, // ID
            { "data": 1 }, // fullName
            { "data": 2 }, // gender
            { "data": 3 }, // age
            { "data": 4 }, // phone
            { "data": 5 }, // email
            { "data": 6, "orderable": false, "searchable": false }, // loginStatus (formatted server-side)
            { "data": 7 }, // gameStatus
            { "data": 8, "orderable": false, "searchable": false }, // status (formatted server-side)
            { "data": 9, "orderable": false, "searchable": false }  // Actions (generated server-side)
        ],
        // Optional: Set default ordering
        "order": [[0, 'desc']], // Order by ID descending by default

        // Optional: Customize language options if needed
        // "language": { processing: "Loading..." }

        // Optional: Add Buttons extension for export etc.
        // dom: 'Bfrtip', buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });

    // --- Apply the search ---
    // Use event delegation for efficiency on the inputs in the footer
    $('#playersTable tfoot').on('keyup change clear', 'input.column-search', function() {
        var columnIndex = $(this).data('column-index');
        if (table.column(columnIndex).search() !== this.value) {
            table
                .column(columnIndex)
                .search(this.value) // Apply the search for this column
                .draw();            // Redraw the table (triggers AJAX)
        }
    });

    // --- Add placeholders for action button functions (if not already global) ---
    // Define these functions globally or within this scope if needed
    // They are called by the inline onclick handlers generated in ajax_players.php
    window.viewPlayer = function(id) {
         alert('View player ID: ' + id);
         // TODO: Implement actual view logic (e.g., AJAX fetch details, show modal)
         console.log("View action for ID:", id);
     };

     window.editPlayer = function(id) {
         alert('Edit player ID: ' + id);
         // TODO: Implement actual edit logic (e.g., redirect, show modal with form)
         console.log("Edit action for ID:", id);
         // Example redirect: window.location.href = '<?php echo BASE_URL; ?>edit_player.php?id=' + id;
     };

     window.deletePlayer = function(id) {
         if (confirm('Are you sure you want to delete player ID: ' + id + '?')) {
             alert('Deleting player ID: ' + id);
             // TODO: Implement actual delete logic (e.g., AJAX POST to delete endpoint)
             console.log("Delete action for ID:", id);

             // Example using AJAX POST with jQuery
             /*
             $.ajax({
                 url: '<?php echo BASE_URL; ?>delete_player_endpoint.php', // Replace with your actual endpoint
                 type: 'POST',
                 data: { id: id },
                 success: function(response) {
                     // Assuming response indicates success
                     console.log('Delete successful:', response);
                     table.ajax.reload(null, false); // Reload table data, keeping pagination
                 },
                 error: function(xhr, status, error) {
                     console.error('Delete failed:', error);
                     alert('Failed to delete player.');
                 }
             });
             */
         }
     };

});
</script>

<?php
// Footer already includes closing </body></html> and DB connection closing
// No need to include footer.php again here
?>