<?php
// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
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

    // Check if approved_request table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'approved_request'");
    $tableExists = $tableCheck->num_rows > 0;
    
    // Get table structure if it exists
    $tableStructure = [];
    if ($tableExists) {
        $describeTable = $conn->query("DESCRIBE approved_request");
        while ($row = $describeTable->fetch_assoc()) {
            $tableStructure[] = $row;
        }
    }
    
    // Count records in the table
    $recordCount = 0;
    if ($tableExists) {
        $countQuery = $conn->query("SELECT COUNT(*) as count FROM approved_request");
        $recordCount = $countQuery->fetch_assoc()['count'];
    }
    
    // Get sample records if any exist
    $sampleRecords = [];
    if ($tableExists && $recordCount > 0) {
        $sampleQuery = $conn->query("SELECT * FROM approved_request LIMIT 3");
        while ($row = $sampleQuery->fetch_assoc()) {
            $sampleRecords[] = $row;
        }
    }
    
    // Return debug information
    echo json_encode([
        "success" => true,
        "table_exists" => $tableExists,
        "table_structure" => $tableStructure,
        "record_count" => $recordCount,
        "sample_records" => $sampleRecords
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>