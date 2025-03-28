<?php
require_once __DIR__ . '/config/config.php'; // Adjust path as needed

// --- Basic Security/Session Check (Optional but recommended) ---
// You might want to ensure only logged-in users can access this data
/*
if (!isLoggedIn()) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}
*/


// --- Database Table ---
$table = 'players';

// --- Table's Primary Key ---
$primaryKey = 'id';

// --- Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database.
// The `dt` parameter represents the DataTables column identifier (usually index).
// The `formatter` is optional, used for custom formatting.
$columns = [
    ['db' => 'id',          'dt' => 0],
    ['db' => 'fullName',    'dt' => 1],
    ['db' => 'gender',      'dt' => 2],
    ['db' => 'age',         'dt' => 3],
    ['db' => 'phone',       'dt' => 4],
    ['db' => 'email',       'dt' => 5],
    [
        'db' => 'loginStatus',
        'dt' => 6,
        'formatter' => function($d, $row) {
            $badge = ($d == 'Online') ? 'success' : 'secondary';
            return '<span class="badge bg-' . $badge . '">' . htmlspecialchars($d) . '</span>';
        }
    ],
    ['db' => 'gameStatus',  'dt' => 7],
    [
        'db' => 'status',
        'dt' => 8,
        'formatter' => function($d, $row) {
            $badge = 'secondary';
            if ($d == 'Active') $badge = 'success';
            elseif ($d == 'Banned') $badge = 'danger';
            elseif ($d == 'Inactive') $badge = 'warning';
            return '<span class="badge bg-' . $badge . '">' . htmlspecialchars($d) . '</span>';
        }
    ],
    [ // Actions column - not directly from DB
        'db' => 'id', // Use ID for generating links/buttons if needed
        'dt' => 9,
        'formatter' => function($d, $row) {
            // $d is the value of 'id' (player's id)
            $viewUrl = BASE_URL . 'view_player.php?id=' . $d; // <-- Create URL
            return '<a href="' . htmlspecialchars($viewUrl) . '" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye"></i></a> ' . // <-- Use <a> tag
                   '<button class="btn btn-sm btn-warning" title="Edit" onclick="editPlayer('.$d.')"><i class="bi bi-pencil"></i></button> ' . // Keep others as buttons for now
                   '<button class="btn btn-sm btn-danger" title="Delete" onclick="deletePlayer('.$d.')"><i class="bi bi-trash"></i></button>';
        },
        'searchable' => false, // Make actions column non-searchable
        'orderable' => false  // Make actions column non-orderable
    ]
];

// --- Build SQL Query Components ---
$limit = '';
$offset = '';
$order = '';
$where = '';

// --- Pagination ---
if (isset($_POST['start']) && $_POST['length'] != -1) {
    $offset = intval($_POST['start']);
    $limit = intval($_POST['length']);
}

// --- Ordering ---
if (isset($_POST['order']) && count($_POST['order'])) {
    $orderBy = [];
    $dtColumns = array_column($columns, 'dt'); // Get an array of just the 'dt' values

    foreach ($_POST['order'] as $orderItem) {
        $columnIndex = intval($orderItem['column']);
        $columnDir = $orderItem['dir'] === 'asc' ? 'ASC' : 'DESC'; // Sanitize direction

        // Find the corresponding database column definition
        $columnDef = null;
        foreach($columns as $col) {
            if ($col['dt'] == $columnIndex) {
                $columnDef = $col;
                break;
            }
        }

        // Check if the column is orderable and exists
        if ($columnDef && isset($columnDef['db']) && (!isset($columnDef['orderable']) || $columnDef['orderable'] !== false)) {
            $dbColumn = $columnDef['db'];
            // Basic protection against invalid column names (though relying on the $columns array is better)
            // You might want a stricter allow-list check if db names are complex
            if (preg_match('/^[a-zA-Z0-9_]+$/', $dbColumn)) {
                 $orderBy[] = "`" . $dbColumn . "` " . $columnDir;
            }
        }
    }

    if (count($orderBy)) {
        $order = 'ORDER BY ' . implode(', ', $orderBy);
    }
}

// --- Filtering (Global Search) ---
$globalWhere = [];
if (isset($_POST['search']) && $_POST['search']['value'] != '') {
    $searchValue = '%' . $conn->real_escape_string($_POST['search']['value']) . '%'; // Escape and add wildcards

    foreach ($columns as $columnDef) {
        // Check if the column is searchable and has a db counterpart
         if (isset($columnDef['db']) && (!isset($columnDef['searchable']) || $columnDef['searchable'] !== false)) {
            $dbColumn = $columnDef['db'];
             if (preg_match('/^[a-zA-Z0-9_]+$/', $dbColumn)) { // Basic column name validation
                 $globalWhere[] = "`" . $dbColumn . "` LIKE '" . $searchValue . "'";
             }
        }
    }
}

// --- Filtering (Column Specific Search) ---
$columnWhere = [];
if (isset($_POST['columns'])) {
    foreach ($_POST['columns'] as $columnIndex => $columnData) {
        // Find the corresponding column definition by 'dt' index
         $columnDef = null;
         foreach($columns as $col) {
            if ($col['dt'] == $columnIndex) {
                $columnDef = $col;
                break;
            }
         }

        // Check if searchable, has a value, and has a db counterpart
        if (
            $columnDef &&
            isset($columnDef['db']) &&
            (!isset($columnDef['searchable']) || $columnDef['searchable'] !== false) &&
            isset($columnData['search']) && $columnData['search']['value'] != ''
        ) {
            $colSearchValue = '%' . $conn->real_escape_string($columnData['search']['value']) . '%';
            $dbColumn = $columnDef['db'];
             if (preg_match('/^[a-zA-Z0-9_]+$/', $dbColumn)) { // Basic column name validation
                 $columnWhere[] = "`" . $dbColumn . "` LIKE '" . $colSearchValue . "'";
             }
        }
    }
}

// --- Combine WHERE clauses ---
$whereClauses = [];
if (!empty($globalWhere)) {
    $whereClauses[] = '(' . implode(' OR ', $globalWhere) . ')';
}
if (!empty($columnWhere)) {
    $whereClauses[] = '(' . implode(' AND ', $columnWhere) . ')'; // Use AND for column filters
}

if (!empty($whereClauses)) {
    $where = 'WHERE ' . implode(' AND ', $whereClauses);
}


// --- Build the main query ---
$dbColumns = array_map(function($col) { return "`" . $col['db'] . "`"; }, array_filter($columns, function($col){ return isset($col['db']); }));
$sql = "SELECT " . implode(', ', $dbColumns) . " FROM `$table` $where $order";

// Add limit and offset for pagination
if ($limit !== '' && $offset !== '') {
    $sql .= " LIMIT " . $limit . " OFFSET " . $offset;
} elseif ($limit !== '') {
    // Handle cases where only limit is set (e.g., length = -1 but start=0)
     $sql .= " LIMIT " . $limit;
}


// --- Execute main query ---
$mainResult = $conn->query($sql);
$data = [];
if ($mainResult) {
    while ($row = $mainResult->fetch_assoc()) {
        $rowData = [];
        foreach ($columns as $columnDef) {
            $colIndex = $columnDef['dt'];
            $colDb = $columnDef['db'] ?? null; // DB column name
            $value = $colDb ? ($row[$colDb] ?? null) : null;

            // Apply formatter if defined
            if (isset($columnDef['formatter'])) {
                 $rowData[$colIndex] = $columnDef['formatter']($value, $row);
            } else {
                 $rowData[$colIndex] = htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); // Basic XSS protection
            }
        }
         // Ensure the array is indexed numerically for DataTables
        ksort($rowData); // Sort by key (dt index)
        $data[] = array_values($rowData); // Convert to numerically indexed array
    }
    $mainResult->free();
} else {
    // --- Handle SQL Error ---
    error_log("DataTables Server-Side SQL Error: " . $conn->error . " | Query: " . $sql);
    // Return an error structure that DataTables can understand
    echo json_encode([
        "draw"            => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal"    => 0,
        "recordsFiltered" => 0,
        "data"            => [],
        "error"           => "Could not retrieve data. Check server logs." // User-friendly error
    ]);
    $conn->close();
    exit();
}


// --- Get total records count (before filtering) ---
$totalResult = $conn->query("SELECT COUNT(`{$primaryKey}`) FROM `$table`");
$recordsTotal = $totalResult ? $totalResult->fetch_row()[0] : 0;
if ($totalResult) $totalResult->free();


// --- Get filtered records count (with filtering, without pagination) ---
$filteredSql = "SELECT COUNT(`{$primaryKey}`) FROM `$table` $where";
$filteredResult = $conn->query($filteredSql);
$recordsFiltered = $filteredResult ? $filteredResult->fetch_row()[0] : 0;
if ($filteredResult) $filteredResult->free();


// --- Construct JSON Response ---
$output = [
    "draw"            => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    "recordsTotal"    => intval($recordsTotal),
    "recordsFiltered" => intval($recordsFiltered),
    "data"            => $data,
    // "sql_debug"       => $sql // Optional: for debugging the final query
];

// --- Send Response ---
header('Content-Type: application/json');
echo json_encode($output);

// --- Close connection (already done in footer.php if included, but good practice here too) ---
if (isset($conn)) {
    $conn->close();
}
exit(); // Important to prevent any extra output

// --- Helper function stubs (if not using config.php's isLoggedIn) ---
/*
function isLoggedIn() {
    if (session_status() == PHP_SESSION_NONE) session_start();
    return isset($_SESSION['user_id']);
}
*/

// --- Dummy function placeholders for actions (replace with actual logic/modals) ---
?>
<script>
    // These functions are called by the buttons generated in the 'formatter'
    function viewPlayer(id) {
        alert('View player ID: ' + id);
        // Implement view logic (e.g., open modal with player details via AJAX)
    }
    function editPlayer(id) {
        alert('Edit player ID: ' + id);
        // Implement edit logic (e.g., redirect to edit page or open modal)
    }
    function deletePlayer(id) {
        if (confirm('Are you sure you want to delete player ID: ' + id + '?')) {
            alert('Deleting player ID: ' + id);
            // Implement delete logic (e.g., send AJAX request to delete)
            // Remember to redraw the table afterwards: $('#playersTable').DataTable().ajax.reload();
        }
    }
</script>
<?php // The script tag above won't actually run here, it's just to show the JS functions expected by the action buttons ?>