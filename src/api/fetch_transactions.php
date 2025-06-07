<?php
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
                    'approved' as type,
                    id, 
                    reference_number, 
                    activity, 
                    purpose, 
                    date_need_from, 
                    date_need_until, 
                    time_need_from, 
                    time_need_until, 
                    department, 
                    venue_name, 
                    venue_location, 
                    requestor_name, 
                    approved_by, 
                    approved_at
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
                    'declined' as type,
                    id, 
                    reference_number, 
                    activity, 
                    purpose, 
                    date_need_from, 
                    date_need_until, 
                    time_need_from, 
                    time_need_until, 
                    department, 
                    venue_name, 
                    venue_location, 
                    requestor_name, 
                    declined_by, 
                    declined_at
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
    
    // Sort by date (newest first)
    usort($allTransactions, function($a, $b) {
        $dateA = isset($a['approved_at']) ? $a['approved_at'] : $a['declined_at'];
        $dateB = isset($b['approved_at']) ? $b['approved_at'] : $b['declined_at'];
        return strtotime($dateB) - strtotime($dateA);
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