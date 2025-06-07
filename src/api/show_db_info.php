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
                $columns[] = $column;
            }
            
            // Get sample data (first 5 rows)
            $dataResult = $conn->query("SELECT * FROM $table LIMIT 5");
            $data = [];
            while ($row = $dataResult->fetch_assoc()) {
                $data[] = $row;
            }
            
            $tableInfo[$table] = [
                "exists" => true,
                "columns" => $columns,
                "sample_data" => $data,
                "row_count" => $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count']
            ];
        } else {
            $tableInfo[$table] = [
                "exists" => false
            ];
        }
    }

    // Return database info
    echo json_encode([
        "success" => true,
        "database" => $dbname,
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