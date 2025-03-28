<?php
require_once __DIR__ . '/config/config.php'; // Adjust path as needed

// --- Optional Security/Session Check ---
/*
if (!isLoggedIn()) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}
*/

// --- Database Table ---
$table = 'games'; // Main table
$primaryKey = 'g.id'; // Use alias because of join

// --- Columns Configuration ---
// Includes columns from both 'games' (aliased as 'g') and 'players' (aliased as 'p')
$columns = [
    ['db' => 'g.id',          'dt' => 0, 'alias' => 'id'],
    ['db' => 'g.gameID',      'dt' => 1, 'alias' => 'gameID'],
    ['db' => 'p.fullName',    'dt' => 2, 'alias' => 'playerName'], // Joined column
    ['db' => 'g.gameType',    'dt' => 3, 'alias' => 'gameType'],
    ['db' => 'g.gameSkillMode','dt' => 4, 'alias' => 'gameSkillMode'],
    [
        'db' => 'g.gameOutcome',
        'dt' => 5,
        'alias' => 'gameOutcome',
        'formatter' => function($d, $row) {
            $badge = 'secondary'; // Default
            if ($d == 'Win') $badge = 'success';
            elseif ($d == 'Loss') $badge = 'danger';
            elseif ($d == 'Draw') $badge = 'info';
            elseif ($d == 'Pending') $badge = 'warning';
            elseif ($d == 'Cancelled') $badge = 'dark';
            return '<span class="badge bg-' . $badge . '">' . htmlspecialchars($d) . '</span>';
        }
    ],
    [
        'db' => 'g.balance',
        'dt' => 6,
        'alias' => 'balance',
        'formatter' => function($d, $row) {
            if ($d === null) return 'N/A';
            $amount = floatval($d);
            $class = $amount >= 0 ? 'text-success' : 'text-danger';
            // Basic currency formatting (adjust as needed)
            return '<span class="' . $class . '">' . ($amount >= 0 ? '+' : '') . number_format($amount, 2) . '</span>';
        }
    ],
    [
        'db' => 'g.gameStartTime',
        'dt' => 7,
        'alias' => 'gameStartTime',
        'formatter' => function($d, $row) {
            return $d ? date('Y-m-d H:i:s', strtotime($d)) : 'N/A'; // Format date/time
        }
    ],
    [
        'db' => 'g.gameEndTime',
        'dt' => 8,
        'alias' => 'gameEndTime',
        'formatter' => function($d, $row) {
            return $d ? date('Y-m-d H:i:s', strtotime($d)) : 'N/A'; // Format date/time
        }
    ],
    [
        'db' => 'g.status',
        'dt' => 9,
        'alias' => 'status',
        'formatter' => function($d, $row) {
             $badge = 'secondary'; // Default
            if ($d == 'Finished') $badge = 'success';
            elseif ($d == 'InProgress') $badge = 'primary';
            elseif ($d == 'Aborted') $badge = 'warning';
            elseif ($d == 'Started') $badge = 'info';
             return '<span class="badge bg-' . $badge . '">' . htmlspecialchars($d) . '</span>';
        }
    ],
    [ // Actions column (read-only example)
        'db' => 'g.id', // Use game ID for actions
        'dt' => 10,
        'alias' => 'actions',
        'formatter' => function($d, $row) {
            // $d is the value of 'g.id' here
            return '<button class="btn btn-sm btn-info" title="View Details" onclick="viewGameDetails('.$d.')"><i class="bi bi-eye"></i></button>';
                   // Add other actions like 'Replay', 'Analyze' later if needed
        },
        'searchable' => false,
        'orderable' => false
    ]
];

// --- SQL Query Building ---
$limit = '';
$offset = '';
$order = '';
$where = '';
$join = ' LEFT JOIN players AS p ON g.playerID = p.id '; // Define the JOIN clause

// Pagination
if (isset($_POST['start']) && $_POST['length'] != -1) {
    $offset = intval($_POST['start']);
    $limit = intval($_POST['length']);
}

// Ordering
if (isset($_POST['order']) && count($_POST['order'])) {
    $orderBy = [];
    foreach ($_POST['order'] as $orderItem) {
        $columnIndex = intval($orderItem['column']);
        $columnDef = null;
        foreach($columns as $col) { if ($col['dt'] == $columnIndex) { $columnDef = $col; break; } }

        if ($columnDef && isset($columnDef['db']) && (!isset($columnDef['orderable']) || $columnDef['orderable'] !== false)) {
            $dbColumn = $columnDef['db']; // This now includes alias e.g., 'p.fullName'
             // Basic validation (allow alias.column or just column)
            if (preg_match('/^[a-zA-Z0-9_]+\.?[a-zA-Z0-9_]+$/', $dbColumn)) {
                $columnDir = $orderItem['dir'] === 'asc' ? 'ASC' : 'DESC';
                $orderBy[] = $dbColumn . " " . $columnDir; // Use db name directly (with alias)
            }
        }
    }
    if (count($orderBy)) { $order = 'ORDER BY ' . implode(', ', $orderBy); }
} else {
     // Default order if none specified by DataTables
     $order = "ORDER BY g.gameStartTime DESC";
}


// Filtering (Global and Column)
$globalWhere = [];
$columnWhere = [];

// Global search
if (isset($_POST['search']) && $_POST['search']['value'] != '') {
    $searchValue = '%' . $conn->real_escape_string($_POST['search']['value']) . '%';
    foreach ($columns as $columnDef) {
         if (isset($columnDef['db']) && (!isset($columnDef['searchable']) || $columnDef['searchable'] !== false)) {
             $dbColumn = $columnDef['db']; // includes alias
             if (preg_match('/^[a-zA-Z0-9_]+\.?[a-zA-Z0-9_]+$/', $dbColumn)) {
                $globalWhere[] = $dbColumn . " LIKE '" . $searchValue . "'";
             }
         }
    }
}

// Column Search
if (isset($_POST['columns'])) {
    foreach ($_POST['columns'] as $columnIndex => $columnData) {
        $columnDef = null;
        foreach($columns as $col) { if ($col['dt'] == $columnIndex) { $columnDef = $col; break; } }

        if ( $columnDef && isset($columnDef['db']) && (!isset($columnDef['searchable']) || $columnDef['searchable'] !== false) && isset($columnData['search']) && $columnData['search']['value'] != '' ) {
            $colSearchValue = '%' . $conn->real_escape_string($columnData['search']['value']) . '%';
            $dbColumn = $columnDef['db']; // includes alias
            if (preg_match('/^[a-zA-Z0-9_]+\.?[a-zA-Z0-9_]+$/', $dbColumn)) {
                $columnWhere[] = $dbColumn . " LIKE '" . $colSearchValue . "'";
            }
        }
    }
}

// Combine WHERE clauses
$whereClauses = [];
if (!empty($globalWhere)) { $whereClauses[] = '(' . implode(' OR ', $globalWhere) . ')'; }
if (!empty($columnWhere)) { $whereClauses[] = '(' . implode(' AND ', $columnWhere) . ')'; }
if (!empty($whereClauses)) { $where = 'WHERE ' . implode(' AND ', $whereClauses); }

// --- Construct and Execute Queries ---

// Main Data Query
$selectColumns = [];
foreach ($columns as $col) {
    if (isset($col['db'])) {
        $selectColumns[] = $col['db'] . (isset($col['alias']) ? ' AS ' . $col['alias'] : '');
    }
}
$sql = "SELECT " . implode(', ', $selectColumns) .
       " FROM `$table` AS g " .
       $join . $where . $order;
if ($limit !== '') { $sql .= " LIMIT " . $limit . ($offset !== '' ? " OFFSET " . $offset : ''); }

$mainResult = $conn->query($sql);
$data = [];
$errorMsg = null;

if ($mainResult) {
    while ($row = $mainResult->fetch_assoc()) {
        $rowData = [];
        foreach ($columns as $columnDef) {
            $colAlias = $columnDef['alias'] ?? null; // Use alias if defined
            $value = $colAlias ? ($row[$colAlias] ?? null) : null;

            if (isset($columnDef['formatter'])) {
                 $rowData[$columnDef['dt']] = $columnDef['formatter']($value, $row);
            } elseif ($colAlias !== null) { // Ensure we have an alias to fetch
                 $rowData[$columnDef['dt']] = htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
            } else {
                 $rowData[$columnDef['dt']] = ''; // Should not happen if alias is set for all db cols
            }
        }
        ksort($rowData);
        $data[] = array_values($rowData);
    }
    $mainResult->free();
} else {
    $errorMsg = "SQL Error: " . $conn->error . " | Query: " . $sql;
    error_log("DataTables Server-Side Error (Games): " . $errorMsg);
}


// Total Records Count (without filter, with join)
$totalResult = $conn->query("SELECT COUNT(g.id) FROM `$table` AS g " . $join);
$recordsTotal = $totalResult ? $totalResult->fetch_row()[0] : 0;
if ($totalResult) $totalResult->free();

// Filtered Records Count (with filter and join)
$filteredSql = "SELECT COUNT(g.id) FROM `$table` AS g " . $join . $where;
$filteredResult = $conn->query($filteredSql);
$recordsFiltered = $filteredResult ? $filteredResult->fetch_row()[0] : 0;
if ($filteredResult) $filteredResult->free();


// --- JSON Response ---
$output = [
    "draw"            => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    "recordsTotal"    => intval($recordsTotal),
    "recordsFiltered" => intval($recordsFiltered),
    "data"            => $data,
];

// Add error message to output if query failed
if ($errorMsg !== null) {
    $output['error'] = "Could not retrieve game data. Check server logs.";
}

header('Content-Type: application/json');
echo json_encode($output);

if (isset($conn)) { $conn->close(); }
exit();
?>