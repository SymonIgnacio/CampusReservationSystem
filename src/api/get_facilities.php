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

    // Get all facilities with all columns
    $sql = "SELECT id, venue, campus, capacity, description FROM facilities ORDER BY venue ASC";
    
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $facilities = [];
    while ($row = $result->fetch_assoc()) {
        $facilities[] = [
            'id' => $row['id'],
            'resource_id' => $row['id'],  // For compatibility with adminEvents.jsx
            'venue' => $row['venue'],
            'name' => $row['venue'],      // For compatibility with adminEvents.jsx
            'campus' => $row['campus'],
            'capacity' => $row['capacity'],
            'description' => $row['description']
        ];
    }

    // Return results
    echo json_encode([
        "success" => true,
        "facilities" => $facilities
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in get_facilities.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}