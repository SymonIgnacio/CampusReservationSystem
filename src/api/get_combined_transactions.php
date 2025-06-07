<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

try {
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
                    approved_by, 
                    approved_at, 
                    department, 
                    venue_name, 
                    venue_location, 
                    requestor_name
                FROM approved_request";
    
    $approvedResult = $conn->query($approvedSql);
    $approvedTransactions = [];
    
    if ($approvedResult) {
        while ($row = $approvedResult->fetch_assoc()) {
            $approvedTransactions[] = $row;
        }
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
                    declined_by, 
                    declined_at, 
                    department, 
                    venue_name, 
                    venue_location, 
                    requestor_name
                FROM declined_request";
    
    $declinedResult = $conn->query($declinedSql);
    $declinedTransactions = [];
    
    if ($declinedResult) {
        while ($row = $declinedResult->fetch_assoc()) {
            $declinedTransactions[] = $row;
        }
    }

    // Combine both results
    $allTransactions = array_merge($approvedTransactions, $declinedTransactions);
    
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