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

    // Check if tables exist
    $tables = ['approved_request', 'declined_request'];
    $tableStatus = [];
    
    foreach ($tables as $table) {
        $tableCheck = $conn->query("SHOW TABLES LIKE '$table'");
        $exists = $tableCheck->num_rows > 0;
        
        $tableStatus[$table] = [
            'exists' => $exists
        ];
        
        if ($exists) {
            // Get table structure
            $describeTable = $conn->query("DESCRIBE $table");
            $structure = [];
            while ($row = $describeTable->fetch_assoc()) {
                $structure[] = $row;
            }
            $tableStatus[$table]['structure'] = $structure;
            
            // Count records
            $countQuery = $conn->query("SELECT COUNT(*) as count FROM $table");
            $tableStatus[$table]['count'] = $countQuery->fetch_assoc()['count'];
            
            // Get sample records
            if ($tableStatus[$table]['count'] > 0) {
                $sampleQuery = $conn->query("SELECT * FROM $table LIMIT 3");
                $samples = [];
                while ($row = $sampleQuery->fetch_assoc()) {
                    $samples[] = $row;
                }
                $tableStatus[$table]['samples'] = $samples;
            }
        }
    }

    // Return results
    echo json_encode([
        "success" => true,
        "tables" => $tableStatus
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>