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

    // Count approved requests
    $approvedSql = "SELECT COUNT(*) as count FROM approved_request";
    $approvedResult = $conn->query($approvedSql);
    
    if (!$approvedResult) {
        throw new Exception("Query failed for approved count: " . $conn->error);
    }
    
    $approvedCount = $approvedResult->fetch_assoc()['count'];

    // Count declined requests
    $declinedSql = "SELECT COUNT(*) as count FROM declined_request";
    $declinedResult = $conn->query($declinedSql);
    
    if (!$declinedResult) {
        throw new Exception("Query failed for declined count: " . $conn->error);
    }
    
    $declinedCount = $declinedResult->fetch_assoc()['count'];

    // Count pending requests
    $pendingSql = "SELECT COUNT(*) as count FROM request WHERE status = 'pending'";
    $pendingResult = $conn->query($pendingSql);
    
    if (!$pendingResult) {
        throw new Exception("Query failed for pending count: " . $conn->error);
    }
    
    $pendingCount = $pendingResult->fetch_assoc()['count'];

    // Return results
    echo json_encode([
        "success" => true,
        "counts" => [
            "approved" => $approvedCount,
            "declined" => $declinedCount,
            "pending" => $pendingCount,
            "total" => $approvedCount + $declinedCount + $pendingCount
        ]
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in get_transactions_count.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}