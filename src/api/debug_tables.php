<?php
// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]));
}

// Get table structure
$tables = [];

// Check approved_request table
$result = $conn->query("SHOW TABLES LIKE 'approved_request'");
if ($result->num_rows > 0) {
    $columns = [];
    $columnsResult = $conn->query("SHOW COLUMNS FROM approved_request");
    while ($column = $columnsResult->fetch_assoc()) {
        $columns[] = $column;
    }
    
    // Get sample data
    $dataResult = $conn->query("SELECT * FROM approved_request LIMIT 3");
    $data = [];
    if ($dataResult) {
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    $tables['approved_request'] = [
        'exists' => true,
        'columns' => $columns,
        'sample_data' => $data,
        'count' => $conn->query("SELECT COUNT(*) as count FROM approved_request")->fetch_assoc()['count']
    ];
} else {
    $tables['approved_request'] = [
        'exists' => false
    ];
}

// Check reservations table for comparison
$result = $conn->query("SHOW TABLES LIKE 'reservations'");
if ($result->num_rows > 0) {
    $columns = [];
    $columnsResult = $conn->query("SHOW COLUMNS FROM reservations");
    while ($column = $columnsResult->fetch_assoc()) {
        $columns[] = $column;
    }
    
    $tables['reservations'] = [
        'exists' => true,
        'columns' => $columns,
        'count' => $conn->query("SELECT COUNT(*) as count FROM reservations")->fetch_assoc()['count']
    ];
} else {
    $tables['reservations'] = [
        'exists' => false
    ];
}

echo json_encode([
    "success" => true,
    "tables" => $tables
]);

$conn->close();
?>