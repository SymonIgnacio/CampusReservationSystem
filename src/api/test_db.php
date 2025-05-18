<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Direct CORS headers first
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
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

    // Check if database exists
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    
    if ($result) {
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
    }

    // Return results
    echo json_encode([
        "success" => true,
        "message" => "Database connection successful",
        "tables" => $tables,
        "db_name" => $dbname
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in test_db.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>