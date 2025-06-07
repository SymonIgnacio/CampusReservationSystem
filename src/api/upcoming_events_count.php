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

    // Count upcoming events from approved_request table
    $sql = "SELECT COUNT(*) as upcoming_count FROM approved_request WHERE date_need_from >= CURDATE()";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $row = $result->fetch_assoc();
    $upcomingCount = $row['upcoming_count'];

    // Return results
    echo json_encode([
        "success" => true,
        "upcoming_count" => $upcomingCount
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in upcoming_events_count.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}