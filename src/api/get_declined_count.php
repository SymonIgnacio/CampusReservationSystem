<?php
// Disable error display in response to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log errors to file instead
ini_set('log_errors', 1);
ini_set('error_log', 'c:/xampp/htdocs/CampusReservationSystem/php_errors.log');

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

    // Count declined requests from the declined_request table
    $query = "SELECT COUNT(*) as declined_count FROM declined_request";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Error counting declined requests: " . $conn->error);
    }

    $row = $result->fetch_assoc();
    $declinedCount = $row['declined_count'];

    echo json_encode([
        "success" => true,
        "declined_count" => $declinedCount
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in get_declined_count.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}