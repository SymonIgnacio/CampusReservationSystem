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

    // Get approved requests
    $approvedSql = "SELECT 
                    'approved' as status_type,
                    id, 
                    reference_number, 
                    activity, 
                    purpose, 
                    date_need_from, 
                    date_need_until, 
                    time_need_from, 
                    time_need_until, 
                    status, 
                    approved_by as handled_by, 
                    approved_at as transaction_date, 
                    department, 
                    venue_name, 
                    venue_location, 
                    requestor_name
                FROM approved_request";
    
    $approvedResult = $conn->query($approvedSql);
    
    if (!$approvedResult) {
        throw new Exception("Query failed for approved requests: " . $conn->error);
    }
    
    $approvedTransactions = [];
    while ($row = $approvedResult->fetch_assoc()) {
        $approvedTransactions[] = $row;
    }

    // Get declined requests
    $declinedSql = "SELECT 
                    'declined' as status_type,
                    id, 
                    reference_number, 
                    activity, 
                    purpose, 
                    date_need_from, 
                    date_need_until, 
                    time_need_from, 
                    time_need_until, 
                    status, 
                    declined_by as handled_by, 
                    declined_at as transaction_date, 
                    department, 
                    venue_name, 
                    venue_location, 
                    requestor_name
                FROM declined_request";
    
    $declinedResult = $conn->query($declinedSql);
    
    if (!$declinedResult) {
        throw new Exception("Query failed for declined requests: " . $conn->error);
    }
    
    $declinedTransactions = [];
    while ($row = $declinedResult->fetch_assoc()) {
        $declinedTransactions[] = $row;
    }

    // Combine both results
    $allTransactions = array_merge($approvedTransactions, $declinedTransactions);
    
    // Sort by transaction date (newest first)
    usort($allTransactions, function($a, $b) {
        return strtotime($b['transaction_date']) - strtotime($a['transaction_date']);
    });

    // Return results
    echo json_encode([
        "success" => true,
        "transactions" => $allTransactions
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>