<?php
// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {
    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get table structure
    $tables = ["approved_request", "declined_request"];
    $tableInfo = [];

    foreach ($tables as $table) {
        // Check if table exists
        $tableExists = $conn->query("SHOW TABLES LIKE '$table'")->num_rows > 0;
        
        if ($tableExists) {
            // Get columns
            $columnsResult = $conn->query("SHOW COLUMNS FROM $table");
            $columns = [];
            while ($column = $columnsResult->fetch_assoc()) {
                $columns[] = $column['Field'];
            }
            
            $tableInfo[$table] = $columns;
        } else {
            $tableInfo[$table] = [];
        }
    }

    // Return database structure
    echo json_encode([
        "success" => true,
        "tables" => $tableInfo
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>