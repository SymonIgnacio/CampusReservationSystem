<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include CORS fix first to handle all CORS headers properly
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

    // Get current date and time
    $currentDateTime = date('Y-m-d H:i:s');

    // Get approved requests
    $sql = "SELECT 
        CONCAT('A', id) as transaction_id,
        reference_number,
        request_by,
        department_organization,
        activity as event_name,
        venue,
        date_need_from,
        date_need_until,
        start_time,
        end_time,
        'approved' as display_status,
        approved_by as processed_by,
        approved_at as transaction_date
    FROM approved_request
    UNION
    SELECT 
        CONCAT('D', id) as transaction_id,
        reference_number,
        request_by,
        department_organization,
        activity as event_name,
        venue,
        date_need_from,
        date_need_until,
        start_time,
        end_time,
        'declined' as display_status,
        rejected_by as processed_by,
        rejected_at as transaction_date
    FROM declined_request
    ORDER BY transaction_date DESC";
    
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }

    // Return results
    echo json_encode([
        "success" => true,
        "transactions" => $transactions
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in transactions.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}