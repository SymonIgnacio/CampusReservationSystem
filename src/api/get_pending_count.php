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

    // Count only GSO pending requests (admin/GSO should not see VPO pending count)
    $query = "SELECT COUNT(*) as pending_count FROM request WHERE status = 'pending_gso' OR status = 'pending' OR status = '' OR status IS NULL";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Error counting pending requests: " . $conn->error);
    }

    $row = $result->fetch_assoc();
    $pendingCount = $row['pending_count'];

    // Also get the actual pending requests for debugging
    $debugQuery = "SELECT id, reference_number, activity, status FROM request WHERE status = 'pending_gso' OR status = 'pending' OR status = '' OR status IS NULL";
    $debugResult = $conn->query($debugQuery);
    $pendingRequests = [];
    while ($debugRow = $debugResult->fetch_assoc()) {
        $pendingRequests[] = $debugRow;
    }

    echo json_encode([
        "success" => true,
        "pending_count" => $pendingCount,
        "debug_requests" => $pendingRequests
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in get_pending_count.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}