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

    // Get only GSO pending requests (admin/GSO should not see VPO pending)
    $sql = "SELECT * FROM request WHERE status = 'pending_gso' OR status = 'pending' OR status = '' OR status IS NULL ORDER BY date_created DESC";
    
    error_log("Admin requests query: " . $sql);
    
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }

    // Return results
    echo json_encode([
        "success" => true,
        "requests" => $requests
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in get_requests.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}