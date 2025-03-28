<?php
require_once '../config/db.php'; // Go up one directory
require_login(); // Ensure user is logged in

// DB table to use
$table = 'players';

// Table's primary key
$primaryKey = 'id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array('db' => 'id', 'dt' => 'id'),
    array('db' => 'fullName', 'dt' => 'fullName'),
    array('db' => 'gender', 'dt' => 'gender'),
    array('db' => 'age', 'dt' => 'age'),
    array('db' => 'phone', 'dt' => 'phone'),
    array('db' => 'email', 'dt' => 'email'),
    array('db' => 'loginStatus', 'dt' => 'loginStatus'),
    array('db' => 'gameStatus', 'dt' => 'gameStatus'),
    array('db' => 'status', 'dt' => 'status'),
    // Add 'createdAt' and 'updatedAt' if needed, but maybe not directly displayed
);

// Include Server-Side Processing class
// IMPORTANT: Download ssp.class.php from DataTables GitHub:
// https://github.com/DataTables/DataTables/blob/master/examples/server_side/scripts/ssp.class.php
// Save it in the 'actions' folder or a 'lib' folder and adjust the path.
require 'ssp.class.php'; // Make sure this path is correct

// SQL server connection information
$sql_details = array(
    'user' => DB_USER,
    'pass' => DB_PASS,
    'db'   => DB_NAME,
    'host' => DB_HOST
);

// Base WHERE condition (e.g., to exclude soft-deleted records)
$where = "status != 'Deleted'";

// Output data as json format required by DataTables
echo json_encode(
    SSP::complex($_GET, $sql_details, $table, $primaryKey, $columns, $where)
    // Use SSP::simple if you don't need complex WHERE clauses initially
    // SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);

?>