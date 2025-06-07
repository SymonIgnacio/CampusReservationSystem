<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include CORS fix
require_once 'cors_fix.php';

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

    // Get all equipment
    $sql = "SELECT * FROM equipment ORDER BY name ASC";
    
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $equipment = [];
    while ($row = $result->fetch_assoc()) {
        $equipment[] = $row;
    }

    // Return results
    echo json_encode([
        "success" => true,
        "equipment" => $equipment
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in equipment.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}